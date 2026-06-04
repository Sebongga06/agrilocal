function switchTab(tab, btn) {
    ['all','products','vendors'].forEach(t => {
        document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
    });
    document.querySelectorAll('.fav-tab').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
}
function openSwitchVendorModal() { window.location.href = 'index.php?url=home'; }
