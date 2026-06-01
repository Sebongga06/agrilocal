<?php $pageTitle = 'AgriLocal - Add Product'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/vendor-add-product.css?v=<?= time(); ?>">
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
               style="text-decoration:none;color:var(--red-accent);display:flex;align-items:center;gap:5px;font-weight:bold;">
                <span class="material-icons-round">arrow_back</span> Back to Inventory
            </a>
            <h1 class="headline" style="margin-top:15px;">Add New Product</h1>
            <p style="color:#666;">Fill in the details below to list your fresh produce.</p>
        </header>

        <!-- Validation errors -->
        <?php if (!empty($errors)): ?>
            <ul class="error-list" style="margin-top:20px;">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="index.php?url=vendor/addProduct" enctype="multipart/form-data">
        <div class="form-grid">

            <!-- LEFT: form fields -->
            <section>
                <div class="card">
                    <h3 class="headline" style="margin-bottom:20px;font-size:18px;">General Information</h3>

                    <div class="input-group">
                        <label for="name">Product Name <span style="color:var(--red-accent);">*</span></label>
                        <input type="text" id="name" name="name"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="e.g. Organic Cavendish Banana" required>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="input-group">
                            <label for="category_id">Category <span style="color:var(--red-accent);">*</span></label>
                            <select id="category_id" name="category_id" required>
                                <option value="">— Select —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['cat_id']; ?>"
                                        <?php echo (($_POST['category_id'] ?? '') == $cat['cat_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['cat_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="unit">Unit <span style="color:var(--red-accent);">*</span></label>
                            <input type="text" id="unit" name="unit"
                                   value="<?php echo htmlspecialchars($_POST['unit'] ?? ''); ?>"
                                   placeholder="kg, pc, bundle…" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="price">Price per Unit (₱) <span style="color:var(--red-accent);">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01"
                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                               placeholder="0.00" required>
                    </div>

                    <div class="input-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  placeholder="Tell customers about your product…"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="card" style="margin-top:0;">
                    <h3 class="headline" style="margin-bottom:20px;font-size:18px;">Inventory & Availability</h3>

                    <div class="input-group">
                        <label for="stock_quantity">Stock Quantity <span style="color:var(--red-accent);">*</span></label>
                        <input type="number" id="stock_quantity" name="stock_quantity" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? ''); ?>"
                               placeholder="0" required>
                    </div>

                    <div class="toggle-row">
                        <label class="toggle-label">
                            <input type="checkbox" name="is_in_season" value="1"
                                <?php echo (!isset($_POST['name']) || isset($_POST['is_in_season'])) ? 'checked' : ''; ?>>
                            In Season
                        </label>
                        <label class="toggle-label">
                            <input type="checkbox" name="is_available" value="1"
                                <?php echo (!isset($_POST['name']) || isset($_POST['is_available'])) ? 'checked' : ''; ?>>
                            Active (visible to buyers)
                        </label>
                    </div>
                </div>

                <div class="card" style="margin-top:0;">
                    <h3 class="headline" style="margin-bottom:20px;font-size:18px;">Product Image</h3>

                    <!-- Preview (hidden until image chosen) -->
                    <div id="imgPreviewWrap" style="display:none;position:relative;margin-bottom:12px;">
                        <img id="imgPreview" src="" alt=""
                             style="width:100%;border-radius:10px;max-height:220px;object-fit:cover;display:block;">
                        <button type="button" onclick="removeImage()"
                                style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                                title="Remove image">
                            <span class="material-icons-round" style="color:white;font-size:16px;">close</span>
                        </button>
                    </div>

                    <!-- Drop zone (hidden once image chosen) -->
                    <label for="image" id="dropZone"
                           style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;border:2px dashed var(--light-green);padding:36px 20px;border-radius:12px;cursor:pointer;background:#fafafa;transition:0.2s;">
                        <span class="material-icons-round" style="font-size:44px;color:var(--green-accent);">cloud_upload</span>
                        <p style="font-family:'Roboto Slab';font-size:14px;margin:0;" id="dropLabel">Click to upload product image</p>
                        <p style="font-size:12px;color:#888;margin:0;">JPG, PNG, WebP — max 5 MB</p>
                        <input type="file" id="image" name="image" accept="image/*"
                               style="display:none;" onchange="previewImage(this)">
                    </label>
                    <!-- Hidden flag sent when user removes a chosen image -->
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="" disabled>
                </div>

                <div style="margin-top:20px;display:flex;gap:15px;">
                    <button type="submit" class="btn-primary" style="flex:1;padding:15px;">
                        <span class="material-icons-round" style="vertical-align:middle;font-size:18px;">add_circle</span>
                        List Product
                    </button>
                    <a href="index.php?url=vendor/inventory"
                       style="flex:0.4;padding:15px;background:white;border:1px solid #ddd;border-radius:6px;cursor:pointer;font-family:'Roboto Slab';text-align:center;text-decoration:none;color:#555;">
                        Cancel
                    </a>
                </div>
            </section>

            <!-- RIGHT: live preview -->
            <aside>
                <div class="card preview-card">
                    <h3 class="headline" style="margin-bottom:15px;font-size:15px;text-align:center;color:var(--red-accent);">Customer Preview</h3>
                    <div id="previewImgWrap" style="width:100%;height:180px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                        <span class="material-icons-round" style="font-size:40px;color:#ccc;">image</span>
                    </div>
                    <div style="margin-top:15px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <h2 id="prevName" style="font-size:18px;color:var(--dark-green);">Product Title</h2>
                            <span class="badge success" id="prevSeason">In Season</span>
                        </div>
                        <p id="prevPrice" style="color:var(--red-accent);font-weight:bold;font-size:18px;margin:8px 0;">₱0.00</p>
                        <p id="prevDesc" style="font-size:13px;color:#666;line-height:1.5;">Description will appear here.</p>
                    </div>
                </div>

            </aside>

        </div>
        </form>
    </main>
</div>

<script src="assets/js/vendor-add-product.js?v=<?= time(); ?>"></script>
</body>
</html>