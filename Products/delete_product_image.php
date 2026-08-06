<?php
require '../Admin/session_check.php';
require '../db_connect.php'; // defines $pdo (PDO)

$imageId = $_GET['id'] ?? '';

if ($imageId === '') {
    header('Location: manage_products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE image_id = ?");
    $stmt->execute([$imageId]);
    $path = $stmt->fetchColumn();

    $del = $pdo->prepare("DELETE FROM product_images WHERE image_id = ?");
    $del->execute([$imageId]);

    if ($path && file_exists('../uploads/products/' . $path)) {
        unlink('../uploads/products/' . $path);
    }

    header('Location: manage_products.php?msg=' . urlencode('Image removed.'));
} catch (PDOException $e) {
    header('Location: manage_products.php?err=' . urlencode('Could not remove image.'));
}
exit;