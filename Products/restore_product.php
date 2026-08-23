<?php
require '../Admin/session_check.php';
require '../db_connect.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE products SET is_active = 1 WHERE product_id = ?");
    $stmt->execute([$id]);

    header('Location: manage_products.php?view=archived&msg=' . urlencode('Product restored.'));
} catch (PDOException $e) {
    header('Location: manage_products.php?view=archived&err=' . urlencode('Could not restore product.'));
}
exit;