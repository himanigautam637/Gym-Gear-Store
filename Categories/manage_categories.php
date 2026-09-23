<?php
require '../Admin/session_check.php';
require '../db_connect.php';

$view = ($_GET['status'] ?? 'active') === 'archived' ? 'archived' : 'active';

$categories = [];
try {
    $stmt = $pdo->prepare("
        SELECT cat.category_id, cat.category_name, cat.description,
               (SELECT COUNT(*) FROM products p WHERE p.category_id = cat.category_id) AS product_count
        FROM categories cat
        WHERE cat.is_active = ?
        ORDER BY cat.category_id DESC
    ");
    $stmt->execute([$view === 'archived' ? 0 : 1]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
<title>Categories | Online Gym Gear Store</title>
<link rel="stylesheet" href="../Admin/assets/admin.css?v=2">
<style>
    .category-tabs { display: flex; gap: 6px; margin-bottom: 16px; }
    .category-tab {
        padding: 9px 18px; border-radius: 8px 8px 0 0; font-size: 13px; font-weight: bold;
        color: var(--text-muted); text-decoration: none; border: 1px solid transparent;
    }
    .category-tab:hover { color: var(--navy); }
    .category-tab.active {
        color: var(--navy); background: var(--card); border-color: var(--border);
        border-bottom-color: var(--card); position: relative; top: 1px;
    }
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
            <li><a href="../Products/manage_products.php">Products</a></li>
            <li><a href="manage_categories.php" class="active">Categories</a></li>
            <li><a href="../Admin/manage_orders.php">Orders</a></li>
            <li><a href="../Admin/manage_clients.php">Registered Clients</a></li>
            <li><a href="../Admin/manage_messages.php">Messages</a></li>
            <li><a href="../Admin/analytics.php">Analytics</a></li>
        </ul>
    </nav>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Categories</h1>
            <div class="date"><?= count($categories) ?> <?= $view === 'archived' ? 'archived categories' : 'total categories' ?></div>
        </div>
        <div class="topbar-actions">
            <div class="admin-chip">
                <span class="dot"></span>
                <?= htmlspecialchars($_SESSION['admin_name']) ?>
            </div>
            <a href="../Admin/logout.php" class="topbar-logout">Log Out</a>
        </div>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="category-tabs">
        <a href="manage_categories.php" class="category-tab <?= $view === 'active' ? 'active' : '' ?>">Active Categories</a>
        <a href="manage_categories.php?status=archived" class="category-tab <?= $view === 'archived' ? 'active' : '' ?>">Archived Categories</a>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2><?= $view === 'archived' ? 'Archived Categories' : 'All Categories' ?></h2>
            <?php if ($view === 'active'): ?>
                <button class="btn btn-primary" onclick="openAddCategory()">+ Add Category</button>
            <?php endif; ?>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="4" class="empty-row"><?= $view === 'archived' ? 'No archived categories.' : 'No categories added yet. Click "Add Category" to create one.' ?></td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['category_name']) ?></strong></td>
                        <td class="text-muted-cell"><?= htmlspecialchars(mb_strimwidth($c['description'] ?? '', 0, 90, '...')) ?></td>
                        <td><?= (int)$c['product_count'] ?></td>
                        <td>
                            <?php if ($view === 'archived'): ?>
                                <a class="btn-icon" style="color:var(--green);"
                                   href="restore_category.php?id=<?= $c['category_id'] ?>">Restore</a>
                            <?php else: ?>
                                <button class="btn-icon btn-edit"
                                    onclick='openEditCategory(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                                <a class="btn-icon btn-delete"
                                   href="delete_category.php?id=<?= $c['category_id'] ?>"
                                   onclick="return confirmDelete('Hide this category from the store? It will be archived, not permanently deleted.')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="categoryModal">
    <div class="modal-box">
        <h2 id="categoryModalTitle">Add Category</h2>
        <form action="save_category.php" method="POST">
            <input type="hidden" name="category_id" id="cat_id" value="">

            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="category_name" id="cat_name" required placeholder="e.g. Dumbbells">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="cat_description" placeholder="Short description of this category"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('categoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script src="../Admin/assets/admin.js"></script>
<script>
function openAddCategory() {
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_name').value = '';
    document.getElementById('cat_description').value = '';
    openModal('categoryModal');
}

function openEditCategory(cat) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('cat_id').value = cat.category_id;
    document.getElementById('cat_name').value = cat.category_name;
    document.getElementById('cat_description').value = cat.description || '';
    openModal('categoryModal');
}
</script>

</body>
</html>