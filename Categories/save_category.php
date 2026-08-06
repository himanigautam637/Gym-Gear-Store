<?php
require '../Admin/session_check.php';
require '../db_connect.php'; // defines $pdo (PDO)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_categories.php');
    exit;
}

$categoryId  = trim($_POST['category_id'] ?? '');
$name        = trim($_POST['category_name'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($name === '') {
    header('Location: manage_categories.php?err=' . urlencode('Category name is required.'));
    exit;
}

try {
    if ($categoryId !== '') {
        $stmt = $pdo->prepare("UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?");
        $stmt->execute([$name, $description, $categoryId]);
        $msg = 'Category updated successfully.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $msg = 'Category added successfully.';
    }

    header('Location: manage_categories.php?msg=' . urlencode($msg));
    exit;
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        header('Location: manage_categories.php?err=' . urlencode('A category with this name already exists.'));
    } else {
        header('Location: manage_categories.php?err=' . urlencode('Database error: ' . $e->getMessage()));
    }
    exit;
}