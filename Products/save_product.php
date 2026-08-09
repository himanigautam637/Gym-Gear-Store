<?php
require '../Admin/session_check.php';
require '../db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_products.php');
    exit;
}

$productId   = trim($_POST['product_id'] ?? '');
$name        = trim($_POST['product_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$categoryId  = trim($_POST['category_id'] ?? '');
$price       = trim($_POST['price'] ?? '');
$stock       = trim($_POST['stock'] ?? '');
$status      = trim($_POST['status'] ?? 'Available');

if ($name === '' || $categoryId === '' || $price === '' || $stock === '') {
    header('Location: manage_products.php?err=' . urlencode('Please fill in all required fields.'));
    exit;
}

if (!in_array($status, ['Available', 'Out of Stock'])) {
    $status = 'Available';
}

$uploadDir = '../uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    if ($productId !== '') {
        /* -------- UPDATE -------- */
        $stmt = $pdo->prepare("UPDATE products SET product_name = ?, description = ?, category_id = ?, price = ?, stock = ?, status = ? WHERE product_id = ?");
        $stmt->execute([$name, $description, $categoryId, $price, $stock, $status, $productId]);
        $msg = 'Product updated successfully.';
    } else {
        /* -------- INSERT -------- */
        $stmt = $pdo->prepare("INSERT INTO products (product_name, description, category_id, price, stock, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $categoryId, $price, $stock, $status]);
        $productId = $pdo->lastInsertId();
        $msg = 'Product added successfully.';
    }

    
    if (!empty($_FILES['images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");

        foreach ($_FILES['images']['name'] as $i => $fileName) {
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                continue;
            }
            if ($_FILES['images']['size'][$i] > 2 * 1024 * 1024) {
                continue;
            }

            $newName = 'prod_' . uniqid() . '_' . $i . '.' . $ext;
            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $newName)) {
                $imgStmt->execute([$productId, $newName]);
            }
        }
    }

    header('Location: manage_products.php?msg=' . urlencode($msg));
    exit;
} catch (PDOException $e) {
    header('Location: manage_products.php?err=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}