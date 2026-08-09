<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$productId = trim($_POST['product_id'] ?? '');
$quantity  = max(1, (int)($_POST['quantity'] ?? 1));

if ($productId === '') {
    header('Location: ../index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT stock, status FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: ../index.php?err=' . urlencode('Product not found.'));
        exit;
    }

    if ($product['status'] === 'Out of Stock' || $product['stock'] < 1) {
        header('Location: ../index.php?err=' . urlencode('This product is currently out of stock.'));
        exit;
    }

    if ($quantity > $product['stock']) {
        $quantity = $product['stock'];
    }

    if (isset($_SESSION['user_id'])) {
        /* -------- Logged-in user: store in the cart table -------- */
        $existing = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $existing->execute([$_SESSION['user_id'], $productId]);
        $row = $existing->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $newQty = min($row['quantity'] + $quantity, $product['stock']);
            $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?")->execute([$newQty, $row['cart_id']]);
        } else {
            $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")
                ->execute([$_SESSION['user_id'], $productId, $quantity]);
        }
    } else {
       
        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }
        $current = $_SESSION['guest_cart'][$productId] ?? 0;
        $_SESSION['guest_cart'][$productId] = min($current + $quantity, $product['stock']);
    }

    header('Location: cart.php?msg=' . urlencode('Added to cart.'));
    exit;
} catch (PDOException $e) {
    header('Location: ../index.php?err=' . urlencode('Could not add to cart.'));
    exit;
}