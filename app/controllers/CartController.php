<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../../config/Database.php';

class CartController extends Controller
{
    private function getBuyerId(): int
    {
        if (!isset($_SESSION['user']['user_id'])) {
            die('Please log in first.');
        }

        return (int)$_SESSION['user']['user_id'];
    }

    private function groupItemsByVendor(array $items): array
    {
        $groupedItems = [];

        foreach ($items as $item) {
            $vendorId = (int)$item['vnd_id'];

            if (!isset($groupedItems[$vendorId])) {
                $groupedItems[$vendorId] = [
                    'vendor_id' => $item['vnd_id'],
                    'vendor_name' => $item['vnd_farm_name'],
                    'vendor_address' => $item['vnd_address'],
                    'items' => [],
                    'subtotal' => 0,
                ];
            }

            $groupedItems[$vendorId]['items'][] = $item;
            $groupedItems[$vendorId]['subtotal'] += (float)$item['subtotal'];
        }

        return $groupedItems;
    }

    public function index()
    {
        $userId = $this->getBuyerId();

        $cartModel = new Cart();
        $items = $cartModel->getCartItems($userId);
        $summary = $cartModel->getCartSummary($userId);

        $this->view('buyer/cart', [
            'groupedItems' => $this->groupItemsByVendor($items),
            'summary' => $summary,
        ]);
    }
public function add()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?url=products');
        exit;
    }

    $isAjax = (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );

    try {
        $userId = $this->getBuyerId();

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $specialInstructions = trim($_POST['special_instructions'] ?? '');

        if ($productId <= 0) {
            throw new Exception('Product ID is required.');
        }

        $cartModel = new Cart();
        $saved = $cartModel->addItem(
            $userId,
            $productId,
            $quantity,
            $specialInstructions !== '' ? $specialInstructions : null
        );

        if (!$saved) {
            throw new Exception('Failed to add product to cart.');
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart.'
            ]);
            exit;
        }

        header('Location: index.php?url=products');
        exit;
    } catch (Throwable $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }

        die($e->getMessage());
    }
}
    public function update()
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);

        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

        $newSubtotal = 0;
        $newTotal    = 0;

        if ($cartItemId > 0) {
            $cartModel = new Cart();
            $cartModel->updateItemQuantity($cartItemId, $quantity);

            // Recalculate subtotal for this item
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT cit_unit_price FROM tbl_cart_item WHERE cit_id = ? LIMIT 1");
            $stmt->execute([$cartItemId]);
            $row  = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $newSubtotal = round((float)$row['cit_unit_price'] * $quantity, 2);
            }

            // Recalculate grand total
            $userId  = (int)($_SESSION['user']['user_id'] ?? 0);
            $summary = $cartModel->getCartSummary($userId);
            $newTotal = $summary['grand_total'];
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'     => true,
                'subtotal'    => number_format($newSubtotal, 2),
                'grand_total' => number_format($newTotal, 2),
                'items_total' => number_format($newTotal, 2),
            ]);
            exit;
        }

        header('Location: index.php?url=cart');
        exit;
    }

    public function remove($cartItemId = null)
    {
        $cartItemId = (int)($cartItemId ?? 0);

        if ($cartItemId > 0) {
            $cartModel = new Cart();
            $cartModel->removeItem($cartItemId);
        }

        header('Location: index.php?url=cart');
        exit;
    }

    public function clear()
    {
        $userId = $this->getBuyerId();

        $cartModel = new Cart();
        $cartModel->clearCart($userId);

        header('Location: index.php?url=cart');
        exit;
    }

      public function checkout()
    {
        $userId = $this->getBuyerId();

        $cartModel = new Cart();
        $items = $cartModel->getCartItems($userId);
        $summary = $cartModel->getCartSummary($userId);
        $grouped = $this->groupItemsByVendor($items);

        $summary['delivery_charge'] = 0.00;
        $summary['grand_total'] = $summary['items_total'] + $summary['handling_charge'];

        $this->view('buyer/checkout', [
            'items' => $items,
            'groupedItems' => $grouped,
            'summary' => $summary,
            'error' => null,
            'old' => [
                'delivery_method' => 'pickup',
                'delivery_address' => '',
                'pickup_time_slot' => '',
                'notes' => '',
            ],
        ]);
    }

    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function deliveryFeeForKm(float $km): float
    {
        $chargedKm = max(1, (int)ceil($km));

        if ($chargedKm <= 1) {
            return 18.00;
        }

        return 18.00 + (($chargedKm - 1) * 15.00);
    }

    private function getBuyerAddress(PDO $db, int $userId, string $deliveryAddress = ''): string
    {
        if ($deliveryAddress !== '') {
            return $deliveryAddress . ', Philippines';
        }

        $stmt = $db->prepare("
            SELECT user_address, user_city, user_region
            FROM tbl_user
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $buyer = $stmt->fetch(PDO::FETCH_ASSOC);

        $parts = array_filter([
            $buyer['user_address'] ?? '',
            $buyer['user_city'] ?? '',
            $buyer['user_region'] ?? '',
            'Philippines'
        ]);

        return trim(implode(', ', $parts));
    }

    private function geocode(string $address): ?array
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        $queries = [];

        // 1. Full address as-is
        $queries[] = $address;

        // 2. Drop the first component and try the rest
        if (count($parts) >= 3) {
            $queries[] = implode(', ', array_slice($parts, 1));
        }

        // 3. Last two components only (city + region/province)
        if (count($parts) >= 2) {
            $queries[] = implode(', ', array_slice($parts, -2));
        }

        // 4. Last component alone (region / province)
        if (count($parts) >= 1) {
            $queries[] = end($parts);
        }

        // 5. Structured search: city= + state= (more reliable for Philippine municipalities)
        if (count($parts) >= 3) {
            $filtered = array_values(array_filter($parts, fn($p) => strtolower($p) !== 'philippines'));

            if (count($filtered) >= 2) {
                $city  = $filtered[count($filtered) - 2];
                $state = $filtered[count($filtered) - 1];
                $structured = $this->nominatimStructured($city, $state);
                if ($structured) {
                    return $structured;
                }
            }
        }

        foreach (array_unique($queries) as $query) {
            $result = $this->nominatim($query);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    private function nominatimStructured(string $city, string $state): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=json'
            . '&limit=1'
            . '&countrycodes=ph'
            . '&addressdetails=1'
            . '&city=' . urlencode($city)
            . '&state=' . urlencode($state);

        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 10,
                'ignore_errors' => true,
                'header'        => "User-Agent: AgriLocal/1.0 (agrilocal@example.com)\r\nAccept: application/json\r\n",
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
            return null;
        }

        return [
            'lat' => (float)$data[0]['lat'],
            'lng' => (float)$data[0]['lon'],
        ];
    }

    private function nominatim(string $query): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=json'
            . '&limit=1'
            . '&countrycodes=ph'
            . '&viewbox=116.87%2C4.59%2C126.61%2C21.12'
            . '&bounded=0'
            . '&addressdetails=1'
            . '&q=' . urlencode($query);

        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 10,
                'ignore_errors' => true,
                'header'        => "User-Agent: AgriLocal/1.0 (agrilocal@example.com)\r\nAccept: application/json\r\n",
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            $err = error_get_last();
            error_log('[AgriLocal] Nominatim request failed for "' . $query . '": ' . ($err['message'] ?? 'unknown'));
            return null;
        }

        $data = json_decode($response, true);

        if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
            error_log('[AgriLocal] Nominatim no result for "' . $query . '"');
            return null;
        }

        return [
            'lat' => (float)$data[0]['lat'],
            'lng' => (float)$data[0]['lon'],
        ];
    }

    private function calculateVendorDeliveryFee(PDO $db, int $vendorId, array $buyerCoords): float
    {
        // Ensure lat/lng columns exist before querying them
        static $colsChecked = false;
        if (!$colsChecked) {
            $cols = array_column(
                $db->query("SHOW COLUMNS FROM tbl_vendor")->fetchAll(PDO::FETCH_ASSOC),
                'Field'
            );
            if (!in_array('vnd_lat', $cols)) {
                $db->exec("ALTER TABLE tbl_vendor ADD COLUMN vnd_lat DECIMAL(10,7) NULL DEFAULT NULL");
            }
            if (!in_array('vnd_lng', $cols)) {
                $db->exec("ALTER TABLE tbl_vendor ADD COLUMN vnd_lng DECIMAL(10,7) NULL DEFAULT NULL");
            }
            $colsChecked = true;
        }
        $stmt = $db->prepare("
            SELECT vnd_lat, vnd_lng, vnd_address
            FROM tbl_vendor
            WHERE vnd_id = ?
            LIMIT 1
        ");
        $stmt->execute([$vendorId]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vendor) {
            return 50.00;
        }

        $vendorLat = (float)($vendor['vnd_lat'] ?? 0);
        $vendorLng = (float)($vendor['vnd_lng'] ?? 0);

        if ($vendorLat === 0.0 || $vendorLng === 0.0) {
            $vendorCoords = $this->geocode(($vendor['vnd_address'] ?? '') . ', Philippines');

            if ($vendorCoords) {
                $vendorLat = $vendorCoords['lat'];
                $vendorLng = $vendorCoords['lng'];

                $save = $db->prepare("UPDATE tbl_vendor SET vnd_lat = ?, vnd_lng = ? WHERE vnd_id = ?");
                $save->execute([$vendorLat, $vendorLng, $vendorId]);
            }
        }

        if ($vendorLat === 0.0 || $vendorLng === 0.0) {
            return 50.00;
        }

        $distanceKm = self::haversine(
            $buyerCoords['lat'],
            $buyerCoords['lng'],
            $vendorLat,
            $vendorLng
        );

        return self::deliveryFeeForKm($distanceKm);
    }

    public function placeOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=cart/checkout');
            exit;
        }

        $userId = $this->getBuyerId();

        $deliveryMethod = $_POST['delivery_method'] ?? 'pickup';
        $deliveryMethod = in_array($deliveryMethod, ['pickup', 'delivery'], true) ? $deliveryMethod : 'pickup';

        $deliveryAddress = trim($_POST['delivery_address'] ?? '');
        $pickupTimeSlot = trim($_POST['pickup_time_slot'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // ── Payment fields ────────────────────────────────────────────
        $paymentMethod    = trim($_POST['payment_method'] ?? 'cash');
        $paymentMethod    = in_array($paymentMethod, ['cash', 'gcash', 'maya'], true) ? $paymentMethod : 'cash';
        $paymentReference = trim($_POST['payment_reference'] ?? '');
        $paymentProofPath = null;

        $cartModel = new Cart();
        $items = $cartModel->getCartItems($userId);
        $summary = $cartModel->getCartSummary($userId);
        $groupedItems = $this->groupItemsByVendor($items);

        $error = null;

        if (empty($items)) {
            $error = 'Your cart is empty.';
        } elseif ($deliveryMethod === 'delivery'
                  && empty($_POST['buyer_lat'])
                  && empty($_POST['buyer_lng'])) {
            $error = 'Please drop a pin on the map to set your delivery location.';
        } elseif (in_array($paymentMethod, ['gcash', 'maya'])) {
            if ($paymentReference === '') {
                $error = 'Please enter your e-wallet reference number.';
            } elseif (empty($_FILES['payment_proof']['name'])) {
                $error = 'Please upload your proof of payment screenshot.';
            }
        }

        // ── Upload proof of payment ───────────────────────────────────
        if ($error === null && in_array($paymentMethod, ['gcash', 'maya']) && !empty($_FILES['payment_proof']['name'])) {
            $uploadDir  = __DIR__ . '/../../public/uploads/payment_proofs/';
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $file       = $_FILES['payment_proof'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Proof of payment upload failed.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Proof of payment must be under 5 MB.';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt)) {
                    $error = 'Only JPG, PNG, WebP, or GIF allowed for proof of payment.';
                } else {
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $filename = 'proof_' . uniqid('', true) . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                        $paymentProofPath = 'uploads/payment_proofs/' . $filename;
                    } else {
                        $error = 'Could not save proof of payment. Please try again.';
                    }
                }
            }
        }

        $db = (new Database())->getConnection();

        $buyerCoords = null;

        if ($deliveryMethod === 'delivery' && $error === null) {
            $pinLat = isset($_POST['buyer_lat']) && $_POST['buyer_lat'] !== '' ? (float)$_POST['buyer_lat'] : null;
            $pinLng = isset($_POST['buyer_lng']) && $_POST['buyer_lng'] !== '' ? (float)$_POST['buyer_lng'] : null;

            if ($pinLat !== null && $pinLng !== null
                && $pinLat >= 4.5 && $pinLat <= 21.5
                && $pinLng >= 116.0 && $pinLng <= 127.0) {
                $buyerCoords = ['lat' => $pinLat, 'lng' => $pinLng];
            } else {
                $error = 'Please drop a pin on the map to set your delivery location.';
            }
        }

        if ($error !== null) {
            $this->view('buyer/checkout', [
                'items' => $items,
                'groupedItems' => $groupedItems,
                'summary' => $summary,
                'error' => $error,
                'old' => [
                    'delivery_method'  => $deliveryMethod,
                    'delivery_address' => $deliveryAddress,
                    'pickup_time_slot' => $pickupTimeSlot,
                    'notes'            => $notes,
                    'payment_method'   => $paymentMethod,
                ],
            ]);
            return;
        }

        $orderModel = new Order();

        try {
            $db->beginTransaction();

            $createdOrderIds = [];

            foreach ($groupedItems as $vendorGroup) {
                $vendorSubtotal = 0.00;
                $orderItems = [];

                foreach ($vendorGroup['items'] as $item) {
                    $vendorSubtotal += (float)$item['subtotal'];

                    $orderItems[] = [
                        'product_id' => (int)$item['prd_id'],
                        'quantity'   => (int)$item['cit_quantity'],
                        'unit_price' => (float)$item['cit_unit_price'],
                    ];
                }

                $vendorDeliveryFee = 0.00;

                if ($deliveryMethod === 'delivery' && $buyerCoords) {
                    $vendorDeliveryFee = $this->calculateVendorDeliveryFee(
                        $db,
                        (int)$vendorGroup['vendor_id'],
                        $buyerCoords
                    );
                }

                $orderTotal = $vendorSubtotal + $vendorDeliveryFee;

                $orderId = $orderModel->placeOrder([
                    'user_id'           => $userId,
                    'vendor_id'         => (int)$vendorGroup['vendor_id'],
                    'total_amount'      => $orderTotal,
                    'delivery_method'   => $deliveryMethod,
                    'delivery_address'  => $deliveryMethod === 'delivery' ? $deliveryAddress : null,
                    'pickup_time_slot'  => $pickupTimeSlot !== '' ? $pickupTimeSlot : null,
                    'payment_method'    => $paymentMethod,
                    'payment_reference' => $paymentReference !== '' ? $paymentReference : null,
                    'payment_proof'     => $paymentProofPath,
                    'notes'             => $notes !== '' ? $notes : null,
                ]);

                if (!$orderId) {
                    throw new RuntimeException('Failed to create the order.');
                }

                $ok = $orderModel->insertOrderItems((int)$orderId, $orderItems);

                if (!$ok) {
                    throw new RuntimeException('Failed to save order items.');
                }

                $createdOrderIds[] = (int)$orderId;
            }

            $cartModel->clearCart($userId);
            $db->commit();

            if (!empty($createdOrderIds)) {
                $_SESSION['last_order_ids'] = $createdOrderIds;
            }

            header('Location: index.php?url=orderconfirmation');
            exit;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $this->view('buyer/checkout', [
                'items' => $items,
                'groupedItems' => $groupedItems,
                'summary' => $summary,
                'error' => 'Unable to place the order right now. Please try again.',
                'old' => [
                    'delivery_method'  => $deliveryMethod,
                    'delivery_address' => $deliveryAddress,
                    'pickup_time_slot' => $pickupTimeSlot,
                    'notes'            => $notes,
                    'payment_method'   => $paymentMethod,
                ],
            ]);
        }
    }
}