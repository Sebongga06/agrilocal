// Live preview
document.getElementById('name').addEventListener('input', function () {
    document.getElementById('prevName').textContent = this.value || 'Product Title';
});
document.getElementById('price').addEventListener('input', function () {
    const v = parseFloat(this.value) || 0;
    document.getElementById('prevPrice').textContent = '₱' + v.toFixed(2);
});
document.getElementById('description').addEventListener('input', function () {
    document.getElementById('prevDesc').textContent = this.value || 'Description will appear here.';
});
document.querySelector('[name="is_in_season"]').addEventListener('change', function () {
    const badge = document.getElementById('prevSeason');
    badge.textContent = this.checked ? 'In Season' : 'Off Season';
    badge.style.background = this.checked ? '' : '#eee';
    badge.style.color = this.checked ? '' : '#777';
});

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            // Show preview image + hide drop zone
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('imgPreviewWrap').style.display = 'block';
            document.getElementById('dropZone').style.display = 'none';
            // Also update the right-panel preview
            const wrap = document.getElementById('previewImgWrap');
            wrap.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    // Clear the file input
    const fileInput = document.getElementById('image');
    fileInput.value = '';
    // Hide preview, show drop zone
    document.getElementById('imgPreviewWrap').style.display = 'none';
    document.getElementById('dropZone').style.display = 'flex';
    // Reset right-panel preview
    document.getElementById('previewImgWrap').innerHTML =
        '<span class="material-icons-round" style="font-size:40px;color:#ccc;">image</span>';
}
