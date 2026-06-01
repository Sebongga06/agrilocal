<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/screen.css?v=2">
    <link rel="stylesheet" href="assets/css/order.css?v=<?= time(); ?>">
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
                        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'buyer'): ?>
                        <?php if (!empty($_SESSION['user']['has_vendor'])): ?>
                        <button type="button" onclick="openSwitchVendorModal()">Switch to Vendor</button>
                        <?php else: ?>
                        <a href="index.php?url=farmer">Become a Vendor</a>
                        <?php endif; ?>
                        <?php endif; ?>
                        <button type="button" onclick="logoutUser()">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="padding-top:2rem; padding-bottom:3rem;">
        <div style="max-width:1100px; margin:0 auto;">
            <h1 style="font-family:'Roboto Slab',serif; color:var(--dark-green,#1a3c2e); margin-bottom:1.5rem;">My Orders</h1>

            <?php if (!empty($flash)): ?>
                <div class="flash-msg flash-<?= htmlspecialchars($flash['type']); ?>">
                    <?= htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <?php
                        $status     = strtolower($order['ord_status'] ?? 'pending');
                        $orderId    = (int)$order['ord_id'];
                        $canCancel  = in_array($status, ['pending', 'confirmed'], true);
                        $canReceive = $status === 'ready';
                        $canReview  = $status === 'completed' && !in_array($orderId, $reviewedOrders ?? []);
                        $items      = $order['items'] ?? [];
                    ?>
                    <div class="order-card">
                        <div class="order-card-header" onclick="toggleItems(<?= $orderId; ?>)">
                            <div class="order-meta">
                                <h3>Order #<?= $orderId; ?></h3>
                                <p>Vendor: <?= htmlspecialchars($order['vnd_farm_name'] ?? 'Unknown'); ?></p>
                                <p>Date: <?= !empty($order['ord_dateCreated']) ? date('F j, Y g:i A', strtotime($order['ord_dateCreated'])) : 'N/A'; ?></p>
                                <p><?= (int)($order['item_count'] ?? 0); ?> item(s) &nbsp;
                                    <button class="btn-expand" id="expand-<?= $orderId; ?>">
                                        <span class="material-icons">expand_more</span> View items
                                    </button>
                                </p>
                            </div>

                            <div class="order-right">
                                <div class="order-amount">₱<?= number_format((float)($order['ord_total_amount'] ?? 0), 2); ?></div>
                                <span class="status-badge status-<?= htmlspecialchars($status); ?>">
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))); ?>
                                </span>

                                <div class="order-actions" onclick="event.stopPropagation()">
                                    <?php if ($canCancel): ?>
                                        <a href="index.php?url=orders/edit/<?= $orderId; ?>" class="btn-cancel"
                                           style="text-decoration:none; display:inline-flex; align-items:center; gap:4px;"
                                           onclick="event.stopPropagation()">
                                            <span class="material-icons" style="font-size:13px;">edit</span> Edit
                                        </a>
                                        <button type="button" class="btn-cancel"
                                            onclick="confirmAction('cancel', <?= $orderId; ?>, 'Cancel order #<?= $orderId; ?>?', 'This order will be cancelled.')">
                                            Cancel Order
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canReceive): ?>
                                        <button type="button" class="btn-received"
                                            onclick="confirmAction('received', <?= $orderId; ?>, 'Mark as Received?', 'Confirm you have received order #<?= $orderId; ?>.')">
                                            <span class="material-icons" style="font-size:14px;vertical-align:middle;">check_circle</span>
                                            Mark as Received
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($canReview): ?>
                                        <button type="button" class="btn-review"
                                            onclick="openReviewModal(<?= $orderId; ?>, <?= (int)$order['ord_vendor_id']; ?>, '<?= htmlspecialchars(addslashes($order['vnd_farm_name'] ?? 'Vendor'), ENT_QUOTES); ?>')">
                                            <span class="material-icons" style="font-size:14px;vertical-align:middle;">star_rate</span>
                                            Rate &amp; Review
                                        </button>
                                    <?php elseif ($status === 'completed' && in_array($orderId, $reviewedOrders ?? [])): ?>
                                        <span class="btn-reviewed">
                                            <span class="material-icons" style="font-size:14px;vertical-align:middle;">check</span>
                                            Reviewed
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="order-items-panel" id="items-<?= $orderId; ?>">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <div class="order-item-row">
                                        <span class="order-item-name">
                                            <?= htmlspecialchars($item['prd_name']); ?>
                                            <span style="color:#888; font-weight:400;">× <?= (int)$item['oit_quantity']; ?> <?= htmlspecialchars($item['prd_unit']); ?></span>
                                        </span>
                                        <span class="order-item-price">₱<?= number_format((float)$item['oit_subtotal'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <div class="order-item-row" style="font-weight:700; color:#1a3c2e;">
                                    <span>Total</span>
                                    <span>₱<?= number_format((float)($order['ord_total_amount'] ?? 0), 2); ?></span>
                                </div>
                            <?php else: ?>
                                <p style="color:#888; font-size:.88rem;">No item details available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background:#fff; border:1px solid #e7e7e7; border-radius:16px; padding:2rem; text-align:center;">
                    <h3 style="margin:0 0 .75rem; font-family:'Roboto Slab',serif; color:var(--dark-green,#1a3c2e);">No orders yet</h3>
                    <p style="margin:0 0 1rem; color:#666;">You have not placed any orders yet.</p>
                    <a href="index.php?url=products" class="btn-primary" style="display:inline-flex; text-decoration:none;">
                        <span class="material-icons">storefront</span> Browse Products
                    </a>
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
        </div>
        <div class="footer-bottom"><p>&copy; 2026 AgriLocal</p></div>
    </footer>

    <!-- Confirmation Modal -->
    <div class="confirm-backdrop" id="confirmModalBackdrop">
        <div class="confirm-modal">
            <h4 id="confirmTitle">Are you sure?</h4>
            <p id="confirmMessage"></p>
            <div class="confirm-btns">
                <button type="button" class="btn-no"  id="confirmCancelBtn">No, go back</button>
                <button type="button" class="btn-ok"  id="confirmOkBtn">Yes, confirm</button>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="confirm-backdrop" id="reviewModalBackdrop">
        <div class="confirm-modal review-modal">
            <h4 id="reviewModalTitle">Rate &amp; Review</h4>
            <p id="reviewModalSubtitle" style="margin-bottom:1rem;"></p>
            <form id="reviewForm" method="POST" action="">
                <input type="hidden" name="vendor_id" id="reviewVendorId">

                <!-- Star rating -->
                <div class="star-rating" id="starRating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i; ?>" value="<?= $i; ?>" required>
                        <label for="star<?= $i; ?>" title="<?= $i; ?> star<?= $i > 1 ? 's' : ''; ?>">
                            <span class="material-icons">star</span>
                        </label>
                    <?php endfor; ?>
                </div>

                <input type="text" name="title" id="reviewTitle" placeholder="Review title (optional)"
                    maxlength="100" style="width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1px solid #ddd;border-radius:8px;font-size:.9rem;margin-bottom:.75rem;">

                <textarea name="comment" id="reviewComment" placeholder="Share your experience (optional)"
                    rows="3" maxlength="1000"
                    style="width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1px solid #ddd;border-radius:8px;font-size:.9rem;resize:vertical;margin-bottom:1.25rem;"></textarea>

                <div class="confirm-btns">
                    <button type="button" class="btn-no" id="reviewCancelBtn">Cancel</button>
                    <button type="submit" class="btn-ok" id="reviewSubmitBtn">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/screen.js"></script>
    <script src="assets/js/order.js?v=<?= time(); ?>"></script>
</body>
</html>
