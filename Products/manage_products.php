<?php
require '../Admin/session_check.php';
require '../db_connect.php'; 

$search = trim($_GET['search'] ?? '');

$products = [];
try {
    $sql = "
        SELECT p.product_id, p.product_name, p.description, p.price, p.stock, p.status,
               p.category_id, cat.category_name,
               (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS thumbnail
        FROM products p
        LEFT JOIN categories cat ON cat.category_id = p.category_id
    ";
    $params = [];
    if ($search !== '') {
        $sql .= " WHERE p.product_name LIKE ? OR cat.category_name LIKE ? ";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    $sql .= " ORDER BY p.product_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}

/* Fetch full image gallery per product, for the edit modal */
$galleries = [];
try {
    $imgStmt = $pdo->query("SELECT image_id, product_id, image_path FROM product_images ORDER BY image_id ASC");
    foreach ($imgStmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $galleries[$img['product_id']][] = $img;
    }
} catch (PDOException $e) {
    $galleries = [];
}

$categories = [];
try {
    $categories = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

$message = $_GET['msg'] ?? '';
$error   = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products | Gym Gear Store</title>
<link rel="stylesheet" href="../Admin/assets/admin.css">
<style>
    .gallery { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
    .gallery-item { position: relative; }
    .gallery-item img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
    .gallery-item a {
        position: absolute; top: -6px; right: -6px;
        background: var(--red); color: #fff; border-radius: 50%;
        width: 18px; height: 18px; font-size: 11px; line-height: 18px;
        text-align: center; text-decoration: none; font-weight: bold;
    }
    .admin-search { display: flex; align-items: center; gap: 8px; flex: 1; max-width: 320px; margin: 0 16px; }
    .admin-search input {
        flex: 1; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;
    }
    .admin-search input:focus { outline: none; border-color: var(--orange); }
    .admin-search button {
        background: var(--navy); color: #fff; border: none; border-radius: 6px;
        padding: 8px 14px; font-size: 12px; font-weight: bold; cursor: pointer;
    }
    .admin-search button:hover { background: var(--navy-light); }
    .admin-search .clear-search { font-size: 12px; color: var(--text-muted); text-decoration: none; white-space: nowrap; }
    .admin-search .clear-search:hover { color: var(--red); }
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>GYM GEAR STORE</h2>
        <span>Admin Panel</span>
    </div>
    <nav>
        <ul>
            <li><a href="../Admin/admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_products.php" class="active">Products</a></li>
            <li><a href="../Categories/manage_categories.php">Categories</a></li>
            <li><a href="../Admin/manage_orders.php">Orders</a></li>
            <li><a href="../Admin/manage_clients.php">Registered Clients</a></li>
            <li><a href="../Admin/manage_messages.php">Messages</a></li>
        </ul>
    </nav>
    <div class="logout-link">
        <a href="../Admin/logout.php">Log Out</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Products</h1>
            <div class="date"><?= count($products) ?> total products</div>
        </div>
        <div class="admin-chip">
            <span class="dot"></span>
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
        </div>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>All Products</h2>
            <form action="manage_products.php" method="GET" class="admin-search">
                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="manage_products.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>
            <?php if (empty($categories)): ?>
                <span style="font-size:12px;color:var(--red);">Add a category first before adding products.</span>
            <?php else: ?>
                <button class="btn btn-primary" onclick="openAddProduct()">+ Add Product</button>
            <?php endif; ?>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="8" class="empty-row"><?= $search !== '' ? 'No products matched "' . htmlspecialchars($search) . '".' : 'No products added yet.' ?></td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <img class="thumb"
                                 src="<?= $p['thumbnail'] ? '../uploads/products/' . htmlspecialchars($p['thumbnail']) : '' ?>"
                                 alt="<?= htmlspecialchars($p['product_name']) ?>"
                                 onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2246%22 height=%2246%22><rect width=%2246%22 height=%2246%22 fill=%22%23eef1f5%22/></svg>'">
                        </td>
                        <td><strong><?= htmlspecialchars($p['product_name']) ?></strong></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></td>
                        <td class="text-muted-cell"><?= htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 70, '...')) ?></td>
                        <td>Rs. <?= number_format((float)$p['price'], 2) ?></td>
                        <td class="<?= $p['stock'] <= 5 ? 'stock-low' : 'stock-ok' ?>"><?= (int)$p['stock'] ?></td>
                        <td><span class="badge <?= $p['status'] === 'Available' ? 'Delivered' : 'Cancelled' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                        <td>
                            <button class="btn-icon btn-edit"
                                onclick='openEditProduct(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($galleries[$p['product_id']] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                            <button class="btn-icon" style="color:var(--green);"
                                onclick="openRestock(<?= $p['product_id'] ?>, '<?= htmlspecialchars(addslashes($p['product_name'])) ?>')">Restock</button>
                            <a class="btn-icon btn-delete"
                               href="delete_product.php?id=<?= $p['product_id'] ?>"
                               onclick="return confirmDelete('Delete this product and all its images? This cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Product Modal -->
<div class="modal-overlay" id="productModal">
    <div class="modal-box">
        <h2 id="productModalTitle">Add Product</h2>
        <form action="save_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="p_id" value="">

            <div class="form-group" id="p_gallery_wrap" style="display:none;">
                <label>Current Images</label>
                <div class="gallery" id="p_gallery"></div>
                <span style="font-size:11px;color:var(--text-muted);">Click the red X to remove an image</span>
            </div>

            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="product_name" id="p_name" required placeholder="e.g. Adjustable Dumbbell 20kg">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="p_category" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="p_description" placeholder="Product details, material, size, etc."></textarea>
            </div>

            <div class="form-group">
                <label>Price (Rs.)</label>
                <input type="number" name="price" id="p_price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock" id="p_stock" min="0" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="p_status">
                    <option value="Available">Available</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>

            <div class="form-group">
                <label>Product Images</label>
                <input type="file" name="images[]" accept="image/png, image/jpeg, image/webp" multiple>
                <span style="font-size:11px;color:var(--text-muted);">You can select multiple images. New ones are added to the gallery.</span>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('productModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="restockModal">
    <div class="modal-box">
        <h2 id="restockModalTitle">Restock Product</h2>
        <form action="restock_product.php" method="POST">
            <input type="hidden" name="product_id" id="r_product_id" value="">
            <div class="form-group">
                <label>Quantity to Add</label>
                <input type="number" name="restock_qty" min="1" required autofocus>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('restockModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<script src="../Admin/assets/admin.js"></script>
<script>
function openRestock(productId, productName) {
    document.getElementById('r_product_id').value = productId;
    document.getElementById('restockModalTitle').textContent = 'Restock: ' + productName;
    openModal('restockModal');
}
function openAddProduct() {
    document.getElementById('productModalTitle').textContent = 'Add Product';
    document.getElementById('p_id').value = '';
    document.getElementById('p_name').value = '';
    document.getElementById('p_description').value = '';
    document.getElementById('p_price').value = '';
    document.getElementById('p_stock').value = '';
    document.getElementById('p_status').value = 'Available';
    document.getElementById('p_gallery_wrap').style.display = 'none';
    openModal('productModal');
}

function openEditProduct(p, images) {
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('p_id').value = p.product_id;
    document.getElementById('p_name').value = p.product_name;
    document.getElementById('p_description').value = p.description || '';
    document.getElementById('p_price').value = p.price;
    document.getElementById('p_stock').value = p.stock;
    document.getElementById('p_status').value = p.status;
    document.getElementById('p_category').value = p.category_id;

    var galleryWrap = document.getElementById('p_gallery_wrap');
    var gallery = document.getElementById('p_gallery');
    gallery.innerHTML = '';

    if (images && images.length > 0) {
        galleryWrap.style.display = 'block';
        images.forEach(function (img) {
            var div = document.createElement('div');
            div.className = 'gallery-item';
            div.innerHTML = '<img src="../uploads/products/' + img.image_path + '">' +
                '<a href="delete_product_image.php?id=' + img.image_id + '&product_id=' + p.product_id +
                '" onclick="return confirmDelete(\'Remove this image?\')">x</a>';
            gallery.appendChild(div);
        });
    } else {
        galleryWrap.style.display = 'none';
    }
    openModal('productModal');
}
</script>

</body>
</html>