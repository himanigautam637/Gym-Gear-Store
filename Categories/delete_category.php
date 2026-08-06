<?php
require '../Admin/session_check.php';
require '../db_connect.php'; // defines $pdo (PDO)

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_categories.php');
    exit;
}

try {
    $del = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
    $del->execute([$id]);
    header('Location: manage_categories.php?msg=' . urlencode('Category deleted.'));
} catch (PDOException $e) {
    // Likely a foreign key constraint failure because products still reference this category
    header('Location: manage_categories.php?err=' . urlencode('Cannot delete: products are still linked to this category.'));
}
exit;