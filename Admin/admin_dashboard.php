<?php
require 'session_check.php';
require '../db_connect.php'; // db_connect.php lives in the project root, defines $pdo (PDO)

/* ---------------------------------------------------------
   Stat queries (PDO)
   Adjust table/column names below if yours differ.
--------------------------------------------------------- */
function count_rows($pdo, $sql) {
    try {
        $stmt = $pdo->query($sql);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

$totalProducts   = count_rows($pdo, "SELECT COUNT(*) FROM products");
$totalCategories = count_rows($pdo, "SELECT COUNT(*) FROM categories");
$totalOrders     = count_rows($pdo, "SELECT COUNT(*) FROM orders");
$totalClients    = count_rows($pdo, "SELECT COUNT(*) FROM users");
$pendingOrders   = count_rows($pdo, "SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'");

/* Order status breakdown (for the status bars) */
$statusCounts = [
    'Pending'   => 0,
    'Confirmed' => 0,
    'Packed'    => 0,
    'Shipped'   => 0,
    'Delivered' => 0,
    'Cancelled' => 0,
];
try {
    $statusStmt = $pdo->query("SELECT order_status, COUNT(*) AS cnt FROM orders GROUP BY order_status");
    foreach ($statusStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($statusCounts[$row['order_status']])) {
            $statusCounts[$row['order_status']] = (int) $row['cnt'];
        }
    }
} catch (PDOException $e) {
    // table/column may not exist yet — bars just render at 0
}
$statusMax = max(1, max($statusCounts));

/* Recent orders (latest 6) */
$recentOrders = [];
try {
    $ordersStmt = $pdo->query("
        SELECT o.order_id, u.full_name, o.total_amount, o.order_status, o.order_date
        FROM orders o
        LEFT JOIN users u ON u.user_id = o.user_id
        ORDER BY o.order_date DESC
        LIMIT 6
    ");
    $recentOrders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentOrders = [];
}

/* Recently added products (latest 5) */
$recentProducts = [];
try {
    $productsStmt = $pdo->query("
        SELECT p.product_id, p.product_name, p.price, p.stock, cat.category_name
        FROM products p
        LEFT JOIN categories cat ON cat.category_id = p.category_id
        ORDER BY p.product_id DESC
        LIMIT 5
    ");
    $recentProducts = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentProducts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Gym Gear Store</title>
<style>
    :root {
        --navy: #0C2340;
        --navy-light: #16345c;
        --orange: #FF6B35;
        --orange-light: #ff9d80;
        --bg: #f4f6f9;
        --card: #ffffff;
        --text-muted: #6b7280;
        --border: #e6e9ee;
        --green: #2e7d32;
        --red: #c62828;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: var(--bg);
        min-height: 100vh;
        display: flex;
        color: #1f2937;
    }

    /* ---------------- Sidebar ---------------- */
    .sidebar {
        width: 240px;
        background-color: var(--navy);
        color: #ffffff;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding: 25px 0;
        position: sticky;
        top: 0;
    }

    .sidebar .brand {
        text-align: center;
        padding-bottom: 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 20px;
    }

    .sidebar .brand h2 {
        font-size: 18px;
        letter-spacing: 1px;
    }

    .sidebar .brand span {
        display: block;
        font-size: 12px;
        color: #c9d4e0;
        margin-top: 4px;
    }

    .sidebar nav ul {
        list-style: none;
    }

    .sidebar nav ul li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 25px;
        color: #dbe4f0;
        text-decoration: none;
        font-size: 14px;
        border-left: 3px solid transparent;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .sidebar nav ul li a:hover,
    .sidebar nav ul li a.active {
        background-color: rgba(255,107,53,0.12);
        border-left: 3px solid var(--orange);
        color: #ffffff;
    }

    .sidebar .logout-link {
        margin-top: auto;
        padding: 12px 25px;
    }

    .sidebar .logout-link a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--orange-light);
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
    }

    .sidebar .logout-link a:hover {
        color: var(--orange);
    }

    /* ---------------- Main ---------------- */
    .main {
        flex: 1;
        padding: 30px 40px;
        max-width: 100%;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .topbar h1 {
        color: var(--navy);
        font-size: 24px;
    }

    .topbar .date {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .admin-chip {
        background-color: var(--card);
        border: 1px solid var(--border);
        border-radius: 30px;
        padding: 8px 16px;
        font-size: 14px;
        color: var(--navy);
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .admin-chip .dot {
        width: 8px;
        height: 8px;
        background-color: var(--green);
        border-radius: 50%;
        display: inline-block;
    }

    /* ---------------- Stat cards ---------------- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 18px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--card);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-top: 4px solid var(--orange);
        display: flex;
        flex-direction: column;
        gap: 6px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }

    .stat-card.alt {
        border-top-color: var(--navy);
    }

    .stat-card h3 {
        font-size: 12px;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .value {
        font-size: 28px;
        font-weight: bold;
        color: var(--navy);
    }

    /* ---------------- Content grid ---------------- */
    .content-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 950px) {
        .content-grid { grid-template-columns: 1fr; }
    }

    .panel {
        background-color: var(--card);
        border-radius: 10px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .panel-header h2 {
        font-size: 16px;
        color: var(--navy);
    }

    .panel-header a {
        font-size: 12px;
        color: var(--orange);
        text-decoration: none;
        font-weight: bold;
    }

    .panel-header a:hover { text-decoration: underline; }

    /* ---------------- Order status bars ---------------- */
    .status-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .status-row:last-child { margin-bottom: 0; }

    .status-label {
        width: 90px;
        font-size: 13px;
        color: #444;
        flex-shrink: 0;
    }

    .status-track {
        flex: 1;
        background-color: #eef1f5;
        border-radius: 20px;
        height: 14px;
        overflow: hidden;
    }

    .status-fill {
        height: 100%;
        border-radius: 20px;
        background: linear-gradient(90deg, var(--orange), var(--orange-light));
    }

    .status-fill.navy {
        background: linear-gradient(90deg, var(--navy), var(--navy-light));
    }

    .status-count {
        width: 28px;
        text-align: right;
        font-size: 13px;
        font-weight: bold;
        color: var(--navy);
        flex-shrink: 0;
    }

    /* ---------------- Tables ---------------- */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    thead th {
        text-align: left;
        color: #8a93a3;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.4px;
        padding: 8px 6px;
        border-bottom: 2px solid var(--border);
    }

    tbody td {
        padding: 12px 6px;
        border-bottom: 1px solid var(--border);
        color: #333;
    }

    tbody tr:last-child td { border-bottom: none; }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        text-transform: capitalize;
    }

    .badge.Pending    { background: #fff3e0; color: #e65100; }
    .badge.Confirmed  { background: #e0f2f1; color: #00695c; }
    .badge.Packed     { background: #fff8e1; color: #f57f17; }
    .badge.Shipped    { background: #ede7f6; color: #4527a0; }
    .badge.Delivered  { background: #e8f5e9; color: var(--green); }
    .badge.Cancelled  { background: #ffebee; color: var(--red); }

    .empty-row {
        text-align: center;
        color: var(--text-muted);
        padding: 20px 0;
        font-style: italic;
    }

    .stock-low { color: var(--red); font-weight: bold; }
    .stock-ok  { color: var(--green); font-weight: bold; }
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
            <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
            <li><a href="../Products/manage_products.php">Products</a></li>
            <li><a href="../Categories/manage_categories.php">Categories</a></li>
            <li><a href="manage_orders.php">Orders</a></li>
            <li><a href="manage_clients.php">Registered Clients</a></li>
        </ul>
    </nav>
    <div class="logout-link">
        <a href="logout.php">Log Out</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <div class="date"><?= date('l, F j, Y') ?></div>
        </div>
        <div class="admin-chip">
            <span class="dot"></span>
            <?= htmlspecialchars($_SESSION['admin_name']) ?>
        </div>
    </div>

    <div class="stats-grid">
        <a href="../Products/manage_products.php" class="stat-card">
            <h3>Total Products</h3>
            <div class="value"><?= $totalProducts ?></div>
        </a>
        <a href="../Categories/manage_categories.php" class="stat-card alt">
            <h3>Categories</h3>
            <div class="value"><?= $totalCategories ?></div>
        </a>
        <a href="manage_orders.php" class="stat-card">
            <h3>Total Orders</h3>
            <div class="value"><?= $totalOrders ?></div>
        </a>
        <a href="manage_clients.php" class="stat-card alt">
            <h3>Registered Clients</h3>
            <div class="value"><?= $totalClients ?></div>
        </a>
        <a href="manage_orders.php" class="stat-card">
            <h3>Pending Orders</h3>
            <div class="value"><?= $pendingOrders ?></div>
        </a>
    </div>

    <div class="content-grid">
        <div class="panel">
            <div class="panel-header">
                <h2>Recent Orders</h2>
                <a href="manage_orders.php">View all</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="empty-row">No orders yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($o['order_id']) ?></td>
                            <td><?= htmlspecialchars($o['full_name'] ?? 'Guest') ?></td>
                            <td>Rs. <?= number_format((float)$o['total_amount'], 2) ?></td>
                            <td><span class="badge <?= htmlspecialchars($o['order_status']) ?>"><?= htmlspecialchars($o['order_status']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($o['order_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h2>Order Status Overview</h2>
            </div>
            <?php $isNavy = false; foreach ($statusCounts as $label => $count): ?>
            <div class="status-row">
                <div class="status-label"><?= $label ?></div>
                <div class="status-track">
                    <div class="status-fill <?= $isNavy ? 'navy' : '' ?>" style="width: <?= ($count / $statusMax) * 100 ?>%;"></div>
                </div>
                <div class="status-count"><?= $count ?></div>
            </div>
            <?php $isNavy = !$isNavy; endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Recently Added Products</h2>
            <a href="../Products/manage_products.php">Manage products</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentProducts)): ?>
                    <tr><td colspan="4" class="empty-row">No products added yet</td></tr>
                <?php else: ?>
                    <?php foreach ($recentProducts as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['product_name']) ?></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></td>
                        <td>Rs. <?= number_format((float)$p['price'], 2) ?></td>
                        <td class="<?= $p['stock'] <= 5 ? 'stock-low' : 'stock-ok' ?>"><?= (int)$p['stock'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>