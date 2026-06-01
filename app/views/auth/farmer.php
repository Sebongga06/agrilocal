<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AgriLocal</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;500;600;700&family=Roboto+Slab:wght@500;600;700;800&family=Material+Icons&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/farmer.css?v=5">
  <style>
    /* ── password wrapper ── */
    .pw-wrap { position: relative; margin-bottom: 10px; }
    .pw-wrap .auth-input { padding-right: 44px; width: 100%; box-sizing: border-box; margin-bottom: 0; }
    /* hide browser's native password reveal button */
    .pw-wrap .auth-input::-ms-reveal,
    .pw-wrap .auth-input::-ms-clear,
    .pw-wrap .auth-input::-webkit-credentials-auto-fill-button,
    .pw-wrap .auth-input::-webkit-contacts-auto-fill-button,
    .pw-wrap .auth-input::-webkit-textfield-decoration-container { display: none !important; }
    .auth-input[type="password"]::-ms-reveal,
    .auth-input[type="password"]::-ms-clear { display: none !important; }
    .pw-toggle {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #888; padding: 0;
      display: flex; align-items: center;
    }
    .pw-toggle .material-icons { font-size: 20px; }

    /* ── field error ── */
    .field-error {
      display: flex; align-items: center; gap: 5px;
      color: #c0392b; font-size: 0.8rem; margin: 2px 0 10px 2px;
      background: #fff5f5; border: 1px solid #fecaca; border-radius: 6px;
      padding: 6px 10px;
    }
    .field-error::before { content: '⚠'; font-size: .85rem; }

    /* ── password strength ── */
    .pw-strength-bar {
      height: 4px; border-radius: 2px; margin: 4px 0 6px;
      background: #eee; overflow: hidden;
    }
    .pw-strength-fill {
      height: 100%; width: 0; border-radius: 2px;
      transition: width .3s, background .3s;
    }
    .pw-strength-label { font-size: .75rem; color: #888; margin-bottom: 10px; }

    /* ── terms row ── */
    .terms-row { display: flex; align-items: flex-start; gap: 8px; margin: 8px 0 12px; font-size: 0.85rem; color: #444; }
    .terms-row input[type="checkbox"] { margin-top: 3px; flex-shrink: 0; accent-color: #2d6a4f; }
    .terms-row a { color: var(--green, #2d6a4f); text-decoration: underline; }

    /* ── switch-to-vendor modal ── */
    .sv-modal-backdrop {
      display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45);
      z-index: 9999; align-items: center; justify-content: center;
    }
    .sv-modal-backdrop.show { display: flex; }
    .sv-modal {
      background: #fff; border-radius: 16px; padding: 2rem; width: 360px;
      max-width: 95vw; box-shadow: 0 20px 60px rgba(0,0,0,.2); position: relative;
    }
    .sv-modal h3 { margin: 0 0 .5rem; font-family: 'Roboto Slab', serif; color: #1a3c2e; }
    .sv-modal p  { margin: 0 0 1rem; color: #555; font-size: .9rem; }
    .sv-modal .auth-input { width: 100%; box-sizing: border-box; margin-bottom: 4px; }
    .sv-modal-close {
      position: absolute; top: 12px; right: 14px; background: none; border: none;
      font-size: 1.3rem; cursor: pointer; color: #888;
    }
    .sv-modal .btn-main { width: 100%; margin-top: 10px; }
    .sv-error { color: #c0392b; font-size: .82rem; margin-bottom: 8px; display: none; }

    /* ── terms modal ── */
    .terms-modal-backdrop {
      display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5);
      z-index: 10000; align-items: center; justify-content: center;
    }
    .terms-modal-backdrop.show { display: flex; }
    .terms-modal {
      background: #fff; border-radius: 16px; padding: 2rem; width: 520px;
      max-width: 95vw; max-height: 80vh; overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,.2); position: relative;
    }
    .terms-modal h3 { margin: 0 0 1rem; font-family: 'Roboto Slab', serif; color: #1a3c2e; }
    .terms-modal p  { color: #555; font-size: .88rem; line-height: 1.6; margin-bottom: .75rem; }
    .terms-modal-close {
      position: absolute; top: 12px; right: 14px; background: none; border: none;
      font-size: 1.3rem; cursor: pointer; color: #888;
    }

    /* ── buyer-side red accent buttons ── */
    .btn-primary {
      background: var(--red-accent, #9a3718) !important;
      border-color: var(--red-accent, #9a3718) !important;
    }
    .btn-primary:hover { background: #7d2c12 !important; }
  </style>
  <!-- Leaflet CSS (vendor pin map) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>

<?php
$fieldError = $fieldError ?? [];
?>

  <!-- Splash -->
  <div id="splashScreen" class="splash"<?= (!empty($error) || !empty($fieldError)) ? ' style="display:none"' : ''; ?>>
    <div class="container">
      <div class="icons">
        <img src="./assets/img/eggplant.png" class="icon" alt="Eggplant">
        <img src="./assets/img/lettuce.png"  class="icon" alt="Lettuce">
        <img src="./assets/img/tomato.png"   class="icon" alt="Tomato">
      </div>
      <div class="text">LOADING</div>
    </div>
  </div>

  <!-- Hero page -->
  <div id="page1" class="page active">
    <section class="hero-page">
      <div class="hero-header-wrap">
        <header class="hero-nav">
          <div class="nav-left">
            <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
            <span class="brand-name">AgriLocal</span>
          </div>
          <div class="nav-right">
            <nav class="nav-links">
              <a href="#aboutSection">About</a>
              <a href="#howItWorksSection">How It Works</a>
              <a href="#contactSection">Contact</a>
            </nav>
          </div>
        </header>
      </div>
      <div class="hero-content">
        <div class="hero-inner">
          <h1>Organic and Local produced</h1>
          <p>Fresh, local, and seasonal. Shop directly from farmers and artisans for just-picked produce, homemade goods, and handmade crafts.</p>
          <button class="hero-main-btn" id="getStartedBtn">Get Started</button>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="aboutSection" class="info-section about-section">
      <div class="info-container">
        <div class="info-header">
          <h2>About AgriLocal</h2>
          <p>Connecting communities with fresh, local agriculture</p>
        </div>
        <div class="info-content">
          <div class="info-card">
            <div class="info-icon"><span class="material-icons">eco</span></div>
            <h3>Sustainable Farming</h3>
            <p>We support local farmers who practice sustainable and organic farming methods, ensuring fresh produce while protecting our environment.</p>
          </div>
          <div class="info-card">
            <div class="info-icon"><span class="material-icons">people</span></div>
            <h3>Community Focused</h3>
            <p>AgriLocal builds direct connections between farmers and consumers, creating a thriving local food ecosystem that benefits everyone.</p>
          </div>
          <div class="info-card">
            <div class="info-icon"><span class="material-icons">verified</span></div>
            <h3>Quality Assured</h3>
            <p>Every vendor on our platform is verified and committed to delivering the highest quality products directly from farm to table.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="howItWorksSection" class="info-section how-it-works-section">
      <div class="info-container">
        <div class="info-header">
          <h2>How It Works</h2>
          <p>Simple steps to get fresh local produce</p>
        </div>
        <div class="steps-content">
          <div class="step-card">
            <div class="step-number">1</div>
            <div class="step-icon"><span class="material-icons">person_add</span></div>
            <h3>Create Account</h3>
            <p>Sign up as a buyer or vendor in just a few minutes. Choose your role and complete your profile.</p>
          </div>
          <div class="step-card">
            <div class="step-number">2</div>
            <div class="step-icon"><span class="material-icons">map</span></div>
            <h3>Explore Vendors</h3>
            <p>Browse local vendors on our interactive map. Filter by category, distance, and ratings to find what you need.</p>
          </div>
          <div class="step-card">
            <div class="step-number">3</div>
            <div class="step-icon"><span class="material-icons">shopping_cart</span></div>
            <h3>Shop &amp; Order</h3>
            <p>Add products to your cart and place orders directly from your favorite local vendors.</p>
          </div>
          <div class="step-card">
            <div class="step-number">4</div>
            <div class="step-icon"><span class="material-icons">local_shipping</span></div>
            <h3>Receive Fresh Produce</h3>
            <p>Get your fresh, locally-sourced products delivered or pick them up from the vendor's location.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contactSection" class="info-section contact-section">
      <div class="info-container">
        <div class="info-header">
          <h2>Get In Touch</h2>
          <p>We'd love to hear from you</p>
        </div>
        <div class="contact-content">
          <div class="contact-info">
            <div class="contact-item">
              <div class="contact-item-icon"><span class="material-icons">email</span></div>
              <div class="contact-item-text">
                <h3>Email Us</h3>
                <p>Reach out to our support team at support@agrilocal.com for any inquiries or assistance.</p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-item-icon"><span class="material-icons">phone</span></div>
              <div class="contact-item-text">
                <h3>Call Us</h3>
                <p>Connect with us by phone at +1 (234) 567-890 during business hours for immediate support.</p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-item-icon"><span class="material-icons">mail</span></div>
              <div class="contact-item-text">
                <h3>Send a Message</h3>
                <p>Fill out the contact form to send us a message directly. We'll get back to you as soon as possible.</p>
              </div>
            </div>
          </div>
          <div class="contact-form-wrapper">
            <form id="contactForm" novalidate>
              <input type="text" name="name" placeholder="Your Name" required>
              <input type="email" name="email" placeholder="Your Email" required>
              <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
              <button type="submit" class="btn-contact">Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
      <div class="footer-container">
        <div class="footer-column">
          <h3 class="footer-logo"><img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;"> AgriLocal</h3>
          <p class="footer-tagline">Supporting local agriculture, one harvest at a time.</p>
        </div>
        <div class="footer-column">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="#aboutSection">About Us</a></li>
            <li><a href="#howItWorksSection">How It Works</a></li>
            <li><a href="#contactSection">Contact</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h4>Support</h4>
          <ul class="footer-links">
            <li><a href="mailto:support@agrilocal.com">support@agrilocal.com</a></li>
            <li><a href="tel:+1234567890">+1 (234) 567-890</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 AgriLocal. All rights reserved.</p>
      </div>
    </footer>

    <!-- Return to Top Button -->
    <button id="returnToTopBtn" class="return-to-top" title="Return to top">
      <span class="material-icons">arrow_upward</span>
    </button>

  </div><!-- end #page1 -->
  <div id="roleModal" class="modal">
    <div class="modal-card">
      <div class="modal-close" id="closeModal">✕</div>
      <h2>Join AgriLocal</h2>
      <div class="role-options">
        <button type="button" class="role-card" id="buyerRoleBtn">
          <div class="role-icon"><img src="./assets/img/shopping-cart.png" alt="Buyer" /></div>
          <h3>Buyer</h3><p>Shop fresh produce</p>
        </button>
        <button type="button" class="role-card" id="vendorRoleBtn">
          <div class="role-icon"><img src="./assets/img/store.png" alt="Vendor" /></div>
          <h3>Vendor</h3><p>Sell your harvest</p>
        </button>
      </div>
      <div class="login-link" id="alreadyAccountLink">Already have an account?</div>
    </div>
  </div>

  <!-- Auth page -->
  <div id="page3" class="page">
    <section class="auth-page">
      <div class="auth-wrapper">
        <div class="auth-left"></div>
        <div class="auth-right">
          <button type="button" id="backToLandingBtn" onclick="showPage('page1')"
            style="display:flex;align-items:center;gap:4px;background:none;border:none;
                   color:#276749;font-family:'Roboto Slab',serif;font-size:.85rem;
                   font-weight:600;cursor:pointer;padding:0;margin-bottom:1rem;">
            <span class="material-icons" style="font-size:18px;">arrow_back</span> Back
          </button>
          <div class="tabs">
            <button id="loginTab"    class="tab-btn active" type="button">Login</button>
            <button id="registerTab" class="tab-btn"        type="button">Register</button>
          </div>

          <!-- ── LOGIN FORM ── -->
          <form id="loginForm" class="form-section active" method="POST" action="index.php?url=auth/login" novalidate autocomplete="off">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="role" id="loginRoleInput" value="<?= htmlspecialchars($_POST['role'] ?? 'buyer'); ?>">

            <?php if (!empty($fieldError['role'])): ?>
              <div class="error-box"><?= htmlspecialchars($fieldError['role']); ?></div>
            <?php endif; ?>

            <div>
              <input class="auth-input" type="email" name="email" placeholder="Email" autocomplete="off">
              <?php if (!empty($fieldError['email'])): ?>
                <span class="field-error"><?= htmlspecialchars($fieldError['email']); ?></span>
              <?php endif; ?>
            </div>

            <div class="pw-wrap">
              <input class="auth-input" type="password" name="password" id="loginPassword" placeholder="Password" autocomplete="new-password">
              <button type="button" class="pw-toggle" onclick="togglePw('loginPassword', this)" aria-label="Toggle password visibility">
                <span class="material-icons">visibility</span>
              </button>
            </div>
            <?php if (!empty($fieldError['password'])): ?>
              <span class="field-error"><?= htmlspecialchars($fieldError['password']); ?></span>
            <?php endif; ?>

            <div class="login-options">
              <label class="remember-me"><input type="checkbox"> Remember me</label>
              <a href="#" class="forgot-password">Forgot password?</a>
            </div>

            <button class="btn-main" type="submit">Login</button>
          </form>

          <!-- ── REGISTER FORM ── -->
          <form id="registerForm" class="form-section" method="POST" action="index.php?url=auth/register" novalidate>
            <input type="hidden" name="role" id="registerRoleInput" value="buyer">
            <p id="registerRoleLabel" style="font-size:.82rem;font-weight:600;color:#276749;margin:0 0 10px;text-align:center;letter-spacing:.3px;">Create Buyer Account</p>

            <?php if (!empty($error) && empty($fieldError)): ?>
              <div class="error-box"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div>
              <input class="auth-input" type="text" name="full_name" placeholder="Full name" autocomplete="name">
              <?php if (!empty($fieldError['full_name'])): ?>
                <span class="field-error"><?= htmlspecialchars($fieldError['full_name']); ?></span>
              <?php endif; ?>
            </div>

            <div>
              <input class="auth-input" type="email" name="email" placeholder="Email" autocomplete="email">
              <?php if (!empty($fieldError['email'])): ?>
                <span class="field-error"><?= htmlspecialchars($fieldError['email']); ?></span>
              <?php endif; ?>
            </div>

            <div class="pw-wrap">
              <input class="auth-input" type="password" name="password" id="registerPassword" placeholder="Password" autocomplete="new-password">
              <button type="button" class="pw-toggle" onclick="togglePw('registerPassword', this)" aria-label="Toggle password visibility">
                <span class="material-icons">visibility</span>
              </button>
              <?php if (!empty($fieldError['password'])): ?>
                <span class="field-error"><?= htmlspecialchars($fieldError['password']); ?></span>
              <?php endif; ?>
            </div>
            <div class="pw-strength-bar"><div class="pw-strength-fill" id="strengthFill"></div></div>
            <div class="pw-strength-label" id="strengthLabel"></div>

            <input class="auth-input" type="tel" name="phone" placeholder="Phone Number" style="margin-bottom:10px;">

            <!-- Address — same search+map style for both buyer and vendor -->
            <div id="buyerAddressFields">
              <p style="font-size:.78rem;font-weight:600;color:#276749;margin:0 0 6px;">
                <span class="material-icons" style="font-size:13px;vertical-align:middle;">location_on</span>
                Your Location
              </p>
              <!-- Address search with autocomplete -->
              <div style="position:relative;margin-bottom:8px;">
                <input type="text" id="buyerAddrSearch"
                       placeholder="Search your address…"
                       autocomplete="off"
                       style="width:100%;box-sizing:border-box;height:42px;padding:0 14px;
                              border:1.5px solid #9ae6b4;border-radius:8px;font-size:.88rem;
                              outline:none;font-family:inherit;background:#f0fff4;color:#10212b;">
                <div id="buyerAddrDropdown"
                     style="display:none;position:absolute;left:0;right:0;top:100%;
                            background:#fff;border:1.5px solid #9ae6b4;border-top:none;
                            border-radius:0 0 8px 8px;z-index:500;max-height:180px;
                            overflow-y:auto;box-shadow:0 6px 16px rgba(0,0,0,.1);"></div>
              </div>
              <!-- Map -->
              <div style="border:1.5px solid #9ae6b4;border-radius:10px;overflow:hidden;">
                <div style="display:flex;align-items:center;gap:.4rem;padding:.4rem .75rem;
                            background:#f0fff4;border-bottom:1px solid #9ae6b4;
                            font-size:.78rem;font-weight:600;color:#276749;">
                  <span class="material-icons" style="font-size:14px;">pin_drop</span>
                  Click the map or search above to pin your location
                  <button type="button" id="btnBuyerMyLocation"
                          style="margin-left:auto;display:flex;align-items:center;gap:3px;
                                 padding:.28rem .6rem;background:#276749;color:#fff;
                                 border:none;border-radius:6px;font-size:.75rem;
                                 font-weight:600;cursor:pointer;white-space:nowrap;">
                    <span class="material-icons" style="font-size:13px;">my_location</span> My Location
                  </button>
                </div>
                <div id="buyerRegMap" style="height:200px;width:100%;cursor:crosshair;"></div>
                <div id="buyerPinFooter"
                     style="padding:.4rem .75rem;background:#f0fff4;font-size:.75rem;color:#555;">
                  No location pinned yet.
                </div>
              </div>
              <!-- hidden structured fields still submitted for DB -->
              <input type="hidden" name="region"   id="regRegionHidden">
              <input type="hidden" name="city"     id="regCityHidden">
              <input type="hidden" name="barangay" id="regBarangayInput">
            </div>
            <!-- Hidden address + lat/lng — written by address geocoding or pin map -->
            <input type="hidden" name="address" id="regAddressInput">
            <input type="hidden" name="reg_lat" id="regAddrLat">
            <input type="hidden" name="reg_lng" id="regAddrLng">

            <!-- Vendor farm location — address search + pin map (only shown for vendor role) -->
            <div id="vndMapWrap" style="display:none;">
              <p style="font-size:.78rem;font-weight:600;color:#276749;margin:0 0 6px;">
                <span class="material-icons" style="font-size:13px;vertical-align:middle;">location_on</span>
                Farm Location
              </p>

              <!-- Address search with autocomplete -->
              <div style="position:relative;margin-bottom:8px;">
                <input type="text" id="vndAddrSearch"
                       placeholder="Search your farm address…"
                       autocomplete="off"
                       style="width:100%;box-sizing:border-box;height:42px;padding:0 14px;
                              border:1.5px solid #9ae6b4;border-radius:8px;font-size:.88rem;
                              outline:none;font-family:inherit;background:#f0fff4;color:#10212b;">
                <div id="vndAddrDropdown"
                     style="display:none;position:absolute;left:0;right:0;top:100%;
                            background:#fff;border:1.5px solid #9ae6b4;border-top:none;
                            border-radius:0 0 8px 8px;z-index:500;max-height:180px;
                            overflow-y:auto;box-shadow:0 6px 16px rgba(0,0,0,.1);"></div>
              </div>

              <!-- Map -->
              <div style="border:1.5px solid #9ae6b4;border-radius:10px;overflow:hidden;">
                <div style="display:flex;align-items:center;gap:.4rem;padding:.4rem .75rem;
                            background:#f0fff4;border-bottom:1px solid #9ae6b4;
                            font-size:.78rem;font-weight:600;color:#276749;">
                  <span class="material-icons" style="font-size:14px;">pin_drop</span>
                  Click the map or search above to pin your farm location
                  <button type="button" id="btnVndMyLocation"
                          style="margin-left:auto;display:flex;align-items:center;gap:3px;
                                 padding:.28rem .6rem;background:#276749;color:#fff;
                                 border:none;border-radius:6px;font-size:.75rem;
                                 font-weight:600;cursor:pointer;white-space:nowrap;">
                    <span class="material-icons" style="font-size:13px;">my_location</span> My Location
                  </button>
                </div>
                <div id="vndRegMap" style="height:220px;width:100%;cursor:crosshair;"></div>
                <div id="vndPinFooter"
                     style="padding:.4rem .75rem;background:#f0fff4;font-size:.75rem;color:#555;">
                  No location pinned yet.
                </div>
              </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="terms-row">
              <input type="checkbox" name="terms" id="termsCheck" value="1">
              <label for="termsCheck">
                I agree to the <a href="#" id="openTermsLink">Terms and Conditions</a>
              </label>
            </div>
            <?php if (!empty($fieldError['terms'])): ?>
              <span class="field-error"><?= htmlspecialchars($fieldError['terms']); ?></span>
            <?php endif; ?>

            <button class="btn-main" type="submit">Create Account</button>
          </form>
        </div>
      </div>
    </section>
  </div>

  <!-- ── Switch-to-Vendor Modal ── -->
  <div class="sv-modal-backdrop" id="svModalBackdrop">
    <div class="sv-modal">
      <button class="sv-modal-close" id="svModalClose" type="button">✕</button>
      <h3>Switch to Vendor</h3>
      <p>Enter your vendor account password to switch profiles.</p>
      <span class="sv-error" id="svError"></span>
      <div class="pw-wrap">
        <input class="auth-input" type="password" id="svPassword" placeholder="Vendor account password" autocomplete="current-password">
        <button type="button" class="pw-toggle" onclick="togglePw('svPassword', this)" aria-label="Toggle password visibility">
          <span class="material-icons">visibility</span>
        </button>
      </div>
      <button class="btn-main" id="svSubmitBtn" type="button">Switch Account</button>
    </div>
  </div>

  <!-- ── Terms & Conditions Modal ── -->
  <div class="terms-modal-backdrop" id="termsModalBackdrop">
    <div class="terms-modal">
      <button class="terms-modal-close" id="termsModalClose" type="button">✕</button>
      <h3>Terms and Conditions</h3>
      <p>Welcome to AgriLocal. By creating an account, you agree to the following terms:</p>
      <p><strong>1. Account Responsibility</strong><br>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
      <p><strong>2. Use of Platform</strong><br>AgriLocal is a marketplace connecting local farmers and buyers. You agree to use the platform only for lawful purposes and in accordance with these terms.</p>
      <p><strong>3. Vendor Listings</strong><br>Vendors are responsible for the accuracy of their product listings, pricing, and availability. AgriLocal does not guarantee the quality of products listed.</p>
      <p><strong>4. Transactions</strong><br>All transactions are between buyers and vendors. AgriLocal facilitates the connection but is not a party to any transaction.</p>
      <p><strong>5. Privacy</strong><br>Your personal information is collected and used in accordance with our Privacy Policy. We do not sell your data to third parties.</p>
      <p><strong>6. Modifications</strong><br>AgriLocal reserves the right to modify these terms at any time. Continued use of the platform constitutes acceptance of the updated terms.</p>
      <p style="margin-top:1rem; color:#888; font-size:.8rem;">Last updated: April 2026</p>
    </div>
  </div>

<script>
  let selectedRole = 'buyer';

  // ── page helpers ──
  function showPage(id) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const t = document.getElementById(id);
    if (t) t.classList.add('active');
  }

  // ── password toggle ──
  function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    const icon = btn.querySelector('.material-icons');
    if (icon) icon.textContent = isHidden ? 'visibility_off' : 'visibility';
  }

  // ── role inputs sync ──
  function syncRoleInputs() {
    document.getElementById('loginRoleInput').value    = selectedRole;
    document.getElementById('registerRoleInput').value = selectedRole;
  }

  // ── auth image ──
  function updateAuthImage() {
    const authLeft = document.querySelector('.auth-left');
    if (!authLeft) return;
    const loginActive = document.getElementById('loginForm').classList.contains('active');
    const map = {
      buyer:  { login: './assets/img/straberry.jpg', register: './assets/img/chicken.jpg' },
      vendor: { login: './assets/img/farmer.jpg',    register: './assets/img/farmerhat.jpg' },
    };
    authLeft.style.backgroundImage = `url('${map[selectedRole][loginActive ? 'login' : 'register']}')`;
  }

  // ── role modal ──
  const roleModal = document.getElementById('roleModal');
  document.getElementById('getStartedBtn').addEventListener('click', () => roleModal.classList.add('show'));
  document.getElementById('closeModal').addEventListener('click',    () => roleModal.classList.remove('show'));
  roleModal.addEventListener('click', e => { if (e.target === roleModal) roleModal.classList.remove('show'); });

  // Smooth scroll for nav links
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', e => {
      const href = link.getAttribute('href');
      if (href && href.startsWith('#')) {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Return to Top
  const returnToTopBtn = document.getElementById('returnToTopBtn');
  if (returnToTopBtn) {
    window.addEventListener('scroll', () => {
      returnToTopBtn.classList.toggle('show', window.pageYOffset > 300);
    });
    returnToTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  // ── show login tab ──
  function showLoginTab() {
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('registerTab').classList.remove('active');
    document.getElementById('loginForm').classList.add('active');
    document.getElementById('registerForm').classList.remove('active');
    syncRoleInputs();
    clearError();
    updateAuthImage();
  }

  // ── show register tab ──
  function showRegisterTab() {
    document.getElementById('registerTab').classList.add('active');
    document.getElementById('loginTab').classList.remove('active');
    document.getElementById('registerForm').classList.add('active');
    document.getElementById('loginForm').classList.remove('active');
    syncRoleInputs();
    clearError();
    updateAuthImage();
    updateRegisterLabel();
    syncVndMap();
  }

  // ── update register heading to reflect selected role ──
  function updateRegisterLabel() {
    const label = document.getElementById('registerRoleLabel');
    if (label) {
      label.textContent = selectedRole === 'vendor'
        ? 'Create Vendor Account'
        : 'Create Buyer Account';
    }
  }

  // ── pick role from modal ──
  // Buyer/Vendor card → go straight to Register tab
  // "Already have an account?" → go to Login tab
  function pickRole(role, destination) {
    selectedRole = role;
    syncRoleInputs();
    roleModal.classList.remove('show');
    showPage('page3');
    if (destination === 'register') {
      showRegisterTab();
    } else {
      showLoginTab();
    }
  }

  document.getElementById('buyerRoleBtn').addEventListener('click',       () => pickRole('buyer',  'login'));
  document.getElementById('vendorRoleBtn').addEventListener('click',      () => pickRole('vendor', 'login'));
  document.getElementById('alreadyAccountLink').addEventListener('click', () => {
    roleModal.classList.remove('show');
    showPage('page3');
    showLoginTab();
  });

  // ── tab buttons ──
  document.getElementById('loginTab').addEventListener('click',    showLoginTab);
  document.getElementById('registerTab').addEventListener('click', showRegisterTab);

  function clearError() {
    document.querySelectorAll('.error-box').forEach(el => el.style.display = 'none');
  }

  // ── Negros Island location data (shared with profile edit) ──
  const negrosData = {
    "Negros Occidental": [
      "Bacolod City","Bago City","Cadiz City","Himamaylan City","Kabankalan City",
      "La Carlota City","Sagay City","San Carlos City","Silay City","Talisay City",
      "Victorias City","Escalante City",
      "Binalbagan","Calatrava","Candoni","Cauayan","Enrique B. Magalona",
      "Hinigaran","Hinoba-an","Ilog","Isabela","La Castellana",
      "Manapla","Moises Padilla","Murcia","Pontevedra","Pulupandan",
      "Salvador Benedicto","San Enrique","Toboso","Valladolid"
    ],
    "Negros Oriental": [
      "Bais City","Bayawan City","Canlaon City","Dumaguete City",
      "Guihulngan City","Tanjay City",
      "Amlan","Ayungon","Bacong","Basay","Bindoy","Dauin","Jimalalud",
      "La Libertad","Mabinay","Manjuyod","Pamplona","San Jose",
      "Santa Catalina","Siaton","Sibulan","Tayasan","Valencia",
      "Vallehermoso","Zamboanguita"
    ],
    "Siquijor": [
      "Enrique Villanueva","Larena","Lazi","Maria","San Juan","Siquijor"
    ]
  };

  // Address is now set directly by the map/search for both buyer and vendor.
  // regUpdateFullAddress kept as no-op for any legacy references.
  function regUpdateFullAddress() {}

  // Geocode address → lat/lng via Nominatim before form submit
  async function geocodeRegAddress(addressStr) {
    if (!addressStr) return null;
    try {
      const q   = encodeURIComponent(addressStr + ', Philippines');
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ph&addressdetails=1&q=${q}`,
        { headers: { 'Accept-Language': 'en' } }
      );
      const data = await res.json();
      if (data && data[0] && data[0].lat) {
        return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
      }
    } catch (e) { /* non-fatal */ }
    return null;
  }

  // Intercept form submit: geocode address, fill hidden fields, then submit
  document.getElementById('registerForm').addEventListener('submit', async function (e) {
    const addrInput = document.getElementById('regAddressInput');
    const latInput  = document.getElementById('regAddrLat');
    const lngInput  = document.getElementById('regAddrLng');

    // If coords already set (e.g. from vendor pin map), skip geocoding
    if (latInput.value && lngInput.value) return;

    const addr = addrInput?.value?.trim();
    if (!addr) return; // no address — let server handle it

    e.preventDefault(); // hold submission while we geocode

    const btn = this.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }

    const coords = await geocodeRegAddress(addr);
    if (coords) {
      latInput.value = coords.lat;
      lngInput.value = coords.lng;
    }

    if (btn) { btn.disabled = false; btn.textContent = 'Create Account'; }
    this.submit(); // now actually submit
  });

  syncRoleInputs();
  updateAuthImage();

  // ── Switch-to-Vendor modal ──
  const svBackdrop  = document.getElementById('svModalBackdrop');
  const svError     = document.getElementById('svError');
  const svPassword  = document.getElementById('svPassword');
  const svSubmitBtn = document.getElementById('svSubmitBtn');

  window.openSwitchVendorModal = function () {
    svError.style.display = 'none';
    svPassword.value = '';
    svBackdrop.classList.add('show');
    svPassword.focus();
  };

  document.getElementById('svModalClose').addEventListener('click', () => svBackdrop.classList.remove('show'));
  svBackdrop.addEventListener('click', e => { if (e.target === svBackdrop) svBackdrop.classList.remove('show'); });

  svSubmitBtn.addEventListener('click', async function () {
    svError.style.display = 'none';
    const pw = svPassword.value.trim();
    if (!pw) { svError.textContent = 'Password is required.'; svError.style.display = 'block'; return; }

    svSubmitBtn.disabled = true;
    svSubmitBtn.textContent = 'Switching…';

    try {
      const fd = new FormData();
      fd.append('vendor_password', pw);
      const res  = await fetch('index.php?url=auth/switchToVendor', {
        method: 'POST', body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        svError.textContent = data.message || 'Switch failed.';
        svError.style.display = 'block';
      }
    } catch (e) {
      svError.textContent = 'Something went wrong. Please try again.';
      svError.style.display = 'block';
    } finally {
      svSubmitBtn.disabled = false;
      svSubmitBtn.textContent = 'Switch Account';
    }
  });

  // ── Password strength ──
  const regPw = document.getElementById('registerPassword');
  const strengthFill  = document.getElementById('strengthFill');
  const strengthLabel = document.getElementById('strengthLabel');
  if (regPw && strengthFill) {
    regPw.addEventListener('input', function () {
      const v = this.value;
      let score = 0;
      if (v.length >= 8)              score++;
      if (/[A-Z]/.test(v))            score++;
      if (/[0-9]/.test(v))            score++;
      if (/[^A-Za-z0-9]/.test(v))     score++;
      const levels = [
        { w: '0%',   bg: '#eee',     label: '' },
        { w: '25%',  bg: '#e74c3c',  label: 'Weak' },
        { w: '50%',  bg: '#e67e22',  label: 'Fair' },
        { w: '75%',  bg: '#f1c40f',  label: 'Good' },
        { w: '100%', bg: '#27ae60',  label: 'Strong' },
      ];
      const lvl = v.length === 0 ? levels[0] : levels[score] || levels[1];
      strengthFill.style.width      = lvl.w;
      strengthFill.style.background = lvl.bg;
      strengthLabel.textContent     = lvl.label;
      strengthLabel.style.color     = lvl.bg;
    });
  }

  // ── Terms modal ──
  const termsBackdrop = document.getElementById('termsModalBackdrop');
  document.getElementById('openTermsLink').addEventListener('click', e => {
    e.preventDefault();
    termsBackdrop.classList.add('show');
  });
  document.getElementById('termsModalClose').addEventListener('click', () => termsBackdrop.classList.remove('show'));
  termsBackdrop.addEventListener('click', e => { if (e.target === termsBackdrop) termsBackdrop.classList.remove('show'); });

  // ── splash / error redirect ──
  window.addEventListener('load', function () {
    const hasError = <?= (!empty($error) || !empty($fieldError)) ? 'true' : 'false'; ?>;
    const isRegisterError = <?= (($formType ?? '') === 'register') ? 'true' : 'false'; ?>;
    // Restore submitted role so vendor login errors stay on the vendor tab
    const submittedRole = <?= json_encode($_POST['role'] ?? 'buyer'); ?>;

    if (hasError) {
      selectedRole = submittedRole;
      syncRoleInputs();
      const splash = document.getElementById('splashScreen');
      if (splash) splash.style.display = 'none';
      showPage('page3');
      if (isRegisterError) {
        showRegisterTab();
      } else {
        showLoginTab();
      }
      return;
    }

    setTimeout(() => {
      const splash = document.getElementById('splashScreen');
      if (splash) splash.classList.add('hide');
    }, 1200);
  });
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Buyer registration pin map ────────────────────────────────────
let buyerRegMap = null, buyerRegMarker = null;

function initBuyerRegMap() {
  if (buyerRegMap) { buyerRegMap.invalidateSize(); return; }
  buyerRegMap = L.map('buyerRegMap').setView([10.6755, 122.9588], 10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
  }).addTo(buyerRegMap);
  buyerRegMap.on('click', function (e) { placeBuyerRegPin(e.latlng.lat, e.latlng.lng); });
}

async function placeBuyerRegPin(lat, lng) {
  document.getElementById('regAddrLat').value = lat;
  document.getElementById('regAddrLng').value = lng;
  if (buyerRegMarker) {
    buyerRegMarker.setLatLng([lat, lng]);
  } else {
    buyerRegMarker = L.marker([lat, lng], { draggable: true }).addTo(buyerRegMap);
    buyerRegMarker.on('dragend', function () {
      const p = buyerRegMarker.getLatLng();
      placeBuyerRegPin(p.lat, p.lng);
    });
  }
  const footer = document.getElementById('buyerPinFooter');
  if (footer) footer.textContent = 'Locating…';
  const addr = await reverseGeocodeVnd(lat, lng); // reuse same reverse geocode fn
  if (footer) footer.textContent = '📍 ' + addr;
  const addrHidden = document.getElementById('regAddressInput');
  if (addrHidden) addrHidden.value = addr;
  buyerRegMarker.bindPopup(`<b>Your location</b><br><small>${addr}</small>`).openPopup();
}

// Buyer address search autocomplete
let buyerSearchTimer = null;
const buyerAddrSearch   = document.getElementById('buyerAddrSearch');
const buyerAddrDropdown = document.getElementById('buyerAddrDropdown');

if (buyerAddrSearch) {
  buyerAddrSearch.addEventListener('input', function () {
    clearTimeout(buyerSearchTimer);
    const q = this.value.trim();
    if (q.length < 4) { buyerAddrDropdown.style.display = 'none'; return; }
    buyerSearchTimer = setTimeout(async () => {
      try {
        const res  = await fetch(
          `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=ph&addressdetails=1&q=${encodeURIComponent(q + ', Philippines')}`,
          { headers: { 'Accept-Language': 'en' } }
        );
        const results = await res.json();
        buyerAddrDropdown.innerHTML = '';
        if (!results.length) {
          buyerAddrDropdown.innerHTML = '<div style="padding:.45rem .75rem;font-size:.8rem;color:#888;">No results found.</div>';
          buyerAddrDropdown.style.display = 'block';
          return;
        }
        results.forEach(r => {
          const item = document.createElement('div');
          item.style.cssText = 'padding:.4rem .75rem;font-size:.8rem;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#333;line-height:1.4;';
          item.textContent = r.display_name;
          item.addEventListener('mouseenter', () => item.style.background = '#f0fff4');
          item.addEventListener('mouseleave', () => item.style.background = '');
          item.addEventListener('mousedown', e => {
            e.preventDefault();
            const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
            buyerAddrSearch.value = r.display_name;
            buyerAddrDropdown.style.display = 'none';
            const addrHidden = document.getElementById('regAddressInput');
            if (addrHidden) addrHidden.value = r.display_name;
            if (buyerRegMap) buyerRegMap.setView([lat, lng], 15);
            placeBuyerRegPin(lat, lng);
          });
          buyerAddrDropdown.appendChild(item);
        });
        buyerAddrDropdown.style.display = 'block';
      } catch { buyerAddrDropdown.style.display = 'none'; }
    }, 400);
  });
  buyerAddrSearch.addEventListener('blur', () => setTimeout(() => { buyerAddrDropdown.style.display = 'none'; }, 150));
}

document.getElementById('btnBuyerMyLocation')?.addEventListener('click', function () {
  if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
  const btn = this;
  btn.innerHTML = '<span class="material-icons" style="font-size:13px;">my_location</span> Locating…';
  navigator.geolocation.getCurrentPosition(
    pos => {
      if (buyerRegMap) buyerRegMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
      placeBuyerRegPin(pos.coords.latitude, pos.coords.longitude);
      btn.innerHTML = '<span class="material-icons" style="font-size:13px;">my_location</span> My Location';
    },
    () => {
      alert('Unable to get your location.');
      btn.innerHTML = '<span class="material-icons" style="font-size:13px;">my_location</span> My Location';
    }
  );
});

// ── Vendor farm pin map ───────────────────────────────────────────
let vndMap = null, vndMarker = null;

function showVndMap() {
  const wrap = document.getElementById('vndMapWrap');
  if (wrap) wrap.style.display = 'block';
  setTimeout(initVndMap, 60);
}

function hideVndMap() {
  const wrap = document.getElementById('vndMapWrap');
  if (wrap) wrap.style.display = 'none';
  document.getElementById('regAddrLat').value = '';
  document.getElementById('regAddrLng').value = '';
}

function initVndMap() {
  if (vndMap) { vndMap.invalidateSize(); return; }
  vndMap = L.map('vndRegMap').setView([10.6755, 122.9588], 10);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
  }).addTo(vndMap);
  vndMap.on('click', function (e) { placeVndPin(e.latlng.lat, e.latlng.lng); });
}

async function reverseGeocodeVnd(lat, lng) {
  try {
    const res  = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`,
      { headers: { 'Accept-Language': 'en' } }
    );
    const data = await res.json();
    return data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
  } catch { return `${lat.toFixed(5)}, ${lng.toFixed(5)}`; }
}

async function placeVndPin(lat, lng) {
  document.getElementById('regAddrLat').value = lat;
  document.getElementById('regAddrLng').value = lng;
  if (vndMarker) {
    vndMarker.setLatLng([lat, lng]);
  } else {
    vndMarker = L.marker([lat, lng], { draggable: true }).addTo(vndMap);
    vndMarker.on('dragend', function () {
      const p = vndMarker.getLatLng();
      placeVndPin(p.lat, p.lng);
    });
  }
  const footer = document.getElementById('vndPinFooter');
  if (footer) footer.textContent = 'Locating…';
  const addr = await reverseGeocodeVnd(lat, lng);
  if (footer) footer.textContent = '📍 ' + addr;
  // Also store the resolved address in the hidden address field
  const addrHidden = document.getElementById('regAddressInput');
  if (addrHidden) addrHidden.value = addr;
  vndMarker.bindPopup(`<b>Farm location</b><br><small>${addr}</small>`).openPopup();
}

// Address search autocomplete
let vndSearchTimer = null;
const vndAddrSearch   = document.getElementById('vndAddrSearch');
const vndAddrDropdown = document.getElementById('vndAddrDropdown');

if (vndAddrSearch) {
  vndAddrSearch.addEventListener('input', function () {
    clearTimeout(vndSearchTimer);
    const q = this.value.trim();
    if (q.length < 4) { vndAddrDropdown.style.display = 'none'; return; }
    vndSearchTimer = setTimeout(async () => {
      try {
        const res  = await fetch(
          `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=ph&addressdetails=1&q=${encodeURIComponent(q + ', Philippines')}`,
          { headers: { 'Accept-Language': 'en' } }
        );
        const results = await res.json();
        vndAddrDropdown.innerHTML = '';
        if (!results.length) {
          vndAddrDropdown.innerHTML = '<div style="padding:.45rem .75rem;font-size:.8rem;color:#888;">No results found.</div>';
          vndAddrDropdown.style.display = 'block';
          return;
        }
        results.forEach(r => {
          const item = document.createElement('div');
          item.style.cssText = 'padding:.4rem .75rem;font-size:.8rem;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#333;line-height:1.4;';
          item.textContent = r.display_name;
          item.addEventListener('mouseenter', () => item.style.background = '#f0fff4');
          item.addEventListener('mouseleave', () => item.style.background = '');
          item.addEventListener('mousedown', e => {
            e.preventDefault();
            const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
            vndAddrSearch.value = r.display_name;
            vndAddrDropdown.style.display = 'none';
            // Store in hidden address field
            const addrHidden = document.getElementById('regAddressInput');
            if (addrHidden) addrHidden.value = r.display_name;
            if (vndMap) vndMap.setView([lat, lng], 15);
            placeVndPin(lat, lng);
          });
          vndAddrDropdown.appendChild(item);
        });
        vndAddrDropdown.style.display = 'block';
      } catch { vndAddrDropdown.style.display = 'none'; }
    }, 400);
  });
  vndAddrSearch.addEventListener('blur', () => setTimeout(() => { vndAddrDropdown.style.display = 'none'; }, 150));
}

document.getElementById('btnVndMyLocation').addEventListener('click', function () {
  if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
  const btn = this;
  btn.innerHTML = '<span class="material-icons" style="font-size:13px;">my_location</span> Locating…';
  navigator.geolocation.getCurrentPosition(
    pos => {
      if (vndMap) vndMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
      placeVndPin(pos.coords.latitude, pos.coords.longitude);
      btn.innerHTML = '<span class="material-icons" style="font-size:13px;">my_location</span> My Location';
    },
    () => {
      alert('Unable to get your location.');
      btn.innerHTML = '<span class="material-icons" style="font-size:13px;">my_location</span> My Location';
    }
  );
});

// Show/hide map based on selected role
function syncVndMap() {
  const isVendor = typeof selectedRole !== 'undefined' && selectedRole === 'vendor';
  const buyerFields = document.getElementById('buyerAddressFields');
  const buyerHint   = document.getElementById('buyerAddressHint');
  if (buyerFields) buyerFields.style.display = isVendor ? 'none' : '';
  if (buyerHint)   buyerHint.style.display   = 'none'; // always hidden now
  if (isVendor) {
    showVndMap();
  } else {
    hideVndMap();
    // Init buyer map when switching to buyer register
    setTimeout(initBuyerRegMap, 60);
  }
}

// Sync on page load if register tab is already active (e.g. after validation error)
window.addEventListener('load', function () {
  if (document.getElementById('registerForm').classList.contains('active')) {
    syncVndMap();
  }
});
</script>
</body>
</html>
