<?php $pageTitle = 'Shop Profile - AgriLocal'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="assets/css/vendor-profile.css?v=<?= time(); ?>">
</head>
<body>
    <div class="dashboard-container">
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
                    <li><a href="index.php?url=vendor/orders" style="position:relative;">
    <span class="material-icons-round">shopping_basket</span> Orders
    <?php
    $urgentCount = $urgentCount ?? 0;
    if ($urgentCount > 0):
    ?>
    <span style="position:absolute;top:6px;right:8px;width:8px;height:8px;background:#e53e3e;border-radius:50%;border:2px solid #fff;display:inline-block;" title="<?= $urgentCount; ?> order(s) due within 24 hours"></span>
    <?php endif; ?>
</a></li>
                    <li class="active"><a href="index.php?url=vendor/profile"><span class="material-icons-round">store</span> Shop Profile</a></li>
                </ul>
                <hr class="sidebar-divider">
                <ul>
                    <?php if (!empty($_SESSION['user']['has_buyer'])): ?>
                    <li><a href="index.php?url=auth/switchToBuyer"><span class="material-icons-round">swap_horiz</span> Switch to Buyer</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?url=auth/logout"><span class="material-icons-round">logout</span> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="headline">Shop Profile</h1>
                    <p style="color: #666; font-size: 14px;">Manage how your farm appears to customers.</p>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;">
                <button type="button" class="btn-primary" onclick="openVendorEdit()"
                    style="display:flex;align-items:center;gap:6px;font-size:14px;padding:10px 18px;">
                    <span class="material-icons-round" style="font-size:18px;">edit</span> Edit Profile
                </button>
                </div>
            </header>

            <div class="profile-header" <?php if (!empty($vendor['vnd_cover_photo'])): ?>style="background: linear-gradient(rgba(0,0,0,0.35),rgba(0,0,0,0.35)), url('<?= htmlspecialchars($vendor['vnd_cover_photo']); ?>') center/cover no-repeat;"<?php endif; ?>>
                <div class="profile-avatar">
                    <?php
                    $initials = strtoupper(substr($vendor['vnd_farm_name'] ?? 'V', 0, 1));
                    $words    = explode(' ', trim($vendor['vnd_farm_name'] ?? ''));
                    if (count($words) >= 2) {
                        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    }
                    ?>
                    <div style="width:100%;height:100%;background:var(--dark-green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-family:'Roboto Slab',serif;font-weight:700;letter-spacing:.05em;">
                        <?= htmlspecialchars($initials); ?>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <aside>
                    <div class="card">
                        <h3 class="headline" style="font-size: 18px; margin-bottom: 15px; color: var(--dark-green);">Shop Status</h3>
                        <div class="info-row">
                            <span style="color:#888;">Vendor ID</span>
                            <span style="font-weight:bold;">#<?php echo (int)($vendor['vnd_id'] ?? 0); ?></span>
                        </div>
                        <div class="info-row">
                            <span style="color:#888;">Verification</span>
                            <span class="tag">Vendor</span>
                        </div>
                        <div class="info-row">
                            <span style="color:#888;">Store Rating</span>
                            <span style="color: #FFB800; font-weight: bold;">★ <?php echo number_format((float)($vendor['vnd_rating_avg'] ?? 0), 1); ?></span>
                        </div>
                    </div>

                    <div class="card" style="border-left: 4px solid var(--green-accent);">
                        <h4 style="font-family: 'Roboto Slab'; margin-bottom: 10px;">Pickup Address</h4>
                        <p style="font-size: 13px; color: #555; line-height: 1.5;" id="vendorAddress">
                            <?php echo nl2br(htmlspecialchars($vendor['vnd_address'] ?? 'No address set.')); ?>
                        </p>
                    </div>
                </aside>

                <section>
                    <div class="card">
                        <h3 class="headline" style="font-size: 18px; margin-bottom: 10px;" id="vendorFarmName">About <?php echo htmlspecialchars($vendor['vnd_farm_name'] ?? 'Your Farm'); ?></h3>
                        <p style="color: #555; line-height: 1.6; font-size: 15px;" id="vendorFarmDesc">
                            <?php echo nl2br(htmlspecialchars($vendor['vnd_farm_desc'] ?? 'No farm description available yet.')); ?>
                        </p>
                    </div>
                </section>
            </div>
        </main>
    </div>

<!-- Vendor Edit Profile Modal -->
<div class="vep-backdrop" id="vepBackdrop">
  <div class="vep-modal">

    <div class="vep-modal-header">
      <h3>
        <span class="material-icons-round">store</span>
        Edit Shop Profile
      </h3>
      <button class="vep-close" id="vepClose" type="button">✕</button>
    </div>

    <div class="vep-modal-body">
      <div class="vep-error" id="vepError"></div>
      <div class="vep-success" id="vepSuccess"></div>

      <form id="vepForm" enctype="multipart/form-data" autocomplete="off">

        <!-- Cover Photo -->
        <div class="vep-section-title"><span class="material-icons-round" style="font-size:14px;">image</span> Cover Photo</div>
        <div class="vep-cover-wrap" id="vepCoverWrap">
          <?php if (!empty($vendor['vnd_cover_photo'])): ?>
            <img src="<?= htmlspecialchars($vendor['vnd_cover_photo']); ?>" id="vepCoverImg" alt="Cover">
          <?php else: ?>
            <div class="vep-cover-placeholder" id="vepCoverPlaceholder">
              <span class="material-icons-round">add_photo_alternate</span>
              <span>No cover photo</span>
            </div>
          <?php endif; ?>
        </div>
        <label for="vepCoverPicker" class="vep-photo-label" style="margin-bottom:.9rem;">
          <span class="material-icons-round" style="font-size:15px;">add_photo_alternate</span> Change Cover Photo
        </label>
        <input type="file" id="vepCoverPicker" name="cover_photo" accept="image/*" style="display:none;">

        <!-- Vendor Info -->
        <div class="vep-section-title"><span class="material-icons-round" style="font-size:14px;">badge</span> Vendor Info</div>
        <div class="vep-field">
          <label>Owner / Display Name <span style="color:#c0392b;">*</span></label>
          <input type="text" name="owner_name"
            value="<?= htmlspecialchars(($vData ?? [])['vnd_owner_name'] ?? $vendor['vnd_farm_name'] ?? $_SESSION['user']['name'] ?? ''); ?>"
            required placeholder="Your name as it appears on the store">
        </div>
        <div class="vep-field">
          <label>Farm / Shop Name</label>
          <input type="text" name="farm_name" value="<?= htmlspecialchars($vendor['vnd_farm_name'] ?? ''); ?>" placeholder="e.g. TA-ALA Farms Inc">
        </div>
        <div class="vep-field">
          <label>Farm / Pickup Address <span style="color:#c0392b;">*</span></label>
          <input type="text" name="address" id="vepAddressInput"
            value="<?= htmlspecialchars($vendor['vnd_address'] ?? ''); ?>"
            required
            placeholder="e.g. Brgy. Cabug, Bacolod City, Negros Occidental">
          <small style="color:#888;font-size:.75rem;display:block;margin-top:.25rem;">
            <span class="material-icons-round" style="font-size:12px;vertical-align:middle;">location_on</span>
            Type your address or drop a pin on the map below.
          </small>
        </div>

        <!-- Farm Location Map -->
        <div class="vep-section-title">
          <span class="material-icons-round" style="font-size:14px;">map</span> Farm / Pickup Location
        </div>
        <div class="vep-map-wrap">
          <div class="vep-map-header">
            <span class="material-icons-round" style="font-size:15px;color:#276749;">pin_drop</span>
            <span>Click the map to pin your exact farm location</span>
            <button type="button" id="vepMyLocation" class="vep-myloc-btn">
              <span class="material-icons-round" style="font-size:14px;">my_location</span> My Location
            </button>
          </div>
          <div id="vepMap"></div>
          <div class="vep-map-footer" id="vepMapFooter">
            <?php if (!empty($vendor['vnd_lat']) && !empty($vendor['vnd_lng'])): ?>
              <span class="material-icons-round" style="font-size:13px;color:#276749;">check_circle</span>
              Location saved: <?= number_format((float)$vendor['vnd_lat'], 5); ?>, <?= number_format((float)$vendor['vnd_lng'], 5); ?>
            <?php else: ?>
              No location pinned yet — drop a pin or type your address above.
            <?php endif; ?>
          </div>
        </div>

        <!-- Hidden coordinate inputs submitted with the form -->
        <input type="hidden" name="vendor_lat" id="vepVendorLat"
               value="<?= htmlspecialchars((string)($vendor['vnd_lat'] ?? '')); ?>">
        <input type="hidden" name="vendor_lng" id="vepVendorLng"
               value="<?= htmlspecialchars((string)($vendor['vnd_lng'] ?? '')); ?>">

        <div class="vep-field">
          <label>Farm Description</label>
          <textarea name="farm_desc" placeholder="Tell buyers about your farm…"><?= htmlspecialchars($vendor['vnd_farm_desc'] ?? ''); ?></textarea>
        </div>
        <div class="vep-field">
          <label>Pickup Instructions</label>
          <textarea name="pickup_instructions" placeholder="e.g. Pickup at main gate. Call before arrival."><?= htmlspecialchars($vendor['vnd_pickup_instructions'] ?? ''); ?></textarea>
        </div>

        <!-- Change Password -->
        <div class="vep-section-title"><span class="material-icons-round" style="font-size:14px;">lock</span> Change Password <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#bbb;">(optional)</span></div>
        <div class="vep-field">
          <label>Current Password</label>
          <div class="vep-pw-wrap">
            <input type="password" name="current_password" id="vepCurPw" placeholder="Enter current password" autocomplete="new-password">
            <button type="button" class="vep-pw-toggle" onclick="toggleVepPw('vepCurPw',this)"><span class="material-icons">visibility</span></button>
          </div>
        </div>
        <div class="vep-field">
          <label>New Password</label>
          <div class="vep-pw-wrap">
            <input type="password" name="new_password" id="vepNewPw" placeholder="Enter new password" autocomplete="new-password">
            <button type="button" class="vep-pw-toggle" onclick="toggleVepPw('vepNewPw',this)"><span class="material-icons">visibility</span></button>
          </div>
        </div>

      </form>
    </div>

    <div class="vep-modal-footer">
      <button type="button" class="vep-cancel" onclick="document.getElementById('vepBackdrop').classList.remove('show')">Cancel</button>
      <button type="button" class="vep-submit" id="vepSubmitBtn" onclick="document.getElementById('vepForm').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}))">
        <span class="material-icons-round" style="font-size:18px;">save</span> Save Changes
      </button>
    </div>

  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Pass saved vendor coordinates to JS
window.vendorMapData = {
    lat: <?= !empty($vendor['vnd_lat']) ? (float)$vendor['vnd_lat'] : 'null'; ?>,
    lng: <?= !empty($vendor['vnd_lng']) ? (float)$vendor['vnd_lng'] : 'null'; ?>
};
</script>
<script src="assets/js/vendor-profile.js?v=<?= time(); ?>"></script>
</body>
</html>