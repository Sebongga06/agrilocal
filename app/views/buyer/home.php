<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AgriLocal - Home</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap"
    rel="stylesheet"
  />

  <link rel="stylesheet" href="assets/css/screen.css?v=2">
  <link rel="stylesheet" href="assets/css/home.css?v=<?= time(); ?>">

  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  />
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
        <a href="index.php?url=vendors">Vendors</a>
      </div>

      <div class="search-bar">
        <span class="material-icons">search</span>
        <input type="text" placeholder="Search vendors or products…" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="search_query" readonly onfocus="this.removeAttribute('readonly')" />
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
                <a href="index.php?url=farmer#register-vendor">Become a Vendor</a>
              <?php endif; ?>
            <?php endif; ?>
            <button type="button" onclick="logoutUser()">Logout</button>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <section class="home-hero">
    <div class="home-hero-inner">
      <div class="home-hero-text">
        <h1>Find fresh local farms near you</h1>
        <p>
          Explore trusted vendors, filter by category and distance, then open the map
          to preview nearby stores before visiting their full vendor page.
        </p>

        <div class="hero-badges">
          <div class="hero-badge">
            <span class="material-icons">place</span>
            Live vendor map
          </div>
          <div class="hero-badge">
            <span class="material-icons">tune</span>
            Smart filters
          </div>
          <div class="hero-badge">
            <span class="material-icons">storefront</span>
            Store previews
          </div>
        </div>
      </div>

      <div class="home-hero-card">
        <h3>Explore local agriculture</h3>
        <p>
          Use the interactive map to discover fresh produce, nearby farms, dairy
          suppliers, and herb growers around your area.
        </p>
        <button class="btn-primary" onclick="document.getElementById('mapSection').scrollIntoView({ behavior: 'smooth' })">
          <span class="material-icons">explore</span>
          Explore Nearby Vendors
        </button>
      </div>
    </div>
  </section>

  <section class="home-section" id="mapSection">
    <div class="home-section-header">
      <div>
        <h2>Nearby Vendors</h2>
        <p>Click a map pin to open a vendor preview.</p>
      </div>
    </div>

    <div class="home-layout">
      <aside class="home-sidebar">
        <div class="home-filter-block">
          <div class="distance-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
            <div class="home-filter-title" style="font-size:.85rem; font-weight:600; color:#444;">Distance</div>
            <div class="distance-value" style="font-size:.85rem; color:#555;"><span id="distanceValue">10</span> km</div>
          </div>
          <input
            type="range"
            min="1"
            max="20"
            value="10"
            class="distance-slider"
            id="distanceSlider"
            style="width:100%; height:4px; accent-color:var(--green-accent,#2d6a4f);"
          />
        </div>

        <div class="home-filter-block">
          <div class="home-filter-title" style="font-size:.85rem; font-weight:600; color:#444; margin-bottom:8px;">Categories</div>
          <div class="chip-group" id="categoryChips">
            <button class="chip active" data-category="all" type="button">All</button>
            <button class="chip" data-category="vegetables" type="button">Vegetables</button>
            <button class="chip" data-category="fruits" type="button">Fruits</button>
            <button class="chip" data-category="dairy" type="button">Dairy</button>
            <button class="chip" data-category="herbs" type="button">Herbs</button>
          </div>
        </div>

        <div class="home-filter-block"></div>
      </aside>

      <div>
        <div class="map-panel">
          <div class="map-toolbar">
            <div class="map-legend">
              <span class="legend-item"><span class="legend-dot" style="background:#4caf50;"></span> Vegetables</span>
              <span class="legend-item"><span class="legend-dot" style="background:#ff9800;"></span> Fruits</span>
              <span class="legend-item"><span class="legend-dot" style="background:#2196f3;"></span> Dairy</span>
              <span class="legend-item"><span class="legend-dot" style="background:#8e44ad;"></span> Herbs</span>
            </div>
            <div class="map-count-text">
              Showing <strong id="vendorCount">0</strong> vendors
            </div>
          </div>
          <div id="map"></div>
        </div>

        <div class="vendor-grid-wrap">
          <div class="vendor-grid" id="vendorGrid"></div>
        </div>
      </div>
    </div>
  </section>

  <div class="modal-backdrop" id="modalBackdrop">
    <div class="modal">
      <img id="modalImage" src="" alt="Vendor preview" />
      <div class="modal-content">
        <div class="modal-header">
          <h3 id="modalTitle">Vendor Name</h3>
          <button class="close-btn" id="closeModal" type="button">&times;</button>
        </div>
        <div class="modal-meta" id="modalMeta" style="display:flex;gap:.5rem;flex-wrap:wrap;margin:.4rem 0 .6rem;font-size:.82rem;color:#666;"></div>
        <div class="modal-text" id="modalText"></div>
        <div class="modal-tags" id="modalTags"></div>
        <div class="modal-products" id="modalProducts" style="margin:.6rem 0;font-size:.83rem;color:#555;"></div>
        <div class="modal-actions">
          <a href="#" id="modalStoreLink" class="btn-primary">View Store</a>
          <button class="btn-secondary" id="focusOnMapBtn" type="button">Back to Map</button>
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

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="assets/js/screen.js"></script>

<?php
$vendors = $vendors ?? [];

$vendorsForJs = array_map(function ($vendor) {
    $vendorId = (int)($vendor['id'] ?? 0);

    return [
        'id'          => $vendorId,
        'name'        => $vendor['name'] ?? 'Vendor',
        'category'    => strtolower($vendor['category'] ?? 'vegetables'),
        'rating'      => (float)($vendor['rating'] ?? 0),
        'reviews'     => (int)($vendor['reviews'] ?? 0),
        'distance'    => (float)($vendor['distance'] ?? 0),
        'lat'         => (float)($vendor['lat'] ?? 10.6755),
        'lng'         => (float)($vendor['lng'] ?? 122.9588),
        'image'       => !empty($vendor['image']) ? $vendor['image'] : 'https://via.placeholder.com/600x300?text=Vendor',
        'products'    => $vendor['products'] ?? 'No products listed yet',
        'description' => $vendor['description'] ?? 'No description available yet.',
        'address'     => $vendor['address'] ?? '',
        'storeUrl'    => 'index.php?url=vendors/store/' . $vendorId,
    ];
}, $vendors);
?>

<script>
  window.vendorsData = <?= json_encode($vendorsForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>

  <script src="assets/js/home.js?v=<?= time(); ?>"></script>

  <!-- Switch-to-Vendor Modal -->
  <style>
    .sv-modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;}
    .sv-modal-backdrop.show{display:flex;}
    .sv-modal{background:#fff;border-radius:16px;padding:2rem;width:360px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;}
    .sv-modal h3{margin:0 0 .5rem;font-family:'Roboto Slab',serif;color:#1a3c2e;}
    .sv-modal p{margin:0 0 1rem;color:#555;font-size:.9rem;}
    .sv-modal-close{position:absolute;top:12px;right:14px;background:none;border:none;font-size:1.3rem;cursor:pointer;color:#888;}
    .sv-error{color:#c0392b;font-size:.82rem;margin-bottom:8px;display:none;}
    .sv-pw-wrap{position:relative;margin-bottom:4px;}
    .sv-pw-wrap input{width:100%;box-sizing:border-box;padding:.65rem 2.5rem .65rem .85rem;border:1px solid #ddd;border-radius:10px;font-size:.95rem;outline:none;}
    .sv-pw-wrap input::-ms-reveal,.sv-pw-wrap input::-ms-clear,.sv-pw-wrap input::-webkit-credentials-auto-fill-button{display:none !important;}
    .sv-pw-wrap input:focus{border-color:#2d6a4f;}
    .sv-pw-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#888;display:flex;align-items:center;}
    .sv-pw-toggle .material-icons{font-size:18px;}
    .sv-submit{width:100%;margin-top:12px;padding:.7rem;background:#9a3718;color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:600;cursor:pointer;}
    .sv-submit:hover{background:#7d2c12;}
    .sv-submit:disabled{opacity:.6;cursor:not-allowed;}
  </style>
  <div class="sv-modal-backdrop" id="svModalBackdrop">
    <div class="sv-modal">
      <button class="sv-modal-close" id="svModalClose" type="button">✕</button>
      <h3>Switch to Vendor</h3>
      <p>Enter your vendor account password to switch profiles.</p>
      <span class="sv-error" id="svError"></span>
      <div class="sv-pw-wrap">
        <input type="password" id="svPassword" placeholder="Vendor account password" autocomplete="current-password">
        <button type="button" class="sv-pw-toggle" onclick="toggleSvPw()" aria-label="Toggle password">
          <span class="material-icons" id="svPwIcon">visibility</span>
        </button>
      </div>
      <button class="sv-submit" id="svSubmitBtn" type="button">Switch Account</button>
    </div>
  </div>
  <script>
    function openSwitchVendorModal() {
      const b = document.getElementById('svModalBackdrop');
      document.getElementById('svError').style.display = 'none';
      document.getElementById('svPassword').value = '';
      b.classList.add('show');
      document.getElementById('svPassword').focus();
    }
    function toggleSvPw() {
      const inp  = document.getElementById('svPassword');
      const icon = document.getElementById('svPwIcon');
      const hide = inp.type === 'password';
      inp.type   = hide ? 'text' : 'password';
      icon.textContent = hide ? 'visibility_off' : 'visibility';
    }
    document.getElementById('svModalClose').addEventListener('click', () =>
      document.getElementById('svModalBackdrop').classList.remove('show'));
    document.getElementById('svModalBackdrop').addEventListener('click', function (e) {
      if (e.target === this) this.classList.remove('show');
    });
    document.getElementById('svSubmitBtn').addEventListener('click', async function () {
      const errEl = document.getElementById('svError');
      errEl.style.display = 'none';
      const pw = document.getElementById('svPassword').value.trim();
      if (!pw) { errEl.textContent = 'Password is required.'; errEl.style.display = 'block'; return; }
      this.disabled = true; this.textContent = 'Switching…';
      try {
        const fd = new FormData(); fd.append('vendor_password', pw);
        const res  = await fetch('index.php?url=auth/switchToVendor', {
          method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.success) { window.location.href = data.redirect; }
        else { errEl.textContent = data.message || 'Switch failed.'; errEl.style.display = 'block'; }
      } catch (e) {
        errEl.textContent = 'Something went wrong.'; errEl.style.display = 'block';
      } finally {
        this.disabled = false; this.textContent = 'Switch Account';
      }
    });
  </script>
</body>
</html>