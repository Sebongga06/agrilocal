function openSwitchVendorModal() {
    window.location.href = 'index.php?url=home';
}

// ── DOM refs ──────────────────────────────────────────────────────────────────
const deliveryInputs       = document.querySelectorAll('input[name="delivery_method"]');
const pickupOptions        = document.getElementById('pickupOptions');
const deliveryOptions      = document.getElementById('deliveryOptions');
const coDelivery           = document.getElementById('co-delivery');
const coTotal              = document.getElementById('co-total');
const reviewTotal          = document.querySelector('.review-total span:last-child');
const buyerLatInput        = document.getElementById('buyer_lat');
const buyerLngInput        = document.getElementById('buyer_lng');
const deliveryAddressInput = document.getElementById('delivery_address');

const cart = window.cartData || {
    itemsTotal: 0,
    deliveryCharge: 0,
    handlingCharge: 0,
    vendorCount: 1
};

let currentDeliveryFee = 0;
let deliveryMap        = null;
let pinMarker          = null;
let feeTimer           = null;

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(value) {
    return '₱' + Number(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function updateSummary(deliveryFee) {
    currentDeliveryFee = Number(deliveryFee || 0);
    const method = document.querySelector('input[name="delivery_method"]:checked')?.value || 'pickup';
    const fee    = method === 'delivery' ? currentDeliveryFee : 0;
    const total  = Number(cart.itemsTotal || 0) + fee + Number(cart.handlingCharge || 0);

    if (coDelivery) {
        coDelivery.textContent = method === 'pickup' ? 'Free (Pickup)' : fmt(fee);
        coDelivery.style.color = method === 'pickup' ? '#276749' : '#333';
    }
    if (coTotal)     coTotal.textContent     = fmt(total);
    if (reviewTotal) reviewTotal.textContent = fmt(total);
}

function setFeeStatus(text, color) {
    const el = document.getElementById('deliveryFeeStatus');
    if (el) { el.textContent = text; el.style.color = color || '#276749'; }
}

function setFeeDetail(text) {
    const el = document.getElementById('deliveryFeeDetail');
    if (el) el.innerHTML = text.replace(/\n/g, '<br>');
}

// ── Reverse geocode lat/lng → readable address ────────────────────────────────
async function reverseGeocode(lat, lng) {
    try {
        const res  = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`,
            { headers: { 'Accept-Language': 'en' } }
        );
        const data = await res.json();
        return data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    } catch {
        return `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
    }
}

// ── Place / move the pin ──────────────────────────────────────────────────────
async function setPin(lat, lng) {
    buyerLatInput.value = lat;
    buyerLngInput.value = lng;

    if (pinMarker) {
        pinMarker.setLatLng([lat, lng]);
    } else {
        pinMarker = L.marker([lat, lng], { draggable: true }).addTo(deliveryMap);
        pinMarker.on('dragend', function () {
            const pos = pinMarker.getLatLng();
            setPin(pos.lat, pos.lng);
        });
    }

    const label = document.getElementById('pinAddressLabel');
    if (label) label.textContent = 'Locating address…';

    const address = await reverseGeocode(lat, lng);

    if (label) label.textContent = address;
    if (deliveryAddressInput) deliveryAddressInput.value = address;

    pinMarker.bindPopup(`<b>Your delivery location</b><br><small>${address}</small>`).openPopup();

    setFeeStatus('Calculating delivery fee…', '#276749');
    setFeeDetail('₱18 first km · ₱15 per additional km');
    clearTimeout(feeTimer);
    feeTimer = setTimeout(loadDeliveryFee, 400);
}

// ── Initialise Leaflet map ────────────────────────────────────────────────────
function initDeliveryMap() {
    if (deliveryMap) {
        setTimeout(() => deliveryMap.invalidateSize(), 120);
        return;
    }

    deliveryMap = L.map('deliveryMap').setView([10.6765, 122.9509], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(deliveryMap);

    deliveryMap.on('click', function (e) {
        setPin(e.latlng.lat, e.latlng.lng);
    });

    setTimeout(() => deliveryMap.invalidateSize(), 120);

    // Restore pin if coords already set (e.g. after form error)
    const lat = parseFloat(buyerLatInput?.value);
    const lng = parseFloat(buyerLngInput?.value);
    if (!isNaN(lat) && !isNaN(lng)) {
        setPin(lat, lng);
        deliveryMap.setView([lat, lng], 14);
    }
}

// ── Address search box — forward geocode with dropdown ────────────────────────
let addrSearchTimer = null;
let addrSearchAbort = null;

const addrSearchInput    = document.getElementById('buyerAddressSearch');
const addrDropdown       = document.getElementById('buyerAddrDropdown');
const addrSpinner        = document.getElementById('buyerAddrSpinner');

function closeAddrDropdown() {
    if (addrDropdown) addrDropdown.style.display = 'none';
}

async function searchAddress(query) {
    if (!query || query.length < 4) { closeAddrDropdown(); return; }

    if (addrSpinner) addrSpinner.style.display = 'inline';

    try {
        const q   = encodeURIComponent(query + ', Philippines');
        const url = `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=ph&addressdetails=1&q=${q}`;
        const res = await fetch(url, { headers: { 'Accept-Language': 'en' } });
        const results = await res.json();

        if (!addrDropdown) return;

        if (!results || results.length === 0) {
            addrDropdown.innerHTML = `<div style="padding:.55rem .85rem;font-size:.82rem;color:#888;">No results found.</div>`;
            addrDropdown.style.display = 'block';
            return;
        }

        addrDropdown.innerHTML = '';
        results.forEach(r => {
            const item = document.createElement('div');
            item.style.cssText = 'padding:.5rem .85rem;font-size:.82rem;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#333;line-height:1.4;';
            item.textContent = r.display_name;
            item.addEventListener('mouseenter', () => item.style.background = '#f0fff4');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('mousedown', (e) => {
                // mousedown fires before blur — prevent input blur from closing dropdown first
                e.preventDefault();
                const lat = parseFloat(r.lat);
                const lng = parseFloat(r.lon);
                addrSearchInput.value = r.display_name;
                closeAddrDropdown();
                if (deliveryMap) deliveryMap.setView([lat, lng], 16);
                setPin(lat, lng);
            });
            addrDropdown.appendChild(item);
        });
        addrDropdown.style.display = 'block';

    } catch {
        closeAddrDropdown();
    } finally {
        if (addrSpinner) addrSpinner.style.display = 'none';
    }
}

addrSearchInput?.addEventListener('input', function () {
    clearTimeout(addrSearchTimer);
    const val = this.value.trim();
    if (val.length < 4) { closeAddrDropdown(); return; }
    addrSearchTimer = setTimeout(() => searchAddress(val), 500);
});

addrSearchInput?.addEventListener('blur', () => {
    // Small delay so mousedown on a dropdown item fires first
    setTimeout(closeAddrDropdown, 150);
});

addrSearchInput?.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAddrDropdown();
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (e.target !== addrSearchInput) closeAddrDropdown();
});

// "Use My Location" button
document.getElementById('btnMyLocation')?.addEventListener('click', function () {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }
    this.disabled = true;
    this.innerHTML = '<span class="material-icons" style="font-size:14px;">my_location</span> Locating…';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            deliveryMap.setView([pos.coords.latitude, pos.coords.longitude], 16);
            setPin(pos.coords.latitude, pos.coords.longitude);
            this.disabled = false;
            this.innerHTML = '<span class="material-icons" style="font-size:14px;">my_location</span> Use My Location';
        },
        () => {
            alert('Could not get your location. Please allow location access or drop a pin manually.');
            this.disabled = false;
            this.innerHTML = '<span class="material-icons" style="font-size:14px;">my_location</span> Use My Location';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});

// ── Fee calculation ───────────────────────────────────────────────────────────
async function loadDeliveryFee() {
    const lat = buyerLatInput?.value;
    const lng = buyerLngInput?.value;

    if (!lat || !lng) {
        setFeeStatus('Drop a pin to calculate delivery fee', '#276749');
        setFeeDetail('₱18 first km · ₱15 per additional km');
        updateSummary(0);
        return;
    }

    setFeeStatus('Calculating delivery fee…', '#276749');

    let rawText = '';
    try {
        const fd = new FormData();
        fd.append('buyer_lat', lat);
        fd.append('buyer_lng', lng);
        if (deliveryAddressInput?.value) {
            fd.append('delivery_address', deliveryAddressInput.value);
        }

        const res = await fetch('index.php?url=delivery/calculate', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        rawText = await res.text();
        const jsonStart = rawText.indexOf('{');
        const data      = JSON.parse(jsonStart >= 0 ? rawText.slice(jsonStart) : rawText);

        if (data.success) {
            const vendors    = data.vendors || [];
            const totalFee   = Number(data.total_fee || 0);
            const hasErrors  = vendors.some(v => v.error);

            updateSummary(totalFee);

            // Build per-vendor breakdown lines
            const lines = vendors.map(v => {
                if (v.error) {
                    return `⚠ ${v.vendor_name}: ${v.error}`;
                }
                const dist = v.distance_km != null
                    ? `${Number(v.distance_km).toFixed(1)} km`
                    : '? km';
                return `${v.vendor_name}: ${dist} → ${fmt(v.delivery_fee)}`;
            });

            if (hasErrors) {
                setFeeStatus('⚠ Some vendors have location issues — check details below', '#c0392b');
            } else {
                setFeeStatus(`✓ Delivery fee: ${fmt(totalFee)}`, '#276749');
            }

            setFeeDetail(lines.join('\n') || '₱18 first km · ₱15 per additional km');
        } else {
            updateSummary(0);
            setFeeStatus(data.message || 'Could not calculate fee.', '#c0392b');
            setFeeDetail(data.buyer_address ? 'Location: ' + data.buyer_address : '');
        }
    } catch (err) {
        updateSummary(0);
        const hint = rawText ? rawText.replace(/<[^>]+>/g, '').trim().slice(0, 200) : err.message;
        setFeeStatus('Server error — see detail below', '#c0392b');
        setFeeDetail(hint || 'Unknown error');
        console.error('[DeliveryFee] raw response:', rawText, err);
    }
}

// ── Section visibility ────────────────────────────────────────────────────────
function syncDeliverySections() {
    const selected = document.querySelector('input[name="delivery_method"]:checked')?.value || 'pickup';

    if (pickupOptions)   pickupOptions.style.display   = selected === 'pickup'   ? 'block' : 'none';
    if (deliveryOptions) deliveryOptions.style.display = selected === 'delivery' ? 'block' : 'none';

    if (selected === 'delivery') {
        initDeliveryMap();
        loadDeliveryFee();
    } else {
        updateSummary(0);
    }
}

deliveryInputs.forEach(input => input.addEventListener('change', syncDeliverySections));
syncDeliverySections();

// ── Payment method toggle ─────────────────────────────────────────────────────
const qrImages = { gcash: 'assets/img/payments/gcash-qr.svg', maya: 'assets/img/payments/maya-qr.svg' };
const qrLabels = { gcash: 'Pay via GCash — Scan QR', maya: 'Pay via Maya — Scan QR' };

function syncPaymentUI() {
    const selected   = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
    const qrBox      = document.getElementById('qrPaymentBox');
    const qrImg      = document.getElementById('qrImage');
    const qrLabel    = document.getElementById('qrMethodLabel');
    const refInput   = document.getElementById('payment_reference');
    const proofInput = document.getElementById('payment_proof');

    if (!qrBox) return;

    if (selected === 'gcash' || selected === 'maya') {
        qrBox.style.display = 'block';
        if (qrImg)    qrImg.src           = qrImages[selected];
        if (qrLabel)  qrLabel.textContent = qrLabels[selected];
        if (refInput)   refInput.required   = true;
        if (proofInput) proofInput.required = true;
    } else {
        qrBox.style.display = 'none';
        if (refInput)   refInput.required   = false;
        if (proofInput) proofInput.required = false;
    }
}

document.querySelectorAll('input[name="payment_method"]').forEach(r =>
    r.addEventListener('change', syncPaymentUI)
);

document.getElementById('payment_proof')?.addEventListener('change', function () {
    const file    = this.files[0];
    const preview = document.getElementById('proofPreview');
    const img     = document.getElementById('proofPreviewImg');
    if (!file || !preview || !img) return;
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(file);
});

syncPaymentUI();
