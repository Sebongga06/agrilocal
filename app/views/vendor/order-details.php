<?php
$pageTitle  = 'AgriLocal - Order #' . str_pad($order['ord_id'], 4, '0', STR_PAD_LEFT);
$statusMap  = [
    'pending'   => ['label' => 'Pending',    'step' => 1, 'color' => '#B7791F', 'bg' => '#FFF4E5'],
    'confirmed' => ['label' => 'Confirmed',  'step' => 2, 'color' => '#2B6CB0', 'bg' => '#EBF8FF'],
    'ready'     => ['label' => 'Ready',      'step' => 3, 'color' => '#276749', 'bg' => '#F0FFF4'],
    'picked_up' => ['label' => 'Picked Up',  'step' => 4, 'color' => '#4A5568', 'bg' => '#E2E8F0'],
    'completed' => ['label' => 'Completed',  'step' => 4, 'color' => '#4A5568', 'bg' => '#E2E8F0'],
    'cancelled' => ['label' => 'Cancelled',  'step' => 0, 'color' => '#C53030', 'bg' => '#FFF5F5'],
];
$status     = $order['ord_status'];
$statusInfo = $statusMap[$status] ?? $statusMap['pending'];
$isClosed   = in_array($status, ['completed', 'picked_up', 'cancelled']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/vendor-order-details.css?v=<?= time(); ?>">
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
                <span class="material-icons-round" style="font-size:18px;">check_circle</span> Order updated successfully.
            </div>
        <?php endif; ?>

        <header>
            <?php
            $fromTab  = htmlspecialchars($_GET['from'] ?? 'new');
            $validTabs = ['new', 'ready', 'completed', 'cancelled'];
            if (!in_array($fromTab, $validTabs)) $fromTab = 'new';
            ?>
            <a href="index.php?url=vendor/orders&tab=<?= $fromTab; ?>" style="text-decoration:none;color:var(--red-accent);font-weight:bold;display:flex;align-items:center;gap:5px;">
                <span class="material-icons-round">arrow_back</span> Back to Orders
            </a>
            <?php
            $isDueSoon = false;
            if (!empty($order['ord_pickup_time_slot']) && in_array($order['ord_status'], ['pending','confirmed'])) {
                $slotTime = strtotime($order['ord_pickup_time_slot']);
                $now      = time();
                $diff     = $slotTime - $now;
                $isDueSoon = $diff > 0 && $diff <= 86400; // within 24 hours
            }
            if ($isDueSoon):
            ?>
            <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:10px 16px;margin-top:12px;display:flex;align-items:center;gap:10px;font-size:14px;color:#856404;">
                <span class="material-icons-round" style="font-size:20px;color:#e65100;">schedule</span>
                <strong>Due Soon</strong> — Pickup/delivery scheduled for
                <strong><?php echo date('F d, Y \a\t h:i A', strtotime($order['ord_pickup_time_slot'])); ?></strong>.
                Please prepare this order.
            </div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:15px;">
                <div>
                    <h1 class="headline">Order #<?php echo str_pad($order['ord_id'], 4, '0', STR_PAD_LEFT); ?></h1>
                    <p style="color:#666;">Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['ord_dateCreated'])); ?></p>
                </div>
                <span class="badge" style="background:<?php echo $statusInfo['bg']; ?>;color:<?php echo $statusInfo['color']; ?>;padding:8px 15px;">
                    <?php echo $statusInfo['label']; ?>
                </span>
            </div>
        </header>

        <div class="details-grid">

            <!-- LEFT COLUMN -->
            <section>
                <div class="card">
                    <!-- Status timeline -->
                    <?php if ($status !== 'cancelled'): ?>
                    <div class="status-timeline">
                        <div class="status-line"></div>
                        <?php
                        $steps = [1 => 'Placed', 2 => 'Confirmed', 3 => 'Ready', 4 => 'Completed'];
                        foreach ($steps as $n => $label):
                            $cls = '';
                            if ($n < $statusInfo['step'])       $cls = 'done';
                            elseif ($n === $statusInfo['step'])  $cls = 'active';
                        ?>
                        <div class="status-step <?php echo $cls; ?>">
                            <span class="step-circle"><?php echo $n; ?></span>
                            <small><?php echo $label; ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <h3 class="headline" style="font-size:18px;margin-bottom:20px;">Items Ordered</h3>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left;border-bottom:1px solid #eee;">
                                <th style="padding:10px 0;">Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th style="text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item):
                            $images = json_decode($item['prd_images'] ?? '[]', true);
                            $imgSrc = !empty($images[0]) ? htmlspecialchars($images[0]) : null;
                        ?>
                            <tr style="border-bottom:1px solid #f5f5f5;">
                                <td style="padding:14px 0;">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <?php if ($imgSrc): ?>
                                            <img src="<?php echo $imgSrc; ?>" alt="product"
                                                 style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                                        <?php else: ?>
                                            <div style="width:40px;height:40px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                                <span class="material-icons-round" style="color:#ccc;font-size:18px;">image</span>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($item['prd_name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo $item['oit_quantity'] + 0; ?> <?php echo htmlspecialchars($item['prd_unit']); ?></td>
                                <td>₱<?php echo number_format($item['oit_unit_price'], 2); ?></td>
                                <td style="text-align:right;">₱<?php echo number_format($item['oit_subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (!empty($order['ord_notes'])): ?>
                    <div style="margin-top:20px;padding:14px;background:#f9f9f9;border-radius:8px;">
                        <span class="info-label">Customer Notes</span>
                        <p style="font-size:14px;color:#555;">"<?php echo htmlspecialchars($order['ord_notes']); ?>"</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action buttons — vendor can only mark as ready or cancel -->
                <?php if (!$isClosed): ?>
                <div class="action-row">
                    <?php if (in_array($status, ['pending', 'confirmed'])): ?>
                    <form method="POST" action="index.php?url=vendor/orderDetails/<?php echo $order['ord_id']; ?>" style="flex:1;">
                        <input type="hidden" name="action" value="mark_ready">
                        <input type="hidden" name="from_tab" value="<?= $fromTab; ?>">
                        <button type="submit" class="btn-primary" style="width:100%;padding:14px;font-size:14px;">
                            <span class="material-icons-round" style="vertical-align:middle;font-size:18px;">check_circle</span>
                            Mark as Ready
                        </button>
                    </form>
                    <button type="button" class="btn-cancel-order" onclick="document.getElementById('cancelModal').style.display='flex';">
                        <span class="material-icons-round" style="vertical-align:middle;font-size:18px;">cancel</span>
                        Cancel Order
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- RIGHT COLUMN -->
            <aside>
                <div class="card">
                    <h3 class="headline" style="font-size:18px;margin-bottom:15px;">Customer Info</h3>
                    <!-- Name -->
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                        <div style="width:40px;height:40px;border-radius:50%;background:var(--light-green);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span class="material-icons-round" style="font-size:20px;color:var(--dark-green);">person</span>
                        </div>
                        <div>
                            <div style="font-family:'Roboto Slab',serif;font-weight:700;color:var(--dark-green);">
                                <?php echo htmlspecialchars($order['user_fname'] . ' ' . $order['user_lname']); ?>
                            </div>
                            <div style="font-size:12px;color:#aaa;">Customer</div>
                        </div>
                    </div>
                    <!-- Contact details -->
                    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;">
                        <div style="display:flex;align-items:center;gap:8px;color:#555;">
                            <span class="material-icons-round" style="font-size:16px;color:var(--green-accent);">email</span>
                            <a href="mailto:<?php echo htmlspecialchars($order['user_email']); ?>" style="color:#555;text-decoration:none;">
                                <?php echo htmlspecialchars($order['user_email']); ?>
                            </a>
                        </div>
                        <?php if (!empty($order['user_phone'])): ?>
                        <div style="display:flex;align-items:center;gap:8px;color:#555;">
                            <span class="material-icons-round" style="font-size:16px;color:var(--green-accent);">phone</span>
                            <a href="tel:<?php echo htmlspecialchars($order['user_phone']); ?>" style="color:#555;text-decoration:none;">
                                <?php echo htmlspecialchars($order['user_phone']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['ord_delivery_address'])): ?>
                        <div style="display:flex;align-items:flex-start;gap:8px;color:#555;">
                            <span class="material-icons-round" style="font-size:16px;color:var(--green-accent);margin-top:1px;">location_on</span>
                            <span><?php echo htmlspecialchars($order['ord_delivery_address']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card" style="margin-top:0;">
                    <h3 class="headline" style="font-size:18px;margin-bottom:15px;">
                        <?php echo $order['ord_delivery_method'] === 'pickup' ? 'Pickup Details' : 'Delivery Details'; ?>
                    </h3>
                    <div>
                        <span class="info-label">Method</span>
                        <p style="font-weight:bold;"><?php echo ucfirst($order['ord_delivery_method']); ?></p>
                    </div>
                    <?php if (!empty($order['ord_pickup_time_slot'])): ?>
                    <div style="margin-top:14px;">
                        <span class="info-label">Preferred Time Slot</span>
                        <p style="font-weight:bold;"><?php echo date('F d, Y | h:i A', strtotime($order['ord_pickup_time_slot'])); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['ord_delivery_address'])): ?>
                    <div style="margin-top:14px;">
                        <span class="info-label">Delivery Address</span>
                        <p style="font-size:14px;"><?php echo htmlspecialchars($order['ord_delivery_address']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card" style="background:var(--dark-green);color:white;margin-top:0;">
                    <h3 style="font-family:'Roboto Slab';margin-bottom:15px;">Payment</h3>
                    <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:18px;margin-bottom:12px;">
                        <span>Total</span>
                        <span style="color:var(--green-accent);">₱<?php echo number_format($order['ord_total_amount'], 2); ?></span>
                    </div>
                    <hr style="border:none;border-top:1px solid rgba(255,255,255,0.2);margin:10px 0;">

                    <?php
                    $payMethod = $order['ord_payment_method'] ?? 'cash';
                    $payStatus = $order['ord_payment_status'] ?? 'unpaid';
                    $payRef    = $order['ord_payment_reference'] ?? '';
                    $payProof  = $order['ord_payment_proof'] ?? '';

                    $methodLabels = ['cash' => 'Cash on Delivery/Pickup', 'gcash' => 'GCash', 'maya' => 'Maya'];
                    $statusColors = [
                        'unpaid'               => '#fbd38d',
                        'pending_verification' => '#f6ad55',
                        'paid'                 => '#9ae6b4',
                        'rejected'             => '#feb2b2',
                        'refunded'             => '#feb2b2',
                        'pending'              => '#fbd38d',
                    ];
                    $statusLabels = [
                        'unpaid'               => 'Unpaid',
                        'pending_verification' => '⏳ Pending Verification',
                        'paid'                 => '✓ Paid',
                        'rejected'             => '✗ Rejected',
                        'refunded'             => 'Refunded',
                        'pending'              => 'Pending',
                    ];
                    $statusColor = $statusColors[$payStatus] ?? '#fbd38d';
                    $statusLabel = $statusLabels[$payStatus] ?? ucfirst($payStatus);
                    ?>

                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.65);">Method</span>
                        <span style="font-weight:600;"><?= htmlspecialchars($methodLabels[$payMethod] ?? ucfirst($payMethod)); ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                        <span style="color:rgba(255,255,255,0.65);">Status</span>
                        <span style="font-weight:700;color:<?= $statusColor; ?>;"><?= $statusLabel; ?></span>
                    </div>

                    <?php if ($payRef !== ''): ?>
                    <div style="margin-top:8px;">
                        <span style="font-size:12px;color:rgba(255,255,255,0.6);">Reference #</span>
                        <p style="font-size:13px;font-weight:600;word-break:break-all;margin:2px 0 0;"><?= htmlspecialchars($payRef); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($payProof !== ''): ?>
                    <div style="margin-top:12px;">
                        <span style="font-size:12px;color:rgba(255,255,255,0.6);">Proof of Payment</span>
                        <a href="<?= htmlspecialchars($payProof); ?>" target="_blank" style="display:block;margin-top:6px;">
                            <img src="<?= htmlspecialchars($payProof); ?>" alt="Proof of Payment"
                                 style="width:100%;max-height:180px;object-fit:contain;border-radius:8px;border:2px solid rgba(255,255,255,0.2);background:#fff;">
                        </a>
                        <p style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:4px;text-align:center;">Click image to view full size</p>
                    </div>
                    <?php endif; ?>

                    <?php if ($payStatus === 'pending_verification' && !$isClosed): ?>
                    <div style="margin-top:14px;display:flex;gap:8px;">
                        <form method="POST" action="index.php?url=vendor/orderDetails/<?= $order['ord_id']; ?>" style="flex:1;">
                            <input type="hidden" name="action" value="verify_payment">
                            <input type="hidden" name="from_tab" value="<?= $fromTab; ?>">
                            <button type="submit"
                                style="width:100%;padding:9px 0;background:#9ae6b4;color:#1a3c2e;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;">
                                <span class="material-icons-round" style="font-size:16px;">check_circle</span> Verify
                            </button>
                        </form>
                        <form method="POST" action="index.php?url=vendor/orderDetails/<?= $order['ord_id']; ?>" style="flex:1;">
                            <input type="hidden" name="action" value="reject_payment">
                            <input type="hidden" name="from_tab" value="<?= $fromTab; ?>">
                            <button type="submit"
                                style="width:100%;padding:9px 0;background:#feb2b2;color:#742a2a;border:none;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;">
                                <span class="material-icons-round" style="font-size:16px;">cancel</span> Reject
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>

        </div>
    </main>
</div>

<!-- Cancel confirmation modal -->
<div class="modal-overlay" id="cancelModal">
    <div class="modal-box">
        <div style="width:48px;height:48px;border-radius:50%;background:#fff3f3;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <span class="material-icons-round" style="color:var(--red-accent);font-size:24px;">cancel</span>
        </div>
        <h3>Cancel this order?</h3>
        <p>This will mark the order as cancelled. The customer will be notified. This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn-cancel" onclick="document.getElementById('cancelModal').style.display='none';">Go Back</button>
            <form method="POST" action="index.php?url=vendor/orderDetails/<?php echo $order['ord_id']; ?>" style="display:inline;">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="from_tab" value="<?= $fromTab ?? 'new'; ?>">
                <button type="submit" class="modal-btn-confirm">Yes, Cancel Order</button>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/vendor-order-details.js?v=<?= time(); ?>"></script>
</body>
</html>