document.addEventListener('DOMContentLoaded', function () {

    const accountTrigger = document.getElementById('accountTrigger');
    const accountDropdown = document.getElementById('accountDropdown');

    if (accountTrigger && accountDropdown) {
        accountTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            accountDropdown.classList.toggle('show');
        });

        accountTrigger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                accountDropdown.classList.toggle('show');
            }
        });

        accountDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        document.addEventListener('click', function () {
            accountDropdown.classList.remove('show');
        });
    }

    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');

    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const form = this.closest('form');
            const productName = this.getAttribute('data-product') || 'Product';

            showNotification(`${productName} added to cart`, 'success');

            if (form) {
                form.submit();
            }
        });
    });

    const favoriteBtns = document.querySelectorAll('.favorite-btn');

    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productName = this.getAttribute('data-product') || 'Product';
            const icon = this.querySelector('.favorite-icon');
            if (!icon) return;

            if (this.classList.contains('active')) {
                this.classList.remove('active');
                icon.textContent = 'favorite_border';
                showNotification(`${productName} removed from favorites`, 'favorite');
            } else {
                this.classList.add('active');
                icon.textContent = 'favorite';
                showNotification(`${productName} added to favorites`, 'favorite');
            }
        });
    });

    const tabs = document.querySelectorAll('.profile-tab');
    const reviewsTab = document.getElementById('reviewsTab');
    const mediaTab = document.getElementById('mediaTab');

    if (tabs.length > 0 && reviewsTab && mediaTab) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const target = this.getAttribute('data-tab');

                if (target === 'reviewsTab') {
                    reviewsTab.classList.add('active');
                    mediaTab.classList.remove('active');
                } else {
                    mediaTab.classList.add('active');
                    reviewsTab.classList.remove('active');
                }
            });
        });
    }
});

function showNotification(message, type = '') {
    const notification = document.createElement('div');
    notification.className = `notification show ${type}`;
    notification.innerHTML = `<span class="material-icons">check_circle</span> ${message}`;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

function logoutUser() {
    window.location.href = "index.php?url=auth/logout";
}