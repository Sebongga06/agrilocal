<?php
require_once __DIR__ . '/../../config/Database.php';

use PragmaRX\Google2FAQRCode\Google2FA;

class TwofactorController extends Controller
{
    private PDO $db;
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->db       = (new Database())->getConnection();
        $this->google2fa = new Google2FA();
    }

    private function requireUser(): int
    {
        if (empty($_SESSION['user']['user_id'])) {
            header('Location: index.php?url=farmer');
            exit;
        }
        return (int)$_SESSION['user']['user_id'];
    }

    // ── Show setup page (generate QR code) ──────────────────────────
    public function setup()
    {
        $userId = $this->requireUser();

        $stmt = $this->db->prepare("SELECT user_2fa_secret, user_2fa_enabled, user_email FROM tbl_user WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Generate a new secret if not yet set
        if (empty($user['user_2fa_secret'])) {
            $secret = $this->google2fa->generateSecretKey();
            $this->db->prepare("UPDATE tbl_user SET user_2fa_secret = ? WHERE user_id = ?")->execute([$secret, $userId]);
            $user['user_2fa_secret'] = $secret;
        }

        $qrCodeData = $this->google2fa->getQRCodeInline(
            'AgriLocal',
            $user['user_email'],
            $user['user_2fa_secret']
        );

        // Wrap in img tag if it's a raw base64 string
        if (strpos($qrCodeData, '<img') === false) {
            $qrCode = '<img src="' . $qrCodeData . '" alt="QR Code">';
        } else {
            $qrCode = $qrCodeData;
        }

        $role = $_SESSION['user']['role'] ?? 'buyer';

        $this->view('auth/2fa-setup', [
            'secret'    => $user['user_2fa_secret'],
            'qrCode'    => $qrCode,
            'enabled'   => (bool)$user['user_2fa_enabled'],
            'role'      => $role,
        ]);
    }

    // ── Enable 2FA (verify first code after scanning) ────────────────
    public function enable()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=twofactor/setup'); exit;
        }

        $userId = $this->requireUser();
        $code   = trim($_POST['code'] ?? '');

        $stmt = $this->db->prepare("SELECT user_2fa_secret FROM tbl_user WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['user_2fa_secret'])) {
            $_SESSION['2fa_error'] = 'No secret found. Please try setup again.';
            header('Location: index.php?url=twofactor/setup'); exit;
        }

        $valid = $this->google2fa->verifyKey($row['user_2fa_secret'], $code);

        if (!$valid) {
            $_SESSION['2fa_error'] = 'Invalid code. Please try again.';
            header('Location: index.php?url=twofactor/setup'); exit;
        }

        $this->db->prepare("UPDATE tbl_user SET user_2fa_enabled = 1 WHERE user_id = ?")->execute([$userId]);
        $_SESSION['user']['2fa_enabled'] = true;
        $_SESSION['2fa_success'] = '2FA has been enabled successfully.';

        $role = $_SESSION['user']['role'] ?? 'buyer';
        header('Location: index.php?url=' . ($role === 'vendor' ? 'vendor/dashboard' : 'home'));
        exit;
    }

    // ── Disable 2FA ──────────────────────────────────────────────────
    public function disable()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=twofactor/setup'); exit;
        }

        $userId = $this->requireUser();

        $this->db->prepare("UPDATE tbl_user SET user_2fa_enabled = 0, user_2fa_secret = NULL WHERE user_id = ?")->execute([$userId]);
        $_SESSION['user']['2fa_enabled'] = false;
        $_SESSION['2fa_success'] = '2FA has been disabled.';

        $role = $_SESSION['user']['role'] ?? 'buyer';
        header('Location: index.php?url=' . ($role === 'vendor' ? 'vendor/dashboard' : 'home'));
        exit;
    }

    // ── Show verification page (called after password login) ─────────
    public function verify()
    {
        // Must have a pending session (password verified but 2FA not yet done)
        if (empty($_SESSION['2fa_pending'])) {
            header('Location: index.php?url=farmer'); exit;
        }

        $this->view('auth/2fa-verify', [
            'error' => $_SESSION['2fa_error'] ?? null,
        ]);
        unset($_SESSION['2fa_error']);
    }

    // ── Process verification code ────────────────────────────────────
    public function check()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['2fa_pending'])) {
            header('Location: index.php?url=farmer'); exit;
        }

        $code   = trim($_POST['code'] ?? '');
        $userId = (int)$_SESSION['2fa_pending']['user_id'];

        $stmt = $this->db->prepare("SELECT user_2fa_secret FROM tbl_user WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $valid = $row && $this->google2fa->verifyKey($row['user_2fa_secret'], $code);

        if (!$valid) {
            $_SESSION['2fa_error'] = 'Invalid code. Please try again.';
            header('Location: index.php?url=twofactor/verify'); exit;
        }

        // 2FA passed — complete the session
        $pending  = $_SESSION['2fa_pending'];
        unset($_SESSION['2fa_pending']);

        $_SESSION['user'] = $pending['user_session'];
        if (!empty($pending['vendor_id'])) {
            $_SESSION['vendor_id'] = $pending['vendor_id'];
        }

        header('Location: ' . $pending['redirect']);
        exit;
    }
}
