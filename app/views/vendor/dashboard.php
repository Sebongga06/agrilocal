<?php $pageTitle = 'AgriLocal - Vendor Dashboard'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/vendor-dashboard.css?v=<?= time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <a class="sidebar-logo" href="index.php?url=vendor/dashboard">
                <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
                <span class="logo-text">AgriLocal</span>
            </a>
            <nav class="nav-menu">
                <p class="menu-label">Main Menu</p>
                <ul>
                    <li class="active"><a href="index.php?url=vendor/dashboard"><span class="material-icons-round">dashboard</span> Dashboard</a></li>
                    <li><a href="index.php?url=vendor/inventory"><span class="material-icons-round">inventory_2</span> Inventory</a></li>
                    <li><a href="index.php?url=vendor/orders" style="position:relative;">
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

        <main class="main-content">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1 class="headline">Dashboard Overview</h1>
                <div class="search-container">
                    <span class="material-icons-round" style="color: #999;">search</span>
                    <input type="text" placeholder="Search orders...">
                </div>
            </header>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div class="card" style="padding: 20px;">
                    <p style="font-size: 14px; color: #888;">Today's Sales</p>
                    <h2 style="margin: 10px 0;">₱<?php echo number_format($todaySales, 2); ?></h2>
                    <span style="color: #888; font-size: 12px;">Orders placed today</span>
                </div>
                <div class="card" style="padding: 20px;">
                    <p style="font-size: 14px; color: #888;">Pending Orders</p>
                    <h2 style="margin: 10px 0;"><?php echo str_pad((int)($counts['new_count'] ?? 0), 2, '0', STR_PAD_LEFT); ?></h2>
                    <span style="color: var(--red-accent); font-size: 12px; font-weight: bold;">
                        <?php echo (int)($counts['ready_count'] ?? 0); ?> Ready for Pickup
                    </span>
                </div>
                <div class="card" style="padding: 20px;">
                    <p style="font-size: 14px; color: #888;">Total Products</p>
                    <h2 style="margin: 10px 0;"><?php echo str_pad((int)($stats['total'] ?? 0), 2, '0', STR_PAD_LEFT); ?></h2>
                    <span style="color: #888; font-size: 12px;"><?php echo (int)($stats['low_stock'] ?? 0); ?> low stock</span>
                </div>
            </div>

            <div class="card" style="margin-top: 25px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 class="headline" style="font-size: 18px;">Sales Analytics</h3>
                    <select style="padding: 5px 15px; border-radius: 5px; border: 1px solid #eee; font-family: inherit;">
                        <option>Weekly</option>
                        <option>Monthly</option>
                    </select>
                </div>
                <div style="height: 200px; background: linear-gradient(to top, rgba(162, 187, 137, 0.1) 0%, transparent 100%); border-bottom: 2px solid var(--dark-green); position: relative; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                    <p style="color: var(--dark-green); font-style: italic; opacity: 0.5; font-size: 14px;">[ Sales Trend Graph Visual ]</p>
                </div>
            </div>

            <div class="card" style="margin-top: 25px; padding: 20px;">
                <h3 class="headline" style="font-size: 18px; margin-bottom: 15px;">Recent Transactions</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 1px solid #eee; color: #888; font-size: 13px;">
                            <th style="padding-bottom: 10px;">Order ID</th>
                            <th style="padding-bottom: 10px;">Customer</th>
                            <th style="padding-bottom: 10px;">Amount</th>
                            <th style="padding-bottom: 10px;">Status</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 14px;">
                        <?php if (empty($recent)): ?>
                            <tr>
                                <td colspan="4" style="padding:30px 0;text-align:center;color:#aaa;">No orders yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent as $r):
                                $statusColors = [
                                    'pending'   => ['bg' => '#FFF4E5', 'color' => '#B7791F', 'label' => 'Pending'],
                                    'confirmed' => ['bg' => '#EBF8FF', 'color' => '#2B6CB0', 'label' => 'Confirmed'],
                                    'ready'     => ['bg' => '#F0FFF4', 'color' => '#276749', 'label' => 'Ready'],
                                    'picked_up' => ['bg' => '#E2E8F0', 'color' => '#4A5568', 'label' => 'Picked Up'],
                                    'completed' => ['bg' => '#E2E8F0', 'color' => '#4A5568', 'label' => 'Completed'],
                                    'cancelled' => ['bg' => '#FFF5F5', 'color' => '#C53030', 'label' => 'Cancelled'],
                                ];
                                $sc = $statusColors[$r['ord_status']] ?? $statusColors['pending'];
                            ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 14px 0;">
                                    <a href="index.php?url=vendor/orderDetails/<?php echo $r['ord_id']; ?>"
                                       style="text-decoration:none;color:var(--dark-green);font-weight:bold;">
                                        #ORD-<?php echo str_pad($r['ord_id'], 4, '0', STR_PAD_LEFT); ?>
                                    </a>
                                </td>
                                <td><strong><?php echo htmlspecialchars($r['user_fname'] . ' ' . substr($r['user_lname'], 0, 1) . '.'); ?></strong></td>
                                <td>₱<?php echo number_format($r['ord_total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge" style="background:<?php echo $sc['bg']; ?>;color:<?php echo $sc['color']; ?>;">
                                        <?php echo $sc['label']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if (!empty($recent)): ?>
                <div style="text-align:right;margin-top:14px;">
                    <a href="index.php?url=vendor/orders" style="font-size:13px;color:var(--green-accent);text-decoration:none;font-weight:bold;">
                        View all orders →
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>

        <aside style="width: 300px; padding: 40px 24px; background: #fafafa; border-left: 1px solid #eee;">
            <div class="card" style="text-align: center; padding: 25px;">
                <div class="vendor-avatar">
                    <span class="material-icons-round" style="font-size: 40px;">eco</span>
                </div>
                <h3 style="font-family: 'Roboto Slab'; margin-bottom: 5px;"><?php echo htmlspecialchars($_SESSION['vendor_farm_name'] ?? 'My Farm'); ?></h3>
                <p style="font-size: 12px; color: #888; margin-bottom: 20px;">Verified Vendor</p>
                <a href="index.php?url=vendor/profile" class="btn-primary" style="text-decoration: none; display: block; width: 100%; font-size: 14px; padding: 10px 0;">View Store</a>
            </div>

            <div class="card" style="background: var(--light-green); margin-top: 20px; padding: 20px; border: none;">
                <h4 style="color: var(--dark-green); font-family: 'Roboto Slab'; display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-round" style="font-size: 18px;">lightbulb</span> Vendor Tips
                </h4>
                <p style="font-size: 13px; margin-top: 10px; color: #444; line-height: 1.4;">Keep your stock updated to stay at the top of customer searches!</p>
                <a href="#" style="display: block; margin-top: 15px; color: var(--dark-red); font-weight: bold; text-decoration: none; font-size: 12px;">Learn More →</a>
            </div>

            <div class="card" style="margin-top: 20px; padding: 20px;">
                <h4 style="font-family: 'Roboto Slab'; margin-bottom: 15px;">Quick Actions</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 12px;">
                        <a href="index.php?url=vendor/addProduct" style="text-decoration: none; color: var(--green-accent); font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <span class="material-icons-round" style="font-size: 18px;">add_circle</span> Add New Product
                        </a>
                    </li>
                    <li>
                        <a href="index.php?url=vendor/profile" style="text-decoration: none; color: var(--green-accent); font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <span class="material-icons-round" style="font-size: 18px;">settings</span> Edit Schedule
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</body>
</html>