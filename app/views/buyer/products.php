<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/screen.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/products.css?v=<?= time(); ?>">
</head>

<body>

    <div id="notification" class="notification"></div>

    <?php
    $filters = $filters ?? [];
    $selectedCategories = $filters['categories'] ?? [];
    $maxPrice = $filters['max_price'] ?? 5000;
    $sort = $filters['sort'] ?? 'relevance';
    $inStock = !empty($filters['in_stock']);
    $inSeason = !empty($filters['in_season']);
    ?>

    <nav class="main-nav">
        <div class="nav-container">
            <div class="logo">
                <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
                <a href="index.php?url=home">AgriLocal</a>
            </div>

            <div class="nav-links">
                <a href="index.php?url=home">Home</a>
                <a href="index.php?url=products" style="color: var(--green-accent);">Products</a>
                <a href="index.php?url=vendors">Vendors</a>
            </div>

            <div class="search-bar">
                <span class="material-icons" id="navSearchIcon" style="cursor:pointer;">search</span>
                <input type="text" id="navSearchInput"
                       placeholder="Search products, vendors…"
                       value="<?= htmlspecialchars($search ?? ''); ?>"
                       autocomplete="off">
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

    <div class="page-hero products-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>All Products</h1>
            <p>Browse fresh produce from local farmers</p>
        </div>
    </div>

    <div class="container">
        <div class="products-layout">
            <form class="filter-sidebar" method="GET" action="index.php" id="filterForm">
                <input type="hidden" name="url" value="products">
                <input type="hidden" name="search" id="filterSearchInput" value="<?= htmlspecialchars($search ?? ''); ?>">

                <h3>Filters</h3>

                <div class="filter-group">
                    <h4>Category</h4>
                    <label>
                        <input type="checkbox" name="category[]" value="Vegetables" <?= in_array('Vegetables', $selectedCategories, true) ? 'checked' : ''; ?>>
                        Vegetables
                    </label>
                    <label>
                        <input type="checkbox" name="category[]" value="Fruits" <?= in_array('Fruits', $selectedCategories, true) ? 'checked' : ''; ?>>
                        Fruits
                    </label>
                    <label>
                        <input type="checkbox" name="category[]" value="Herbs" <?= in_array('Herbs', $selectedCategories, true) ? 'checked' : ''; ?>>
                        Herbs
                    </label>
                    <label>
                        <input type="checkbox" name="category[]" value="Dairy" <?= in_array('Dairy', $selectedCategories, true) ? 'checked' : ''; ?>>
                        Dairy
                    </label>
                </div>

                <div class="filter-group">
                    <h4>Price Range</h4>
                    <input
                        type="range"
                        min="0"
                        max="5000"
                        step="50"
                        value="<?= htmlspecialchars((string)$maxPrice); ?>"
                        oninput="document.getElementById('priceRangeValue').textContent = '₱' + this.value; document.getElementById('max_price').value = this.value;">
                    <input type="hidden" name="max_price" id="max_price" value="<?= htmlspecialchars((string)$maxPrice); ?>">
                    <div style="display:flex; justify-content:space-between;">
                        <span>₱0</span>
                        <span id="priceRangeValue">₱<?= htmlspecialchars((string)$maxPrice); ?></span>
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Availability</h4>
                    <label>
                        <input type="checkbox" name="in_stock" value="1" <?= $inStock ? 'checked' : ''; ?>>
                        In Stock
                    </label>
                    <label>
                        <input type="checkbox" name="in_season" value="1" <?= $inSeason ? 'checked' : ''; ?>>
                        In Season
                    </label>
                </div>

                <button type="submit" class="btn-secondary" style="width:100%; justify-content:center;">
                    Apply Filters
                </button>
            </form>

            <div class="products-content">
                <div class="results-header">
                    <div class="results-count">
                        <?php if (!empty($search)): ?>
                            <span>
                                <?= count($products ?? []) ?> result<?= count($products ?? []) !== 1 ? 's' : ''; ?>
                                for <strong>"<?= htmlspecialchars($search); ?>"</strong>
                                &nbsp;<a href="index.php?url=products" style="font-size:.8rem;color:#9a3718;text-decoration:none;">✕ Clear</a>
                            </span>
                        <?php else: ?>
                            <span><?= count($products ?? []) ?> products found</span>
                        <?php endif; ?>
                    </div>

                    <form method="GET" action="index.php" id="sortForm">
                        <input type="hidden" name="url" value="products">

                        <?php foreach ($selectedCategories as $category): ?>
                            <input type="hidden" name="category[]" value="<?= htmlspecialchars($category); ?>">
                        <?php endforeach; ?>

                        <input type="hidden" name="max_price" value="<?= htmlspecialchars((string)$maxPrice); ?>">

                        <?php if ($inStock): ?>
                            <input type="hidden" name="in_stock" value="1">
                        <?php endif; ?>

                        <?php if ($inSeason): ?>
                            <input type="hidden" name="in_season" value="1">
                        <?php endif; ?>

                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
                        <?php endif; ?>

                        <select class="sort-dropdown" name="sort" onchange="document.getElementById('sortForm').submit()">
                            <option value="relevance" <?= $sort === 'relevance' ? 'selected' : ''; ?>>Sort by: Relevance</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : ''; ?>>Sort by: Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : ''; ?>>Sort by: Price: High to Low</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : ''; ?>>Sort by: Newest</option>
                        </select>
                    </form>
                </div>

                <div class="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <a href="index.php?url=products/detail/<?= $product['prd_id']; ?>" class="product-card-link-inline">
                                    <img src="<?= htmlspecialchars($product['image']); ?>"
                                         alt="<?= htmlspecialchars($product['prd_name']); ?>">

                                    <div class="product-info">
                                        <h4><?= htmlspecialchars($product['prd_name']); ?></h4>
                                        <p class="vendor-name"><?= htmlspecialchars($product['vnd_farm_name']); ?></p>
                                        <p class="price">₱<?= number_format($product['prd_price'], 2); ?></p>

                                        <?php if (!empty($product['prd_description'])): ?>
                                            <p class="product-description">
                                                <?= htmlspecialchars($product['prd_description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>

                                <div class="product-info">
                                    <div class="product-actions">
                                      <form method="POST" action="index.php?url=cart/add" class="product-add-to-cart-form ajax-cart-form" style="flex:1;">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['prd_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-secondary add-to-cart-direct" style="width:100%;justify-content:center;"
                                            data-product="<?= htmlspecialchars($product['prd_name']); ?>">
                                            <span class="material-icons">shopping_cart</span> Add to Cart
                                        </button>
                                      </form>
                                      <button type="button"
                                          class="fav-toggle-btn"
                                          data-product-id="<?= (int)$product['prd_id']; ?>"
                                          title="Save to Favorites"
                                          style="background:none;border:1.5px solid #fecaca;border-radius:8px;color:#c53030;cursor:pointer;padding:.45rem .6rem;display:flex;align-items:center;flex-shrink:0;transition:.15s;">
                                          <span class="material-icons" style="font-size:18px;">favorite_border</span>
                                      </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card" style="padding: 24px;">
                            <h3 style="margin-bottom: 10px;">No products found</h3>
                            <p style="color:#666;">There are no available products matching your filters.</p>
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

<script src="assets/js/screen.js"></script>
<script src="assets/js/products.js?v=<?= time(); ?>"></script>
<script>
// Wire nav search bar to the filter form so results stay on this page
(function () {
    const navInput    = document.getElementById('navSearchInput');
    const navIcon     = document.getElementById('navSearchIcon');
    const filterInput = document.getElementById('filterSearchInput');
    const form        = document.getElementById('filterForm');
    if (!navInput || !form) return;

    function submitSearch() {
        filterInput.value = navInput.value.trim();
        form.submit();
    }

    navInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); submitSearch(); }
    });
    navIcon.addEventListener('click', submitSearch);
})();
</script>
</body>

</html>