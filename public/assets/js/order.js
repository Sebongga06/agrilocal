// ── Expandable items ──
function toggleItems(orderId) {
    const panel = document.getElementById('items-' + orderId);
    const btn   = document.getElementById('expand-' + orderId);
    if (!panel) return;
    const isOpen = panel.classList.toggle('open');
    if (btn) btn.classList.toggle('open', isOpen);
}

function openSwitchVendorModal() {
    window.location.href = 'index.php?url=home';
}

// ── Confirmation modal ──
let _confirmAction = null;
let _confirmOrderId = null;

function confirmAction(action, orderId, title, message) {
    _confirmAction  = action;
    _confirmOrderId = orderId;
    document.getElementById('confirmTitle').textContent   = title;
    document.getElementById('confirmMessage').textContent = message;
    document.getElementById('confirmModalBackdrop').classList.add('show');
}

document.getElementById('confirmCancelBtn').addEventListener('click', () => {
    document.getElementById('confirmModalBackdrop').classList.remove('show');
});
document.getElementById('confirmModalBackdrop').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('show');
});
document.getElementById('confirmOkBtn').addEventListener('click', function () {
    if (!_confirmAction || !_confirmOrderId) return;
    this.disabled = true; this.textContent = 'Processing…';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `index.php?url=orders/${_confirmAction}/${_confirmOrderId}`;
    document.body.appendChild(form);
    form.submit();
});

// ── Review modal ──
function openReviewModal(orderId, vendorId, vendorName) {
    document.getElementById('reviewForm').action = `index.php?url=orders/review/${orderId}`;
    document.getElementById('reviewVendorId').value = vendorId;
    document.getElementById('reviewModalTitle').textContent = 'Rate & Review';
    document.getElementById('reviewModalSubtitle').textContent = vendorName;
    // Reset form
    document.getElementById('reviewForm').reset();
    document.getElementById('reviewModalBackdrop').classList.add('show');
}

document.getElementById('reviewCancelBtn').addEventListener('click', () => {
    document.getElementById('reviewModalBackdrop').classList.remove('show');
});
document.getElementById('reviewModalBackdrop').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('show');
});
document.getElementById('reviewSubmitBtn').addEventListener('click', function () {
    const rating = document.querySelector('input[name="rating"]:checked');
    if (!rating) {
        alert('Please select a star rating.');
        return false;
    }
});
