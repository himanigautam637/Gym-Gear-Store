<?php
require 'session_check.php';
require '../db_connect.php'; 

$clients = [];
try {
    $stmt = $pdo->query("
        SELECT u.user_id, u.full_name, u.email, u.phone, u.address, u.created_at,
               (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) AS order_count
        FROM users u
        ORDER BY u.created_at DESC
    ");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clients = [];
}

$message = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registered Clients | Gym Gear Store</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <h2>GYM GEAR STORE</h2>
        <span>Admin Panel</span>
    </div>
    <nav>
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="../Products/manage_products.php">Products</a></li>
            <li><a href="../Categories/manage_categories.php">Categories</a></li>
            <li><a href="manage_orders.php">Orders</a></li>
            <li><a href="manage_clients.php" class="active">Registered Clients</a></li>
            <li><a href="admin_register.php">Add Admin</a></li>
        </ul>
    </nav>
    <div class="logout-link">
        <a href="logout.php">Log Out</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Registered Clients</h1>
            <div class="date"><?= count($clients) ?> total clients</div>
        </div>
        <div class="admin-chip">
            <span class="dot"></span>
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
        </div>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>All Clients</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Orders Placed</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="6" class="empty-row">No clients registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($clients as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                        <td class="text-muted-cell"><?= htmlspecialchars(mb_strimwidth($c['address'] ?? '-', 0, 40, '...')) ?></td>
                        <td><?= (int)$c['order_count'] ?></td>
                        <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>