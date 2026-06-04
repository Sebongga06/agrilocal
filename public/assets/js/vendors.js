function openSwitchVendorModal() {
    window.location.href = 'index.php?url=home';
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('vendorSearchFilter');
    const categoryInputs = document.querySelectorAll('input[name="vendorCategoryFilter"]');
    const cards          = document.querySelectorAll('.vendor-card');
    const countText      = document.getElementById('vendorCountText');

    function applyVendorFilters() {
        const keyword          = (searchInput?.value || '').trim().toLowerCase();
        const selectedCategory = document.querySelector('input[name="vendorCategoryFilter"]:checked')?.value || 'all';
        let visibleCount = 0;

        cards.forEach(card => {
            // search by vendor name only
            const name     = card.dataset.name || '';
            const category = card.dataset.category || '';

            const matchesName     = keyword === '' || name.includes(keyword);
            const matchesCategory = selectedCategory === 'all' || category === selectedCategory;

            const show = matchesName && matchesCategory;
            card.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        if (countText) countText.textContent = visibleCount;
    }

    // real-time on every keystroke
    if (searchInput) searchInput.addEventListener('input', applyVendorFilters);

    // real-time on category change
    categoryInputs.forEach(input => input.addEventListener('change', applyVendorFilters));
});
