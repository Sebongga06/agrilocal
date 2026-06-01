<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@500;600;700&family=Noto+Serif:wght@400;500&family=Material+Icons&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Noto Serif',serif;background:linear-gradient(135deg,#2d6a4f 0%,#52b788 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;}
        .card{background:#fff;border-radius:20px;padding:2.5rem 2rem;width:100%;max-width:400px;box-shadow:0 24px 60px rgba(0,0,0,.18);text-align:center;}
        .icon-wrap{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#2d6a4f,#52b788);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;}
        .icon-wrap .material-icons{font-size:32px;color:#fff;}
        h1{font-family:'Roboto Slab',serif;font-size:1.4rem;color:#1a3c2e;margin-bottom:.4rem;}
        p{color:#666;font-size:.9rem;line-height:1.6;margin-bottom:1.5rem;}
        .error-box{background:#fff5f5;color:#c53030;border:1px solid #feb2b2;border-radius:10px;padding:.75rem 1rem;font-size:.85rem;margin-bottom:1rem;display:flex;align-items:center;gap:.4rem;}
        .code-input{width:100%;text-align:center;font-size:2rem;font-family:'Roboto Slab',serif;letter-spacing:.5rem;padding:.75rem 1rem;border:2px solid #ddd;border-radius:12px;outline:none;margin-bottom:1.25rem;}
        .code-input:focus{border-color:#2d6a4f;}
        .btn{width:100%;padding:.8rem;background:#9a3718;color:#fff;border:none;border-radius:12px;font-family:'Roboto Slab',serif;font-size:1rem;font-weight:600;cursor:pointer;}
        .btn:hover{background:#7d2c12;}
        .back-link{display:block;margin-top:1rem;font-size:.85rem;color:#888;text-decoration:none;}
        .back-link:hover{color:#2d6a4f;}
    </style>
</head>
<body>
<div class="card">
    <div class="icon-wrap">
        <span class="material-icons">lock</span>
    </div>
    <h1>Two-Factor Authentication</h1>
    <p>Enter the 6-digit code from your authenticator app to continue.</p>

    <?php if (!empty($error)): ?>
        <div class="error-box">
            <span class="material-icons" style="font-size:16px;">error</span>
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=twofactor/check">
        <input
            type="text"
            name="code"
            class="code-input"
            placeholder="000000"
            maxlength="6"
            pattern="\d{6}"
            inputmode="numeric"
            autocomplete="one-time-code"
            autofocus
            required>
        <button type="submit" class="btn">Verify</button>
    </form>

    <a href="index.php?url=auth/logout" class="back-link">← Back to Login</a>
</div>
</body>
</html>
