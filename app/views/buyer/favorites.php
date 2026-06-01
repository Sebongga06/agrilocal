<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/screen.css?v=2">
    <link rel="stylesheet" href="assets/css/favorites.css?v=<?= time(); ?>">
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
            <a href="index.php?url=favorites" class="nav-icon-item" style="color:var(--red-accent);">
                <span class="material-icons">favorite</span>
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

<div class="fav-page">

    <?php if (!empty($flash)): ?>
        <div class="flash-msg flash-<?= htmlspecialchars($flash['type']); ?>">
            <?= htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="fav-header">
        <h1><span class="material-icons">favorite</span> My Favorites</h1>
        <span class="fav-count" id="favCount"><?= count($favorites ?? []); ?> saved item<?= count($favorites ?? []) !== 1 ? 's' : ''; ?></span>
    </div>

    <?php
    $allFavs     = $favorites ?? [];
    $productFavs = array_values(array_filter($allFavs, fn($f) => $f['type'] === 'product'));
    $vendorFavs  = array_values(array_filter($allFavs, fn($f) => $f['type'] === 'vendor'));
    ?>

    <div class="fav-tabs">
        <button class="fav-tab active" onclick="switchTab('all', this)">All (<?= count($allFavs); ?>)</button>
        <button class="fav-tab" onclick="switchTab('products', this)">Products (<?= count($productFavs); ?>)</button>
        <button class="fav-tab" onclick="switchTab('vendors', this)">Vendors (<?= count($vendorFavs); ?>)</button>
    </div>

    <?php if (empty($allFavs)): ?>
        <div class="fav-empty">
            <span class="material-icons">favorite_border</span>
            <h3>No favorites yet</h3>
            <p>Save products and vendors you love to find them quickly later.</p>
            <a href="index.php?url=products" class="btn-primary" style="font-size:.85rem;padding:.5rem 1.2rem;">
                <span class="material-icons" style="font-size:16px;">storefront</span> Browse Products
            </a>
        </div>
    <?php else: ?>

        <div class="fav-grid" id="tab-all">
            <?php foreach ($allFavs as $fav): ?>
                <?php echo buildFavCard($fav); ?>
            <?php endforeach; ?>
        </div>

        <div class="fav-grid" id="tab-products" style="display:none;">
            <?php if (empty($productFavs)): ?>
                <p style="color:#888;">No product favorites yet.</p>
            <?php else: ?>
                <?php foreach ($productFavs as $fav): ?>
                    <?php echo buildFavCard($fav); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="fav-grid" id="tab-vendors" style="display:none;">
            <?php if (empty($vendorFavs)): ?>
                <p style="color:#888;">No vendor favorites yet.</p>
            <?php else: ?>
                <?php foreach ($vendorFavs as $fav): ?>
                    <?php echo buildFavCard($fav); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<?php
function buildFavCard(array $fav): string
{
    $isProduct = $fav['type'] === 'product';
    $favId     = (int)$fav['fav_id'];
    $image     = htmlspecialchars($fav['image'] ?? '');
    $typeLabel = $isProduct ? 'Product' : 'Vendor';

    if ($isProduct) {
        $name    = htmlspecialchars($fav['prd_name'] ?? 'Unknown Product');
        $sub     = htmlspecialchars($fav['prd_vendor_name'] ?? '');
        $link    = 'index.php?url=products/detail/' . (int)$fav['fav_product_id'];
        $btnText = 'View Product';
        $price   = '<div class="fav-card-price">₱' . number_format((float)($fav['prd_price'] ?? 0), 2) . ' / ' . htmlspecialchars($fav['prd_unit'] ?? '') . '</div>';
    } else {
        $name    = htmlspecialchars($fav['fav_vnd_farm_name'] ?? 'Unknown Vendor');
        $sub     = htmlspecialchars($fav['fav_vnd_address'] ?? '');
        $link    = 'index.php?url=vendors/store/' . (int)$fav['fav_vendor_id'];
        $btnText = 'View Store';
        $price   = '';
    }

    return <<<HTML
    <div class="fav-card">
        <a href="{$link}" class="fav-card-img-wrap">
            <img src="{$image}" alt="{$name}" loading="lazy">
        </a>
        <div class="fav-card-body">
            <div class="fav-card-type">{$typeLabel}</div>
            <a href="{$link}" class="fav-card-name">{$name}</a>
            <div class="fav-card-sub">{$sub}</div>
            {$price}
            <div class="fav-card-actions">
                <a href="{$link}" class="btn-primary">{$btnText}</a>
                <form method="POST" action="index.php?url=favorites/remove" style="margin:0;">
                    <input type="hidden" name="fav_id" value="{$favId}">
                    <button type="submit" class="fav-remove-btn" title="Remove from favorites">
                        <span class="material-icons">delete_outline</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    HTML;
}
?>

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
            </ul>
        </div>
    </div>
    <div class="footer-bottom"><p>&copy; 2026 AgriLocal. All rights reserved.</p></div>
</footer>

<script src="assets/js/screen.js"></script>
<script src="assets/js/favorites.js?v=<?= time(); ?>"></script>
</body>
</html>
