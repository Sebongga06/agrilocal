// ── Vendor favorite toggle ──
const favVendorBtn = document.getElementById('favVendorBtn');
if (favVendorBtn) {
  favVendorBtn.addEventListener('click', async function () {
    const vendorId = this.dataset.vendorId;
    const icon     = document.getElementById('favVendorIcon');
    const label    = document.getElementById('favVendorLabel');
    try {
      const fd = new FormData();
      fd.append('vendor_id', vendorId);
      const res  = await fetch('index.php?url=favorites/toggle', {
        method: 'POST', body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await res.json();
      if (data.success) {
        if (data.action === 'added') {
          icon.textContent  = 'favorite';
          label.textContent = 'Favorited';
          favVendorBtn.style.background   = '#fff5f5';
          favVendorBtn.style.borderColor  = '#9a3718';
          favVendorBtn.style.color        = '#9a3718';
          showNotification('Vendor added to favorites!', 'success');
        } else {
          icon.textContent  = 'favorite_border';
          label.textContent = 'Favorite';
          favVendorBtn.style.background  = '';
          favVendorBtn.style.borderColor = '';
          favVendorBtn.style.color       = '';
          showNotification('Removed from favorites.', 'success');
        }
      }
    } catch (e) {
      showNotification('Could not update favorites.', 'error');
    }
  });
}

// ── Switch-to-Vendor Modal ──
function openSwitchVendorModal() {
  document.getElementById('svError').style.display = 'none';
  document.getElementById('svPassword').value = '';
  document.getElementById('svModalBackdrop').classList.add('show');
  document.getElementById('svPassword').focus();
}
function toggleSvPw() {
  const inp  = document.getElementById('svPassword');
  const icon = document.getElementById('svPwIcon');
  const hide = inp.type === 'password';
  inp.type = hide ? 'text' : 'password';
  icon.textContent = hide ? 'visibility_off' : 'visibility';
}
document.getElementById('svModalClose').addEventListener('click', () =>
  document.getElementById('svModalBackdrop').classList.remove('show'));
document.getElementById('svModalBackdrop').addEventListener('click', function (e) {
  if (e.target === this) this.classList.remove('show');
});
document.getElementById('svSubmitBtn').addEventListener('click', async function () {
  const errEl = document.getElementById('svError');
  errEl.style.display = 'none';
  const pw = document.getElementById('svPassword').value.trim();
  if (!pw) { errEl.textContent = 'Password is required.'; errEl.style.display = 'block'; return; }
  this.disabled = true; this.textContent = 'Switching…';
  try {
    const fd = new FormData(); fd.append('vendor_password', pw);
    const res  = await fetch('index.php?url=auth/switchToVendor', {
      method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    if (data.success) { window.location.href = data.redirect; }
    else { errEl.textContent = data.message || 'Switch failed.'; errEl.style.display = 'block'; }
  } catch (e) {
    errEl.textContent = 'Something went wrong.'; errEl.style.display = 'block';
  } finally {
    this.disabled = false; this.textContent = 'Switch Account';
  }
});
