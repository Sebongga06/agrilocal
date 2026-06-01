<?php
require_once __DIR__ . '/../../config/Database.php';

class Cart {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getOrCreateCart($userId) {
        $stmt = $this->db->prepare("SELECT * FROM tbl_cart WHERE crt_user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return $cart;
        }

        $stmt = $this->db->prepare("INSERT INTO tbl_cart (crt_user_id) VALUES (?)");
        $stmt->execute([$userId]);

        $cartId = $this->db->lastInsertId();

        $stmt = $this->db->prepare("SELECT * FROM tbl_cart WHERE crt_id = ?");
        $stmt->execute([$cartId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addItem($userId, $productId, $quantity = 1, $specialInstructions = null) {
        $cart = $this->getOrCreateCart($userId);
        $cartId = $cart['crt_id'];

        $stmt = $this->db->prepare("
            SELECT prd_id, prd_price
            FROM tbl_product
            WHERE prd_id = ?
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM tbl_cart_item
            WHERE cit_cart_id = ? AND cit_product_id = ?
            LIMIT 1
        ");
        $stmt->execute([$cartId, $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE tbl_cart_item
                SET cit_quantity = cit_quantity + ?
                WHERE cit_id = ?
            ");
            return $stmt->execute([$quantity, $existing['cit_id']]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO tbl_cart_item (
                cit_cart_id,
                cit_product_id,
                cit_quantity,
                cit_unit_price,
                cit_special_instructions
            ) VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $cartId,
            $productId,
            $quantity,
            $product['prd_price'],
            $specialInstructions
        ]);
    }

    public function getCartItems($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                ci.cit_id,
                ci.cit_cart_id,
                ci.cit_product_id,
                ci.cit_quantity,
                ci.cit_unit_price,
                ci.cit_special_instructions,
                p.prd_id,
                p.prd_name,
                p.prd_unit,
                p.prd_images,
                p.prd_vendor_id,
                v.vnd_id,
                v.vnd_farm_name,
                v.vnd_address
            FROM tbl_cart c
            JOIN tbl_cart_item ci ON c.crt_id = ci.cit_cart_id
            JOIN tbl_product p ON ci.cit_product_id = p.prd_id
            JOIN tbl_vendor v ON p.prd_vendor_id = v.vnd_id
            WHERE c.crt_user_id = ?
            ORDER BY v.vnd_farm_name ASC, ci.cit_id ASC
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $images = json_decode($item['prd_images'] ?? '[]', true);
            $item['image'] = (!empty($images) && is_array($images))
                ? $images[0]
                : 'https://via.placeholder.com/120x120?text=No+Image';

            $item['subtotal'] = $item['cit_quantity'] * $item['cit_unit_price'];
        }

        return $items;
    }

    public function updateItemQuantity($cartItemId, $quantity) {
        $quantity = max(1, (int)$quantity);

        $stmt = $this->db->prepare("
            UPDATE tbl_cart_item
            SET cit_quantity = ?
            WHERE cit_id = ?
        ");
        return $stmt->execute([$quantity, $cartItemId]);
    }

    public function removeItem($cartItemId) {
        $stmt = $this->db->prepare("DELETE FROM tbl_cart_item WHERE cit_id = ?");
        return $stmt->execute([$cartItemId]);
    }

    public function clearCart($userId) {
        $cart = $this->getOrCreateCart($userId);

        $stmt = $this->db->prepare("DELETE FROM tbl_cart_item WHERE cit_cart_id = ?");
        return $stmt->execute([$cart['crt_id']]);
    }

    public function getCartSummary($userId) {
        $items = $this->getCartItems($userId);

        $itemsTotal = 0;
        foreach ($items as $item) {
            $itemsTotal += $item['subtotal'];
        }

        $deliveryCharge = 0.00;
        $handlingCharge = 0.00;
        $grandTotal = $itemsTotal + $deliveryCharge + $handlingCharge;

        return [
            'items_total' => $itemsTotal,
            'delivery_charge' => $deliveryCharge,
            'handling_charge' => $handlingCharge,
            'grand_total' => $grandTotal
        ];
    }
}