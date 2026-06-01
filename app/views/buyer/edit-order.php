<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order #<?= (int)$order['ord_id']; ?> | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/screen.css?v=2">
    <link rel="stylesheet" href="assets/css/edit-order.css?v=<?= time(); ?>">
</head>
<body>

<nav class="main-nav">
    <div class="nav-container">
        <div class="logo">
            <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
            <a href="index.php?url=home">AgriLocal</a>
        </div>
        <div class="nav-links">
            <a href="index.php?url=home">Home</a>
            <a href="index.php?url=products">Products</a>
            <a href="index.php?url=vendors">Vendors</a>
        </div>
        <div class="nav-icons">
            <a href="index.php?url=cart" class="nav-icon-item">
                <span class="material-icons">shopping_cart</span>
                <span class="icon-label">Cart</span>
            </a>
            <div class="nav-icon-item account-trigger" id="accountTrigger">
                <span class="material-icons">person_outline</span>
                <span class="icon-label">Account</span>
                <div class="account-dropdown-menu" id="accountDropdown">
                    <a href="index.php?url=profile">My Account</a>
                    <a href="index.php?url=orders">My Orders</a>
                    <button type="button" onclick="logoutUser()">Logout</button>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="edit-order-wrap">
    <a href="index.php?url=orders" class="back-link">
        <span class="material-icons" style="font-size:16px;">arrow_back</span> Back to My Orders
    </a>

    <h1>Edit Order #<?= (int)$order['ord_id']; ?></h1>
    <p class="sub">Vendor: <?= htmlspecialchars($order['vnd_farm_name'] ?? ''); ?> &nbsp;·&nbsp; Only editable while pending or confirmed.</p>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $e): ?>
                <div><?= htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="edit-card">
        <form method="POST" action="index.php?url=orders/update/<?= (int)$order['ord_id']; ?>">

            <div class="field-group">
                <label>Delivery Method</label>
                <div class="radio-row">
                    <label>
                        <input type="radio" name="delivery_method" value="pickup"
                               <?= ($order['ord_delivery_method'] ?? 'pickup') === 'pickup' ? 'checked' : ''; ?>
                               onchange="toggleAddr(this.value)">
                        Store Pickup
                    </label>
                    <label>
                        <input type="radio" name="delivery_method" value="delivery"
                               <?= ($order['ord_delivery_method'] ?? '') === 'delivery' ? 'checked' : ''; ?>
                               onchange="toggleAddr(this.value)">
                        Delivery
                    </label>
                </div>
            </div>

            <div class="field-group" id="addrGroup"
                 style="<?= ($order['ord_delivery_method'] ?? 'pickup') !== 'delivery' ? 'display:none;' : ''; ?>">
                <label>Delivery Address</label>
                <textarea name="delivery_address" placeholder="Enter full delivery address…"><?= htmlspecialchars($order['ord_delivery_address'] ?? ''); ?></textarea>
            </div>

            <div class="field-group">
                <label>Preferred Pickup / Delivery Time</label>
                <input type="datetime-local" name="pickup_time_slot"
                       value="<?= !empty($order['ord_pickup_time_slot']) ? date('Y-m-d\TH:i', strtotime($order['ord_pickup_time_slot'])) : ''; ?>">
            </div>

            <div class="field-group">
                <label>Order Notes</label>
                <textarea name="notes" placeholder="Any special instructions…"><?= htmlspecialchars($order['ord_notes'] ?? ''); ?></textarea>
            </div>

            <div class="btn-row">
                <a href="index.php?url=orders" class="btn-back">
                    <span class="material-icons" style="font-size:16px;">close</span> Cancel
                </a>
                <button type="submit" class="btn-save">
                    <span class="material-icons" style="font-size:16px;">save</span> Save Changes
                </button>
            </div>

        </form>
    </div>
</div>

<script src="assets/js/screen.js"></script>
<script src="assets/js/edit-order.js?v=<?= time(); ?>"></script>
</body>
</html>
