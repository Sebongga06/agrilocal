<?php
require_once __DIR__ . '/../../config/Database.php';

class AuthController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function login()
    {
        $error      = null;
        $fieldError = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role     = $_POST['role'] ?? 'buyer';

            if ($email === '')    $fieldError['email']    = 'Email is required.';
            if ($password === '') $fieldError['password'] = 'Password is required.';

            if (empty($fieldError)) {
                $stmt = $this->db->prepare("SELECT * FROM tbl_user WHERE user_email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $fieldError['email'] = 'No account found with this email.';
                } elseif ($role === 'buyer') {
                    // Pure vendor-only accounts have no buyer password — just show incorrect password
                    if ($user['user_role'] === 'vendor' && empty($user['user_vendor_pass'])) {
                        $fieldError['password'] = 'Incorrect password. Please try again.';
                    } elseif ($user['user_pass'] !== $password) {
                        // Wrong password (also catches vendor password entered on buyer tab)
                        $fieldError['password'] = 'Incorrect password. Please try again.';
                    } else {
                        $isFirst = $this->isFirstLogin($user['user_id']);
                        $this->incrementLoginCount($user['user_id']);

                        // Legacy vendor accounts (user_role = 'vendor') must land on the vendor dashboard
                        if ($user['user_role'] === 'vendor') {
                            if ($isFirst) {
                                $this->startVendorSession($user);
                                $this->redirect('index.php?url=twofactor/setup');
                            }
                            if (!empty($user['user_2fa_enabled'])) {
                                $this->store2faPending($user, 'vendor', 'index.php?url=vendor/dashboard');
                                $this->redirect('index.php?url=twofactor/verify');
                            }
                            $this->startVendorSession($user);
                            $this->redirect('index.php?url=vendor/dashboard');
                        }

                        if ($isFirst) {
                            $this->startBuyerSession($user);
                            $this->redirect('index.php?url=twofactor/setup');
                        }
                        if (!empty($user['user_2fa_enabled'])) {
                            $this->store2faPending($user, 'buyer', 'index.php?url=home');
                            $this->redirect('index.php?url=twofactor/verify');
                        }
                        $this->startBuyerSession($user);
                        $this->redirect('index.php?url=home');
                    }
                } else {
                    $vendorPass     = $user['user_vendor_pass'] ?? null;
                    $isLegacyVendor = ($user['user_role'] === 'vendor');

                    if (!empty($vendorPass)) {
                        if ($vendorPass !== $password) {
                            $fieldError['password'] = 'Incorrect vendor password. Please try again.';
                        } else {
                            $isFirst = $this->isFirstLogin($user['user_id']);
                            $this->incrementLoginCount($user['user_id']);
                            if ($isFirst) {
                                $this->startVendorSession($user);
                                $this->redirect('index.php?url=twofactor/setup');
                            }
                            if (!empty($user['user_2fa_enabled'])) {
                                $this->store2faPending($user, 'vendor', 'index.php?url=vendor/dashboard');
                                $this->redirect('index.php?url=twofactor/verify');
                            }
                            $this->startVendorSession($user);
                            $this->redirect('index.php?url=vendor/dashboard');
                        }
                    } elseif ($isLegacyVendor) {
                        if ($user['user_pass'] !== $password) {
                            $fieldError['password'] = 'Incorrect password. Please try again.';
                        } else {
                            $isFirst = $this->isFirstLogin($user['user_id']);
                            $this->incrementLoginCount($user['user_id']);
                            if ($isFirst) {
                                $this->startVendorSession($user);
                                $this->redirect('index.php?url=twofactor/setup');
                            }
                            if (!empty($user['user_2fa_enabled'])) {
                                $this->store2faPending($user, 'vendor', 'index.php?url=vendor/dashboard');
                                $this->redirect('index.php?url=twofactor/verify');
                            }
                            $this->startVendorSession($user);
                            $this->redirect('index.php?url=vendor/dashboard');
                        }
                    } else {
                        $fieldError['role'] = 'This account has no vendor profile. Register as a vendor first.';
                    }
                }
            }
        }

        $this->view('auth/farmer', ['error' => $error, 'fieldError' => $fieldError, 'formType' => 'login']);
    }

    public function register()
    {
        $error      = null;
        $fieldError = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $role     = $_POST['role'] ?? 'buyer';
            $terms    = $_POST['terms'] ?? '';

            // Address fields — now structured like profile edit
            $barangay = trim($_POST['barangay'] ?? '');
            $city     = trim($_POST['city']     ?? '');
            $region   = trim($_POST['region']   ?? '');
            // Full address auto-built on client; fallback to building it here
            $address  = trim($_POST['address']  ?? '');
            if ($address === '') {
                $address = implode(', ', array_filter([$barangay, $city, $region]));
            }

            // Coordinates: vendor pin map uses reg_lat/reg_lng,
            // buyer address geocoding also uses reg_lat/reg_lng (set by JS before submit)
            $regLat = isset($_POST['reg_lat']) && $_POST['reg_lat'] !== '' ? (float)$_POST['reg_lat'] : null;
            $regLng = isset($_POST['reg_lng']) && $_POST['reg_lng'] !== '' ? (float)$_POST['reg_lng'] : null;
            // Also accept reg_addr_lat/lng from the address geocoding hidden fields
            if ($regLat === null && isset($_POST['reg_addr_lat']) && $_POST['reg_addr_lat'] !== '') {
                $regLat = (float)$_POST['reg_addr_lat'];
                $regLng = (float)$_POST['reg_addr_lng'];
            }
            // Validate Philippines bounding box
            if ($regLat !== null && ($regLat < 4.5 || $regLat > 21.5 || $regLng < 116.0 || $regLng > 127.0)) {
                $regLat = null; $regLng = null;
            }

            if ($fullName === '') $fieldError['full_name'] = 'Full name is required.';
            if ($email === '')    $fieldError['email']     = 'Email is required.';
            if ($password === '') $fieldError['password']  = 'Password is required.';
            if ($terms !== '1')   $fieldError['terms']     = 'You must agree to the Terms and Conditions.';

            if (empty($fieldError)) {
                $this->ensureUserLocationColumns();

                $check = $this->db->prepare("SELECT user_id, user_vendor_pass FROM tbl_user WHERE user_email = ? LIMIT 1");
                $check->execute([$email]);
                $existing = $check->fetch(PDO::FETCH_ASSOC);

                if ($existing && $role === 'buyer') {
                    $fieldError['email'] = 'An account with this email already exists.';

                } elseif ($existing && $role === 'vendor') {
                    if (!empty($existing['user_vendor_pass'])) {
                        $fieldError['email'] = 'This account already has a vendor profile.';
                    } else {
                        $this->db->prepare("UPDATE tbl_user SET user_vendor_pass = ? WHERE user_id = ?")
                                 ->execute([$password, $existing['user_id']]);

                        $this->db->prepare("
                            INSERT INTO tbl_vendor (vnd_user_id, vnd_farm_name, vnd_address, vnd_lat, vnd_lng)
                            VALUES (?, ?, ?, ?, ?)
                        ")->execute([$existing['user_id'], $fullName . "'s Farm", $address ?: 'Address not set', $regLat, $regLng]);

                        // Update user lat/lng if we have coords
                        if ($regLat !== null) {
                            try {
                                $this->db->prepare("UPDATE tbl_user SET user_lat=?, user_lng=? WHERE user_id=?")
                                         ->execute([$regLat, $regLng, $existing['user_id']]);
                            } catch (\PDOException $e) {}
                        }

                        $stmt = $this->db->prepare("SELECT * FROM tbl_user WHERE user_id = ? LIMIT 1");
                        $stmt->execute([$existing['user_id']]);
                        $this->startVendorSession($stmt->fetch(PDO::FETCH_ASSOC));
                        $this->redirect('index.php?url=twofactor/setup');
                    }

                } else {
                    // Brand new account
                    $nameParts  = preg_split('/\s+/', $fullName, 2);
                    $fname      = $nameParts[0] ?? '';
                    $lname      = $nameParts[1] ?? '';
                    // Both roles store password in user_pass (column is NOT NULL).
                    // Vendors additionally get user_vendor_pass set so the vendor login tab works.
                    $userRole   = ($role === 'vendor') ? 'vendor' : 'buyer';
                    $userPass   = $password;
                    $vendorPass = ($role === 'vendor') ? $password : null;

                    $stmt = $this->db->prepare("
                        INSERT INTO tbl_user
                            (user_email, user_pass, user_vendor_pass, user_fname, user_lname,
                             user_phone, user_role, user_address, user_city, user_region,
                             user_lat, user_lng)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $saved = $stmt->execute([
                        $email, $userPass, $vendorPass, $fname, $lname,
                        $phone ?: null,
                        $userRole,
                        $address ?: null,
                        $city    ?: null,
                        $region  ?: null,
                        $regLat,
                        $regLng,
                    ]);

                    if ($saved) {
                        $userId = (int)$this->db->lastInsertId();

                        // Save barangay if column exists
                        if ($barangay !== '') {
                            try {
                                $bCols = $this->db->query("SHOW COLUMNS FROM tbl_user LIKE 'user_barangay'")->fetchAll();
                                if (empty($bCols)) {
                                    $this->db->exec("ALTER TABLE tbl_user ADD COLUMN user_barangay VARCHAR(255) NULL DEFAULT NULL");
                                }
                                $this->db->prepare("UPDATE tbl_user SET user_barangay=? WHERE user_id=?")
                                         ->execute([$barangay, $userId]);
                            } catch (\PDOException $e) {}
                        }

                        if ($role === 'vendor') {
                            $vendorAddr = $address ?: implode(', ', array_filter([$barangay, $city, $region])) ?: 'Address not set';
                            $this->db->prepare("
                                INSERT INTO tbl_vendor (vnd_user_id, vnd_farm_name, vnd_address, vnd_lat, vnd_lng)
                                VALUES (?, ?, ?, ?, ?)
                            ")->execute([$userId, $fullName . "'s Farm", $vendorAddr, $regLat, $regLng]);
                        }

                        $stmt = $this->db->prepare("SELECT * FROM tbl_user WHERE user_id = ? LIMIT 1");
                        $stmt->execute([$userId]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($role === 'vendor') {
                            $this->startVendorSession($user);
                        } else {
                            $this->startBuyerSession($user);
                        }
                        $this->redirect('index.php?url=twofactor/setup');
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        }

        $this->view('auth/farmer', ['error' => $error, 'fieldError' => $fieldError, 'formType' => 'register']);
    }

    public function switchToVendor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=home');
            exit;
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $userId   = (int)($_SESSION['user']['user_id'] ?? 0);
        $password = trim($_POST['vendor_password'] ?? '');

        if ($userId <= 0 || $password === '') {
            $msg = 'Password is required.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            $this->redirect('index.php?url=home');
        }

        $stmt = $this->db->prepare("
            SELECT u.*, v.vnd_id
            FROM tbl_user u
            LEFT JOIN tbl_vendor v ON v.vnd_user_id = u.user_id
            WHERE u.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $msg = 'Account not found.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            $this->redirect('index.php?url=home');
        }

        // Determine which password to check
        $vendorPass    = $user['user_vendor_pass'] ?? null;
        $isLegacyVendor = ($user['user_role'] === 'vendor');
        $checkPass     = !empty($vendorPass) ? $vendorPass : ($isLegacyVendor ? $user['user_pass'] : null);

        if (empty($checkPass)) {
            $msg = 'No vendor profile found. Please register as a vendor first.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            $this->redirect('index.php?url=home');
        }

        if ($checkPass !== $password) {
            $msg = 'Incorrect vendor password.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit; }
            $this->redirect('index.php?url=home');
        }

        $this->startVendorSession($user);

        if ($isAjax) {
            echo json_encode(['success' => true, 'redirect' => 'index.php?url=vendor/dashboard']);
            exit;
        }

        $this->redirect('index.php?url=vendor/dashboard');
    }

    public function switchToBuyer()
    {
        if (!isset($_SESSION['user']['user_id'])) {
            $this->redirect('index.php?url=farmer');
        }

        // Only dual-account users can switch to buyer
        if (empty($_SESSION['user']['has_buyer'])) {
            $this->redirect('index.php?url=vendor/dashboard');
        }

        $stmt = $this->db->prepare("SELECT * FROM tbl_user WHERE user_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user']['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $this->startBuyerSession($user);
        }

        $this->redirect('index.php?url=home');
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->redirect('index.php?url=farmer');
    }

    // ── Location column helpers ──────────────────────────────────────

    private function ensureUserLocationColumns(): void
    {
        try {
            $cols = array_column(
                $this->db->query("SHOW COLUMNS FROM tbl_user")->fetchAll(PDO::FETCH_ASSOC),
                'Field'
            );
            if (!in_array('user_lat', $cols)) {
                $this->db->exec("ALTER TABLE tbl_user ADD COLUMN user_lat DECIMAL(10,7) NULL DEFAULT NULL");
            }
            if (!in_array('user_lng', $cols)) {
                $this->db->exec("ALTER TABLE tbl_user ADD COLUMN user_lng DECIMAL(10,7) NULL DEFAULT NULL");
            }
            // Also ensure vendor lat/lng columns exist
            $vCols = array_column(
                $this->db->query("SHOW COLUMNS FROM tbl_vendor")->fetchAll(PDO::FETCH_ASSOC),
                'Field'
            );
            if (!in_array('vnd_lat', $vCols)) {
                $this->db->exec("ALTER TABLE tbl_vendor ADD COLUMN vnd_lat DECIMAL(10,7) NULL DEFAULT NULL");
            }
            if (!in_array('vnd_lng', $vCols)) {
                $this->db->exec("ALTER TABLE tbl_vendor ADD COLUMN vnd_lng DECIMAL(10,7) NULL DEFAULT NULL");
            }
        } catch (\PDOException $e) {
            // Non-fatal — columns may already exist
        }
    }

    // ── First-login helpers ──────────────────────────────────────────

    private function isFirstLogin(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT user_login_count FROM tbl_user WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($row['user_login_count']) && (int)$row['user_login_count'] === 0;
        } catch (\PDOException $e) {
            return false; // Column not yet added — safe fallback
        }
    }

    private function incrementLoginCount(int $userId): void
    {
        try {
            $this->db->prepare("UPDATE tbl_user SET user_login_count = user_login_count + 1 WHERE user_id = ?")
                     ->execute([$userId]);
        } catch (\PDOException $e) {
            // Column not yet added — ignore
        }
    }

    // ── Session helpers ──────────────────────────────────────────────

    private function store2faPending(array $user, string $role, string $redirect): void
    {
        // Build the session data that will be applied after 2FA passes
        if ($role === 'vendor') {
            $stmt = $this->db->prepare("SELECT vnd_id FROM tbl_vendor WHERE vnd_user_id = ? LIMIT 1");
            $stmt->execute([$user['user_id']]);
            $v = $stmt->fetch(PDO::FETCH_ASSOC);
            $vendorId = (int)($v['vnd_id'] ?? 0);

            $userSession = [
                'user_id'     => $user['user_id'],
                'name'        => $user['user_fname'] . ' ' . $user['user_lname'],
                'role'        => 'vendor',
                'email'       => $user['user_email'],
                'has_vendor'  => true,
                'has_buyer'   => !empty($user['user_vendor_pass']),
                'profile_pic' => $user['user_profile_pic'] ?? null,
                '2fa_enabled' => true,
            ];
        } else {
            $vendorId    = 0;
            $userSession = [
                'user_id'     => $user['user_id'],
                'name'        => $user['user_fname'] . ' ' . $user['user_lname'],
                'role'        => 'buyer',
                'email'       => $user['user_email'],
                'has_vendor'  => !empty($user['user_vendor_pass']) || ($user['user_role'] === 'vendor'),
                'profile_pic' => $user['user_profile_pic'] ?? null,
                '2fa_enabled' => true,
            ];
        }

        $_SESSION['2fa_pending'] = [
            'user_id'      => $user['user_id'],
            'user_session' => $userSession,
            'vendor_id'    => $vendorId,
            'redirect'     => $redirect,
        ];
    }

    private function startBuyerSession(array $user): void
    {
        // has_vendor is true only for dual accounts (buyer who also registered as vendor)
        $hasVendor = !empty($user['user_vendor_pass']);

        $_SESSION['user'] = [
            'user_id'     => $user['user_id'],
            'name'        => $user['user_fname'] . ' ' . $user['user_lname'],
            'role'        => 'buyer',
            'email'       => $user['user_email'],
            'has_vendor'  => $hasVendor,
            'profile_pic' => $user['user_profile_pic'] ?? null,
        ];
        unset($_SESSION['vendor_id']);
    }

    private function startVendorSession(array $user): void
    {
        // has_buyer is true only for dual accounts:
        // a buyer (user_role='buyer') who also registered as vendor (has user_vendor_pass)
        $hasBuyer = ($user['user_role'] !== 'vendor') && !empty($user['user_pass']);

        $_SESSION['user'] = [
            'user_id'     => $user['user_id'],
            'name'        => $user['user_fname'] . ' ' . $user['user_lname'],
            'role'        => 'vendor',
            'email'       => $user['user_email'],
            'has_vendor'  => true,
            'has_buyer'   => $hasBuyer,
            'profile_pic' => $user['user_profile_pic'] ?? null,
        ];

        // Look up vendor profile
        if (!empty($user['vnd_id'])) {
            $_SESSION['vendor_id'] = (int)$user['vnd_id'];
        } else {
            $stmt = $this->db->prepare("SELECT vnd_id FROM tbl_vendor WHERE vnd_user_id = ? LIMIT 1");
            $stmt->execute([$user['user_id']]);
            $v = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($v) $_SESSION['vendor_id'] = (int)$v['vnd_id'];
        }
    }
}
