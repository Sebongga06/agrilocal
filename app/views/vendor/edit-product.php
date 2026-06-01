<?php $pageTitle = 'AgriLocal - Edit Product'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/vendor-edit-product.css?v=<?= time(); ?>">
</head>
<body>
<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <a class="sidebar-logo" href="index.php?url=vendor/dashboard">
            <img src="assets/img/logo.png" alt="AgriLocal" style="height:44px;width:44px;object-fit:contain;vertical-align:middle;">
            <span class="logo-text">AgriLocal</span>
        </a>
        <nav class="nav-menu">
            <p class="menu-label">Main Menu</p>
            <ul>
                <li><a href="index.php?url=vendor/dashboard"><span class="material-icons-round">dashboard</span> Dashboard</a></li>
                <li class="active"><a href="index.php?url=vendor/inventory"><span class="material-icons-round">inventory_2</span> Inventory</a></li>
                <li><a href="index.php?url=vendor/orders" style="position:relative;">
    <span class="material-icons-round">shopping_basket</span> Orders
    <?php
    $urgentCount = $urgentCount ?? 0;
    if ($urgentCount > 0):
    ?>
    <span style="position:absolute;top:6px;right:8px;width:8px;height:8px;background:#e53e3e;border-radius:50%;border:2px solid #fff;display:inline-block;" title="<?= $urgentCount; ?> order(s) due within 24 hours"></span>
    <?php endif; ?>
</a></li>
                <li><a href="index.php?url=vendor/profile"><span class="material-icons-round">store</span> Shop Profile</a></li>
            </ul>
            <hr class="sidebar-divider">
            <ul>
                <?php if (!empty($_SESSION['user']['has_buyer'])): ?><li><a href="index.php?url=auth/switchToBuyer"><span class="material-icons-round">swap_horiz</span> Switch to Buyer</a></li><?php endif; ?>
                <li><a href="index.php?url=auth/logout"><span class="material-icons-round">logout</span> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <header>
            <a href="index.php?url=vendor/inventory"
               style="text-decoration:none;color:var(--red-accent);font-weight:bold;display:flex;align-items:center;gap:5px;">
                <span class="material-icons-round">arrow_back</span> Back to Inventory
            </a>
            <h1 class="headline" style="margin-top:15px;">
                Edit: <?php echo htmlspecialchars($product['prd_name']); ?>
            </h1>
        </header>

        <!-- Validation errors -->
        <?php if (!empty($errors)): ?>
            <ul class="error-list" style="margin-top:20px;">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php
            $existingImages = json_decode($product['prd_images'] ?? '[]', true);
            $currentImg     = $existingImages[0] ?? null;
        ?>

        <form id="edit-form"
              method="POST"
              action="index.php?url=vendor/editProduct&id=<?php echo $product['prd_id']; ?>"
              enctype="multipart/form-data">

        <div class="edit-grid">

            <!-- LEFT -->
            <section>
                <div class="card">
                    <h3 class="headline" style="margin-bottom:20px;font-size:18px;color:var(--green-accent);">Basic Details</h3>

                    <div class="input-group">
                        <label for="name">Product Name <span style="color:var(--red-accent);">*</span></label>
                        <input type="text" id="name" name="name"
                               value="<?php echo htmlspecialchars($product['prd_name']); ?>" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="input-group">
                            <label for="category_id">Category <span style="color:var(--red-accent);">*</span></label>
                            <select id="category_id" name="category_id" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['cat_id']; ?>"
                                        <?php echo $product['prd_category_id'] == $cat['cat_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['cat_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="unit">Unit <span style="color:var(--red-accent);">*</span></label>
                            <input type="text" id="unit" name="unit"
                                   value="<?php echo htmlspecialchars($product['prd_unit']); ?>" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="price">Price per Unit (₱) <span style="color:var(--red-accent);">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01"
                               value="<?php echo $product['prd_price']; ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product['prd_description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="card" style="margin-top:0;">
                    <h3 class="headline" style="margin-bottom:20px;font-size:18px;color:var(--green-accent);">Stock & Availability</h3>

                    <div class="input-group">
                        <label for="stock_quantity">Stock Quantity <span style="color:var(--red-accent);">*</span></label>
                        <input type="number" id="stock_quantity" name="stock_quantity" step="0.01" min="0"
                               value="<?php echo $product['prd_stock_quantity'] + 0; ?>" required>
                    </div>

                    <div style="display:flex;gap:30px;">
                        <label class="toggle-label">
                            <input type="checkbox" name="is_in_season" value="1"
                                <?php echo $product['prd_is_in_season'] ? 'checked' : ''; ?>>
                            In Season
                        </label>
                        <label class="toggle-label">
                            <input type="checkbox" name="is_available" value="1"
                                <?php echo $product['prd_is_available'] ? 'checked' : ''; ?>>
                            Active (visible to buyers)
                        </label>
                    </div>
                </div>
            </section>

            <!-- RIGHT -->
            <aside>
                <div class="card">
                    <h3 class="headline" style="margin-bottom:15px;font-size:18px;">Product Image</h3>

                    <!-- Current / preview image -->
                    <div id="imgWrap" style="position:relative;width:100%;height:220px;border-radius:12px;overflow:hidden;background:#f0f0f0;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                        <?php if ($currentImg): ?>
                            <img id="currentImg" src="<?php echo htmlspecialchars($currentImg); ?>"
                                 style="width:100%;height:100%;object-fit:cover;" alt="Product image">
                            <!-- Remove button overlay -->
                            <button type="button" id="removeBtn" onclick="removeImage()"
                                    style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                    title="Remove image">
                                <span class="material-icons-round" style="color:white;font-size:16px;">close</span>
                            </button>
                        <?php else: ?>
                            <span class="material-icons-round" style="font-size:48px;color:#ccc;" id="imgPlaceholder">image</span>
                        <?php endif; ?>
                    </div>

                    <!-- Drop zone -->
                    <label for="image" id="dropZone"
                           style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:2px dashed var(--light-green);padding:24px 20px;border-radius:12px;cursor:pointer;background:#fafafa;transition:0.2s;<?php echo $currentImg ? 'display:none;' : ''; ?>">
                        <span class="material-icons-round" style="font-size:28px;color:var(--green-accent);">photo_camera</span>
                        <p style="font-family:'Roboto Slab';font-size:13px;margin:0;" id="dropLabel">
                            Click to upload image
                        </p>
                        <p style="font-size:11px;color:#888;margin:0;">JPG, PNG, WebP — max 5 MB</p>
                        <input type="file" id="image" name="image" accept="image/*"
                               style="display:none;" onchange="previewNewImage(this)">
                    </label>

                    <!-- Hidden flag: tells controller to clear the image -->
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="" disabled>
                </div>

                <div class="card" style="border:1px solid #ffcccc;background:#fff8f8;">
                    <h4 style="color:var(--dark-red);font-family:'Roboto Slab';">Danger Zone</h4>
                    <p style="font-size:13px;margin:10px 0;color:#555;">
                        Permanently removes this product from your inventory.
                    </p>
                    <button type="button"
                            onclick="openDeleteModal()"
                            style="width:100%;padding:10px;background:none;border:1px solid var(--red-accent);color:var(--red-accent);border-radius:6px;cursor:pointer;font-weight:bold;font-family:'Roboto Slab';">
                        Delete Product
                    </button>
                </div>
            </aside>

        </div>

        <!-- Sticky action bar -->
        <div class="action-bar">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="material-icons-round" style="color:#aaa;font-size:18px;">schedule</span>
                <p style="font-size:13px;color:#888;">
                    Last updated:
                    <?php echo $product['prd_dateUpdated']
                        ? date('M d, Y', strtotime($product['prd_dateUpdated']))
                        : date('M d, Y', strtotime($product['prd_dateCreated'])); ?>
                </p>
            </div>
            <div style="display:flex;gap:15px;">
                <a href="index.php?url=vendor/inventory"
                   style="text-decoration:none;color:#666;padding:12px 20px;font-family:'Roboto Slab';">
                    Discard
                </a>
                <button type="submit" form="edit-form" class="btn-primary" style="padding:12px 40px;">
                    Save Changes
                </button>
            </div>
        </div>

        </form>

        <!-- Standalone delete form (outside main form to avoid nesting) -->
        <form id="delete-form"
              method="POST"
              action="index.php?url=vendor/deleteProduct">
            <input type="hidden" name="product_id" value="<?php echo $product['prd_id']; ?>">
        </form>

        <!-- Custom delete confirmation modal -->
        <div class="modal-overlay" id="deleteModal">
            <div class="modal-box">
                <div class="modal-icon">
                    <span class="material-icons-round" style="color:var(--red-accent);font-size:24px;">delete_forever</span>
                </div>
                <h3>Delete Product?</h3>
                <p>
                    You're about to permanently delete
                    <strong><?php echo htmlspecialchars($product['prd_name']); ?></strong>.
                    This action cannot be undone.
                </p>
                <div class="modal-actions">
                    <button type="button" class="modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="button" class="modal-btn-delete"
                            onclick="document.getElementById('delete-form').submit();">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="assets/js/vendor-edit-product.js?v=<?= time(); ?>"></script>
</body>
</html>