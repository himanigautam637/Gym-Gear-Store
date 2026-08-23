<?php
require '../Admin/session_check.php';
require '../db_connect.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE product_id = ?");
    $stmt->execute([$id]);

    header('Location: manage_products.php?msg=' . urlencode('Product hidden from the store.'));
} catch (PDOException $e) {
    header('Location: manage_products.php?err=' . urlencode('Could not hide product.'));
}
exit;