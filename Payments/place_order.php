<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $userStmt = $pdo->prepare("SELECT address FROM users WHERE user_id = ?");
    $userStmt->execute([$userId]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$userRow) {
        session_unset();
        session_destroy();
        header('Location: checkout.php');
        exit;
    }

    if (!$userRow['address']) {
        header('Location: checkout.php?err=' . urlencode('Please add your address before ordering.'));
        exit;
    }


    $cartStmt = $pdo->prepare("
        SELECT c.cart_id, c.product_id, c.quantity, p.price, p.stock, p.product_name
        FROM cart c
        JOIN products p ON p.product_id = c.product_id
        WHERE c.user_id = ?
    ");
    $cartStmt->execute([$userId]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        header('Location: ../Cart/cart.php?err=' . urlencode('Your cart is empty.'));
        exit;
    }

    
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            header('Location: ../Cart/cart.php?err=' . urlencode($item['product_name'] . ' only has ' . $item['stock'] . ' left in stock.'));
            exit;
        }
    }

    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status)
        VALUES (?, ?, 'Cash on Delivery', 'Pending', 'Pending')
    ");
    $orderStmt->execute([$userId, $total]);
    $orderId = $pdo->lastInsertId();

    $itemStmt  = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ? AND stock >= ?");
    $outStmt   = $pdo->prepare("UPDATE products SET status = 'Out of Stock' WHERE product_id = ? AND stock <= 0");

    foreach ($cartItems as $item) {
        $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);

        $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
        if ($stockStmt->rowCount() === 0) {
            // Someone else bought the last units between our check and now — abort everything
            $pdo->rollBack();
            header('Location: ../Cart/cart.php?err=' . urlencode($item['product_name'] . ' just went out of stock. Please update your cart.'));
            exit;
        }

        $outStmt->execute([$item['product_id']]);
    }

    $paymentStmt = $pdo->prepare("
        INSERT INTO payments (order_id, payment_method, payment_status, payment_date)
        VALUES (?, 'Cash on Delivery', 'Pending', NULL)
    ");
    $paymentStmt->execute([$orderId]);

    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

    $pdo->commit();

    header('Location: order_success.php?order_id=' . $orderId);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: checkout.php?err=' . urlencode('Something went wrong placing your order. Please try again.'));
    exit;
}