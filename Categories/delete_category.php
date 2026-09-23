<?php
require '../Admin/session_check.php';
require '../db_connect.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_categories.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categories SET is_active = 0 WHERE category_id = ?");
    $stmt->execute([$id]);
    header('Location: manage_categories.php?msg=' . urlencode('Category hidden from the store.'));
} catch (PDOException $e) {
    header('Location: manage_categories.php?err=' . urlencode('Could not hide category.'));
}
exit;