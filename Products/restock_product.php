<?php
require '../Admin/session_check.php';
require '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_products.php');
    exit;
}

$productId = trim($_POST['product_id'] ?? '');
$addQty = (int)($_POST['restock_qty'] ?? 0);
$newPrice = trim($_POST['new_price'] ?? '');

if ($productId === '' || $addQty <= 0) {
    header('Location: manage_products.php?err=' . urlencode('Enter a valid quantity to restock.'));
    exit;
}

if ($newPrice === '' || !is_numeric($newPrice) || (float)$newPrice < 0) {
    header('Location: manage_products.php?err=' . urlencode('Enter a valid price.'));
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE products SET stock = stock + ?, price = ?, status = 'Available' WHERE product_id = ?");
    $stmt->execute([$addQty, $newPrice, $productId]);

    header('Location: manage_products.php?msg=' . urlencode('Stock updated (+' . $addQty . '), price set to Rs. ' . number_format((float)$newPrice, 2) . '.'));
    exit;
} catch (PDOException $e) {
    header('Location: manage_products.php?err=' . urlencode('Could not update stock.'));
    exit;
}