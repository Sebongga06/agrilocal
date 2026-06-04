// ── Modal open/close ──────────────────────────────────────────────────────────
function openVendorEdit() {
    document.getElementById('vepError').style.display   = 'none';
    document.getElementById('vepSuccess').style.display = 'none';
    document.getElementById('vepBackdrop').classList.add('show');
    // Defer map init until the modal is visible so Leaflet can measure the container
    setTimeout(initVepMap, 80);
}

function toggleVepPw(id, btn) {
    const inp  = document.getElementById(id);
    const icon = btn.querySelector('.material-icons');
    const hide = inp.type === 'password';
    inp.type         = hide ? 'text' : 'password';
    icon.textContent = hide ? 'visibility_off' : 'visibility';
}

document.getElementById('vepClose').addEventListener('click', () =>
    document.getElementById('vepBackdrop').classList.remove('show'));

document.getElementById('vepBackdrop').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('show');
});

// ── Cover photo preview ───────────────────────────────────────────────────────
document.getElementById('vepCoverPicker').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('vepCoverWrap').innerHTML =
            `<img src="${e.target.result}" id="vepCoverImg"
                  style="width:100%;height:130px;object-fit:cover;display:block;">`;
    };
    reader.readAsDataURL(file);
});

// ── Map state ─────────────────────────────────────────────────────────────────
let vepMap       = null;
let vepPin       = null;
let vepGeoTimer  = null;

const vepLatInput  = () => document.getElementById('vepVendorLat');
const vepLngInput  = () => document.getElementById('vepVendorLng');
const vepAddrInput = () => document.getElementById('vepAddressInput');
const vepFooter    = () => document.getElementById('vepMapFooter');

// ── Initialise Leaflet map ────────────────────────────────────────────────────
function initVepMap() {
    if (vepMap) {
        vepMap.invalidateSize();
        return;
    }

    // Default centre: Bacolod City, Negros Occidental
    const defaultLat = 10.6765;
    const defaultLng = 122.9509;
    const saved      = window.vendorMapData || {};
    const initLat    = saved.lat || defaultLat;
    const initLng    = saved.lng || defaultLng;
    const initZoom   = saved.lat ? 15 : 11;

    vepMap = L.map('vepMap').setView([initLat, initLng], initZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(vepMap);

    // Restore saved pin
    if (saved.lat && saved.lng) {
        placeVepPin(saved.lat, saved.lng, false);
    }

    // Click to place / move pin
    vepMap.on('click', function (e) {
        placeVepPin(e.latlng.lat, e.latlng.lng, true);
    });

    setTimeout(() => vepMap.invalidateSize(), 120);
}

// ── Place or move the vendor pin ──────────────────────────────────────────────
async function placeVepPin(lat, lng, reverseGeocode) {
    vepLatInput().value = lat;
    vepLngInput().value = lng;

    if (vepPin) {
        vepPin.setLatLng([lat, lng]);
    } else {
        vepPin = L.marker([lat, lng], { draggable: true }).addTo(vepMap);
        vepPin.on('dragend', function () {
            const pos = vepPin.getLatLng();
            placeVepPin(pos.lat, pos.lng, true);
        });
    }

    setVepFooter(`📍 ${lat.toFixed(6)}, ${lng.toFixed(6)} — resolving address…`, '#555');

    if (reverseGeocode) {
        const addr = await vepReverseGeocode(lat, lng);
        if (addr) {
            vepAddrInput().value = addr;
        }
        setVepFooter(
            `✓ Location pinned: ${lat.toFixed(6)}, ${lng.toFixed(6)}`,
            '#276749'
        );
        vepPin.bindPopup(`<b>Farm location</b><br><small>${addr || ''}</small>`).openPopup();
    } else {
        setVepFooter(
            `✓ Location saved: ${lat.toFixed(6)}, ${lng.toFixed(6)}`,
            '#276749'
        );
    }
}

// ── Reverse geocode: coords → address string ──────────────────────────────────
async function vepReverseGeocode(lat, lng) {
    try {
        const res  = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`,
            { headers: { 'Accept-Language': 'en' } }
        );
        const data = await res.json();
        return data.display_name || null;
    } catch {
        return null;
    }
}

// ── Forward geocode: address string → coords (move pin) ──────────────────────
async function vepForwardGeocode(address) {
    if (!address || address.length < 5) return;
    try {
        const q   = encodeURIComponent(address + ', Philippines');
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=ph&addressdetails=1&q=${q}`,
            { headers: { 'Accept-Language': 'en' } }
        );
        const results = await res.json();
        if (!results || results.length === 0) {
            vepShowDropdown([]);
            return;
        }
        vepShowDropdown(results);
    } catch {
        // silent — user can still pin manually
    }
}

// ── Dropdown for address suggestions ─────────────────────────────────────────
function vepShowDropdown(results) {
    let dd = document.getElementById('vepAddrDropdown');
    if (!dd) {
        dd = document.createElement('div');
        dd.id = 'vepAddrDropdown';
        dd.style.cssText = [
            'position:absolute', 'left:0', 'right:0', 'top:100%',
            'background:#fff', 'border:1.5px solid #9ae6b4', 'border-top:none',
            'border-radius:0 0 8px 8px', 'z-index:9999',
            'max-height:180px', 'overflow-y:auto',
            'box-shadow:0 6px 16px rgba(0,0,0,.12)',
        ].join(';');
        // Wrap the address input in a relative container if not already
        const inp = vepAddrInput();
        if (inp && inp.parentElement.style.position !== 'relative') {
            inp.parentElement.style.position = 'relative';
        }
        inp?.parentElement?.appendChild(dd);
    }

    dd.innerHTML = '';

    if (results.length === 0) {
        dd.innerHTML = `<div style="padding:.5rem .85rem;font-size:.8rem;color:#888;">No results found.</div>`;
        dd.style.display = 'block';
        return;
    }

    results.forEach(r => {
        const item = document.createElement('div');
        item.style.cssText = 'padding:.45rem .85rem;font-size:.8rem;cursor:pointer;border-bottom:1px solid #f0f0f0;color:#333;line-height:1.4;';
        item.textContent = r.display_name;
        item.addEventListener('mouseenter', () => item.style.background = '#f0fff4');
        item.addEventListener('mouseleave', () => item.style.background = '');
        item.addEventListener('mousedown', (e) => {
            e.preventDefault();
            const lat = parseFloat(r.lat);
            const lng = parseFloat(r.lon);
            vepAddrInput().value = r.display_name;
            dd.style.display = 'none';
            if (vepMap) vepMap.setView([lat, lng], 16);
            placeVepPin(lat, lng, false);
            setVepFooter(`✓ Address found: ${lat.toFixed(6)}, ${lng.toFixed(6)}`, '#276749');
        });
        dd.appendChild(item);
    });
    dd.style.display = 'block';
}

// ── Address field: geocode after user stops typing ────────────────────────────
vepAddrInput()?.addEventListener('input', function () {
    clearTimeout(vepGeoTimer);
    const val = this.value.trim();
    const dd  = document.getElementById('vepAddrDropdown');
    if (val.length < 5) { if (dd) dd.style.display = 'none'; return; }
    vepGeoTimer = setTimeout(() => {
        if (vepMap) vepForwardGeocode(val);
    }, 600);
});

vepAddrInput()?.addEventListener('blur', () => {
    setTimeout(() => {
        const dd = document.getElementById('vepAddrDropdown');
        if (dd) dd.style.display = 'none';
    }, 150);
});

vepAddrInput()?.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const dd = document.getElementById('vepAddrDropdown');
        if (dd) dd.style.display = 'none';
    }
});

// ── "My Location" button ──────────────────────────────────────────────────────
document.getElementById('vepMyLocation')?.addEventListener('click', function () {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }
    this.disabled = true;
    this.innerHTML = '<span class="material-icons-round" style="font-size:14px;">my_location</span> Locating…';
    const btn = this;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const { latitude: lat, longitude: lng } = pos.coords;
            vepMap.setView([lat, lng], 16);
            placeVepPin(lat, lng, true);
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round" style="font-size:14px;">my_location</span> My Location';
        },
        () => {
            alert('Could not get your location. Please allow location access or drop a pin manually.');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round" style="font-size:14px;">my_location</span> My Location';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});

// ── Footer helper ─────────────────────────────────────────────────────────────
function setVepFooter(text, color) {
    const el = vepFooter();
    if (el) { el.textContent = text; el.style.color = color || '#555'; }
}

// ── Form submit ───────────────────────────────────────────────────────────────
document.getElementById('vepForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const errEl  = document.getElementById('vepError');
    const succEl = document.getElementById('vepSuccess');
    const btn    = document.getElementById('vepSubmitBtn');

    errEl.style.display  = 'none';
    succEl.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons-round" style="font-size:18px;animation:spin 1s linear infinite;">refresh</span> Saving…';

    try {
        const fd  = new FormData(this);
        const res = await fetch('index.php?url=profile/updateVendor', {
            method:  'POST',
            body:    fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch {
            errEl.textContent    = 'Server error: ' + text.substring(0, 200);
            errEl.style.display  = 'flex';
            return;
        }

        if (data.success) {
            succEl.textContent   = '✓ ' + data.message;
            succEl.style.display = 'flex';
            setTimeout(() => {
                document.getElementById('vepBackdrop').classList.remove('show');
                location.reload();
            }, 1200);
        } else {
            errEl.textContent   = data.message || 'Update failed. Please try again.';
            errEl.style.display = 'flex';
        }
    } catch (err) {
        errEl.textContent   = 'Network error: ' + err.message;
        errEl.style.display = 'flex';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round" style="font-size:18px;">save</span> Save Changes';
    }
});
