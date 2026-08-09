<?php
require 'session_check.php';
require '../db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_orders.php');
    exit;
}

$orderId = trim($_POST['order_id'] ?? '');
$status  = trim($_POST['order_status'] ?? '');
$allowed = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];

if ($orderId === '' || !in_array($status, $allowed)) {
    header('Location: manage_orders.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->execute([$status, $orderId]);

    // Keep payment_status in sync when an order is marked Delivered (COD is paid on delivery)
    if ($status === 'Delivered') {
        $pdo->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?")->execute([$orderId]);
    }

    header('Location: manage_orders.php?msg=' . urlencode('Order #' . $orderId . ' marked as ' . $status . '.'));
} catch (PDOException $e) {
    header('Location: manage_orders.php');
}
exit;