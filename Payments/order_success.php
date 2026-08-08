<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: checkout.php');
    exit;
}

$orderId = $_GET['order_id'] ?? '';
$order = null;

if ($orderId !== '') {
    $stmt = $pdo->prepare("SELECT order_id, total_amount, order_date FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$order) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Placed | Gym Gear Store</title>
<style>
    :root { --navy:#0C2340; --navy-deep:#081729; --card-dark:#122a4a; --orange:#FF6B35; --green:#4caf50; --muted:#93a2ba; --text:#e8edf5; --border:rgba(255,255,255,0.08); }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background:var(--navy-deep); min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .card { background:var(--card-dark); border:1px solid var(--border); border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.3); max-width:420px; width:90%; text-align:center; padding:40px 30px; }
    .check { width:64px; height:64px; border-radius:50%; background:rgba(76,175,80,0.15); display:flex; align-items:center; justify-content:center; margin:0 auto 18px; }
    .check svg { width:32px; height:32px; stroke:var(--green); fill:none; stroke-width:3; }
    h1 { color:var(--text); font-size:20px; margin-bottom:8px; }
    p { color:var(--muted); font-size:14px; margin-bottom:22px; }
    .order-id { font-weight:bold; color:var(--orange); }
    a.btn { display:inline-block; background:var(--orange); color:#fff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; font-size:14px; }
    a.btn:hover { background:#e85a29; }
</style>
</head>
<body>

<div class="card">
    <div class="check">
        <svg viewBox="0 0 24 24"><path d="M4 12l5 5L20 6"/></svg>
    </div>
    <h1>Order Placed Successfully!</h1>
    <p>Your order <span class="order-id">#<?= htmlspecialchars($order['order_id']) ?></span> has been placed for
       Rs. <?= number_format((float)$order['total_amount'], 2) ?>, payable by Cash on Delivery.</p>
    <a class="btn" href="../my_account.php">View My Orders</a>
</div>

</body>
</html>