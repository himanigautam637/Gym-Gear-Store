<?php
require '../Admin/session_check.php';
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_products.php');
    exit;
}

$productId = trim($_POST['product_id'] ?? '');
$addQty = (int)($_POST['restock_qty'] ?? 0);

if ($productId === '' || $addQty <= 0) {
    header('Location: manage_products.php?err=' . urlencode('Enter a valid quantity to restock.'));
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE products SET stock = stock + ?, status = 'Available' WHERE product_id = ?");
    $stmt->execute([$addQty, $productId]);

    header('Location: manage_products.php?msg=' . urlencode('Stock updated (+' . $addQty . ').'));
    exit;
} catch (PDOException $e) {
    header('Location: manage_products.php?err=' . urlencode('Could not update stock.'));
    exit;
}