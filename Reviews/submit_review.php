<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Gym-Gear-Store/shop.php');
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!isset($_SESSION['user_id'])) {
    header('Location: /Gym-Gear-Store/product-details.php?id=' . $productId . '&err=' . urlencode('Please log in to leave a review.'));
    exit;
}

if ($productId <= 0 || $rating < 1 || $rating > 5) {
    header('Location: /Gym-Gear-Store/product-details.php?id=' . $productId . '&err=' . urlencode('Please select a rating between 1 and 5.'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, user_id, rating, comment)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment]);

    header('Location: /Gym-Gear-Store/product-details.php?id=' . $productId . '&msg=' . urlencode('Your review has been saved.'));
    exit;
} catch (PDOException $e) {
    header('Location: /Gym-Gear-Store/product-details.php?id=' . $productId . '&err=' . urlencode('Could not save your review.'));
    exit;
}