document.addEventListener("DOMContentLoaded", function () {
  const data = window.vendorStoreData || {};
  const vendor = data.vendor || {};
  let products = Array.isArray(data.products) ? data.products : [];
  const reviews = Array.isArray(data.reviews) ? data.reviews : [];

  const productGrid = document.getElementById("vendorProductsGrid");
  const productSearch = document.getElementById("vendorProductSearch");
  const reviewContainer = document.getElementById("vendorReviews");
  const vendorMapEl = document.getElementById("vendorMap");

  const tabs = document.querySelectorAll(".vendor-tab");
  const tabContents = document.querySelectorAll(".vendor-tab-content");

  let cartCount = 0;
  let cartTotal = 0;

  function renderVendorDetails() {
    const vendorName = document.getElementById("vendorName");
    const vendorDescription = document.getElementById("vendorDescription");
    const vendorLocation = document.getElementById("vendorLocationText");
    const vendorRating = document.getElementById("vendorRatingText");
    const vendorPickup = document.getElementById("vendorPickupText");
    const vendorCover = document.getElementById("vendorCover");

    const farmerName = document.getElementById("farmerName");
    const farmerShortIntro = document.getElementById("farmerShortIntro");
    const farmerImage = document.getElementById("farmerImage");
    const vendorAboutText = document.getElementById("vendorAboutText");

    if (vendorName) {
      vendorName.textContent = vendor.name || "Vendor";
    }

    if (vendorDescription) {
      vendorDescription.textContent = vendor.description || "No description available.";
    }

    if (vendorLocation) {
      vendorLocation.textContent = vendor.address || "No address available";
    }

    if (vendorRating) {
      vendorRating.textContent = `${Number(vendor.rating || 0).toFixed(1)} (${vendor.reviews || 0} reviews)`;
    }

    if (vendorPickup) {
      vendorPickup.textContent = vendor.pickup_instructions || "No pickup instructions available.";
    }

    if (vendorCover && vendor.image) {
      vendorCover.src = vendor.image;
      vendorCover.alt = vendor.name || "Vendor";
    }

    if (farmerName) {
      farmerName.textContent = vendor.name || "Vendor";
    }

    if (farmerShortIntro) {
      farmerShortIntro.textContent = vendor.description || "No short intro available.";
    }

    if (farmerImage && vendor.image) {
      farmerImage.src = vendor.image;
      farmerImage.alt = vendor.name || "Vendor";
    }

    if (vendorAboutText) {
      vendorAboutText.innerHTML = escapeHtml(
        vendor.description || "No story available yet."
      ).replace(/\n/g, "<br>");
    }
  }

  function renderProducts(search = "") {
    if (!productGrid) return;

    const keyword = search.trim().toLowerCase();

    const filtered = products.filter(product => {
      return (
        product.name.toLowerCase().includes(keyword) ||
        product.description.toLowerCase().includes(keyword)
      );
    });

    if (filtered.length === 0) {
      productGrid.innerHTML = `
        <div class="profile-card">
          <h3>No products found</h3>
          <p>No matching products for this vendor yet.</p>
        </div>
      `;
      return;
    }

    productGrid.innerHTML = filtered.map(product => `
      <div class="vendor-product-card">
        <a href="index.php?url=products/detail/${product.id}" style="text-decoration:none;display:block;flex-shrink:0;">
          <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" loading="lazy">
        </a>
        <div class="vendor-product-info">
          <a href="index.php?url=products/detail/${product.id}" style="text-decoration:none;">
            <h4>${escapeHtml(product.name)}</h4>
          </a>
          <div class="vendor-product-price">₱${Number(product.price).toFixed(2)} <span style="font-weight:400;font-size:.78rem;color:#888;">/ ${escapeHtml(product.unit)}</span></div>
          <p class="vendor-product-desc">${escapeHtml(product.description || '')}</p>
          <div class="product-card-actions">
            <form method="POST" action="index.php?url=cart/add" class="vendor-cart-form" style="flex:1;margin:0;">
              <input type="hidden" name="product_id" value="${product.id}">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="btn-primary vendor-cart-btn">
                <span class="material-icons" style="font-size:15px;">shopping_cart</span> Add to Cart
              </button>
            </form>
            <button type="button" class="fav-toggle-btn vendor-fav-btn" data-product-id="${product.id}" title="Save to Favorites">
              <span class="material-icons" style="font-size:17px;">favorite_border</span>
            </button>
          </div>
        </div>
      </div>
    `).join("");

    bindCartForms();
    bindFavBtns();
  }

  function renderReviews() {
    if (!reviewContainer) return;

    if (reviews.length === 0) {
      reviewContainer.innerHTML = `
        <div class="profile-card">
          <h3>No reviews yet</h3>
          <p>This vendor has no reviews yet.</p>
        </div>
      `;
      return;
    }

    reviewContainer.innerHTML = reviews.map(review => `
      <div class="profile-card" style="margin-bottom: 16px;">
        <h4>${escapeHtml(review.name)}</h4>
        <p style="margin: 8px 0;">${"★".repeat(Math.max(0, review.rating))}${"☆".repeat(Math.max(0, 5 - review.rating))}</p>
        <p>${escapeHtml(review.comment)}</p>
        <small>${escapeHtml(review.date)}</small>
      </div>
    `).join("");
  }

  function bindTabs() {
    tabs.forEach(tab => {
      tab.addEventListener("click", function () {
        const target = this.dataset.tab;

        tabs.forEach(t => t.classList.remove("active"));
        tabContents.forEach(content => content.classList.remove("active"));

        this.classList.add("active");
        const content = document.getElementById(`tab-${target}`);
        if (content) content.classList.add("active");

        if (target === "location" && vendorMapEl && vendorMapEl._leaflet_map_instance) {
          setTimeout(() => {
            vendorMapEl._leaflet_map_instance.invalidateSize();
          }, 200);
        }
      });
    });
  }

  function bindCartForms() {
    const forms = document.querySelectorAll(".vendor-cart-form");

    forms.forEach(form => {
      form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        try {
          const response = await fetch(form.action, {
            method: "POST",
            body: formData,
            headers: {
              "X-Requested-With": "XMLHttpRequest"
            }
          });

          const result = await response.json();

          if (result.success) {
            cartCount += 1;

            const productId = formData.get("product_id");
            const product = products.find(p => String(p.id) === String(productId));
            if (product) {
              cartTotal += Number(product.price);
            }

            updateCartPreview();

            if (typeof showNotification === "function") {
              showNotification("Added to cart", "success");
            }
          } else {
            if (typeof showNotification === "function") {
              showNotification(result.message || "Failed to add to cart", "error");
            }
          }
        } catch (error) {
          if (typeof showNotification === "function") {
            showNotification("Something went wrong while adding to cart", "error");
          }
        }
      });
    });
  }

  function updateCartPreview() {
    // cart preview removed — no-op kept for compatibility
  }

  function bindFavBtns() {
    document.querySelectorAll('#vendorProductsGrid .fav-toggle-btn').forEach(btn => {      btn.addEventListener('click', async function () {
        const productId = this.dataset.productId;
        const icon = this.querySelector('.material-icons');
        try {
          const fd = new FormData();
          fd.append('product_id', productId);
          const res = await fetch('index.php?url=favorites/toggle', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const data = await res.json();
          if (data.success) {
            if (data.action === 'added') {
              icon.textContent = 'favorite';
              this.style.borderColor = '#9a3718';
              this.style.background = '#fff5f5';
              if (typeof showNotification === 'function') showNotification('Added to favorites!', 'success');
            } else {
              icon.textContent = 'favorite_border';
              this.style.borderColor = '#fecaca';
              this.style.background = 'none';
              if (typeof showNotification === 'function') showNotification('Removed from favorites.', 'success');
            }
          }
        } catch (e) {
          if (typeof showNotification === 'function') showNotification('Could not update favorites.', 'error');
        }
      });
    });
  }
  function initMap() {
    if (!vendorMapEl) return;
    if (typeof L === "undefined") return;

    const lat = Number(vendor.lat || 10.6755);
    const lng = Number(vendor.lng || 122.9588);

    const map = L.map("vendorMap").setView([lat, lng], 13);
    vendorMapEl._leaflet_map_instance = map;

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
      .bindPopup(`<strong>${escapeHtml(vendor.name || "Vendor")}</strong><br>${escapeHtml(vendor.address || "")}`);
  }

  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text ?? "";
    return div.innerHTML;
  }

  if (productSearch) {
    productSearch.addEventListener("input", function () {
      renderProducts(this.value);
    });
  }

  renderVendorDetails();
  bindTabs();
  renderProducts();
  renderReviews();
  initMap();
  updateCartPreview();
});