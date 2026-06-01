<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AgriLocal – My Profile</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;600;700&family=Noto+Serif:wght@400;500&family=Material+Icons&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/screen.css?v=2"/>
  <link rel="stylesheet" href="assets/css/profile-buyer.css?v=<?= time(); ?>"/>
</head>
<body>

<?php
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: index.php?url=farmer'); exit; }

$userData     = $userData ?? [];
$fullName     = trim($user['name'] ?? 'Guest User');
$email        = trim($user['email'] ?? '');
$role         = ucfirst($user['role'] ?? 'buyer');
$handle       = '@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));
$orders       = $orders  ?? [];
$reviews      = $reviews ?? [];
$reviewCount  = count($reviews);
$orderCount   = count($orders);
$profilePic   = !empty($userData['user_profile_pic']) ? $userData['user_profile_pic'] : null;
$nameParts    = explode(' ', $fullName, 2);
$firstName    = $nameParts[0] ?? '';
$lastName     = $nameParts[1] ?? '';
$phone        = $userData['user_phone'] ?? '';
?>

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
      <input type="text" placeholder="Search vendors or products…" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="search_query">
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
        <span class="material-icons">person</span>
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

<main class="profile-page">
  <div class="profile-shell">

    <!-- Cover -->
    <div class="profile-cover"><div class="profile-cover-pattern"></div></div>

    <!-- Top row: avatar + edit button -->
    <div class="profile-top-row">
      <div class="profile-avatar" id="profileAvatarWrap">
        <?php if ($profilePic): ?>
          <img src="<?= htmlspecialchars($profilePic); ?>" alt="Profile" id="profileAvatarImg"
               style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
        <?php else: ?>
          <span class="material-icons" id="profileAvatarIcon">person</span>
        <?php endif; ?>
      </div>
      <div class="profile-top-actions">
        <button type="button" class="btn-edit-profile" onclick="openEditProfile()">
          <span class="material-icons">edit</span> Edit Profile
        </button>
      </div>
    </div>

    <!-- Identity -->
    <div class="profile-identity">
      <h1 class="profile-name"><?= htmlspecialchars($fullName); ?></h1>
      <p class="profile-handle"><?= htmlspecialchars($handle); ?></p>
      <?php if ($email): ?>
      <div class="profile-email-row">
        <span class="material-icons">mail</span>
        <?= htmlspecialchars($email); ?>
      </div>
      <?php endif; ?>
      <span class="profile-role-badge"><?= htmlspecialchars($role); ?></span>
    </div>

    <!-- Stats bar -->
    <div class="profile-stats-bar">
      <div class="stat-item">
        <div class="stat-num"><?= $orderCount; ?></div>
        <div class="stat-label">Orders</div>
      </div>
      <div class="stat-item">
        <div class="stat-num"><?= $reviewCount; ?></div>
        <div class="stat-label">Reviews</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="profile-tabs">
      <button type="button" class="profile-tab active" data-tab="reviewsTab">Activity</button>
    </div>

    <!-- Activity tab -->
    <div id="reviewsTab" class="tab-panel active">
      <div class="profile-grid">

        <!-- Reviews card -->
        <div class="profile-card">
          <h3><span class="material-icons">star</span> My Reviews</h3>
          <?php if ($reviewCount === 0): ?>
            <div class="empty-state">
              <span class="material-icons">rate_review</span>
              <p>You haven't written any reviews yet.</p>
            </div>
          <?php else: ?>
            <?php foreach ($reviews as $review): ?>
              <div class="review">
                <div class="review-title"><?= htmlspecialchars($review['rev_title'] ?? 'Review'); ?></div>
                <div class="review-rating"><?= str_repeat('★', (int)($review['rev_rating'] ?? 0)); ?></div>
                <div class="review-text"><?= htmlspecialchars($review['rev_comment'] ?? ''); ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Recent orders card -->
        <div class="profile-card">
          <h3><span class="material-icons">receipt_long</span> Recent Orders</h3>
          <?php if (empty($orders)): ?>
            <div class="empty-state">
              <span class="material-icons">shopping_bag</span>
              <p>No orders placed yet.</p>
            </div>
          <?php else: ?>
            <?php foreach (array_slice($orders, 0, 6) as $order): ?>
              <?php
                $st = strtolower($order['ord_status'] ?? 'pending');
                $stClass = in_array($st, ['ready','completed','cancelled']) ? $st : '';
              ?>
              <div class="activity-item">
                <div class="activity-icon"><span class="material-icons">local_mall</span></div>
                <div class="activity-text">
                  <strong>Order #<?= (int)$order['ord_id']; ?></strong><br>
                  <?= htmlspecialchars($order['vnd_farm_name'] ?? 'Vendor'); ?>
                  <br>
                  <span class="activity-status <?= $stClass; ?>">
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $st))); ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
            <a href="index.php?url=orders" style="display:block; text-align:center; margin-top:.75rem; font-size:.85rem; color:#2d6a4f; text-decoration:none; font-weight:600;">
              View all orders →
            </a>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <!-- Media tab removed -->

  </div>
</main>

<!-- Edit Profile Modal -->
<div class="ep-modal-backdrop" id="epModalBackdrop">
  <div class="ep-modal">

    <div class="ep-modal-header">
      <h3>
        <span class="material-icons" style="font-size:20px;color:var(--green-accent);">person</span>
        Edit Profile
      </h3>
      <button class="ep-modal-close" id="epModalClose" type="button">✕</button>
    </div>

    <div class="ep-modal-body">
      <div class="ep-error"   id="epError"></div>
      <div class="ep-success" id="epSuccess"></div>

      <form id="epForm" autocomplete="off" enctype="multipart/form-data">

        <!-- Profile picture -->
        <div class="ep-section-title">
          <span class="material-icons" style="font-size:14px;">photo_camera</span> Profile Photo
        </div>
        <div style="text-align:center;margin-bottom:1.1rem;">
          <div id="epAvatarPreview" style="width:72px;height:72px;border-radius:50%;margin:0 auto .5rem;overflow:hidden;background:linear-gradient(135deg,#2d6a4f,#52b788);display:flex;align-items:center;justify-content:center;border:3px solid #e8ede4;">
            <?php if ($profilePic): ?>
              <img src="<?= htmlspecialchars($profilePic); ?>" style="width:100%;height:100%;object-fit:cover;" id="epAvatarImg">
            <?php else: ?>
              <span class="material-icons" style="font-size:36px;color:#fff;" id="epAvatarIcon">person</span>
            <?php endif; ?>
          </div>
          <label for="epPhotoPicker" style="cursor:pointer;font-size:.8rem;color:#2d6a4f;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
            <span class="material-icons" style="font-size:15px;">add_photo_alternate</span> Change Photo
          </label>
          <input type="file" id="epPhotoPicker" name="profile_pic" accept="image/*" style="display:none;">
        </div>

        <!-- Personal info -->
        <div class="ep-section-title">
          <span class="material-icons" style="font-size:14px;">badge</span> Personal Info
        </div>
        <div class="ep-row">
          <div class="ep-field">
            <label>First Name <span style="color:#c0392b;">*</span></label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($firstName); ?>" required>
          </div>
          <div class="ep-field">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($lastName); ?>">
          </div>
        </div>
        <div class="ep-field">
          <label>Phone Number</label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($phone); ?>" placeholder="e.g. 09171234567">
        </div>

        <!-- Address -->
        <div class="ep-section-title" style="margin-top:.75rem;">
          <span class="material-icons" style="font-size:14px;">location_on</span> Address
        </div>
        <div class="ep-row">
          <div class="ep-field">
            <label>Province</label>
            <select name="region" id="epRegionSelect" style="width:100%;box-sizing:border-box;padding:.55rem .85rem;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.9rem;outline:none;font-family:'Noto Serif',serif;height:42px;color:#10212b;background:#fff;">
              <option value="">Select Province</option>
              <?php
                $provinces   = ['Negros Occidental', 'Negros Oriental', 'Siquijor'];
                $savedRegion = $userData['user_region'] ?? '';
                foreach ($provinces as $prov):
              ?>
                <option value="<?= htmlspecialchars($prov); ?>" <?= $savedRegion === $prov ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($prov); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="ep-field">
            <label>City / Municipality</label>
            <select name="city" id="epCitySelect" style="width:100%;box-sizing:border-box;padding:.55rem .85rem;border:1.5px solid #e0e0e0;border-radius:10px;font-size:.9rem;outline:none;font-family:'Noto Serif',serif;height:42px;color:#10212b;background:#fff;">
              <option value=""><?= !empty($userData['user_city']) ? htmlspecialchars($userData['user_city']) : 'Select City / Municipality'; ?></option>
            </select>
          </div>
        </div>
        <div class="ep-field">
          <label>Barangay / Street</label>
          <input type="text" name="barangay" id="epBarangayInput"
                 value="<?= htmlspecialchars($userData['user_barangay'] ?? ''); ?>"
                 placeholder="e.g. Brgy. San Jose, 123 Rizal St."
                 oninput="updateFullAddress()">
        </div>
        <div class="ep-field">
          <label>Full Address <small style="color:#aaa;font-weight:400;">(auto-filled)</small></label>
          <input type="text" name="address" id="epAddressInput"
                 value="<?= htmlspecialchars($userData['user_address'] ?? ''); ?>"
                 placeholder="Full address will appear here">
        </div>

        <!-- Password -->
        <div class="ep-section-title" style="margin-top:.75rem;">
          <span class="material-icons" style="font-size:14px;">lock</span> Change Password
          <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#bbb;font-size:.75rem;">(optional)</span>
        </div>
        <div class="ep-field">
          <label>Current Password</label>
          <div class="ep-pw-wrap">
            <input type="password" name="current_password" id="epCurPw" placeholder="Enter current password" autocomplete="new-password">
            <button type="button" class="ep-pw-toggle" onclick="toggleEpPw('epCurPw',this)"><span class="material-icons">visibility</span></button>
          </div>
        </div>
        <div class="ep-field">
          <label>New Password</label>
          <div class="ep-pw-wrap">
            <input type="password" name="new_password" id="epNewPw" placeholder="Enter new password" autocomplete="new-password">
            <button type="button" class="ep-pw-toggle" onclick="toggleEpPw('epNewPw',this)"><span class="material-icons">visibility</span></button>
          </div>
        </div>

      </form>
    </div>

    <div class="ep-modal-footer">
      <button type="button" class="ep-cancel" onclick="document.getElementById('epModalBackdrop').classList.remove('show')">Cancel</button>
      <button type="button" class="ep-submit" id="epSubmitBtn"
              onclick="document.getElementById('epForm').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}))">
        <span class="material-icons" style="font-size:17px;">save</span> Save Changes
      </button>
    </div>

  </div>
</div>

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

<script src="assets/js/screen.js"></script>
<script src="assets/js/profile.js"></script>
<script>
// ── Negros Island city data ───────────────────────────────────────
const negrosData = {
  "Negros Occidental": [
    "Bacolod City","Bago City","Cadiz City","Himamaylan City","Kabankalan City",
    "La Carlota City","Sagay City","San Carlos City","Silay City","Talisay City",
    "Victorias City","Escalante City","Binalbagan","Calatrava","Candoni","Cauayan",
    "Enrique B. Magalona","Hinigaran","Hinoba-an","Ilog","Isabela","La Castellana",
    "Manapla","Moises Padilla","Murcia","Pontevedra","Pulupandan",
    "Salvador Benedicto","San Enrique","Toboso","Valladolid"
  ],
  "Negros Oriental": [
    "Bais City","Bayawan City","Canlaon City","Dumaguete City","Guihulngan City","Tanjay City",
    "Amlan","Ayungon","Bacong","Basay","Bindoy","Dauin","Jimalalud","La Libertad",
    "Mabinay","Manjuyod","Pamplona","San Jose","Santa Catalina","Siaton","Sibulan",
    "Tayasan","Valencia","Vallehermoso","Zamboanguita"
  ],
  "Siquijor": ["Enrique Villanueva","Larena","Lazi","Maria","San Juan","Siquijor"]
};

function populateEpCities(province, selectedCity) {
  const sel = document.getElementById('epCitySelect');
  sel.innerHTML = '<option value="">Select City / Municipality</option>';
  const cities = negrosData[province] || [];
  cities.forEach(c => {
    const o = document.createElement('option');
    o.value = c; o.textContent = c;
    if (c === selectedCity) o.selected = true;
    sel.appendChild(o);
  });
  sel.disabled = cities.length === 0;
}

document.getElementById('epRegionSelect').addEventListener('change', function () {
  populateEpCities(this.value, '');
  updateFullAddress();
});

// Pre-populate cities for the already-selected province on modal open
(function () {
  const prov = document.getElementById('epRegionSelect').value;
  const city = <?= json_encode($userData['user_city'] ?? ''); ?>;
  if (prov) populateEpCities(prov, city);
})();

function updateFullAddress() {
  const barangay = (document.getElementById('epBarangayInput')?.value || '').trim();
  const city     = (document.getElementById('epCitySelect')?.value    || '').trim();
  const province = (document.getElementById('epRegionSelect')?.value  || '').trim();
  const parts    = [barangay, city, province].filter(Boolean);
  const full     = document.getElementById('epAddressInput');
  if (full) full.value = parts.join(', ');
}

// Wire city change to full address update
document.getElementById('epCitySelect').addEventListener('change', updateFullAddress);

// ── Switch-to-Vendor Modal ────────────────────────────────────────
function openSwitchVendorModal() {
  document.getElementById('svError').style.display = 'none';
  document.getElementById('svPassword').value = '';
  document.getElementById('svModalBackdrop').classList.add('show');
  document.getElementById('svPassword').focus();
}
function toggleSvPw() {
  const inp  = document.getElementById('svPassword');
  const icon = document.getElementById('svPwIcon');
  const hide = inp.type === 'password';
  inp.type = hide ? 'text' : 'password';
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

// ── Edit Profile Modal ────────────────────────────────────────────
function openEditProfile() {
  document.getElementById('epError').style.display   = 'none';
  document.getElementById('epSuccess').style.display = 'none';
  document.getElementById('epModalBackdrop').classList.add('show');
}
function toggleEpPw(inputId, btn) {
  const inp  = document.getElementById(inputId);
  const icon = btn.querySelector('.material-icons');
  const hide = inp.type === 'password';
  inp.type = hide ? 'text' : 'password';
  icon.textContent = hide ? 'visibility_off' : 'visibility';
}

document.getElementById('epModalClose').addEventListener('click', () =>
  document.getElementById('epModalBackdrop').classList.remove('show'));
document.getElementById('epModalBackdrop').addEventListener('click', function (e) {
  if (e.target === this) this.classList.remove('show');
});
// Photo preview
document.getElementById('epPhotoPicker').addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function (e) {
    document.getElementById('epAvatarPreview').innerHTML =
      `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;" id="epAvatarImg">`;
  };
  reader.readAsDataURL(file);
});

// ── Form submit ───────────────────────────────────────────────────
document.getElementById('epForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  const errEl  = document.getElementById('epError');
  const succEl = document.getElementById('epSuccess');
  const btn    = document.getElementById('epSubmitBtn');
  errEl.style.display = 'none';
  succEl.style.display = 'none';
  btn.disabled = true;
  btn.innerHTML = '<span class="material-icons" style="font-size:17px;vertical-align:middle;">refresh</span> Saving…';
  try {
    const fd  = new FormData(this);
    const res = await fetch('index.php?url=profile/update', {
      method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    if (data.success) {
      succEl.textContent = '✓ ' + data.message;
      succEl.style.display = 'block';
      const nameEl   = document.querySelector('.profile-name');
      const handleEl = document.querySelector('.profile-handle');
      if (nameEl   && data.name) nameEl.textContent   = data.name;
      if (handleEl && data.name) handleEl.textContent = '@' + data.name.toLowerCase().replace(/[^a-z0-9]/g, '');
      if (data.profile_pic) {
        const wrap = document.getElementById('profileAvatarWrap');
        if (wrap) wrap.innerHTML = `<img src="${data.profile_pic}" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
      }
      setTimeout(() => document.getElementById('epModalBackdrop').classList.remove('show'), 1200);
    } else {
      errEl.textContent = data.message || 'Update failed.';
      errEl.style.display = 'block';
    }
  } catch (err) {
    errEl.textContent = 'Something went wrong.'; errEl.style.display = 'block';
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<span class="material-icons" style="font-size:17px;vertical-align:middle;">save</span> Save Changes';
  }
});
</script>
</body>
</html>
