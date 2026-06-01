<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($vendor['name'] ?? 'Vendor Store'); ?> | AgriLocal</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/screen.css?v=2">
  <link rel="stylesheet" href="assets/css/vendorStore.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="assets/css/vendor-store.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
 


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

  <main class="vendor-store-page">
    <div class="vendor-store-layout">
      <section class="vendor-store-left">
        <img
          id="vendorCover"
          class="vendor-cover"
          src="<?= htmlspecialchars(!empty($vendor['image']) ? $vendor['image'] : 'https://via.placeholder.com/1200x500?text=Vendor'); ?>"
          alt="<?= htmlspecialchars($vendor['name']); ?>">

        <div class="vendor-left-content">
          <div class="vendor-profile-photo">
            <span class="material-icons">agriculture</span>
          </div>

          <h1 id="vendorName" class="vendor-farm-name"><?= htmlspecialchars($vendor['name']); ?></h1>

          <div class="vendor-rating">
            <span class="material-icons">star</span>
            <span class="material-icons">star</span>
            <span class="material-icons">star</span>
            <span class="material-icons">star</span>
            <span class="material-icons">star_half</span>
            <span id="vendorRatingText"><?= number_format((float)($vendor['rating'] ?? 0), 1); ?> (<?= (int)($vendor['reviews'] ?? 0); ?> reviews)</span>
          </div>

          <div class="vendor-location">
            <span class="material-icons">location_on</span>
            <span id="vendorLocationText"><?= htmlspecialchars($vendor['address'] ?? 'No address available'); ?></span>
          </div>

          <div class="vendor-hours">
            <span class="material-icons">schedule</span>
            <span id="vendorHoursText">Open daily • 6:00 AM – 5:00 PM</span>
          </div>

          <p class="vendor-description" id="vendorDescription"><?= htmlspecialchars($vendor['description'] ?? 'No description available.'); ?></p>

          <div class="vendor-actions">
            <button class="btn-secondary vendor-action-btn fav-vendor-btn"
                type="button"
                data-vendor-id="<?= (int)($vendor['id'] ?? 0); ?>"
                id="favVendorBtn">
              <span class="material-icons" style="font-size:16px;" id="favVendorIcon">favorite_border</span>
              <span id="favVendorLabel">Favorite</span>
            </button>
          </div>
        </div>
      </section>

      <section class="vendor-store-right">
        <div class="vendor-tabs">
          <button class="vendor-tab active" data-tab="products">Products</button>
          <button class="vendor-tab" data-tab="about">About</button>
          <button class="vendor-tab" data-tab="reviews">Reviews <?php if (!empty($reviews)): ?><span style="background:#2d6a4f;color:#fff;border-radius:999px;font-size:.7rem;padding:1px 6px;margin-left:3px;"><?= count($reviews); ?></span><?php endif; ?></button>
          <button class="vendor-tab" data-tab="location">Location</button>
        </div>

        <div class="vendor-tab-content active" id="tab-products">
          <div class="vendor-search">
            <span class="material-icons">search</span>
            <input type="text" id="vendorProductSearch" placeholder="Search within vendor">
          </div>

          <div class="vendor-products-grid" id="vendorProductsGrid"></div>
        </div>

        <div class="vendor-tab-content" id="tab-about">
          <div class="farmer-story-card">
            <div class="farmer-story-hero">
              <img
                id="farmerImage"
                src="<?= htmlspecialchars(!empty($vendor['image']) ? $vendor['image'] : 'https://via.placeholder.com/1200x500?text=Vendor'); ?>"
                alt="<?= htmlspecialchars($vendor['name']); ?>"
                class="farmer-story-image">

              <div class="farmer-story-overlay">
                <div class="farmer-story-badge">Meet Our Farmer</div>
                <h3 id="farmerName" class="farmer-story-name"><?= htmlspecialchars($vendor['name']); ?></h3>
                <p id="farmerShortIntro" class="farmer-story-short"><?= htmlspecialchars($vendor['description'] ?? 'No short intro available.'); ?></p>
              </div>
            </div>

            <div id="vendorAboutText" class="farmer-story-body">
              <?= nl2br(htmlspecialchars($vendor['description'] ?? 'No story available yet.')); ?>
            </div>
          </div>
        </div>

        <div class="vendor-tab-content" id="tab-reviews">
          <?php if (!empty($reviews)): ?>
            <div style="display:flex; flex-direction:column; gap:.75rem;">
              <?php foreach ($reviews as $rev): ?>
                <?php $rating = (int)($rev['rev_rating'] ?? 0); ?>
                <div style="background:#fff; border:1px solid #eee; border-radius:14px; padding:1rem 1.25rem; box-shadow:0 2px 8px rgba(0,0,0,.04);">
                  <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.4rem;">
                    <div style="display:flex; align-items:center; gap:.6rem;">
                      <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2d6a4f,#52b788);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span class="material-icons" style="font-size:18px;color:#fff;">person</span>
                      </div>
                      <div>
                        <div style="font-weight:600;color:#1a3c2e;font-size:.9rem;"><?= htmlspecialchars($rev['rev_name'] ?? 'Customer'); ?></div>
                        <div style="font-size:.75rem;color:#aaa;"><?= !empty($rev['rev_dateCreated']) ? date('M j, Y', strtotime($rev['rev_dateCreated'])) : ''; ?></div>
                      </div>
                    </div>
                    <div style="color:#f5a623;font-size:.9rem;letter-spacing:1px;">
                      <?= str_repeat('★', $rating) . str_repeat('☆', max(0, 5 - $rating)); ?>
                    </div>
                  </div>
                  <?php if (!empty($rev['rev_title'])): ?>
                    <div style="font-weight:600;color:#333;font-size:.88rem;margin-bottom:.25rem;"><?= htmlspecialchars($rev['rev_title']); ?></div>
                  <?php endif; ?>
                  <p style="margin:0;color:#555;font-size:.87rem;line-height:1.6;"><?= htmlspecialchars($rev['rev_comment'] ?? ''); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="text-align:center;padding:3rem 1rem;color:#aaa;">
              <span class="material-icons" style="font-size:40px;display:block;margin-bottom:.5rem;color:#ddd;">rate_review</span>
              <p style="margin:0;font-size:.9rem;">No reviews yet for this vendor.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="vendor-tab-content" id="tab-location">
          <div id="vendorMap"></div>

          <div class="pickup-box">
            <strong>Pickup Instructions</strong>
            <p id="vendorPickupText" class="pickup-text"><?= htmlspecialchars($vendor['pickup_instructions'] ?? 'No pickup instructions available.'); ?></p>
          </div>
        </div>
      </section>
    </div>
  </main>

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

<?php
$productsForJs = array_map(function ($product) {
    $images = json_decode($product['prd_images'] ?? '[]', true);
    $image = (!empty($images) && is_array($images) && !empty($images[0]))
        ? $images[0]
        : 'https://via.placeholder.com/600x400?text=No+Image';

    return [
        'id' => (int)$product['prd_id'],
        'name' => $product['prd_name'] ?? 'Product',
        'description' => $product['prd_description'] ?? '',
        'price' => (float)($product['prd_price'] ?? 0),
        'unit' => $product['prd_unit'] ?? 'pc',
        'stock' => (int)($product['prd_stock_quantity'] ?? 0),
        'image' => $image,
    ];
}, $products ?? []);

$reviewsForJs = array_map(function ($review) {
    return [
        'name' => $review['rev_name'] ?? 'Customer',
        'rating' => (int)($review['rev_rating'] ?? 0),
        'comment' => $review['rev_comment'] ?? 'No review text.',
        'date' => $review['rev_dateCreated'] ?? '',
    ];
}, $reviews ?? []);
?>

<script>
  window.vendorStoreData = {
    vendor: <?= json_encode($vendor, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
    products: <?= json_encode($productsForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
    reviews: <?= json_encode($reviewsForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
  };
</script>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="assets/js/screen.js"></script>
  <script src="assets/js/vendorStore.js?v=<?= time(); ?>"></script>
  <script src="assets/js/vendor-store-extra.js?v=<?= time(); ?>"></script>

  <!-- Switch-to-Vendor Modal -->
  <div class="sv-modal-backdrop" id="svModalBackdrop">
    <div class="sv-modal">
      <button class="sv-modal-close" id="svModalClose" type="button">✕</button>
      <h3>Switch to Vendor</h3>
      <p>Enter your vendor account password to switch profiles.</p>
      <span class="sv-error" id="svError"></span>
      <div class="sv-pw-wrap">
        <input type="password" id="svPassword" placeholder="Vendor account password" autocomplete="new-password">
        <button type="button" class="sv-pw-toggle" onclick="toggleSvPw()" aria-label="Toggle password">
          <span class="material-icons" id="svPwIcon">visibility</span>
        </button>
      </div>
      <button class="sv-submit" id="svSubmitBtn" type="button">Switch Account</button>
    </div>
  </div>
</body>
</html>