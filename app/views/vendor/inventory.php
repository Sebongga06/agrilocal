<?php $pageTitle = 'AgriLocal - Inventory'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/vendor-inventory.css?v=<?= time(); ?>">
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

        <!-- Flash messages -->
        <?php if (isset($_GET['added'])): ?>
            <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                <span class="material-icons-round" style="font-size:18px;">check_circle</span> Product added successfully.
            </div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div style="background:#d4edda;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                <span class="material-icons-round" style="font-size:18px;">check_circle</span> Product updated successfully.
            </div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                <span class="material-icons-round" style="font-size:18px;">delete</span> Product deleted.
            </div>
        <?php endif; ?>

        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
            <div>
                <h1 class="headline">Product Inventory</h1>
                <p style="color:#666;font-size:14px;">Manage your stock levels and seasonal availability.</p>
            </div>
            <a href="index.php?url=vendor/addProduct" class="btn-primary" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
                <span class="material-icons-round">add</span> Add New Product
            </a>
        </header>

        <!-- Stats cards -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:30px;">
            <div class="card" style="padding:15px;margin-top:0;text-align:center;">
                <p style="font-size:12px;color:#666;">Total Items</p>
                <h3 style="color:var(--dark-green);"><?php echo (int)($stats['total'] ?? 0); ?></h3>
            </div>
            <div class="card" style="padding:15px;margin-top:0;text-align:center;border-bottom:3px solid var(--red-accent);">
                <p style="font-size:12px;color:#666;">Low Stock</p>
                <h3 style="color:var(--red-accent);"><?php echo (int)($stats['low_stock'] ?? 0); ?></h3>
            </div>
            <div class="card" style="padding:15px;margin-top:0;text-align:center;border-bottom:3px solid var(--green-accent);">
                <p style="font-size:12px;color:#666;">In Season</p>
                <h3 style="color:var(--green-accent);"><?php echo (int)($stats['in_season'] ?? 0); ?></h3>
            </div>
            <div class="card" style="padding:15px;margin-top:0;text-align:center;">
                <p style="font-size:12px;color:#666;">Drafts</p>
                <h3 style="color:#999;"><?php echo (int)($stats['drafts'] ?? 0); ?></h3>
            </div>
        </div>

        <!-- Search & filter -->
        <form method="GET" action="index.php">
            <input type="hidden" name="url" value="vendor/inventory">
            <div class="card" style="padding:15px;margin-bottom:10px;display:flex;gap:15px;align-items:center;background:var(--grey-light);">
                <div style="flex:1;position:relative;">
                    <span class="material-icons-round" style="position:absolute;left:10px;top:10px;font-size:20px;color:#999;">search</span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by product name..."
                           style="width:100%;padding:10px 10px 10px 40px;border-radius:6px;border:1px solid #ddd;">
                </div>
                <select name="category" style="padding:10px;border-radius:6px;border:1px solid #ddd;font-family:'Noto Serif';">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['cat_id']; ?>"
                            <?php echo $categoryId == $cat['cat_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary" style="padding:10px 20px;">Filter</button>
            </div>
        </form>

        <!-- Product table -->
        <div class="card" style="padding:0;overflow:hidden;">
            <table>
                <thead style="background:var(--light-green);">
                    <tr>
                        <th style="width:80px;">Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Season</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:#888;">
                            No products found.
                            <a href="index.php?url=vendor/addProduct" style="color:var(--green-accent);font-weight:bold;">Add your first product →</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p):
                        $images    = json_decode($p['prd_images'] ?? '[]', true);
                        $imgSrc    = !empty($images[0]) ? htmlspecialchars($images[0]) : null;
                        $isLow     = $p['prd_stock_quantity'] <= 10;
                    ?>
                    <tr>
                        <td>
                            <?php if ($imgSrc): ?>
                                <img src="<?php echo $imgSrc; ?>" alt="product"
                                     style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                                <div style="width:50px;height:50px;background:#eee;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                    <span class="material-icons-round" style="color:#ccc;font-size:20px;">image</span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($p['prd_name']); ?></strong><br>
                            <small style="color:#888;">#<?php echo $p['prd_id']; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($p['cat_name']); ?></td>
                        <td>₱<?php echo number_format($p['prd_price'], 2); ?> <small>/<?php echo htmlspecialchars($p['prd_unit']); ?></small></td>
                        <td>
                            <?php if ($isLow): ?>
                                <span style="color:var(--red-accent);font-weight:bold;"><?php echo $p['prd_stock_quantity'] + 0; ?> <?php echo htmlspecialchars($p['prd_unit']); ?></span><br>
                                <small style="color:var(--red-accent);">Low Stock</small>
                            <?php else: ?>
                                <?php echo $p['prd_stock_quantity'] + 0; ?> <?php echo htmlspecialchars($p['prd_unit']); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['prd_is_in_season']): ?>
                                <span class="badge success">In Season</span>
                            <?php else: ?>
                                <span class="badge" style="background:#eee;color:#777;">Off Season</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['prd_is_available']): ?>
                                <span class="badge" style="background:#e8f5e9;color:#2e7d32;">Active</span>
                            <?php else: ?>
                                <span class="badge" style="background:#f5f5f5;color:#999;">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;white-space:nowrap;">
                            <a href="index.php?url=vendor/editProduct&id=<?php echo $p['prd_id']; ?>"
                               style="text-decoration:none;color:var(--green-accent);margin-right:8px;"
                               title="Edit">
                                <span class="material-icons-round" style="vertical-align:middle;">edit</span>
                            </a>
                            <button type="button"
                                    onclick="openDeleteModal(<?php echo $p['prd_id']; ?>, '<?php echo addslashes(htmlspecialchars($p['prd_name'])); ?>')"
                                    style="background:none;border:none;cursor:pointer;color:#e57373;" title="Delete">
                                <span class="material-icons-round" style="vertical-align:middle;">delete</span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<!-- Shared delete form -->
<form id="delete-form" method="POST" action="index.php?url=vendor/deleteProduct">
    <input type="hidden" name="product_id" id="deleteProductId">
</form>

<!-- Custom delete confirmation modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">
            <span class="material-icons-round" style="color:var(--red-accent);font-size:24px;">delete_forever</span>
        </div>
        <h3>Delete Product?</h3>
        <p>You're about to permanently delete <strong id="deleteProductName"></strong>. This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="modal-btn-delete"
                    onclick="document.getElementById('delete-form').submit();">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<script src="assets/js/vendor-inventory.js?v=<?= time(); ?>"></script>
</body>
</html>