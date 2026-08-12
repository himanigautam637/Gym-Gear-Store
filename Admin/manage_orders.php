<?php
require 'session_check.php';
require '../db_connect.php'; 

$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.order_id, u.full_name, u.email, o.total_amount, o.order_status, o.payment_status, o.order_date
        FROM orders o
        LEFT JOIN users u ON u.user_id = o.user_id
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
}

$statuses = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
$message = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders | Gym Gear Store</title>
<link rel="stylesheet" href="assets/admin.css">
<style>
    .badge.Confirmed { background: #e0f2f1; color: #00695c; }
    .badge.Packed { background: #fff8e1; color: #f57f17; }
    .pay-chip { font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: bold; }
    .pay-chip.Paid { background: #e8f5e9; color: var(--green); }
    .pay-chip.Pending { background: #fff3e0; color: #e65100; }
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
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="../Products/manage_products.php">Products</a></li>
            <li><a href="../Categories/manage_categories.php">Categories</a></li>
            <li><a href="manage_orders.php" class="active">Orders</a></li>
            <li><a href="manage_clients.php">Registered Clients</a></li>
            <li><a href="manage_messages.php">Messages</a></li>
        </ul>
    </nav>
    <div class="logout-link">
        <a href="logout.php">Log Out</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Orders</h1>
            <div class="date"><?= count($orders) ?> total orders</div>
        </div>
        <div class="admin-chip">
            <span class="dot"></span>
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
        </div>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <div class="panel">
        <div class="panel-header">
            <h2>All Orders</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Order Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="empty-row">No orders placed yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($o['order_id']) ?></td>
                        <td><?= htmlspecialchars($o['full_name'] ?? 'Guest') ?></td>
                        <td class="text-muted-cell"><?= htmlspecialchars($o['email'] ?? '-') ?></td>
                        <td>Rs. <?= number_format((float)$o['total_amount'], 2) ?></td>
                        <td><span class="pay-chip <?= htmlspecialchars($o['payment_status']) ?>"><?= htmlspecialchars($o['payment_status']) ?></span></td>
                        <td><?= date('M j, Y', strtotime($o['order_date'])) ?></td>
                        <td>
                            <form action="update_order_status.php" method="POST" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                <select name="order_status" class="status-select" onchange="this.form.submit()">
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $o['order_status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>