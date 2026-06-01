<?php $pageTitle = 'AgriLocal - Orders'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/vendor-orders.css?v=<?= time(); ?>">
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

        <?php if (isset($_GET['updated'])): ?>
            <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                <span class="material-icons-round" style="font-size:18px;">check_circle</span> Order status updated.
            </div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                <span class="material-icons-round" style="font-size:18px;">delete</span> Order permanently deleted.
            </div>
        <?php endif; ?>

        <header style="margin-bottom: 28px;">
            <h1 class="headline">Order Management</h1>
            <p style="color: #666; font-size:14px;">Track and fulfill incoming customer purchases.</p>
        </header>

        <!-- Tabs -->
        <div class="tabs">
            <a href="index.php?url=vendor/orders&tab=new"
               class="tab-item <?php echo $tab === 'new' ? 'active' : ''; ?>">
                New Orders
                <span class="tab-badge"><?php echo (int)($counts['new_count'] ?? 0); ?></span>
            </a>
            <a href="index.php?url=vendor/orders&tab=ready"
               class="tab-item <?php echo $tab === 'ready' ? 'active' : ''; ?>">
                Ready for Pickup
                <span class="tab-badge"><?php echo (int)($counts['ready_count'] ?? 0); ?></span>
            </a>
            <a href="index.php?url=vendor/orders&tab=completed"
               class="tab-item <?php echo $tab === 'completed' ? 'active' : ''; ?>">
                Completed
                <span class="tab-badge"><?php echo (int)($counts['completed_count'] ?? 0); ?></span>
            </a>
            <a href="index.php?url=vendor/orders&tab=cancelled"
               class="tab-item <?php echo $tab === 'cancelled' ? 'active' : ''; ?>">
                Cancelled
                <span class="tab-badge"><?php echo (int)($counts['cancelled_count'] ?? 0); ?></span>
            </a>
        </div>

        <!-- Orders list -->
        <?php if (empty($orders)): ?>
            <div class="card" style="text-align:center;padding:50px;color:#888;">
                <span class="material-icons-round" style="font-size:48px;color:#ccc;">inbox</span>
                <p style="margin-top:12px;">No orders in this category yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $o):
                $statusClass = 'status-badge-' . $o['ord_status'];
                $statusLabel = ucfirst(str_replace('_', ' ', $o['ord_status']));
                $date        = date('M d', strtotime($o['ord_dateCreated']));
            ?>
            <div class="card order-card">
                <div style="text-align:center;">
                    <span style="font-size:11px;color:#888;">#ORD-<?php echo str_pad($o['ord_id'], 4, '0', STR_PAD_LEFT); ?></span>
                    <h4 style="margin-top:4px;color:var(--dark-green);font-size:15px;"><?php echo strtoupper($date); ?></h4>
                </div>
                <div>
                    <h3 style="font-size:15px;"><?php echo htmlspecialchars($o['user_fname'] . ' ' . $o['user_lname']); ?></h3>
                    <p style="font-size:13px;color:#666;">
                        <?php echo (int)$o['item_count']; ?> item<?php echo $o['item_count'] != 1 ? 's' : ''; ?>
                        &bull; <?php echo ucfirst($o['ord_delivery_method']); ?>
                    </p>
                </div>
                <div>
                    <p style="font-size:12px;color:#888;">Total Amount</p>
                    <p style="font-weight:bold;color:var(--red-accent);">₱<?php echo number_format($o['ord_total_amount'], 2); ?></p>
                </div>
                <div>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                </div>
                <div style="text-align:right;">
                    <a href="index.php?url=vendor/orderDetails/<?php echo $o['ord_id']; ?>&from=<?php echo urlencode($tab); ?>"
                       class="btn-primary"
                       style="padding:8px 15px;font-size:12px;text-decoration:none;">
                        View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>
</body>
</html>