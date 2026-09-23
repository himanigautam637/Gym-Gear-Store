<?php
require '../Admin/session_check.php';
require '../db_connect.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_categories.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categories SET is_active = 1 WHERE category_id = ?");
    $stmt->execute([$id]);
    header('Location: manage_categories.php?status=archived&msg=' . urlencode('Category restored.'));
} catch (PDOException $e) {
    header('Location: manage_categories.php?status=archived&err=' . urlencode('Could not restore category.'));
}
exit;