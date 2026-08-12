<?php
require 'session_check.php';
require '../db_connect.php';
require 'mailer.php';
require 'mail_config.php';

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

    if ($status === 'Delivered') {
        $pdo->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?")->execute([$orderId]);
    }

    $userStmt = $pdo->prepare("
        SELECT u.full_name, u.email, o.total_amount
        FROM orders o
        JOIN users u ON u.user_id = o.user_id
        WHERE o.order_id = ?
    ");
    $userStmt->execute([$orderId]);
    $customer = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($customer && !empty($customer['email'])) {
        $mailer = new SmtpMailer(
            SMTP_HOST,
            SMTP_PORT,
            SMTP_USERNAME,
            SMTP_PASSWORD,
            SMTP_FROM_EMAIL,
            SMTP_FROM_NAME
        );

        $subject = "Order #$orderId Update - $status";

        $body = "
            <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;'>
                <h2 style='color:#0C2340;'>Order Status Update</h2>
                <p>Hi " . htmlspecialchars($customer['full_name']) . ",</p>
                <p>Your order <strong>#$orderId</strong> (Rs. " . number_format((float)$customer['total_amount'], 2) . ") is now:</p>
                <p style='font-size:20px;font-weight:bold;color:#FF6B35;'>" . htmlspecialchars($status) . "</p>
                <p>Thank you for shopping with Online Gym Gear Store.</p>
            </div>
        ";

        $mailer->send($customer['email'], $customer['full_name'], $subject, $body);
    }

    header('Location: manage_orders.php?msg=' . urlencode('Order #' . $orderId . ' marked as ' . $status . '.'));
} catch (PDOException $e) {
    header('Location: manage_orders.php');
}
exit;