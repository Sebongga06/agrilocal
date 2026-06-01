<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/screen.css?v=2">
    <link rel="stylesheet" href="assets/css/order-confirmation.css?v=<?= time(); ?>">
</head>
<body>

    <div id="notification" class="notification"></div>

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
            <div class="search-bar">
                <span class="material-icons">search</span>
                <input type="text" placeholder="Search vendors or products" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="search_query" readonly onfocus="this.removeAttribute('readonly')">
            </div>
            <div class="nav-icons">
                <a href="index.php?url=favorites" class="nav-icon-item">
                <span class="material-icons">favorite_border</span>
                <span class="icon-label">Favorites</span>
                </a>
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

    <div class="container">
        <div class="confirmation-page">

            <?php if (!empty($orders)): ?>

                <div class="confirm-hero">
                    <div class="check-icon"><span class="material-icons">check</span></div>
                    <h1>Order Placed Successfully!</h1>
                    <p>Thank you for your order. We've notified the vendor(s) and your order is now pending confirmation.</p>
                </div>

                <?php foreach ($orders as $order): ?>
                    <?php
                        $orderId  = (int)$order['ord_id'];
                        $items    = $order['items'] ?? [];
                        $total    = (float)($order['ord_total_amount'] ?? 0);
                        $method   = ucfirst($order['ord_delivery_method'] ?? 'pickup');
                        $date     = !empty($order['ord_dateCreated'])
                                    ? date('F j, Y g:i A', strtotime($order['ord_dateCreated']))
                                    : 'N/A';
                        $vendor   = htmlspecialchars($order['vnd_farm_name'] ?? 'Vendor');
                        $address  = htmlspecialchars($order['vnd_address'] ?? '');
                        $notes    = htmlspecialchars($order['ord_notes'] ?? '');
                        $pickup   = !empty($order['ord_pickup_time_slot'])
                                    ? date('F j, Y g:i A', strtotime($order['ord_pickup_time_slot']))
                                    : 'Not specified';
                    ?>
                    <div class="order-block">
                        <div class="order-block-header">
                            <div>
                                <h3>Order #<?= $orderId; ?></h3>
                                <div class="meta"><?= $vendor; ?> &nbsp;·&nbsp; <?= $date; ?></div>
                            </div>
                            <span class="status-pill">Pending</span>
                        </div>

                        <div class="order-block-body">

                            <!-- Timeline -->
                            <div class="timeline">
                                <div class="tl-step done">
                                    <div class="tl-dot"></div>
                                    <div class="tl-label">Order Placed</div>
                                </div>
                                <div class="tl-step active">
                                    <div class="tl-dot"></div>
                                    <div class="tl-label">Pending</div>
                                </div>
                                <div class="tl-step">
                                    <div class="tl-dot"></div>
                                    <div class="tl-label">Confirmed</div>
                                </div>
                                <div class="tl-step">
                                    <div class="tl-dot"></div>
                                    <div class="tl-label">Ready</div>
                                </div>
                                <div class="tl-step">
                                    <div class="tl-dot"></div>
                                    <div class="tl-label">Completed</div>
                                </div>
                            </div>

                            <!-- Items -->
                            <?php foreach ($items as $item): ?>
                                <div class="confirm-item-row">
                                    <div>
                                        <div class="confirm-item-name"><?= htmlspecialchars($item['prd_name']); ?></div>
                                        <div class="confirm-item-qty">
                                            <?= (int)$item['oit_quantity']; ?> <?= htmlspecialchars($item['prd_unit']); ?>
                                            × ₱<?= number_format((float)$item['oit_unit_price'], 2); ?>
                                        </div>
                                    </div>
                                    <div class="confirm-item-price">₱<?= number_format((float)$item['oit_subtotal'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Totals -->
                            <div class="order-totals">
                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <span>₱<?= number_format($total, 2); ?></span>
                                </div>
                                <div class="total-row grand">
                                    <span>Total</span>
                                    <span>₱<?= number_format($total, 2); ?></span>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div class="order-meta-grid">
                                <div>
                                    <div class="meta-label">Delivery Method</div>
                                    <div class="meta-value"><?= $method; ?></div>
                                </div>
                                <div>
                                    <div class="meta-label"><?= $method === 'Pickup' ? 'Pickup Time' : 'Preferred Time'; ?></div>
                                    <div class="meta-value"><?= $pickup; ?></div>
                                </div>
                                <div>
                                    <div class="meta-label">Vendor</div>
                                    <div class="meta-value"><?= $vendor; ?></div>
                                </div>
                                <div>
                                    <div class="meta-label">Vendor Address</div>
                                    <div class="meta-value"><?= $address ?: 'N/A'; ?></div>
                                </div>
                                <?php if ($method === 'Delivery' && !empty($order['ord_delivery_address'])): ?>
                                <div style="grid-column:1/-1;">
                                    <div class="meta-label">Delivery Address</div>
                                    <div class="meta-value"><?= htmlspecialchars($order['ord_delivery_address']); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($notes): ?>
                                <div style="grid-column:1/-1;">
                                    <div class="meta-label">Notes</div>
                                    <div class="meta-value"><?= $notes; ?></div>
                                </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="confirm-actions">
                    <a href="index.php?url=orders" class="btn-secondary">View My Orders</a>
                    <a href="index.php?url=products" class="btn-primary">Continue Shopping</a>
                </div>

            <?php else: ?>
                <div style="text-align:center; padding:3rem 1rem;">
                    <h2 style="font-family:'Roboto Slab',serif; color:#1a3c2e;">No order details found.</h2>
                    <p style="color:#666; margin:.75rem 0 1.5rem;">Your order may have already been confirmed.</p>
                    <a href="index.php?url=orders" class="btn-primary" style="display:inline-flex; text-decoration:none;">View My Orders</a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3 class="footer-logo"><img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;"> AgriLocal</h3>
                <p class="footer-tagline">Supporting local agriculture, one harvest at a time.</p>
            </div>
            <div class="footer-column">
                <h4>Account</h4>
                <ul class="footer-links">
                    <li><a href="index.php?url=profile">Profile</a></li>
                    <li><a href="index.php?url=orders">Orders</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Useful Links</h4>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Help Center</h4>
                <ul class="footer-links">
                    <li><a href="#">support@agrilocal.com</a></li>
                    <li><a href="#">+1 234 567 890</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom"><p>&copy; 2026 AgriLocal. All rights reserved.</p></div>
    </footer>

    <script src="assets/js/screen.js"></script>
</body>
</html>
