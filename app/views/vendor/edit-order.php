<?php $pageTitle = 'AgriLocal - Edit Order #' . str_pad($order['ord_id'], 4, '0', STR_PAD_LEFT); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <a class="sidebar-logo" href="index.php?url=vendor/dashboard">
            <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
            <span class="logo-text">AgriLocal</span>
        </a>
        <nav class="nav-menu">
            <p class="menu-label">Main Menu</p>
            <ul>
                <li><a href="index.php?url=vendor/dashboard"><span class="material-icons-round">dashboard</span> Dashboard</a></li>
                <li><a href="index.php?url=vendor/inventory"><span class="material-icons-round">inventory_2</span> Inventory</a></li>
                <li class="active"><a href="index.php?url=vendor/orders" style="position:relative;">
    <span class="material-icons-round">shopping_basket</span> Orders
    <?php
    $urgentCount = $urgentCount ?? 0;
    if ($urgentCount > 0):
    ?>
    <span style="position:absolute;top:6px;right:8px;width:8px;height:8px;background:#e53e3e;border-radius:50%;border:2px solid #fff;display:inline-block;" title="<?= $urgentCount; ?> order(s) due within 24 hours"></span>
    <?php endif; ?>
</a></li>
                <li><a href="index.php?url=vendor/profile"><span class="material-icons-round">store</span> Shop Profile</a></li>
            </ul>
            <hr class="sidebar-divider">
            <ul>
                <?php if (!empty($_SESSION['user']['has_buyer'])): ?><li><a href="index.php?url=auth/switchToBuyer"><span class="material-icons-round">swap_horiz</span> Switch to Buyer</a></li><?php endif; ?>
                <li><a href="index.php?url=auth/logout"><span class="material-icons-round">logout</span> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">

        <header>
            <a href="index.php?url=vendor/orderDetails/<?php echo $order['ord_id']; ?>"
               style="text-decoration:none;color:var(--red-accent);font-weight:bold;display:flex;align-items:center;gap:5px;">
                <span class="material-icons-round">arrow_back</span> Back to Order Details
            </a>
            <div style="margin-top:15px;">
                <h1 class="headline">Edit Order #<?php echo str_pad($order['ord_id'], 4, '0', STR_PAD_LEFT); ?></h1>
                <p style="color:#666;font-size:14px;">Update delivery details and notes. Only editable while order is pending or confirmed.</p>
            </div>
        </header>

        <?php if (!empty($errors)): ?>
            <div style="background:#f8d7da;color:#721c24;padding:14px 18px;border-radius:8px;margin:20px 0;">
                <?php foreach ($errors as $e): ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="material-icons-round" style="font-size:16px;">error</span>
                        <?php echo htmlspecialchars($e); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?url=vendor/editOrder/<?php echo $order['ord_id']; ?>"
              style="max-width:640px;margin-top:24px;">

            <div class="card" style="padding:28px;">

                <!-- Delivery Method -->
                <div class="input-group" style="margin-bottom:20px;">
                    <label style="font-family:'Roboto Slab',serif;font-weight:bold;font-size:14px;display:block;margin-bottom:8px;">
                        Delivery Method
                    </label>
                    <div style="display:flex;gap:16px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                            <input type="radio" name="delivery_method" value="pickup"
                                   <?php echo $order['ord_delivery_method'] === 'pickup' ? 'checked' : ''; ?>
                                   onchange="toggleDeliveryAddr(this.value)">
                            Store Pickup
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                            <input type="radio" name="delivery_method" value="delivery"
                                   <?php echo $order['ord_delivery_method'] === 'delivery' ? 'checked' : ''; ?>
                                   onchange="toggleDeliveryAddr(this.value)">
                            Delivery
                        </label>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="input-group" id="delivery-addr-group"
                     style="margin-bottom:20px;<?php echo $order['ord_delivery_method'] !== 'delivery' ? 'display:none;' : ''; ?>">
                    <label style="font-family:'Roboto Slab',serif;font-weight:bold;font-size:14px;display:block;margin-bottom:8px;">
                        Delivery Address
                    </label>
                    <textarea name="delivery_address" rows="3"
                              style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;font-family:'Noto Serif',serif;font-size:14px;resize:vertical;"
                              placeholder="Enter full delivery address..."><?php echo htmlspecialchars($order['ord_delivery_address'] ?? ''); ?></textarea>
                </div>

                <!-- Pickup Time Slot -->
                <div class="input-group" style="margin-bottom:20px;">
                    <label style="font-family:'Roboto Slab',serif;font-weight:bold;font-size:14px;display:block;margin-bottom:8px;">
                        Preferred Pickup / Delivery Time
                    </label>
                    <input type="datetime-local" name="pickup_time_slot"
                           value="<?php echo !empty($order['ord_pickup_time_slot']) ? date('Y-m-d\TH:i', strtotime($order['ord_pickup_time_slot'])) : ''; ?>"
                           style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;font-family:'Noto Serif',serif;font-size:14px;">
                </div>

                <!-- Notes -->
                <div class="input-group" style="margin-bottom:28px;">
                    <label style="font-family:'Roboto Slab',serif;font-weight:bold;font-size:14px;display:block;margin-bottom:8px;">
                        Order Notes
                    </label>
                    <textarea name="notes" rows="3"
                              style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;font-family:'Noto Serif',serif;font-size:14px;resize:vertical;"
                              placeholder="Any special instructions or notes..."><?php echo htmlspecialchars($order['ord_notes'] ?? ''); ?></textarea>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn-primary" style="flex:1;padding:14px;font-size:14px;">
                        <span class="material-icons-round" style="vertical-align:middle;font-size:18px;">save</span>
                        Save Changes
                    </button>
                    <a href="index.php?url=vendor/orderDetails/<?php echo $order['ord_id']; ?>"
                       style="flex:1;padding:14px;text-align:center;border:1px solid #ddd;border-radius:8px;text-decoration:none;color:#555;font-family:'Roboto Slab',serif;font-weight:bold;font-size:14px;">
                        Cancel
                    </a>
                </div>

            </div>
        </form>

    </main>
</div>

<script src="assets/js/vendor-edit-order.js?v=<?= time(); ?>"></script>
</body>
</html>