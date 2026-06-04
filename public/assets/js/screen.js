(function initAgriPublicRoot() {
    try {
        var cs = document.currentScript;
        if (cs && cs.src) {
            var src = cs.src.split('?')[0];
            var m = src.match(/^(.*)\/assets\/js\/screen(\.min)?\.js$/i);
            if (m && m[1]) {
                window.AGRI_PUBLIC_ROOT = m[1].replace(/\/$/, '');
            }
        }
    } catch (e) { /* ignore */ }
})();

/** Front controller URL (same folder as /assets/). */
function agriIndexPhp() {
    if (typeof window.AGRI_PUBLIC_ROOT === 'string' && window.AGRI_PUBLIC_ROOT) {
        return window.AGRI_PUBLIC_ROOT + '/index.php';
    }
    return 'index.php';
}

function agriResolveHref(href) {
    if (!href || /^https?:\/\//i.test(href)) return href;
    if (typeof window.AGRI_PUBLIC_ROOT === 'string' && window.AGRI_PUBLIC_ROOT) {
        return window.AGRI_PUBLIC_ROOT + '/' + String(href).replace(/^\//, '');
    }
    return href;
}

try {
    window.AGRI_INDEX_PHP = agriIndexPhp();
} catch (e2) { /* ignore */ }

// ── Global nav search (delegated) ───────────────────────────────────────────
// Enter / icon → products catalog. On non-home pages, typing shows vendor
// suggestions (same keyword rules as home.js). Home map page keeps only home.js
// for live map filtering so behavior matches the home screen.
(function attachGlobalNavSearch() {
    function catalogSearchUrl(value) {
        const q = (value || '').trim();
        const base = agriIndexPhp() + '?url=products';
        return q ? base + '&search=' + encodeURIComponent(q) : base;
    }

    function prefillNavSearchFromQuery() {
        const bar = document.querySelector('.main-nav .search-bar');
        if (!bar) return;
        const input = bar.querySelector('input');
        if (!input || input.value) return;
        try {
            const params = new URLSearchParams(window.location.search);
            const s = params.get('search') || '';
            if (s) input.value = s;
        } catch (err) { /* ignore */ }
    }

// Open products page from search icon (and prevent click from doing nothing)
document.addEventListener('click', function (e) {
    const bar = e.target.closest('.main-nav .search-bar');
    if (!bar) return;
    const icon = bar.querySelector('span.material-icons');
    if (!icon || !icon.contains(e.target)) return;

    const input = bar.querySelector('input');
    e.preventDefault();
    hideAllNavSuggest();

    const q = input && input.value ? input.value.trim() : '';
    window.location.href = q
        ? (agriIndexPhp() + '?url=products&search=' + encodeURIComponent(q))
        : (agriIndexPhp() + '?url=products');
});


    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const t = e.target;
        if (!t || t.tagName !== 'INPUT') return;
        if (t.getAttribute('type') === 'hidden') return;
        const bar = t.closest('.main-nav .search-bar');
        if (!bar) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        hideAllNavSuggest();
        window.location.href = catalogSearchUrl(t.value);
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prefillNavSearchFromQuery);
    } else {
        prefillNavSearchFromQuery();
    }
})();

/** Same keyword matching as home.js getFilteredVendors (text fields only). */
function navSearchMatchesVendor(vendor, keywordLower) {
    if (!keywordLower) return true;
    const name = (vendor.name || '').toLowerCase();
    const addr = (vendor.address || '').toLowerCase();
    const prods = String(vendor.products || '').toLowerCase();
    return name.includes(keywordLower) || addr.includes(keywordLower) || prods.includes(keywordLower);
}

function isHomeMapNavContext() {
    return Boolean(
        document.getElementById('vendorGrid')
        && document.getElementById('map')
        && Array.isArray(window.vendorsData)
        && window.vendorsData.length
    );
}

let __navVendorsCache = null;
let __navVendorsFetch = null;

function getNavVendorsPromise() {
    if (Array.isArray(window.vendorsData) && window.vendorsData.length) {
        return Promise.resolve(window.vendorsData);
    }
    if (__navVendorsCache !== null) {
        return Promise.resolve(__navVendorsCache);
    }
    if (!__navVendorsFetch) {
        __navVendorsFetch = fetch(agriIndexPhp() + '?url=home/vendorsJson', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                __navVendorsCache = Array.isArray(data) ? data : [];
                return __navVendorsCache;
            })
            .catch(function () {
                __navVendorsCache = [];
                return __navVendorsCache;
            });
    }
    return __navVendorsFetch;
}

function hideAllNavSuggest() {
    document.querySelectorAll('.global-nav-search-dd.is-open').forEach(function (dd) {
        dd.classList.remove('is-open');
        dd.innerHTML = '';
    });
}

function ensureNavSuggestBox(bar) {
    let dd = bar.querySelector('.global-nav-search-dd');
    if (!dd) {
        dd = document.createElement('div');
        dd.className = 'global-nav-search-dd';
        dd.setAttribute('role', 'listbox');
        dd.setAttribute('aria-label', 'Matching vendors');
        bar.appendChild(dd);
        dd.addEventListener('mousedown', function (e) { e.preventDefault(); });
    }
    return dd;
}

function renderNavSuggestLoading(dd) {
    dd.innerHTML = '<div class="global-nav-search-dd__loading">Loading vendors…</div>';
    dd.classList.add('is-open');
}

function renderNavSuggest(bar, input, vendors, q) {
    const dd = ensureNavSuggestBox(bar);
    const keyword = (q || '').trim().toLowerCase();

    if (!keyword) {
        dd.classList.remove('is-open');
        dd.innerHTML = '';
        return;
    }

    const matches = vendors.filter(function (v) { return navSearchMatchesVendor(v, keyword); }).slice(0, 8);

    let html = '';
    matches.forEach(function (v) {
        const img = v.image || '';
        const name = String(v.name || 'Vendor');
        const sub = (v.address || v.products || '').toString().slice(0, 72);
        const url = agriResolveHref(v.storeUrl || ('index.php?url=vendors/store/' + (v.id || '')));
        html += '<a class="global-nav-search-dd__row" role="option" href="' + escAttr(url) + '">';
        html += '<img class="global-nav-search-dd__thumb" alt="" src="' + escAttr(img) + '">';
        html += '<div class="global-nav-search-dd__meta">';
        html += '<div class="global-nav-search-dd__name">' + escHtml(name) + '</div>';
        html += '<div class="global-nav-search-dd__sub">' + escHtml(sub) + '</div>';
        html += '</div></a>';
    });

    if (matches.length === 0) {
        html = '<div class="global-nav-search-dd__empty">No vendors match. Try product search below.</div>';
    }

    html += '<a class="global-nav-search-dd__footer" href="' + escAttr(catalogSearchUrlForNav(input.value)) + '">';
    html += 'See all products matching &ldquo;' + escHtml(q.trim().slice(0, 40)) + (q.trim().length > 40 ? '…' : '') + '&rdquo;';
    html += '</a>';

    dd.innerHTML = html;
    dd.classList.add('is-open');
}

function escHtml(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
}

function escAttr(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function catalogSearchUrlForNav(value) {
    const q = (value || '').trim();
    const base = agriIndexPhp() + '?url=products';
    return q ? base + '&search=' + encodeURIComponent(q) : base;
}

function initGlobalNavSearchLive() {
    const NAV_PLACEHOLDER = 'Search vendors or products…';

    document.querySelectorAll('.main-nav .search-bar input').forEach(function (inp) {
        if (inp.type === 'hidden') return;
        inp.placeholder = NAV_PLACEHOLDER;
        inp.setAttribute('autocomplete', 'off');
        inp.setAttribute('autocorrect', 'off');
        inp.setAttribute('autocapitalize', 'off');
        inp.setAttribute('spellcheck', 'false');
        if (!inp.dataset.agriNavInit) {
            inp.dataset.agriNavInit = '1';
            inp.setAttribute('readonly', 'readonly');
            inp.addEventListener('focus', function () {
                this.removeAttribute('readonly');
            }, { once: true });
        }
    });

    let suggestTimer = null;

    document.addEventListener('input', function (e) {
        const inp = e.target && e.target.closest && e.target.closest('.main-nav .search-bar input');
        if (!inp || inp.type === 'hidden') return;
        if (isHomeMapNavContext()) return;

        const bar = inp.closest('.main-nav .search-bar');
        if (!bar) return;

        clearTimeout(suggestTimer);
        const q = inp.value;
        if (!q.trim()) {
            hideAllNavSuggest();
            return;
        }

        const dd = ensureNavSuggestBox(bar);
        renderNavSuggestLoading(dd);

        suggestTimer = setTimeout(function () {
            getNavVendorsPromise().then(function (vendors) {
                renderNavSuggest(bar, inp, vendors, q);
            });
        }, 200);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideAllNavSuggest();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.main-nav .search-bar')) hideAllNavSuggest();
    });
}

document.addEventListener('DOMContentLoaded', function () {

    initGlobalNavSearchLive();

    // ── Account dropdown ──────────────────────────────────────────────
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
    window.location.href = agriIndexPhp() + '?url=auth/logout';
}