<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$key      = trim($_POST['key'] ?? '');
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

if ($key === '') {
    header('Location: cart.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT c.product_id, p.stock FROM cart c JOIN products p ON p.product_id = c.product_id WHERE c.cart_id = ? AND c.user_id = ?");
    $stmt->execute([$key, $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $quantity = min($quantity, $row['stock']);
        $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?")->execute([$quantity, $key]);
    }
} else {
   
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->execute([$key]);
    $stock = $stmt->fetchColumn();

    if ($stock !== false && isset($_SESSION['guest_cart'][$key])) {
        $_SESSION['guest_cart'][$key] = min($quantity, (int)$stock);
    }
}

header('Location: cart.php');
exit;