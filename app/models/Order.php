<?php
require_once __DIR__ . '/../../config/Database.php';

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    // -------------------------------------------------------
    // CREATE – place order from cart (single vendor)
    // -------------------------------------------------------
    public function placeOrder(array $data): bool|int
    {
        // Ensure payment columns exist (run-once migration guard)
        $this->ensurePaymentColumns();

        $paymentMethod = $data['payment_method'] ?? 'cash';
        // cash → unpaid, ewallet → pending_verification
        $paymentStatus = ($paymentMethod === 'cash') ? 'unpaid' : 'pending_verification';

        $stmt = $this->db->prepare("
            INSERT INTO tbl_order (
                ord_user_id,
                ord_vendor_id,
                ord_status,
                ord_total_amount,
                ord_delivery_method,
                ord_delivery_address,
                ord_pickup_time_slot,
                ord_payment_method,
                ord_payment_status,
                ord_payment_reference,
                ord_payment_proof,
                ord_notes
            ) VALUES (
                :user_id,
                :vendor_id,
                'pending',
                :total_amount,
                :delivery_method,
                :delivery_address,
                :pickup_time_slot,
                :payment_method,
                :payment_status,
                :payment_reference,
                :payment_proof,
                :notes
            )
        ");

        $ok = $stmt->execute([
            ':user_id'           => $data['user_id'],
            ':vendor_id'         => $data['vendor_id'],
            ':total_amount'      => $data['total_amount'],
            ':delivery_method'   => $data['delivery_method'],
            ':delivery_address'  => $data['delivery_address'],
            ':pickup_time_slot'  => $data['pickup_time_slot'],
            ':payment_method'    => $paymentMethod,
            ':payment_status'    => $paymentStatus,
            ':payment_reference' => $data['payment_reference'] ?? null,
            ':payment_proof'     => $data['payment_proof']     ?? null,
            ':notes'             => $data['notes'],
        ]);

        if (!$ok) {
            return false;
        }

        return (int)$this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // MIGRATE – add payment columns if they don't exist yet
    // -------------------------------------------------------
    private function ensurePaymentColumns(): void
    {
        $cols = array_column(
            $this->db->query("SHOW COLUMNS FROM tbl_order")->fetchAll(PDO::FETCH_ASSOC),
            'Field'
        );

        if (!in_array('ord_payment_method', $cols)) {
            $this->db->exec("ALTER TABLE tbl_order ADD COLUMN ord_payment_method VARCHAR(50) NOT NULL DEFAULT 'cash' AFTER ord_payment_status");
        }
        if (!in_array('ord_payment_reference', $cols)) {
            $this->db->exec("ALTER TABLE tbl_order ADD COLUMN ord_payment_reference VARCHAR(100) NULL AFTER ord_payment_method");
        }
        if (!in_array('ord_payment_proof', $cols)) {
            $this->db->exec("ALTER TABLE tbl_order ADD COLUMN ord_payment_proof VARCHAR(255) NULL AFTER ord_payment_reference");
        }
        // Widen payment_status to support new values
        $this->db->exec("ALTER TABLE tbl_order MODIFY COLUMN ord_payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid'");
    }

    // -------------------------------------------------------
    // UPDATE – vendor verifies or rejects e-wallet payment
    // -------------------------------------------------------
    public function updatePaymentStatus(int $orderId, int $vendorId, string $status): bool
    {
        $allowed = ['paid', 'rejected', 'unpaid', 'pending_verification'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE tbl_order
            SET ord_payment_status = :status,
                ord_dateUpdated    = NOW()
            WHERE ord_id       = :order_id
              AND ord_vendor_id = :vendor_id
        ");

        $stmt->execute([
            ':status'    => $status,
            ':order_id'  => $orderId,
            ':vendor_id' => $vendorId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // CREATE – insert order items
    // -------------------------------------------------------
    public function insertOrderItems(int $orderId, array $items): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tbl_order_item (
                oit_order_id,
                oit_product_id,
                oit_quantity,
                oit_unit_price
            ) VALUES (
                :order_id,
                :product_id,
                :quantity,
                :unit_price
            )
        ");

        foreach ($items as $item) {
            $ok = $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product_id'],
                ':quantity' => $item['quantity'],
                ':unit_price' => $item['unit_price'],
            ]);

            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------
    // READ – all orders for a vendor with tab filter
    // -------------------------------------------------------
    public function getByVendor(int $vendorId, string $tab = 'new'): array
    {
        $statusMap = [
            'new'       => ['pending', 'confirmed'],
            'ready'     => ['ready'],
            'completed' => ['picked_up', 'completed'],
            'cancelled' => ['cancelled'],
        ];

        $statuses = $statusMap[$tab] ?? $statusMap['new'];
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $sql = "
            SELECT
                o.*,
                u.user_fname,
                u.user_lname,
                u.user_phone,
                u.user_email,
                COUNT(oi.oit_id) AS item_count
            FROM tbl_order o
            JOIN tbl_user u ON o.ord_user_id = u.user_id
            LEFT JOIN tbl_order_item oi ON oi.oit_order_id = o.ord_id
            WHERE o.ord_vendor_id = ?
              AND o.ord_status IN ($placeholders)
            GROUP BY o.ord_id
            ORDER BY o.ord_dateCreated DESC, o.ord_id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$vendorId], $statuses));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // READ – count orders with pickup time within 24 hours
    // -------------------------------------------------------
    public function getUrgentCount(int $vendorId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS cnt
            FROM tbl_order
            WHERE ord_vendor_id = :vendor_id
              AND ord_status IN ('pending', 'confirmed')
              AND ord_pickup_time_slot IS NOT NULL
              AND ord_pickup_time_slot BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([':vendor_id' => $vendorId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }
    public function getTabCounts(int $vendorId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN ord_status IN ('pending','confirmed') THEN 1 ELSE 0 END) AS new_count,
                SUM(CASE WHEN ord_status = 'ready' THEN 1 ELSE 0 END) AS ready_count,
                SUM(CASE WHEN ord_status IN ('picked_up','completed') THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN ord_status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
            FROM tbl_order
            WHERE ord_vendor_id = :vendor_id
        ");
        $stmt->execute([
            ':vendor_id' => $vendorId,
        ]);

        $counts = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'new_count' => (int)($counts['new_count'] ?? 0),
            'ready_count' => (int)($counts['ready_count'] ?? 0),
            'completed_count' => (int)($counts['completed_count'] ?? 0),
            'cancelled_count' => (int)($counts['cancelled_count'] ?? 0),
        ];
    }

    // -------------------------------------------------------
    // READ – single order with vendor ownership check
    // -------------------------------------------------------
    public function getById(int $orderId, int $vendorId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                o.*,
                u.user_fname,
                u.user_lname,
                u.user_phone,
                u.user_email
            FROM tbl_order o
            JOIN tbl_user u ON o.ord_user_id = u.user_id
            WHERE o.ord_id = :order_id
              AND o.ord_vendor_id = :vendor_id
            LIMIT 1
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':vendor_id' => $vendorId,
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        return $order ?: null;
    }

    // -------------------------------------------------------
    // READ – items for a single order
    // -------------------------------------------------------
    public function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                oi.*,
                p.prd_name,
                p.prd_unit,
                p.prd_images,
                (oi.oit_quantity * oi.oit_unit_price) AS oit_subtotal
            FROM tbl_order_item oi
            JOIN tbl_product p ON oi.oit_product_id = p.prd_id
            WHERE oi.oit_order_id = :order_id
            ORDER BY oi.oit_id ASC
        ");
        $stmt->execute([
            ':order_id' => $orderId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // READ – today's total sales for a vendor
    // -------------------------------------------------------
    public function getTodaySales(int $vendorId): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(ord_total_amount), 0) AS total
            FROM tbl_order
            WHERE ord_vendor_id = :vendor_id
              AND ord_status NOT IN ('cancelled')
              AND DATE(ord_dateCreated) = CURDATE()
        ");
        $stmt->execute([
            ':vendor_id' => $vendorId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (float)($row['total'] ?? 0);
    }

    // -------------------------------------------------------
    // READ – recent orders for dashboard table
    // -------------------------------------------------------
    public function getRecent(int $vendorId, int $limit = 5): array
    {
        $limit = max(1, (int)$limit);

        $stmt = $this->db->prepare("
            SELECT
                o.ord_id,
                o.ord_status,
                o.ord_total_amount,
                o.ord_dateCreated,
                u.user_fname,
                u.user_lname
            FROM tbl_order o
            JOIN tbl_user u ON o.ord_user_id = u.user_id
            WHERE o.ord_vendor_id = :vendor_id
            ORDER BY o.ord_dateCreated DESC, o.ord_id DESC
            LIMIT $limit
        ");
        $stmt->execute([
            ':vendor_id' => $vendorId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // READ – buyer order history
    // -------------------------------------------------------
    public function getByBuyer(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                o.*,
                v.vnd_farm_name,
                COUNT(oi.oit_id) AS item_count
            FROM tbl_order o
            JOIN tbl_vendor v ON o.ord_vendor_id = v.vnd_id
            LEFT JOIN tbl_order_item oi ON oi.oit_order_id = o.ord_id
            WHERE o.ord_user_id = :user_id
            GROUP BY o.ord_id
            ORDER BY o.ord_dateCreated DESC, o.ord_id DESC
        ");
        $stmt->execute([
            ':user_id' => $userId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // UPDATE – change order status (vendor only)
    // -------------------------------------------------------
    public function updateStatus(int $orderId, int $vendorId, string $status): bool
    {
        $allowed = ['pending', 'confirmed', 'ready', 'picked_up', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        // Sync payment status with order status (must match ENUM: pending, paid, refunded)
        $paymentStatus = 'pending';
        if (in_array($status, ['completed', 'picked_up'])) {
            $paymentStatus = 'paid';
        } elseif ($status === 'cancelled') {
            $paymentStatus = 'refunded';
        }

        $stmt = $this->db->prepare("
            UPDATE tbl_order
            SET
                ord_status         = :status,
                ord_payment_status = :payment_status,
                ord_dateUpdated    = NOW()
            WHERE ord_id       = :order_id
              AND ord_vendor_id = :vendor_id
        ");

        $stmt->execute([
            ':status'         => $status,
            ':payment_status' => $paymentStatus,
            ':order_id'       => $orderId,
            ':vendor_id'      => $vendorId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // UPDATE – edit order notes & delivery details
    //          only allowed while status is pending/confirmed
    // -------------------------------------------------------
    public function update(int $orderId, int $vendorId, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_order
            SET
                ord_delivery_method = :delivery_method,
                ord_delivery_address = :delivery_address,
                ord_pickup_time_slot = :pickup_time_slot,
                ord_notes = :notes,
                ord_dateUpdated = NOW()
            WHERE ord_id = :order_id
              AND ord_vendor_id = :vendor_id
              AND ord_status IN ('pending', 'confirmed')
        ");

        $stmt->execute([
            ':delivery_method' => $data['delivery_method'],
            ':delivery_address' => $data['delivery_address'],
            ':pickup_time_slot' => $data['pickup_time_slot'],
            ':notes' => $data['notes'],
            ':order_id' => $orderId,
            ':vendor_id' => $vendorId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // READ – single order for buyer (ownership check)
    // -------------------------------------------------------
    public function getByIdForBuyer(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, v.vnd_farm_name
            FROM tbl_order o
            JOIN tbl_vendor v ON o.ord_vendor_id = v.vnd_id
            WHERE o.ord_id = :order_id
              AND o.ord_user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $orderId, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // -------------------------------------------------------
    // UPDATE – buyer edits delivery details on their own order
    // -------------------------------------------------------
    public function updateByBuyer(int $orderId, int $userId, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tbl_order
            SET
                ord_delivery_method  = :delivery_method,
                ord_delivery_address = :delivery_address,
                ord_pickup_time_slot = :pickup_time_slot,
                ord_notes            = :notes,
                ord_dateUpdated      = NOW()
            WHERE ord_id    = :order_id
              AND ord_user_id = :user_id
              AND ord_status IN ('pending', 'confirmed')
        ");

        $stmt->execute([
            ':delivery_method'  => $data['delivery_method'],
            ':delivery_address' => $data['delivery_address'],
            ':pickup_time_slot' => $data['pickup_time_slot'],
            ':notes'            => $data['notes'],
            ':order_id'         => $orderId,
            ':user_id'          => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // READ – multiple orders by IDs for a specific buyer
    // -------------------------------------------------------
    public function getByIdsForBuyer(array $orderIds, int $userId): array
    {
        if (empty($orderIds)) return [];

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

        $stmt = $this->db->prepare("
            SELECT o.*, v.vnd_farm_name, v.vnd_address,
                   COUNT(oi.oit_id) AS item_count
            FROM tbl_order o
            JOIN tbl_vendor v ON o.ord_vendor_id = v.vnd_id
            LEFT JOIN tbl_order_item oi ON oi.oit_order_id = o.ord_id
            WHERE o.ord_id IN ($placeholders)
              AND o.ord_user_id = ?
            GROUP BY o.ord_id
            ORDER BY o.ord_id ASC
        ");

        $stmt->execute(array_merge(array_map('intval', $orderIds), [$userId]));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // UPDATE – buyer-side status change (cancel or received)
    // -------------------------------------------------------
    public function updateStatusByBuyer(int $orderId, int $userId, string $status): bool
    {
        $allowed = ['cancelled', 'completed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $paymentStatus = $status === 'completed' ? 'paid' : 'refunded';

        $condition = $status === 'cancelled'
            ? "ord_status IN ('pending', 'confirmed')"
            : "ord_status = 'ready'";

        $stmt = $this->db->prepare("
            UPDATE tbl_order
            SET ord_status         = :status,
                ord_payment_status = :payment_status,
                ord_dateUpdated    = NOW()
            WHERE ord_id      = :order_id
              AND ord_user_id = :user_id
              AND {$condition}
        ");

        $stmt->execute([
            ':status'         => $status,
            ':payment_status' => $paymentStatus,
            ':order_id'       => $orderId,
            ':user_id'        => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    // -------------------------------------------------------
    // READ – single order items for buyer (ownership check)
    // -------------------------------------------------------
    public function getItemsByBuyer(int $orderId, int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.prd_name, p.prd_unit,
                   (oi.oit_quantity * oi.oit_unit_price) AS oit_subtotal
            FROM tbl_order_item oi
            JOIN tbl_product p ON oi.oit_product_id = p.prd_id
            JOIN tbl_order o ON o.ord_id = oi.oit_order_id
            WHERE oi.oit_order_id = :order_id
              AND o.ord_user_id = :user_id
            ORDER BY oi.oit_id ASC
        ");
        $stmt->execute([':order_id' => $orderId, ':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // DELETE – permanently remove a cancelled order
    // -------------------------------------------------------
    public function delete(int $orderId, int $vendorId): bool
    {
        $stmt = $this->db->prepare("
            SELECT ord_status
            FROM tbl_order
            WHERE ord_id = :order_id
              AND ord_vendor_id = :vendor_id
            LIMIT 1
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':vendor_id' => $vendorId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || ($row['ord_status'] ?? '') !== 'cancelled') {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM tbl_order_item
            WHERE oit_order_id = :order_id
        ");
        $stmt->execute([
            ':order_id' => $orderId,
        ]);

        $stmt = $this->db->prepare("
            DELETE FROM tbl_order
            WHERE ord_id = :order_id
              AND ord_vendor_id = :vendor_id
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':vendor_id' => $vendorId,
        ]);

        return $stmt->rowCount() > 0;
    }
}