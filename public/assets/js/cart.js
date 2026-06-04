// ── Real-time quantity update ──────────────────────────────────────
document.querySelectorAll('.cart-qty-autosave').forEach(function(input) {
    let debounceTimer;

    input.addEventListener('input', function() {
        const qty = Math.max(1, parseInt(this.value) || 1);
        this.value = qty;

        const cartItemId = this.dataset.cartItemId;
        const unitPrice  = parseFloat(this.dataset.unitPrice) || 0;

        // Update subtotal immediately (optimistic UI)
        const subtotalEl = document.getElementById('subtotal-' + cartItemId);
        if (subtotalEl) {
            subtotalEl.textContent = '₱' + (unitPrice * qty).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Recalculate items total from all visible subtotals
        recalcItemsTotal();

        // Debounce the server save
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => saveQty(cartItemId, qty), 600);
    });
});

async function saveQty(cartItemId, qty) {
    try {
        const fd = new FormData();
        fd.append('cart_item_id', cartItemId);
        fd.append('quantity', qty);
        await fetch('index.php?url=cart/update', {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
    } catch (e) {
        // silent — UI already updated
    }
}

// ── Recalculate totals from DOM ────────────────────────────────────
function recalcItemsTotal() {
    let total = 0;
    document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
        const val = parseFloat(el.textContent.replace(/[₱,]/g, '')) || 0;
        total += val;
    });

    const itemsEl = document.getElementById('bill-items-total');
    const grandEl = document.getElementById('bill-grand-total');
    const fmt = v => '₱' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    if (itemsEl) itemsEl.textContent = fmt(total);
    if (grandEl) grandEl.textContent = fmt(total); // delivery added at checkout
}

// ── AJAX delete ────────────────────────────────────────────────────
async function removeCartItem(cartItemId, btn) {
    const row = btn.closest('tr.cart-item-row');
    if (!row) return;

    // Remove from DOM immediately — no fade, no delay
    row.remove();
    recalcItemsTotal();

    // Check if the vendor card is now empty and remove it too
    const remaining = document.querySelectorAll('.cart-item-row');
    if (remaining.length === 0) {
        const container = document.querySelector('.cart-items-container');
        if (container) {
            container.innerHTML = `
                <div class="vendor-cart-card">
                    <div class="vendor-cart-header"><h3>Your cart is empty</h3></div>
                    <div style="padding:20px;">
                        <a href="index.php?url=products" class="btn-primary">Browse Products</a>
                    </div>
                </div>`;
        }
    }

    // Fire-and-forget server sync
    try {
        await fetch('index.php?url=cart/remove/' + cartItemId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
    } catch (e) { /* ignore — item already gone from UI */ }
}

// ── Switch-to-vendor modal ─────────────────────────────────────────
function openSwitchVendorModal() {
    const backdrop = document.getElementById('svModalBackdrop');
    if (backdrop) { backdrop.classList.add('show'); return; }
    window.location.href = 'index.php?url=home#switchVendor';
}
