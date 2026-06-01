<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/screen.css?v=2">
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

    <div class="container">
        <div class="cart-page">
            <h1>Your Cart</h1>

            <div class="cart-actions">
                <form method="POST" action="index.php?url=cart/clear">
                    <button class="clear-cart-btn" type="submit">
                        <span class="material-icons">delete_sweep</span> Clear Cart
                    </button>
                </form>
            </div>

            <div class="cart-two-column">
                <div class="cart-items-column">
                    <div class="cart-items-container">

                        <?php if (!empty($groupedItems)): ?>
                            <?php foreach ($groupedItems as $vendor): ?>
                                <div class="vendor-cart-card">
                                    <div class="vendor-cart-header">
                                        <h3><?= htmlspecialchars($vendor['vendor_name']); ?></h3>
                                        <a href="index.php?url=vendors/store/<?= $vendor['vendor_id']; ?>" class="vendor-link">
                                            View Store
                                            <span class="material-icons" style="font-size:16px;">arrow_forward</span>
                                        </a>
                                    </div>

                                    <div class="cart-table-wrapper">
                                        <table class="cart-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Price</th>
                                                    <th>Quantity</th>
                                                    <th>Subtotal</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($vendor['items'] as $item): ?>
                                                    <tr class="cart-item-row" data-item-id="<?= $item['cit_id']; ?>">
                                                        <td class="product-cell">
                                                            <img src="<?= htmlspecialchars($item['image']); ?>"
                                                                alt="<?= htmlspecialchars($item['prd_name']); ?>"
                                                                class="cart-item-img">
                                                            <div class="cart-item-details">
                                                                <h4><?= htmlspecialchars($item['prd_name']); ?></h4>
                                                                <p class="item-vendor"><?= htmlspecialchars($vendor['vendor_name']); ?></p>
                                                                <p style="font-size: 12px; color: #777;">
                                                                    Unit: <?= htmlspecialchars($item['prd_unit']); ?>
                                                                </p>
                                                            </div>
                                                        </td>

                                                        <td class="price-cell">
                                                            ₱<?= number_format($item['cit_unit_price'], 2); ?>
                                                        </td>

                                                        <td class="quantity-cell">
                                                            <div class="cart-quantity-control">
                                                                <input type="number"
                                                                       class="cart-qty-input cart-qty-autosave"
                                                                       value="<?= (int)$item['cit_quantity']; ?>"
                                                                       min="1"
                                                                       data-cart-item-id="<?= $item['cit_id']; ?>"
                                                                       data-unit-price="<?= (float)$item['cit_unit_price']; ?>">
                                                            </div>
                                                        </td>

                                                        <td class="subtotal-cell" id="subtotal-<?= $item['cit_id']; ?>">
                                                            ₱<?= number_format($item['subtotal'], 2); ?>
                                                        </td>

                                                        <td class="action-cell">
                                                            <button type="button"
                                                                class="delete-item-btn"
                                                                onclick="removeCartItem(<?= $item['cit_id']; ?>, this)"
                                                                title="Remove item">
                                                                <span class="material-icons">delete_outline</span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="vendor-cart-footer">
                                        <span class="vendor-subtotal">
                                            Vendor Subtotal:
                                            <strong id="vendor-subtotal-<?= $vendor['vendor_id']; ?>">₱<?= number_format($vendor['subtotal'], 2); ?></strong>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="vendor-cart-card">
                                <div class="vendor-cart-header">
                                    <h3>Your cart is empty</h3>
                                </div>
                                <div style="padding: 20px;">
                                    <a href="index.php?url=products" class="btn-primary">Browse Products</a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="bill-details-column">
                    <div class="bill-details-card">
                        <h3>Bill Details</h3>

                        <div class="bill-row">
                            <span>Items total:</span>
                            <span id="bill-items-total">₱<?= number_format($summary['items_total'] ?? 0, 2); ?></span>
                        </div>

                        <div class="bill-row">
                            <span>Delivery charge:</span>
                            <span id="bill-delivery-charge" style="color:#888;font-size:.85rem;">Set at checkout</span>
                        </div>

                        <div class="bill-row">
                            <span>Handling charge:</span>
                            <span id="bill-handling-charge">₱<?= number_format($summary['handling_charge'] ?? 0, 2); ?></span>
                        </div>

                        <div class="bill-row grand-total">
                            <span>Estimated total:</span>
                            <span id="bill-grand-total">₱<?= number_format($summary['items_total'] ?? 0, 2); ?></span>
                        </div>

                        <p style="font-size:.75rem;color:#aaa;margin-bottom:.75rem;line-height:1.5;">
                            <span class="material-icons" style="font-size:13px;vertical-align:middle;">info</span>
                            Delivery charge is calculated based on distance at checkout.
                        </p>

                        <a href="index.php?url=cart/checkout" class="btn-primary proceed-checkout-btn">
                            Proceed to Checkout <span class="material-icons">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/screen.js"></script>
    <script src="assets/js/cart.js?v=<?= time(); ?>"></script>
</body>
</html>