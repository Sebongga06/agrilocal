<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Setup | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@500;600;700&family=Noto+Serif:wght@400;500&family=Material+Icons&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Noto Serif',serif;background:#f4f6f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
        .card{background:#fff;border-radius:20px;padding:2.5rem 2rem;width:100%;max-width:480px;box-shadow:0 8px 32px rgba(0,0,0,.08);}
        .back-link{display:inline-flex;align-items:center;gap:.3rem;color:#2d6a4f;text-decoration:none;font-size:.85rem;font-weight:600;margin-bottom:1.5rem;}
        .back-link:hover{text-decoration:underline;}
        h1{font-family:'Roboto Slab',serif;font-size:1.4rem;color:#1a3c2e;margin-bottom:.3rem;}
        .subtitle{color:#666;font-size:.88rem;margin-bottom:1.5rem;line-height:1.6;}

        /* Status badge */
        .status-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;font-size:.8rem;font-weight:600;margin-bottom:1.5rem;}
        .status-on{background:#f0fff4;color:#276749;border:1px solid #9ae6b4;}
        .status-off{background:#fff5f5;color:#c53030;border:1px solid #feb2b2;}

        /* Steps */
        .steps{counter-reset:step;margin-bottom:1.5rem;}
        .step{display:flex;gap:.85rem;margin-bottom:1rem;align-items:flex-start;}
        .step-num{width:28px;height:28px;border-radius:50%;background:#2d6a4f;color:#fff;font-family:'Roboto Slab',serif;font-size:.82rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;}
        .step-text{font-size:.88rem;color:#444;line-height:1.6;}
        .step-text strong{color:#1a3c2e;}

        /* QR */
        .qr-wrap{text-align:center;margin:1.25rem 0;}
        .qr-wrap img{border:6px solid #f0f0f0;border-radius:12px;max-width:200px;display:block;margin:0 auto;}
        .secret-box{background:#f8f8f8;border:1px solid #eee;border-radius:10px;padding:.65rem 1rem;font-family:monospace;font-size:1rem;letter-spacing:.15rem;text-align:center;color:#333;margin:.75rem 0 1.25rem;word-break:break-all;}

        /* Form */
        .code-input{width:100%;text-align:center;font-size:1.6rem;font-family:'Roboto Slab',serif;letter-spacing:.4rem;padding:.65rem 1rem;border:2px solid #ddd;border-radius:12px;outline:none;margin-bottom:1rem;}
        .code-input:focus{border-color:#2d6a4f;}
        .btn-enable{width:100%;padding:.75rem;background:#2d6a4f;color:#fff;border:none;border-radius:12px;font-family:'Roboto Slab',serif;font-size:.95rem;font-weight:600;cursor:pointer;}
        .btn-enable:hover{background:#245a40;}
        .btn-disable{width:100%;padding:.75rem;background:#fff5f5;color:#c53030;border:1.5px solid #feb2b2;border-radius:12px;font-family:'Roboto Slab',serif;font-size:.95rem;font-weight:600;cursor:pointer;margin-top:.75rem;}
        .btn-disable:hover{background:#fed7d7;}

        /* Flash */
        .flash-success{background:#f0fff4;color:#276749;border:1px solid #9ae6b4;border-radius:10px;padding:.75rem 1rem;font-size:.88rem;margin-bottom:1rem;display:flex;align-items:center;gap:.4rem;}
    </style>
</head>
<body>
<div class="card">
    <?php
    $backUrl = ($role === 'vendor') ? 'index.php?url=vendor/dashboard' : 'index.php?url=home';
    $skipUrl = $backUrl;
    $success = $_SESSION['2fa_success'] ?? null;
    unset($_SESSION['2fa_success']);
    ?>

    <a href="<?= $backUrl; ?>" class="back-link">
        <span class="material-icons" style="font-size:16px;">arrow_back</span> Back to Profile
    </a>

    <h1>Secure Your Account</h1>
    <p class="subtitle">Set up two-factor authentication to protect your account. You'll need an authenticator app like <strong>Google Authenticator</strong> or <strong>Authy</strong>.</p>

    <?php if ($success): ?>
        <div class="flash-success">
            <span class="material-icons" style="font-size:16px;">check_circle</span>
            <?= htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <span class="status-badge <?= $enabled ? 'status-on' : 'status-off'; ?>">
        <span class="material-icons" style="font-size:14px;"><?= $enabled ? 'verified_user' : 'gpp_bad'; ?></span>
        2FA is <?= $enabled ? 'Enabled' : 'Disabled'; ?>
    </span>

    <?php if (!$enabled): ?>
        <!-- Setup flow -->
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">Install <strong>Google Authenticator</strong> or <strong>Authy</strong> on your phone.</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">Scan the QR code below with your authenticator app.</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">Enter the 6-digit code shown in the app to confirm setup.</div>
            </div>
        </div>

        <div class="qr-wrap">
            <?= $qrCode; ?>
        </div>

        <p style="font-size:.8rem;color:#888;text-align:center;margin-bottom:.3rem;">Or enter this key manually:</p>
        <div class="secret-box"><?= htmlspecialchars($secret); ?></div>

        <form method="POST" action="index.php?url=twofactor/enable">
            <input
                type="text"
                name="code"
                class="code-input"
                placeholder="000000"
                maxlength="6"
                pattern="\d{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                required>
            <button type="submit" class="btn-enable">
                <span class="material-icons" style="vertical-align:middle;font-size:18px;">verified_user</span>
                Enable 2FA
            </button>
        </form>
        <a href="<?= $skipUrl; ?>" style="display:block;text-align:center;margin-top:1rem;font-size:.85rem;color:#aaa;text-decoration:none;">Skip for now →</a>

    <?php else: ?>
        <!-- Already enabled -->
        <p style="font-size:.88rem;color:#555;line-height:1.6;margin-bottom:1.25rem;">
            Two-factor authentication is active on your account. You will be asked for a code every time you log in.
        </p>
        <form method="POST" action="index.php?url=twofactor/disable"
              onsubmit="return confirm('Are you sure you want to disable 2FA? Your account will be less secure.');">
            <button type="submit" class="btn-disable">
                <span class="material-icons" style="vertical-align:middle;font-size:18px;">gpp_bad</span>
                Disable 2FA
            </button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
