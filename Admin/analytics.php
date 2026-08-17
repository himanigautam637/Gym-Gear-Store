<?php
require 'session_check.php';
require '../db_connect.php';

function buildDayRange($startDate, $numDays) {
    $days = [];
    for ($i = 0; $i < $numDays; $i++) {
        $d = (clone $startDate)->modify("+$i days");
        $days[$d->format('Y-m-d')] = ['orders' => 0, 'revenue' => 0.0];
    }
    return $days;
}

function niceCeil($value, $steps = 4) {
    if ($value <= 0) return $steps;
    $rough = $value / $steps;
    $magnitude = pow(10, floor(log10($rough)));
    $residual = $rough / $magnitude;
    if ($residual > 5) $niceResidual = 10;
    elseif ($residual > 2) $niceResidual = 5;
    elseif ($residual > 1) $niceResidual = 2;
    else $niceResidual = 1;
    return $niceResidual * $magnitude * $steps;
}

$startDate = new DateTime('-29 days');
$prevStartDate = new DateTime('-59 days');
$days = buildDayRange($startDate, 30);

try {
    $orderStmt = $pdo->prepare("SELECT DATE(order_date) AS d, COUNT(*) AS c FROM orders WHERE order_date >= ? GROUP BY DATE(order_date)");
    $orderStmt->execute([$startDate->format('Y-m-d 00:00:00')]);
    foreach ($orderStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($days[$row['d']])) $days[$row['d']]['orders'] = (int)$row['c'];
    }

    $revStmt = $pdo->prepare("SELECT DATE(order_date) AS d, SUM(total_amount) AS r FROM orders WHERE order_date >= ? AND order_status != 'Cancelled' GROUP BY DATE(order_date)");
    $revStmt->execute([$startDate->format('Y-m-d 00:00:00')]);
    foreach ($revStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($days[$row['d']])) $days[$row['d']]['revenue'] = (float)$row['r'];
    }

    $prevOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_date >= ? AND order_date < ?");
    $prevOrdersStmt->execute([$prevStartDate->format('Y-m-d 00:00:00'), $startDate->format('Y-m-d 00:00:00')]);
    $prevTotalOrders = (int)$prevOrdersStmt->fetchColumn();

    $prevRevStmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE order_date >= ? AND order_date < ? AND order_status != 'Cancelled'");
    $prevRevStmt->execute([$prevStartDate->format('Y-m-d 00:00:00'), $startDate->format('Y-m-d 00:00:00')]);
    $prevTotalRevenue = (float)$prevRevStmt->fetchColumn();
} catch (PDOException $e) {
    $prevTotalOrders = 0;
    $prevTotalRevenue = 0;
}

$totalOrders = array_sum(array_column($days, 'orders'));
$totalRevenue = array_sum(array_column($days, 'revenue'));
$avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

function pctChange($current, $previous) {
    if ($previous == 0) return $current > 0 ? 100 : 0;
    return (($current - $previous) / $previous) * 100;
}

$ordersChange = pctChange($totalOrders, $prevTotalOrders);
$revenueChange = pctChange($totalRevenue, $prevTotalRevenue);

function renderBarChart($days, $key, $color, $prefix = '', $isCompact = false) {
    $values = array_column($days, $key);
    $niceMax = niceCeil(max($values), 4);
    $count = count($values);
    $dateKeys = array_keys($days);

    $gridHtml = '';
    for ($g = 4; $g >= 0; $g--) {
        $val = ($niceMax / 4) * $g;
        $label = $isCompact
            ? ($val >= 1000 ? round($val / 1000, 1) . 'K' : round($val))
            : round($val);
        $gridHtml .= "<div class='bc-grid-row'><span class='bc-grid-label'>{$prefix}{$label}</span><div class='bc-grid-line'></div></div>";
    }

    $barsHtml = '';
    $i = 0;
    foreach ($values as $v) {
        $heightPct = $niceMax > 0 ? ($v / $niceMax) * 100 : 0;
        $displayVal = $isCompact
            ? ($v >= 1000 ? round($v / 1000, 1) . 'K' : round($v))
            : round($v);
        $showLabel = ($i % 5 === 0 || $i === $count - 1);
        $dateLabel = $showLabel ? date('M j', strtotime($dateKeys[$i])) : '';
        $delay = round($i * 18);

        $barsHtml .= "
            <div class='bc-col'>
                <span class='bc-value'>" . ($v > 0 ? $prefix . $displayVal : '') . "</span>
                <div class='bc-bar' data-target='{$heightPct}' style='background:{$color};transition-delay:{$delay}ms;'></div>
                <span class='bc-xlabel'>{$dateLabel}</span>
            </div>
        ";
        $i++;
    }

    return "
        <div class='bar-chart'>
            <div class='bc-yaxis'>{$gridHtml}</div>
            <div class='bc-plot'>{$barsHtml}</div>
        </div>
    ";
}

$ordersChartHtml = renderBarChart($days, 'orders', 'linear-gradient(180deg, #4d8dff, #2f55d4)', '', false);
$revenueChartHtml = renderBarChart($days, 'revenue', 'linear-gradient(180deg, #66d17a, #2e7d32)', 'Rs. ', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics | Gym Gear Store</title>
<link rel="stylesheet" href="assets/admin.css?v=2">
<style>
    .stat-card .trend { font-size: 12px; font-weight: bold; margin-top: 4px; }
    .stat-card .trend.up { color: var(--green); }
    .stat-card .trend.down { color: var(--red); }

    .bar-chart {
        display: flex;
        gap: 12px;
        height: 320px;
        margin-top: 10px;
    }

    .bc-yaxis {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 50px;
        flex-shrink: 0;
        padding-bottom: 34px;
    }

    .bc-grid-row {
        position: relative;
        display: flex;
        align-items: center;
        height: 0;
    }

    .bc-grid-label {
        font-size: 11px;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .bc-plot {
        position: relative;
        flex: 1;
        display: flex;
        align-items: flex-end;
        gap: 6px;
        border-left: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 0 4px;
        overflow-x: auto;
    }

    .bc-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        flex: 1;
        min-width: 18px;
        height: 100%;
        position: relative;
    }

    .bc-value {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: bold;
        margin-bottom: 4px;
        white-space: nowrap;
    }

    .bc-bar {
        width: 65%;
        min-width: 6px;
        height: 0%;
        border-radius: 4px 4px 0 0;
        transition: height 0.9s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .bc-bar.grown {
        /* height set inline via JS */
    }

    .bc-xlabel {
        position: absolute;
        bottom: -22px;
        font-size: 10px;
        color: var(--text-muted);
        white-space: nowrap;
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
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="../Products/manage_products.php">Products</a></li>
            <li><a href="../Categories/manage_categories.php">Categories</a></li>
            <li><a href="manage_orders.php">Orders</a></li>
            <li><a href="manage_clients.php">Registered Clients</a></li>
            <li><a href="manage_messages.php">Messages</a></li>
            <li><a href="analytics.php" class="active">Analytics</a></li>
        </ul>
    </nav>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h1>Analytics</h1>
            <div class="date">Last 30 days</div>
        </div>
        <div class="topbar-actions">
            <div class="admin-chip">
                <span class="dot"></span>
                <?= htmlspecialchars($_SESSION['admin_name']) ?>
            </div>
            <a href="logout.php" class="topbar-logout">Log Out</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Orders (30d)</h3>
            <div class="value"><?= $totalOrders ?></div>
            <div class="trend <?= $ordersChange >= 0 ? 'up' : 'down' ?>">
                <?= $ordersChange >= 0 ? '&uarr;' : '&darr;' ?> <?= abs(round($ordersChange, 1)) ?>% vs previous 30 days
            </div>
        </div>
        <div class="stat-card alt">
            <h3>Total Revenue (30d)</h3>
            <div class="value">Rs. <?= number_format($totalRevenue, 0) ?></div>
            <div class="trend <?= $revenueChange >= 0 ? 'up' : 'down' ?>">
                <?= $revenueChange >= 0 ? '&uarr;' : '&darr;' ?> <?= abs(round($revenueChange, 1)) ?>% vs previous 30 days
            </div>
        </div>
        <div class="stat-card">
            <h3>Avg. Order Value</h3>
            <div class="value">Rs. <?= number_format($avgOrderValue, 0) ?></div>
        </div>
    </div>

    <div class="panel chart-panel">
        <div class="panel-header">
            <h2>Orders per Day</h2>
        </div>
        <?= $ordersChartHtml ?>
    </div>

    <div class="panel chart-panel">
        <div class="panel-header">
            <h2>Revenue per Day</h2>
        </div>
        
        <?= $revenueChartHtml ?>
    </div>
</div>

<script>
function growBars() {
    var bars = document.querySelectorAll('.bc-bar');
    bars.forEach(function (bar) {
        bar.style.height = '0%';
    });
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            bars.forEach(function (bar) {
                var target = bar.getAttribute('data-target');
                bar.style.height = target + '%';
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', growBars);

window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        growBars();
    }
});
</script>

</body>
</html>