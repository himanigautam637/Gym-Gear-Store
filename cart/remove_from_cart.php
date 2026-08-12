<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$key = $_GET['key'] ?? '';

if ($key === '') {
    header('Location: cart.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?")->execute([$key, $_SESSION['user_id']]);
} else {
    unset($_SESSION['guest_cart'][$key]);
}

header('Location: cart.php');
exit;