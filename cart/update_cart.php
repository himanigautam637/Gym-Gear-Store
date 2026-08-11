<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Gym-Gear-Store/Cart/cart.php');
    exit;
}

$key = $_POST['key'] ?? '';
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

if ($key === '') {
    header('Location: cart.php?err=' . urlencode('Invalid cart item.'));
    exit;
}

try {

    if (isset($_SESSION['user_id'])) {

        $stmt = $pdo->prepare("
            SELECT
                c.cart_id,
                p.stock,
                p.status
            FROM cart c
            INNER JOIN products p
                ON p.product_id = c.product_id
            WHERE c.cart_id = ?
            AND c.user_id = ?
        ");

        $stmt->execute([
            $key,
            $_SESSION['user_id']
        ]);

        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            header('Location: cart.php?err=' . urlencode('Cart item not found.'));
            exit;
        }

        $stock = (int)$item['stock'];

        if ($item['status'] === 'Out of Stock' || $stock <= 0) {

            $delete = $pdo->prepare("
                DELETE FROM cart
                WHERE cart_id = ?
                AND user_id = ?
            ");

            $delete->execute([
                $key,
                $_SESSION['user_id']
            ]);

            header('Location: cart.php?err=' . urlencode('Product is out of stock.'));
            exit;
        }

        $quantity = min($quantity, $stock);

        $update = $pdo->prepare("
            UPDATE cart
            SET quantity = ?
            WHERE cart_id = ?
            AND user_id = ?
        ");

        $update->execute([
            $quantity,
            $key,
            $_SESSION['user_id']
        ]);

    } else {

        $productId = (int)$key;

        $stmt = $pdo->prepare("
            SELECT stock, status
            FROM products
            WHERE product_id = ?
        ");

        $stmt->execute([$productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            header('Location: cart.php?err=' . urlencode('Product not found.'));
            exit;
        }

        $stock = (int)$product['stock'];

        if ($product['status'] === 'Out of Stock' || $stock <= 0) {

            unset($_SESSION['guest_cart'][$productId]);

            header('Location: cart.php?err=' . urlencode('Product is out of stock.'));
            exit;
        }

        $quantity = min($quantity, $stock);

        $_SESSION['guest_cart'][$productId] = $quantity;
    }

    header('Location: cart.php?msg=' . urlencode('Cart updated.'));
    exit;

} catch (PDOException $e) {

    header('Location: cart.php?err=' . urlencode('Could not update cart.'));
    exit;
}