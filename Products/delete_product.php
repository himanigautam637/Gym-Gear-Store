<?php
require '../Admin/session_check.php';
require '../db_connect.php'; // defines $pdo (PDO)

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_products.php');
    exit;
}

try {
    // Remove image files from disk first (DB rows cascade-delete via FK)
    $imgStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $imgStmt->execute([$id]);
    foreach ($imgStmt->fetchAll(PDO::FETCH_COLUMN) as $path) {
        $file = '../uploads/products/' . $path;
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $del = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $del->execute([$id]);

    header('Location: manage_products.php?msg=' . urlencode('Product deleted.'));
} catch (PDOException $e) {
    header('Location: manage_products.php?err=' . urlencode('Could not delete product: ' . $e->getMessage()));
}
exit;