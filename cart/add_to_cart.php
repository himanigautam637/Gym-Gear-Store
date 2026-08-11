<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Gym-Gear-Store/shop.php');
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

if ($productId <= 0) {
    header('Location: /Gym-Gear-Store/shop.php?err=' . urlencode('Invalid product.'));
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT stock, status
        FROM products
        WHERE product_id = ?
    ");

    $stmt->execute([$productId]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: /Gym-Gear-Store/shop.php?err=' . urlencode('Product not found.'));
        exit;
    }

    $stock = (int)$product['stock'];

    if ($product['status'] === 'Out of Stock' || $stock <= 0) {
        header('Location: /Gym-Gear-Store/shop.php?err=' . urlencode('This product is currently out of stock.'));
        exit;
    }

    $quantity = min($quantity, $stock);

    if (isset($_SESSION['user_id'])) {

        $existing = $pdo->prepare("
            SELECT cart_id, quantity
            FROM cart
            WHERE user_id = ?
            AND product_id = ?
        ");

        $existing->execute([
            $_SESSION['user_id'],
            $productId
        ]);

        $row = $existing->fetch(PDO::FETCH_ASSOC);

        if ($row) {

            $newQuantity = min(
                (int)$row['quantity'] + $quantity,
                $stock
            );

            $update = $pdo->prepare("
                UPDATE cart
                SET quantity = ?
                WHERE cart_id = ?
            ");

            $update->execute([
                $newQuantity,
                $row['cart_id']
            ]);

        } else {

            $insert = $pdo->prepare("
                INSERT INTO cart
                (user_id, product_id, quantity)
                VALUES (?, ?, ?)
            ");

            $insert->execute([
                $_SESSION['user_id'],
                $productId,
                $quantity
            ]);
        }

    } else {

        if (!isset($_SESSION['guest_cart'])) {
            $_SESSION['guest_cart'] = [];
        }

        $currentQuantity =
            (int)($_SESSION['guest_cart'][$productId] ?? 0);

        $_SESSION['guest_cart'][$productId] = min(
            $currentQuantity + $quantity,
            $stock
        );
    }

    header('Location: /Gym-Gear-Store/Cart/cart.php?msg=' . urlencode('Product added to cart.'));
    exit;

} catch (PDOException $e) {

    header('Location: /Gym-Gear-Store/shop.php?err=' . urlencode('Could not add product to cart.'));
    exit;
}