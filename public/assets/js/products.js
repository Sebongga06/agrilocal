document.addEventListener('DOMContentLoaded', function () {
    // ── Add to cart (AJAX) ──
    const cartForms = document.querySelectorAll('.ajax-cart-form');
    cartForms.forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const button = form.querySelector('.add-to-cart-direct');
            const productName = button?.getAttribute('data-product') || 'Product';
            const formData = new FormData(form);
            try {
                const response = await fetch(form.action, {
                    method: 'POST', body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (result.success) {
                    showNotification(`${productName} added to cart`, 'success');
                } else {
                    showNotification(result.message || 'Failed to add to cart', 'error');
                }
            } catch (error) {
                showNotification('Something went wrong while adding to cart', 'error');
            }
        });
    });

    // ── Favorite toggle (AJAX) ──
    document.querySelectorAll('.fav-toggle-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const productId = this.dataset.productId;
            const icon = this.querySelector('.material-icons');
            try {
                const fd = new FormData();
                fd.append('product_id', productId);
                const res  = await fetch('index.php?url=favorites/toggle', {
                    method: 'POST', body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    if (data.action === 'added') {
                        icon.textContent = 'favorite';
                        this.style.borderColor = '#9a3718';
                        this.style.background  = '#fff5f5';
                        showNotification('Added to favorites!', 'success');
                    } else {
                        icon.textContent = 'favorite_border';
                        this.style.borderColor = '#fecaca';
                        this.style.background  = 'none';
                        showNotification('Removed from favorites.', 'success');
                    }
                }
            } catch (e) {
                showNotification('Could not update favorites.', 'error');
            }
        });
    });
});
