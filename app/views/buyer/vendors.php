<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendors | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/screen.css?v=2">
    <link rel="stylesheet" href="assets/css/vendors.css?v=<?= time(); ?>">
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
                <a href="index.php?url=vendors" style="color: var(--green-accent);">Vendors</a>
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

    <div class="page-hero vendors-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Our Vendors</h1>
            <p>Browse local farms and stores connected to AgriLocal</p>
        </div>
    </div>

    <div class="container">
        <div class="vendors-layout">
            <aside class="filter-sidebar">
                <h3>Filters</h3>

                <div class="filter-group">
                    <h4>Search Vendor</h4>
                    <input type="text" id="vendorSearchFilter" placeholder="Search by vendor name..." style="width:100%; padding:10px; border:1px solid #ddd; border-radius:10px;" autocomplete="off">
                </div>

                <div class="filter-group">
                    <h4>Category</h4>
                    <label><input type="radio" name="vendorCategoryFilter" value="all" checked> All</label>
                    <label><input type="radio" name="vendorCategoryFilter" value="vegetables"> Vegetables</label>
                    <label><input type="radio" name="vendorCategoryFilter" value="fruits"> Fruits</label>
                    <label><input type="radio" name="vendorCategoryFilter" value="dairy"> Dairy</label>
                    <label><input type="radio" name="vendorCategoryFilter" value="herbs"> Herbs</label>
                </div>
            </aside>

            <div class="vendors-content">
                <div class="results-header">
                    <div class="results-count">
                        <span><span id="vendorCountText"><?= count($vendors ?? []) ?></span> vendors found</span>
                    </div>
                </div>

                <div class="vendors-grid" id="vendorsGrid">
                    <?php if (!empty($vendors)): ?>
                        <?php foreach ($vendors as $vendor): ?>
                            <?php $vendorId = (int)($vendor['id'] ?? 0); ?>
                            <div
                                class="vendor-card"
                                data-name="<?= htmlspecialchars(strtolower($vendor['name'] ?? '')) ?>"
                                data-products="<?= htmlspecialchars(strtolower($vendor['products'] ?? '')) ?>"
                                data-category="<?= htmlspecialchars(strtolower($vendor['category'] ?? 'vegetables')) ?>"
                            >
                                <img
                                    src="<?= htmlspecialchars(!empty($vendor['image']) ? $vendor['image'] : 'https://via.placeholder.com/600x300?text=Vendor'); ?>"
                                    alt="<?= htmlspecialchars($vendor['name']); ?>">

                                <div class="vendor-info">
                                    <h4><?= htmlspecialchars($vendor['name']); ?></h4>

                                    <div class="vendor-rating">
                                        <span class="material-icons" style="font-size:16px; color:#f5a623;">star</span>
                                        <span><?= number_format((float)($vendor['rating'] ?? 0), 1); ?> (<?= (int)($vendor['reviews'] ?? 0); ?> reviews)</span>
                                    </div>

                                    <p class="vendor-location">
                                        <span class="material-icons" style="font-size:16px;">location_on</span>
                                        <?= htmlspecialchars($vendor['address'] ?? 'No address available'); ?>
                                    </p>

                                    <p class="vendor-products">
                                        Products: <?= htmlspecialchars($vendor['products'] ?? 'No products listed yet'); ?>
                                    </p>

                                    <a
                                        href="index.php?url=vendors/store/<?= $vendorId; ?>"
                                        class="btn-secondary vendor-store-btn">
                                        View Store
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-vendors">
                            <h3 style="margin-bottom: 10px;">No vendors found</h3>
                            <p style="color:#666;">There are no vendors available yet.</p>
                        </div>
                    <?php endif; ?>
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
                    <li><a href="index.php?url=profile">Profile</a></li>
                    <li><a href="index.php?url=orders">Orders</a></li>
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
                    <li><a href="#">support@agrilocal.com</a></li>
                    <li><a href="#">+1 234 567 890</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 AgriLocal. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/screen.js"></script>
    <script src="assets/js/vendors.js?v=<?= time(); ?>"></script>
</body>
</html>