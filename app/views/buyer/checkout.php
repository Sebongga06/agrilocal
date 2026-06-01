<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | AgriLocal</title>
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600&family=Noto+Serif:wght@400;600&family=Material+Icons&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/screen.css?v=2">
    <!-- Leaflet for delivery location map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #deliveryMapWrap {
            border: 1.5px solid #9ae6b4;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: .75rem;
        }
        #deliveryMapHeader {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .55rem .85rem;
            background: #f0fff4;
            border-bottom: 1px solid #9ae6b4;
            font-size: .82rem;
            font-weight: 600;
            color: #276749;
        }
        #deliveryMapHeader .material-icons { font-size: 16px; }
        #deliveryMap {
            height: 300px;
            width: 100%;
            cursor: crosshair;
        }
        #deliveryMapFooter {
            padding: .5rem .85rem;
            background: #f0fff4;
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
        }
        #pinAddressLabel {
            flex: 1;
            font-size: .78rem;
            color: #555;
            min-width: 0;
            word-break: break-word;
        }
        #btnMyLocation {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: .35rem .7rem;
            background: #276749;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }
        #btnMyLocation .material-icons { font-size: 14px; }
        #feeStatusBox {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 10px;
            padding: .65rem 1rem;
            margin-bottom: .75rem;
        }
    </style>
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

    <?php
    $old = $old ?? [];
    $deliveryMethod = $old['delivery_method'] ?? 'pickup';
    $deliveryAddress = $old['delivery_address'] ?? '';
    $pickupTimeSlot = $old['pickup_time_slot'] ?? '';
    $notes = $old['notes'] ?? '';
    ?>

    <div class="container">
        <div class="checkout-page">
            <h1>Checkout</h1>

            <?php if (!empty($error)): ?>
                <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
                <div class="checkout-main">
                    <form method="POST" action="index.php?url=cart/placeOrder" enctype="multipart/form-data">
                        <div class="step-card">
                            <div class="step-header">
                                <span class="step-number">1</span>
                                <h3>Delivery Method</h3>
                            </div>
                            <div class="step-content">
                                <label class="radio-option">
                                    <input type="radio" name="delivery_method" value="pickup" <?= $deliveryMethod === 'pickup' ? 'checked' : ''; ?>>
                                    <div class="option-details">
                                        <strong>Pickup (Free)</strong>
                                        <p>Pick up from farm location</p>
                                    </div>
                                </label>

                                <div class="pickup-options" id="pickupOptions">
                                    <label for="pickup_time_slot">Select Pickup Time:</label>
                                    <input
                                        type="datetime-local"
                                        class="calendar-picker"
                                        id="pickup_time_slot"
                                        name="pickup_time_slot"
                                        value="<?= htmlspecialchars($pickupTimeSlot); ?>">

                                    <label for="notes_pickup">Pickup Notes:</label>
                                    <textarea
                                        id="notes_pickup"
                                        name="notes"
                                        rows="2"
                                        placeholder="Instructions from vendor or your pickup notes"><?= htmlspecialchars($notes); ?></textarea>
                                </div>

                                <label class="radio-option">
                                    <input type="radio" name="delivery_method" value="delivery" <?= $deliveryMethod === 'delivery' ? 'checked' : ''; ?>>
                                    <div class="option-details">
                                        <strong>Delivery</strong>
                                        <p>Delivered to your address</p>
                                    </div>
                                </label>

                                <div class="delivery-options" id="deliveryOptions" style="display:none;">

                                    <!-- Fee status -->
                                    <div id="feeStatusBox">
                                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.2rem;">
                                            <span class="material-icons" style="color:#276749;font-size:18px;">local_shipping</span>
                                            <span style="font-size:.85rem;font-weight:600;color:#276749;" id="deliveryFeeStatus">Drop a pin to calculate delivery fee</span>
                                        </div>
                                        <div style="font-size:.75rem;color:#888;" id="deliveryFeeDetail">₱18 first km · ₱15 per additional km</div>
                                    </div>

                                    <!-- Address search box -->
                                    <div style="position:relative;margin-bottom:.75rem;">
                                        <label style="font-size:.8rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                                            <span class="material-icons" style="font-size:13px;vertical-align:middle;color:#276749;">search</span>
                                            Search delivery address
                                        </label>
                                        <div style="position:relative;">
                                            <input
                                                type="text"
                                                id="buyerAddressSearch"
                                                placeholder="e.g. Brgy. Taculing, Bacolod City"
                                                autocomplete="off"
                                                style="width:100%;box-sizing:border-box;padding:.55rem 2.4rem .55rem .85rem;border:1.5px solid #9ae6b4;border-radius:8px;font-size:.88rem;outline:none;font-family:inherit;background:#f0fff4;">
                                            <span id="buyerAddrSpinner" style="display:none;position:absolute;right:.65rem;top:50%;transform:translateY(-50%);font-size:13px;color:#276749;">⏳</span>
                                        </div>
                                        <!-- Autocomplete dropdown -->
                                        <div id="buyerAddrDropdown" style="display:none;position:absolute;left:0;right:0;top:100%;background:#fff;border:1.5px solid #9ae6b4;border-top:none;border-radius:0 0 8px 8px;z-index:500;max-height:200px;overflow-y:auto;box-shadow:0 6px 16px rgba(0,0,0,.1);"></div>
                                    </div>

                                    <!-- Always-visible delivery map -->
                                    <div id="deliveryMapWrap">
                                        <div id="deliveryMapHeader">
                                            <span class="material-icons">pin_drop</span>
                                            Click the map or type an address above to set your delivery location
                                        </div>
                                        <div id="deliveryMap"></div>
                                        <div id="deliveryMapFooter">
                                            <span id="pinAddressLabel">No location selected yet.</span>
                                            <button type="button" id="btnMyLocation">
                                                <span class="material-icons">my_location</span>
                                                Use My Location
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Hidden fields submitted with the form -->
                                    <input type="hidden" name="buyer_lat"        id="buyer_lat"        value="">
                                    <input type="hidden" name="buyer_lng"        id="buyer_lng"        value="">
                                    <input type="hidden" name="delivery_address" id="delivery_address" value="<?= htmlspecialchars($deliveryAddress); ?>">

                                    <label for="pickup_time_slot_delivery">Preferred Delivery Time:</label>
                                    <input
                                        type="datetime-local"
                                        id="pickup_time_slot_delivery"
                                        value="<?= htmlspecialchars($pickupTimeSlot); ?>"
                                        oninput="document.getElementById('pickup_time_slot').value = this.value">

                                    <label for="notes_delivery">Delivery Notes:</label>
                                    <textarea
                                        id="notes_delivery"
                                        rows="2"
                                        placeholder="Special delivery instructions"
                                        oninput="document.getElementById('notes_pickup').value = this.value"><?= htmlspecialchars($notes); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="step-header">
                                <span class="step-number">2</span>
                                <h3>Payment Method</h3>
                            </div>
                            <div class="step-content">
                                <?php $selectedPayment = $old['payment_method'] ?? 'cash'; ?>

                                <label class="radio-option">
                                    <input type="radio" name="payment_method" value="cash"
                                        <?= $selectedPayment === 'cash' ? 'checked' : ''; ?>>
                                    <div class="option-details">
                                        <strong>Cash on Pickup / Delivery</strong>
                                        <p>Pay when you receive your order</p>
                                    </div>
                                </label>

                                <label class="radio-option">
                                    <input type="radio" name="payment_method" value="gcash"
                                        <?= $selectedPayment === 'gcash' ? 'checked' : ''; ?>>
                                    <div class="option-details">
                                        <strong>GCash</strong>
                                        <p>Scan QR and upload proof of payment</p>
                                    </div>
                                </label>

                                <label class="radio-option">
                                    <input type="radio" name="payment_method" value="maya"
                                        <?= $selectedPayment === 'maya' ? 'checked' : ''; ?>>
                                    <div class="option-details">
                                        <strong>Maya</strong>
                                        <p>Scan QR and upload proof of payment</p>
                                    </div>
                                </label>

                                <!-- QR payment box — shown for GCash / Maya -->
                                <div id="qrPaymentBox" style="display:none;margin-top:1rem;border:1.5px solid #e0e0e0;border-radius:12px;overflow:hidden;">
                                    <div style="background:#f7f9f7;padding:.75rem 1rem;border-bottom:1px solid #e0e0e0;display:flex;align-items:center;gap:.5rem;">
                                        <span class="material-icons" style="color:#276749;font-size:18px;">qr_code_2</span>
                                        <span style="font-weight:600;font-size:.9rem;color:#276749;" id="qrMethodLabel">Scan to Pay</span>
                                    </div>
                                    <div style="padding:1rem;display:flex;flex-direction:column;align-items:center;gap:.85rem;">
                                        <img id="qrImage"
                                             src="assets/img/payments/gcash-qr.svg"
                                             alt="QR Code"
                                             style="width:200px;height:200px;border-radius:8px;border:1px solid #e0e0e0;">
                                        <p style="font-size:.78rem;color:#888;text-align:center;margin:0;">
                                            Scan the QR code with your e-wallet app, then fill in the details below.
                                        </p>

                                        <div style="width:100%;">
                                            <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                                                Reference / Transaction Number <span style="color:#c0392b;">*</span>
                                            </label>
                                            <input type="text"
                                                   name="payment_reference"
                                                   id="payment_reference"
                                                   placeholder="e.g. 1234567890"
                                                   style="width:100%;box-sizing:border-box;padding:.6rem .85rem;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.9rem;outline:none;"
                                                   value="<?= htmlspecialchars($_POST['payment_reference'] ?? ''); ?>">
                                        </div>

                                        <div style="width:100%;">
                                            <label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:.3rem;">
                                                Proof of Payment (screenshot) <span style="color:#c0392b;">*</span>
                                            </label>
                                            <input type="file"
                                                   name="payment_proof"
                                                   id="payment_proof"
                                                   accept="image/*"
                                                   style="width:100%;box-sizing:border-box;padding:.5rem;border:1.5px dashed #ccc;border-radius:8px;font-size:.85rem;cursor:pointer;">
                                            <div id="proofPreview" style="margin-top:.5rem;display:none;">
                                                <img id="proofPreviewImg" src="" alt="Preview"
                                                     style="max-width:100%;max-height:160px;border-radius:8px;border:1px solid #e0e0e0;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="step-card">
                            <div class="step-header">
                                <span class="step-number">3</span>
                                <h3>Review Order</h3>
                            </div>
                            <div class="step-content">
                                <div class="order-review">
                                    <h4>Items Ordered</h4>

                                    <?php if (!empty($groupedItems)): ?>
                                        <?php foreach ($groupedItems as $vendor): ?>
                                            <div style="margin-bottom:14px;">
                                                <strong><?= htmlspecialchars($vendor['vendor_name']); ?></strong>
                                            </div>

                                            <?php foreach ($vendor['items'] as $item): ?>
                                                <div class="review-item">
                                                    <span>
                                                        <?= htmlspecialchars($item['prd_name']); ?>
                                                        (<?= (int)$item['cit_quantity']; ?> <?= htmlspecialchars($item['prd_unit']); ?>)
                                                    </span>
                                                    <span>₱<?= number_format($item['subtotal'], 2); ?></span>
                                                </div>
                                            <?php endforeach; ?>

                                            <div class="review-item" style="font-weight:bold;">
                                                <span>Vendor Subtotal</span>
                                                <span>₱<?= number_format($vendor['subtotal'], 2); ?></span>
                                            </div>

                                            <div class="review-divider"></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="review-item">
                                            <span>Your cart is empty.</span>
                                            <span>₱0.00</span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="review-total">
                                        <span>Total:</span>
                                        <span>₱<?= number_format($summary['grand_total'] ?? 0, 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary place-order" <?= empty($items) ? 'disabled' : ''; ?>>
                            Place Order <span class="material-icons">check_circle</span>
                        </button>
                    </form>
                </div>

                <div class="payment-summary order-summary">
                    <h3>Payment Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="co-subtotal">₱<?= number_format($summary['items_total'] ?? 0, 2); ?></span>
                    </div>
                    <div class="summary-row" id="co-delivery-row">
                        <span>Delivery:</span>
                        <span id="co-delivery">Free (Pickup)</span>
                    </div>
                    <div class="summary-row">
                        <span>Handling:</span>
                        <span>₱<?= number_format($summary['handling_charge'] ?? 0, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total to Pay:</span>
                        <span id="co-total">₱<?= number_format($summary['items_total'] ?? 0, 2); ?></span>
                    </div>
                    <p style="font-size:.75rem;color:#aaa;margin-top:.5rem;line-height:1.5;">
                        <span class="material-icons" style="font-size:13px;vertical-align:middle;">info</span>
                        Delivery fee is calculated per vendor: ₱18 for the first km, then ₱15 per additional started km.
                    </p>
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
                    <li><a href="#">Orders</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Useful Links</h4>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 AgriLocal</p>
        </div>
    </footer>

    <script src="assets/js/screen.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // Pass PHP values to JS
    window.cartData = {
        itemsTotal:     <?= (float)($summary['items_total'] ?? 0); ?>,
        deliveryCharge: <?= (float)($summary['delivery_charge'] ?? 50); ?>,
        handlingCharge: <?= (float)($summary['handling_charge'] ?? 0); ?>,
        vendorCount:    <?= count($groupedItems ?? []); ?>
    };
    </script>
    <script src="assets/js/checkout.js?v=<?= time(); ?>"></script>
</body>
</html>