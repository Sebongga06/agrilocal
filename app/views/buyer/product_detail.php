<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['prd_name']); ?> | AgriLocal</title>
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
        <div class="product-detail-page">
            <a href="index.php?url=products" class="back-button">
                <span class="material-icons">arrow_back</span> Back to Products
            </a>

            <div class="product-detail-two-column">
                <div class="product-detail-left">
                    <div class="product-main-image">
                        <img src="<?= htmlspecialchars($product['image']); ?>"
                             alt="<?= htmlspecialchars($product['prd_name']); ?>">
                    </div>

        <div class="contact-seller-card">
                        <h3>Visit Vendor Store</h3>
                        <a href="index.php?url=vendors/store/<?= (int)$product['vnd_id']; ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;margin-top:.5rem;">
                            <span class="material-icons">storefront</span> Go to Vendor Store
                        </a>
                    </div>
                </div>

                <div class="product-detail-right">
                    <div class="product-info-card">
                        <h1><?= htmlspecialchars($product['prd_name']); ?></h1>

                        <div class="product-price-section">
                            <span class="product-price">₱<?= number_format($product['prd_price'], 2); ?></span>
                            <span class="product-unit">/ <?= htmlspecialchars($product['prd_unit']); ?></span>
                        </div>

                        <form id="addToCartForm" action="index.php?url=cart/add">
                            <input type="hidden" name="product_id" value="<?= $product['prd_id']; ?>">

                            <div class="quantity-add-to-cart-row">
                                <div class="quantity-section">
                                    <div class="product-quantity-control">
                                        <button class="qty-btn minus" type="button" onclick="changeQty(-1)">-</button>
                                        <input type="number"
                                               name="quantity"
                                               value="1"
                                               min="1"
                                               max="10"
                                               class="qty-input"
                                               id="productQty">
                                        <button class="qty-btn plus" type="button" onclick="changeQty(1)">+</button>
                                    </div>
                                </div>

                                <button type="submit" class="btn-primary add-to-cart-btn-product" id="addToCartBtn">
                                    <span class="material-icons">shopping_cart</span> ADD TO CART
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="index.php?url=favorites/toggle" style="margin-top:.75rem;">
                            <input type="hidden" name="product_id" value="<?= $product['prd_id']; ?>">
                            <button type="submit" class="btn-secondary" style="width:100%; justify-content:center;">
                                <span class="material-icons">favorite_border</span> Save to Favorites
                            </button>
                        </form>
                        <div class="product-description-section">
                            <h3>Product Description</h3>
                            <p>
                                <?= nl2br(htmlspecialchars($product['prd_description'] ?: 'No description available.')); ?>
                            </p>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-column">
                <h3 class="footer-logo">
                    <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
                    AgriLocal
                </h3>
                <p class="footer-tagline">Supporting local agriculture, one harvest at a time.</p>
            </div>

            <div class="footer-column">
                <h4>Account</h4>
                <ul class="footer-links">
                    <li><a href="#">Profile</a></li>
                    <li><a href="#">Orders</a></li>
                    <li><a href="#">Addresses</a></li>
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
                    <li><a href="mailto:support@agrilocal.com">support@agrilocal.com</a></li>
                    <li><a href="tel:+1234567890">+1 234 567 890</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 AgriLocal. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function openSwitchVendorModal() { window.location.href = 'index.php?url=home'; }

        function changeQty(change) {
            const qtyInput = document.getElementById('productQty');
            let current = parseInt(qtyInput.value) || 1;
            current += change;
            if (current < 1)  current = 1;
            if (current > 10) current = 10;
            qtyInput.value = current;
        }

        document.getElementById('addToCartForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('addToCartBtn');
            btn.disabled = true;

            const fd = new FormData(this);
            try {
                const res  = await fetch('index.php?url=cart/add', {
                    method: 'POST', body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    showNotification('<?= htmlspecialchars($product['prd_name']); ?> added to cart!', 'success');
                } else {
                    showNotification(data.message || 'Failed to add to cart', 'error');
                }
            } catch (err) {
                showNotification('Something went wrong. Please try again.', 'error');
            } finally {
                btn.disabled = false;
            }
        });
    </script>

    <script src="assets/js/screen.js"></script>
</body>

</html>