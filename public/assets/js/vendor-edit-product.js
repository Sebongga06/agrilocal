// Show drop zone on page load if no image exists
(function () {
    const wrap = document.getElementById('imgWrap');
    const zone = document.getElementById('dropZone');
    const hasImage = wrap.querySelector('img') !== null;
    if (!hasImage) {
        zone.style.display = 'flex';
    } else {
        zone.style.display = 'none';
    }
})();

function previewNewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrap = document.getElementById('imgWrap');
            // Re-enable remove button on the new preview
            wrap.innerHTML =
                '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">' +
                '<button type="button" onclick="removeImage()" ' +
                'style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;" title="Remove image">' +
                '<span class="material-icons-round" style="color:white;font-size:16px;">close</span></button>';
            document.getElementById('dropZone').style.display = 'none';
            // Clear the remove flag since we now have a new upload
            const flag = document.getElementById('removeImageFlag');
            flag.value = '';
            flag.disabled = true;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const wrap = document.getElementById('imgWrap');
    // Show placeholder
    wrap.innerHTML = '<span class="material-icons-round" style="font-size:48px;color:#ccc;">image</span>';
    // Show drop zone
    document.getElementById('dropZone').style.display = 'flex';
    // Clear file input
    document.getElementById('image').value = '';
    // Enable the remove flag so the controller knows to clear the image
    const flag = document.getElementById('removeImageFlag');
    flag.value = '1';
    flag.disabled = false;
}

function openDeleteModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking the backdrop
document.getElementById('deleteModal').addEventListener('click', function (e) {
    if (e.target === this) closeDeleteModal();
});
