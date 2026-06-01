<?php
require_once __DIR__ . '/../../config/Database.php';

class ProfileController extends Controller
{
    // ── index ────────────────────────────────────────────────────────
    public function index()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?url=farmer"); exit;
        }

        $user = $_SESSION['user'];
        $role = $user['role'] ?? 'buyer';

        if ($role === 'vendor') {
            require_once __DIR__ . '/../models/Vendor.php';
            $vendorId = (int)($_SESSION['vendor_id'] ?? 0);
            $vendor   = (new Vendor())->getProfileById($vendorId);
            if (!$vendor) die('Vendor profile not found.');

            // vnd_owner_name and vnd_profile_pic are now fetched directly by getProfileById()
            $vData = [
                'vnd_owner_name'  => $vendor['vnd_owner_name']  ?? null,
                'vnd_profile_pic' => $vendor['vnd_profile_pic'] ?? null,
            ];

            $this->view('vendor/profile', [
                'vendor' => $vendor,
                'vData'  => $vData,
            ]);
            return;
        }

        require_once __DIR__ . '/../models/Order.php';
        require_once __DIR__ . '/../models/Vendor.php';

        $userId = (int)($user['user_id'] ?? 0);
        if (!$userId) die("User ID missing in session.");

        $orderModel  = new Order();
        $vendorModel = new Vendor();
        $orders      = $orderModel->getByBuyer($userId);
        $allReviews  = [];

        foreach ($orders as $order) {
            $vid = $order['ord_vendor_id'] ?? null;
            if ($vid) {
                foreach ($vendorModel->getReviews($vid) as $r) {
                    if ((int)$r['rev_user_id'] === $userId) $allReviews[] = $r;
                }
            }
        }

        $db   = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT * FROM tbl_user WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch lat/lng if columns exist (added on first profile save)
        try {
            $s2 = $db->prepare("SELECT user_lat, user_lng FROM tbl_user WHERE user_id = ? LIMIT 1");
            $s2->execute([$userId]);
            $coords = $s2->fetch(PDO::FETCH_ASSOC);
            if ($coords) {
                $userData['user_lat'] = $coords['user_lat'];
                $userData['user_lng'] = $coords['user_lng'];
            }
        } catch (\PDOException $e) {
            $userData['user_lat'] = null;
            $userData['user_lng'] = null;
        }

        $this->view('buyer/profilePageBuyer', [
            'orders'   => $orders,
            'reviews'  => $allReviews,
            'userData' => $userData,
        ]);
    }

    // ── Buyer update — only touches tbl_user ─────────────────────────
    public function update()
    {
        if (!isset($_SESSION['user']['user_id'])) {
            header('Location: index.php?url=farmer'); exit;
        }

        $isAjax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $userId  = (int)$_SESSION['user']['user_id'];
        $fname   = trim($_POST['first_name']       ?? '');
        $lname   = trim($_POST['last_name']        ?? '');
        $phone   = trim($_POST['phone']            ?? '');
        $address = trim($_POST['address']          ?? '');
        $city    = trim($_POST['city']             ?? '');
        $region  = trim($_POST['region']           ?? '');
        $barangay= trim($_POST['barangay']         ?? '');
        $newPass = trim($_POST['new_password']     ?? '');
        $curPass = trim($_POST['current_password'] ?? '');

        // Pinned coordinates from the map
        $pinnedLat = (isset($_POST['buyer_lat']) && $_POST['buyer_lat'] !== '')
            ? (float)$_POST['buyer_lat'] : null;
        $pinnedLng = (isset($_POST['buyer_lng']) && $_POST['buyer_lng'] !== '')
            ? (float)$_POST['buyer_lng'] : null;
        // Validate within Philippines bounding box
        if ($pinnedLat !== null && ($pinnedLat < 4.5  || $pinnedLat > 21.5))  $pinnedLat = null;
        if ($pinnedLng !== null && ($pinnedLng < 116.0 || $pinnedLng > 127.0)) $pinnedLng = null;

        if ($fname === '') {
            return $this->jsonOrRedirect($isAjax, false, 'First name is required.');
        }

        $db   = (new Database())->getConnection();
        $stmt = $db->prepare("SELECT user_pass, user_profile_pic FROM tbl_user WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($newPass !== '' && ($curPass === '' || $row['user_pass'] !== $curPass)) {
            return $this->jsonOrRedirect($isAjax, false, 'Current password is incorrect.');
        }

        // Profile picture
        $profilePic = $row['user_profile_pic'] ?? null;
        if (!empty($_FILES['profile_pic']['name'])) {
            $up = $this->uploadPic($_FILES['profile_pic'], 'users');
            if ($up['error']) return $this->jsonOrRedirect($isAjax, false, $up['error']);
            if ($profilePic) $this->deleteFile($profilePic);
            $profilePic = $up['path'];
        }

        // Ensure lat/lng columns exist on tbl_user
        $userCols = array_column(
            $db->query("SHOW COLUMNS FROM tbl_user")->fetchAll(PDO::FETCH_ASSOC),
            'Field'
        );
        if (!in_array('user_lat', $userCols)) {
            $db->exec("ALTER TABLE tbl_user ADD COLUMN user_lat DECIMAL(10,7) NULL DEFAULT NULL");
        }
        if (!in_array('user_lng', $userCols)) {
            $db->exec("ALTER TABLE tbl_user ADD COLUMN user_lng DECIMAL(10,7) NULL DEFAULT NULL");
        }

        $sql    = "UPDATE tbl_user SET user_fname=:fn, user_lname=:ln, user_phone=:ph, user_profile_pic=:pic";
        $params = [':fn' => $fname, ':ln' => $lname, ':ph' => $phone ?: null, ':pic' => $profilePic, ':id' => $userId];

        if ($newPass !== '') {
            $sql .= ", user_pass=:pw";
            $params[':pw'] = $newPass;
            // If this user also has a vendor profile, keep vendor password in sync
            $vpCheck = $db->prepare("SELECT user_vendor_pass FROM tbl_user WHERE user_id = ? LIMIT 1");
            $vpCheck->execute([$userId]);
            $vpRow = $vpCheck->fetch(PDO::FETCH_ASSOC);
            if (!empty($vpRow['user_vendor_pass'])) {
                $sql .= ", user_vendor_pass=:vpw";
                $params[':vpw'] = $newPass;
            }
        }

        // Save address
        $addrCols = $db->query("SHOW COLUMNS FROM tbl_user LIKE 'user_address'")->fetchAll();
        if (!empty($addrCols)) {
            $sql .= ", user_address=:addr, user_city=:city, user_region=:region";
            $params[':addr']   = $address  ?: null;
            $params[':city']   = $city     ?: null;
            $params[':region'] = $region   ?: null;
        }

        // Save barangay if column exists (add it if not)
        try {
            $bCols = $db->query("SHOW COLUMNS FROM tbl_user LIKE 'user_barangay'")->fetchAll();
            if (empty($bCols)) {
                $db->exec("ALTER TABLE tbl_user ADD COLUMN user_barangay VARCHAR(255) NULL DEFAULT NULL");
            }
            $sql .= ", user_barangay=:barangay";
            $params[':barangay'] = $barangay ?: null;
        } catch (\PDOException $e) { /* non-fatal */ }

        // Save pinned coordinates
        $sql .= ", user_lat=:lat, user_lng=:lng";
        $params[':lat'] = $pinnedLat;
        $params[':lng'] = $pinnedLng;

        $sql .= " WHERE user_id=:id";
        $db->prepare($sql)->execute($params);

        $fullName = trim($fname . ' ' . $lname);
        $_SESSION['user']['name']        = $fullName;
        $_SESSION['user']['profile_pic'] = $profilePic;

        $message = 'Profile updated.';
        if ($pinnedLat !== null && $pinnedLng !== null) {
            $message .= ' Delivery location pinned.';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message, 'name' => $fullName, 'profile_pic' => $profilePic]);
            exit;
        }
        header('Location: index.php?url=profile'); exit;
    }

    // ── Vendor update — only touches tbl_vendor ──────────────────────
    public function updateVendor()
    {
        if (!isset($_SESSION['user']['user_id']) || ($_SESSION['user']['role'] ?? '') !== 'vendor') {
            header('Location: index.php?url=farmer'); exit;
        }

        $isAjax   = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        $vendorId = (int)($_SESSION['vendor_id'] ?? 0);

        $ownerName   = trim($_POST['owner_name']          ?? '');
        $farmName    = trim($_POST['farm_name']           ?? '');
        $address     = trim($_POST['address']             ?? '');
        $farmDesc    = trim($_POST['farm_desc']           ?? '');
        $pickupInstr = trim($_POST['pickup_instructions'] ?? '');
        $newPass     = trim($_POST['new_password']        ?? '');
        $curPass     = trim($_POST['current_password']    ?? '');

        // Pinned coordinates from the map (preferred over geocoding)
        $pinnedLat = (isset($_POST['vendor_lat']) && $_POST['vendor_lat'] !== '')
            ? (float)$_POST['vendor_lat'] : null;
        $pinnedLng = (isset($_POST['vendor_lng']) && $_POST['vendor_lng'] !== '')
            ? (float)$_POST['vendor_lng'] : null;
        // Validate within Philippines bounding box
        if ($pinnedLat !== null && ($pinnedLat < 4.5  || $pinnedLat > 21.5))  $pinnedLat = null;
        if ($pinnedLng !== null && ($pinnedLng < 116.0 || $pinnedLng > 127.0)) $pinnedLng = null;

        if ($ownerName === '') {
            return $this->jsonOrRedirect($isAjax, false, 'Owner name is required.');
        }
        if ($address === '') {
            return $this->jsonOrRedirect($isAjax, false, 'Farm/pickup address is required.');
        }

        $db = (new Database())->getConnection();

        // ── Password change (stored in tbl_user) ─────────────────────
        if ($newPass !== '') {
            $stmt = $db->prepare("SELECT user_pass FROM tbl_user WHERE user_id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user']['user_id']]);
            $uRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($curPass === '' || ($uRow['user_pass'] ?? '') !== $curPass) {
                return $this->jsonOrRedirect($isAjax, false, 'Current password is incorrect.');
            }
            $db->prepare("UPDATE tbl_user SET user_pass = ? WHERE user_id = ?")
               ->execute([$newPass, $_SESSION['user']['user_id']]);
        }

        // ── Cover photo ───────────────────────────────────────────────
        $stmt2 = $db->prepare("SELECT vnd_cover_photo FROM tbl_vendor WHERE vnd_id = ? LIMIT 1");
        $stmt2->execute([$vendorId]);
        $coverPhoto = ($stmt2->fetch(PDO::FETCH_ASSOC) ?: [])['vnd_cover_photo'] ?? null;

        if (!empty($_FILES['cover_photo']['name'])) {
            $up = $this->uploadPic($_FILES['cover_photo'], 'vendors');
            if (!$up['error']) {
                if ($coverPhoto && !str_starts_with($coverPhoto, 'http')) {
                    $this->deleteFile($coverPhoto);
                }
                $coverPhoto = $up['path'];
            }
        }

        // ── Coordinates: pin takes priority, fall back to geocoding ─────
        if ($pinnedLat !== null && $pinnedLng !== null) {
            $lat      = $pinnedLat;
            $lng      = $pinnedLng;
            $geocoded = true;
        } else {
            try {
                $result   = $this->geocodeAddress($address);
                $lat      = $result ? $result['lat'] : null;
                $lng      = $result ? $result['lng'] : null;
                $geocoded = $result !== null;
            } catch (\Throwable $e) {
                $lat      = null;
                $lng      = null;
                $geocoded = false;
            }
        }

        // ── Ensure lat/lng columns exist before saving ────────────────
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

        // ── Save vendor profile ───────────────────────────────────────
        $db->prepare("
            UPDATE tbl_vendor SET
                vnd_owner_name          = :owner,
                vnd_farm_name           = :farm,
                vnd_address             = :addr,
                vnd_farm_desc           = :desc,
                vnd_pickup_instructions = :pickup,
                vnd_cover_photo         = :cover,
                vnd_lat                 = :lat,
                vnd_lng                 = :lng
            WHERE vnd_id = :id
        ")->execute([
            ':owner'  => $ownerName,
            ':farm'   => $farmName ?: $ownerName . "'s Farm",
            ':addr'   => $address,
            ':desc'   => $farmDesc   ?: null,
            ':pickup' => $pickupInstr ?: null,
            ':cover'  => $coverPhoto,
            ':lat'    => $lat,
            ':lng'    => $lng,
            ':id'     => $vendorId,
        ]);

        $_SESSION['user']['name'] = $ownerName;

        $message = 'Profile updated successfully.';
        if ($pinnedLat !== null && $pinnedLng !== null) {
            $message .= ' Farm location pinned on map.';
        } elseif ($geocoded) {
            $message .= ' Address geocoded automatically.';
        } else {
            $message .= ' Note: address could not be geocoded — '
                      . 'delivery coordinates will be resolved at checkout.';
        }

        if ($isAjax) {
            if (ob_get_level() > 0) ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success'     => true,
                'message'     => $message,
                'owner_name'  => $ownerName,
                'cover_photo' => $coverPhoto,
                'geocoded'    => $geocoded !== null,
            ]);
            exit;
        }
        header('Location: index.php?url=profile'); exit;
    }

    /**
     * Geocode a vendor address using OpenStreetMap Nominatim.
     * Tries multiple query strategies for best Philippine address coverage.
     * Returns ['lat' => float, 'lng' => float] or null on failure.
     */
    private function geocodeAddress(string $address): ?array
    {
        $address = rtrim(trim($address), ', ');
        if (stripos($address, 'philippines') === false) {
            $address .= ', Philippines';
        }

        $parts    = array_values(array_filter(array_map('trim', explode(',', $address))));
        $queries  = [];

        // 1. Full address
        $queries[] = $address;

        // 2. Drop first component (house/lot/barangay prefix)
        if (count($parts) >= 3) {
            $queries[] = implode(', ', array_slice($parts, 1));
        }

        // 3. Last two components (city + province)
        if (count($parts) >= 2) {
            $queries[] = implode(', ', array_slice($parts, -2));
        }

        // 4. Structured search — most reliable for PH municipalities
        $filtered = array_values(
            array_filter($parts, fn($p) => strtolower(trim($p)) !== 'philippines')
        );
        if (count($filtered) >= 2) {
            $city   = $filtered[count($filtered) - 2];
            $state  = $filtered[count($filtered) - 1];
            $result = $this->nominatimStructured($city, $state);
            if ($result) return $result;
        }

        // 5. Last component alone (province / region)
        if (count($parts) >= 1) {
            $queries[] = end($parts);
        }

        foreach (array_unique($queries) as $query) {
            $result = $this->nominatimFreeText($query);
            if ($result) return $result;
        }

        return null;
    }

    private function nominatimFreeText(string $query): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=json&limit=1&countrycodes=ph&addressdetails=1'
            . '&q=' . urlencode($query);

        $response = $this->nominatimHttp($url);
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) return null;

        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }

    private function nominatimStructured(string $city, string $state): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search'
            . '?format=json&limit=1&countrycodes=ph&addressdetails=1'
            . '&city='  . urlencode($city)
            . '&state=' . urlencode($state);

        $response = $this->nominatimHttp($url);
        if ($response === null) return null;

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) return null;

        return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
    }

    private function nominatimHttp(string $url): ?string
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 10,
                'ignore_errors' => true,
                'header'        => "User-Agent: AgriLocal/1.0 (agrilocal@example.com)\r\n"
                                 . "Accept: application/json\r\n",
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            error_log('[AgriLocal] Nominatim HTTP failed: ' . $url);
            return null;
        }
        return $response;
    }

    // ── Helpers ──────────────────────────────────────────────────────
    private function jsonOrRedirect(bool $isAjax, bool $success, string $msg): void
    {
        if ($isAjax) {
            if (ob_get_level() > 0) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $msg]);
            exit;
        }
        header('Location: index.php?url=profile'); exit;
    }

    private function uploadPic(array $file, string $subdir): array
    {
        $allowed   = ['image/jpeg', 'image/png', 'image/webp'];
        $allowExt  = ['jpg', 'jpeg', 'png', 'webp'];
        $uploadDir = __DIR__ . '/../../public/uploads/' . $subdir . '/';

        if ($file['error'] !== UPLOAD_ERR_OK)  return ['error' => 'Upload failed.', 'path' => ''];
        if ($file['size'] > 5 * 1024 * 1024)   return ['error' => 'Image must be under 5 MB.', 'path' => ''];

        $mime = mime_content_type($file['tmp_name']) ?: $file['type'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($mime, $allowed) || !in_array($ext, $allowExt))
            return ['error' => 'Only JPG, PNG, WebP allowed.', 'path' => ''];

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid('pic_', true) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename))
            return ['error' => 'Failed to save image.', 'path' => ''];

        return ['error' => '', 'path' => 'uploads/' . $subdir . '/' . $filename];
    }

    private function deleteFile(string $path): void
    {
        if (empty($path) || str_starts_with($path, 'http')) return;
        $full = __DIR__ . '/../../public/' . ltrim($path, '/');
        if (file_exists($full)) @unlink($full);
    }
}
