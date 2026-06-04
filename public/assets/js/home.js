document.addEventListener("DOMContentLoaded", function () {
  const vendors = Array.isArray(window.vendorsData) ? window.vendorsData : [];

  const categoryColors = {
    vegetables: "#4caf50",
    fruits:     "#ff9800",
    dairy:      "#2196f3",
    herbs:      "#8e44ad"
  };

  let activeCategory = "all";
  let activeDistance = 10; // match slider default
  let map;
  let markersLayer;

  const distanceSlider = document.getElementById("distanceSlider");
  const distanceValue  = document.getElementById("distanceValue");
  const vendorGrid     = document.getElementById("vendorGrid");
  const vendorCount    = document.getElementById("vendorCount");
  const categoryChips  = document.querySelectorAll(".chip");

  const modalBackdrop  = document.getElementById("modalBackdrop");
  const modalImage     = document.getElementById("modalImage");
  const modalTitle     = document.getElementById("modalTitle");
  const modalMeta      = document.getElementById("modalMeta");
  const modalText      = document.getElementById("modalText");
  const modalTags      = document.getElementById("modalTags");
  const modalProducts  = document.getElementById("modalProducts");
  const modalStoreLink = document.getElementById("modalStoreLink");
  const closeModal     = document.getElementById("closeModal");
  const focusOnMapBtn  = document.getElementById("focusOnMapBtn");
  const searchInput    = document.querySelector('.main-nav .search-bar input:not([type="hidden"])');

  // ── Map init ──
  function initMap() {
    const mapEl = document.getElementById("map");
    if (!mapEl) return;

    map = L.map("map").setView([10.6755, 122.9588], 12);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);
    renderVendors();
    setTimeout(() => map.invalidateSize(), 200);
  }

  function createColorMarker(color) {
    return L.divIcon({
      className: "",
      html: `<div style="width:18px;height:18px;background:${color};border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 8px rgba(0,0,0,.3);"></div>`,
      iconSize: [18, 18],
      iconAnchor: [9, 18],
      popupAnchor: [0, -16]
    });
  }

  // ── Filter logic ──
  // Distance filter: if vendor.distance > 0 use it, otherwise show all (distance=0 means unknown)
  function getFilteredVendors() {
    const keyword = (searchInput ? searchInput.value.trim().toLowerCase() : "");
    return vendors.filter(v => {
      const catMatch  = activeCategory === "all" || v.category === activeCategory;
      const distMatch = v.distance <= 0 || v.distance <= activeDistance;
      const kwMatch   = keyword === "" ||
                        v.name.toLowerCase().includes(keyword) ||
                        (v.address || "").toLowerCase().includes(keyword) ||
                        (v.products || "").toLowerCase().includes(keyword);
      return catMatch && distMatch && kwMatch;
    });
  }

  // ── Render vendor grid + map markers ──
  function renderVendors() {
    if (!vendorGrid || !vendorCount || !markersLayer) return;

    const filtered = getFilteredVendors();
    vendorGrid.innerHTML = "";
    markersLayer.clearLayers();
    vendorCount.textContent = filtered.length;

    filtered.forEach(vendor => {
      const color = categoryColors[vendor.category] || "#546a40";

      // Map marker
      const marker = L.marker([vendor.lat, vendor.lng], { icon: createColorMarker(color) })
        .addTo(markersLayer);
      marker.bindTooltip(vendor.name, { permanent: false, direction: "top" });
      marker.on("click", () => openVendorModal(vendor));

      // Vendor card
      const locationLine = vendor.address
        ? `<span class="material-icons" style="font-size:14px;color:#52b788;vertical-align:middle;">location_on</span> ${escHtml(vendor.address)}`
        : `<span class="material-icons" style="font-size:14px;color:#aaa;vertical-align:middle;">location_on</span> Location not set`;

      const distanceLine = vendor.distance > 0
        ? `<span class="material-icons" style="font-size:13px;color:#888;vertical-align:middle;">near_me</span> <span style="color:#888;font-size:.8rem;">${vendor.distance} km away</span>`
        : ``;

      const ratingStars = vendor.rating > 0
        ? `<span style="color:#f5a623;">★</span> ${vendor.rating} <span style="color:#aaa;">(${vendor.reviews})</span>`
        : `<span style="color:#aaa;">No reviews yet</span>`;

      const card = document.createElement("div");
      card.className = "home-vendor-card";
      card.style.cursor = "pointer";
      card.innerHTML = `
        <img src="${escHtml(vendor.image)}" alt="${escHtml(vendor.name)}" loading="lazy">
        <div class="home-vendor-info">
          <h4 class="hvc-name">${escHtml(vendor.name)}</h4>
          <div class="hvc-rating">${ratingStars}</div>
          <div class="hvc-location">${locationLine}</div>
          ${distanceLine ? `<div class="hvc-distance">${distanceLine}</div>` : ''}
          <div class="hvc-products">${escHtml(vendor.products)}</div>
          <div class="hvc-actions">
            <button class="btn-home-secondary hvc-preview-btn" type="button">Preview</button>
            <button class="btn-home-primary hvc-store-btn" type="button">View Store</button>
          </div>
        </div>
      `;

      // Preview button → open modal
      card.querySelector(".hvc-preview-btn").addEventListener("click", (e) => {
        e.stopPropagation();
        openVendorModal(vendor);
      });

      // View Store button → go to vendor store
      card.querySelector(".hvc-store-btn").addEventListener("click", (e) => {
        e.stopPropagation();
        window.location.href = vendor.storeUrl;
      });

      // Card click → go to store (clicking outside the buttons)
      card.addEventListener("click", () => { window.location.href = vendor.storeUrl; });
      vendorGrid.appendChild(card);
    });

    if (filtered.length > 0) {
      try {
        const bounds = L.latLngBounds(filtered.map(v => [v.lat, v.lng]));
        map.fitBounds(bounds, { padding: [40, 40] });
      } catch (e) { /* ignore */ }
    }
  }

  // ── Vendor preview modal ──
  function openVendorModal(vendor) {
    if (!modalBackdrop) return;

    modalImage.src     = vendor.image;
    modalImage.alt     = vendor.name;
    modalTitle.textContent = vendor.name;

    // Meta row: address + rating
    if (modalMeta) {
      const addr = vendor.address || "Location not set";
      const rat  = vendor.rating > 0 ? `★ ${vendor.rating} (${vendor.reviews} reviews)` : "No reviews yet";
      modalMeta.innerHTML = `
        <span style="display:flex;align-items:center;gap:3px;">
          <span class="material-icons" style="font-size:14px;color:#52b788;">location_on</span>${escHtml(addr)}
        </span>
        <span style="display:flex;align-items:center;gap:3px;color:#f5a623;">
          <span class="material-icons" style="font-size:14px;">star</span>${rat}
        </span>
      `;
    }

    if (modalText) modalText.textContent = vendor.description || "No description available.";

    if (modalTags) {
      modalTags.innerHTML = `<span class="modal-tag">${escHtml(vendor.category)}</span>`;
    }

    if (modalProducts) {
      modalProducts.innerHTML = vendor.products && vendor.products !== "No products listed yet"
        ? `<strong>Products:</strong> ${escHtml(vendor.products)}`
        : "";
    }

    if (modalStoreLink) modalStoreLink.href = vendor.storeUrl;

    modalBackdrop.style.display = "flex";
  }

  function closeVendorModal() {
    if (modalBackdrop) modalBackdrop.style.display = "none";
  }

  function escHtml(str) {
    const d = document.createElement("div");
    d.textContent = str ?? "";
    return d.innerHTML;
  }

  // ── Event listeners ──
  if (distanceSlider) {
    // Sync initial value
    activeDistance = Number(distanceSlider.value);
    distanceSlider.addEventListener("input", function () {
      activeDistance = Number(this.value);
      if (distanceValue) distanceValue.textContent = activeDistance;
      renderVendors();
    });
  }

  categoryChips.forEach(chip => {
    chip.addEventListener("click", function () {
      categoryChips.forEach(c => c.classList.remove("active"));
      this.classList.add("active");
      activeCategory = this.dataset.category;
      renderVendors();
    });
  });

  if (searchInput) {
    searchInput.addEventListener("input", () => renderVendors());
  }

  if (closeModal)     closeModal.addEventListener("click", closeVendorModal);
  if (modalBackdrop)  modalBackdrop.addEventListener("click", e => { if (e.target === modalBackdrop) closeVendorModal(); });
  if (focusOnMapBtn)  focusOnMapBtn.addEventListener("click", () => {
    closeVendorModal();
    document.getElementById("mapSection")?.scrollIntoView({ behavior: "smooth" });
  });

  initMap();
});
