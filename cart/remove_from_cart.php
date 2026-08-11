<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$key = $_GET['key'] ?? '';

if ($key === '') {
    header('Location: cart.php');
    exit;
}

try {

    if (isset($_SESSION['user_id'])) {

        $stmt = $pdo->prepare("
            DELETE FROM cart
            WHERE cart_id = ?
            AND user_id = ?
        ");

        $stmt->execute([
            $key,
            $_SESSION['user_id']
        ]);

    } else {

        $productId = (int)$key;

        if (isset($_SESSION['guest_cart'][$productId])) {
            unset($_SESSION['guest_cart'][$productId]);
        }
    }

    header('Location: cart.php?msg=' . urlencode('Item removed from cart.'));
    exit;

} catch (PDOException $e) {

    header('Location: cart.php?err=' . urlencode('Could not remove item.'));
    exit;
}