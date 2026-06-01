<?php
require_once __DIR__ . '/../../config/Database.php';

class DeliveryController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // POST  index.php?url=delivery/calculate
    // Expects: buyer_lat, buyer_lng, delivery_address (all from checkout map pin)
    // Returns JSON with per-vendor distance + fee breakdown
    // ──────────────────────────────────────────────────────────────────────────
    public function calculate()
    {
        // Discard any stray output (PHP notices etc.) buffered since index.php
        ob_get_clean();
        ob_start();

        header('Content-Type: application/json');

        $json = function (array $payload) {
            ob_end_clean();
            echo json_encode($payload);
            exit;
        };

        try {
            if (!isset($_SESSION['user']['user_id'])) {
                $json(['success' => false, 'message' => 'Not logged in']);
            }

            $userId = (int)$_SESSION['user']['user_id'];
            $db     = (new Database())->getConnection();

            // ── Ensure vnd_lat / vnd_lng columns exist ────────────────────────
            $vendorCols = array_column(
                $db->query("SHOW COLUMNS FROM tbl_vendor")->fetchAll(PDO::FETCH_ASSOC),
                'Field'
            );
            if (!in_array('vnd_lat', $vendorCols)) {
                $db->exec("ALTER TABLE tbl_vendor ADD COLUMN vnd_lat DECIMAL(10,7) NULL DEFAULT NULL");
            }
            if (!in_array('vnd_lng', $vendorCols)) {
                $db->exec("ALTER TABLE tbl_vendor ADD COLUMN vnd_lng DECIMAL(10,7) NULL DEFAULT NULL");
            }

            // ── Buyer location — pin on map is the only accepted input ────────
            $pinLat = isset($_POST['buyer_lat']) && $_POST['buyer_lat'] !== ''
                ? (float)$_POST['buyer_lat'] : null;
            $pinLng = isset($_POST['buyer_lng']) && $_POST['buyer_lng'] !== ''
                ? (float)$_POST['buyer_lng'] : null;

            $deliveryAddr = trim($_POST['delivery_address'] ?? '');

            if ($pinLat === null || $pinLng === null
                || $pinLat < 4.5  || $pinLat > 21.5
                || $pinLng < 116.0 || $pinLng > 127.0) {
                $json([
                    'success' => false,
                    'message' => 'Please drop a pin on the map to set your delivery location.',
                ]);
            }

            $buyerCoords  = ['lat' => $pinLat, 'lng' => $pinLng];
            $buyerAddress = $deliveryAddr ?: "Pin ({$pinLat}, {$pinLng})";

            // ── Fetch all vendors in the buyer's cart ─────────────────────────
            $stmt = $db->prepare("
                SELECT DISTINCT
                    v.vnd_id,
                    v.vnd_farm_name,
                    v.vnd_address,
                    v.vnd_lat,
                    v.vnd_lng
                FROM tbl_cart c
                JOIN tbl_cart_item ci ON ci.cit_cart_id = c.crt_id
                JOIN tbl_product    p  ON p.prd_id       = ci.cit_product_id
                JOIN tbl_vendor     v  ON v.vnd_id       = p.prd_vendor_id
                WHERE c.crt_user_id = ?
            ");
            $stmt->execute([$userId]);
            $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($vendors)) {
                $json(['success' => false, 'message' => 'Your cart is empty.']);
            }

            $result   = [];
            $totalFee = 0.00;

            foreach ($vendors as $vendor) {
                $vendorLat = isset($vendor['vnd_lat'])  && $vendor['vnd_lat']  !== null
                    ? (float)$vendor['vnd_lat'] : 0.0;
                $vendorLng = isset($vendor['vnd_lng'])  && $vendor['vnd_lng']  !== null
                    ? (float)$vendor['vnd_lng'] : 0.0;

                // ── Auto-geocode if coordinates are missing ────────────────────
                if (($vendorLat === 0.0 || $vendorLng === 0.0) && !empty($vendor['vnd_address'])) {
                    $coords = $this->geocode(trim($vendor['vnd_address']));

                    if ($coords) {
                        $vendorLat = $coords['lat'];
                        $vendorLng = $coords['lng'];

                        // Persist so future checkouts skip geocoding
                        $db->prepare(
                            "UPDATE tbl_vendor SET vnd_lat = ?, vnd_lng = ? WHERE vnd_id = ?"
                        )->execute([$vendorLat, $vendorLng, (int)$vendor['vnd_id']]);
                    }
                }

                // ── Still no coordinates after geocoding attempt ───────────────
                if ($vendorLat === 0.0 || $vendorLng === 0.0) {
                    $result[] = [
                        'vendor_id'    => (int)$vendor['vnd_id'],
                        'vendor_name'  => $vendor['vnd_farm_name'],
                        'distance_km'  => null,
                        'charged_km'   => null,
                        'delivery_fee' => null,
                        'error'        => 'Vendor location could not be determined. '
                                        . 'Please ask the vendor to update their farm address.',
                    ];
                    continue;
                }

                // ── Calculate distance and fee ────────────────────────────────
                $distanceKm  = $this->haversine(
                    $buyerCoords['lat'], $buyerCoords['lng'],
                    $vendorLat, $vendorLng
                );
                $deliveryFee = $this->calcFee($distanceKm);
                $totalFee   += $deliveryFee;

                $result[] = [
                    'vendor_id'    => (int)$vendor['vnd_id'],
                    'vendor_name'  => $vendor['vnd_farm_name'],
                    'distance_km'  => round($distanceKm, 2),
                    'charged_km'   => max(1, (int)ceil($distanceKm)),
                    'delivery_fee' => round($deliveryFee, 2),
                    'error'        => null,
                ];
            }

            $json([
                'success'       => true,
                'vendors'       => $result,
                'total_fee'     => round($totalFee, 2),
                'buyer_address' => $buyerAddress,
            ]);

        } catch (Throwable $e) {
            ob_end_clean();
            ob_start();
            error_log('[AgriLocal] DeliveryController::calculate — ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ]);
            exit;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Geocode an address string → ['lat', 'lng'] or null
    //
    // Strategy (tries each until one succeeds):
    //   1. Full address + ", Philippines"
    //   2. Drop first component (street number / barangay prefix)
    //   3. Last two components (city + province)
    //   4. Nominatim structured search (city= + state=)
    //   5. Last component alone (province / region)
    // ──────────────────────────────────────────────────────────────────────────
    private function geocode(string $address): ?array
    {
        // Always append Philippines to narrow results
        $address = rtrim($address, ', ');
        if (stripos($address, 'philippines') === false) {
            $address .= ', Philippines';
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        $queries = [];

        // 1. Full address
        $queries[] = $address;

        // 2. Drop first component (e.g. house/lot number or barangay prefix)
        if (count($parts) >= 3) {
            $queries[] = implode(', ', array_slice($parts, 1));
        }

        // 3. Last two meaningful parts (city + province/region)
        if (count($parts) >= 2) {
            $queries[] = implode(', ', array_slice($parts, -2));
        }

        // 4. Structured Nominatim search — most reliable for PH municipalities
        $filtered = array_values(
            array_filter($parts, fn($p) => strtolower(trim($p)) !== 'philippines')
        );
        if (count($filtered) >= 2) {
            $city  = $filtered[count($filtered) - 2];
            $state = $filtered[count($filtered) - 1];
            $result = $this->nominatimStructured($city, $state);
            if ($result) return $result;
        }

        // 5. Last component alone (province / region)
        if (count($parts) >= 1) {
            $queries[] = end($parts);
        }

        foreach (array_unique($queries) as $query) {
            $result = $this->nominatim($query);
            if ($result) return $result;
        }

        return null;
    }

    // ── Nominatim free-text search ────────────────────────────────────────────
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

        $response = $this->httpGet($url);
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
            error_log('[AgriLocal] Nominatim no result for: ' . $query);
            return null;
        }

        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }

    // ── Nominatim structured search (city= + state=) ──────────────────────────
    private function nominatimStructured(string $city, string $state): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=json'
            . '&limit=1'
            . '&countrycodes=ph'
            . '&addressdetails=1'
            . '&city='  . urlencode($city)
            . '&state=' . urlencode($state);

        $response = $this->httpGet($url);
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
            return null;
        }

        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }

    // ── Shared HTTP GET with timeout + SSL bypass ─────────────────────────────
    private function httpGet(string $url): ?string
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 10,
                'ignore_errors' => true,
                'header'        => "User-Agent: AgriLocal/1.0 (agrilocal@example.com)\r\n"
                                 . "Accept: application/json\r\n",
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);

        if ($response === false) {
            $err = error_get_last();
            error_log('[AgriLocal] HTTP GET failed for ' . $url . ': ' . ($err['message'] ?? 'unknown'));
            return null;
        }

        return $response;
    }

    // ── Haversine distance formula (returns km) ───────────────────────────────
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371; // Earth radius in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // ── Delivery fee formula ──────────────────────────────────────────────────
    // first 1 km = ₱18 · every additional started km = ₱15
    // Examples: 0.5→₱18  1.0→₱18  1.2→₱33  2.0→₱33  3.0→₱48
    private function calcFee(float $km): float
    {
        $charged = max(1, (int)ceil($km));   // minimum 1 km

        if ($charged <= 1) {
            return 18.00;
        }

        return 18.00 + (($charged - 1) * 15.00);
    }
}
