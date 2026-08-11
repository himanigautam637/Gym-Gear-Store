<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'cart.php';

$isLoggedIn = isset($_SESSION['user_id']);

$items = [];
$total = 0;
$cartCount = 0;

$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';

try {

    if ($isLoggedIn) {

        $stmt = $pdo->prepare("
            SELECT
                c.cart_id,
                c.product_id,
                c.quantity,
                p.product_name,
                p.price,
                p.stock,
                p.status,
                c2.category_name,

                (
                    SELECT pi.image_path
                    FROM product_images pi
                    WHERE pi.product_id = p.product_id
                    ORDER BY pi.image_id ASC
                    LIMIT 1
                ) AS thumbnail

            FROM cart c

            INNER JOIN products p
                ON p.product_id = c.product_id

            LEFT JOIN categories c2
                ON c2.category_id = p.category_id

            WHERE c.user_id = ?

            ORDER BY c.cart_id DESC
        ");

        $stmt->execute([
            $_SESSION['user_id']
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {

            $row['key'] = $row['cart_id'];

            if (
                (int)$row['stock'] <= 0 ||
                $row['status'] === 'Out of Stock'
            ) {

                $row['quantity'] = 0;
                $row['subtotal'] = 0;

            } else {

                if ((int)$row['quantity'] > (int)$row['stock']) {
                    $row['quantity'] = (int)$row['stock'];
                }

                $row['subtotal'] =
                    (float)$row['price'] *
                    (int)$row['quantity'];

                $total += $row['subtotal'];

                $cartCount +=
                    (int)$row['quantity'];
            }

            $items[] = $row;
        }

    } else {

        $guestCart =
            $_SESSION['guest_cart'] ?? [];

        if (!empty($guestCart)) {

            $productIds =
                array_keys($guestCart);

            $placeholders = implode(
                ',',
                array_fill(
                    0,
                    count($productIds),
                    '?'
                )
            );

            $stmt = $pdo->prepare("
                SELECT
                    p.product_id,
                    p.product_name,
                    p.price,
                    p.stock,
                    p.status,
                    c.category_name,

                    (
                        SELECT pi.image_path
                        FROM product_images pi
                        WHERE pi.product_id = p.product_id
                        ORDER BY pi.image_id ASC
                        LIMIT 1
                    ) AS thumbnail

                FROM products p

                LEFT JOIN categories c
                    ON c.category_id = p.category_id

                WHERE p.product_id IN ($placeholders)
            ");

            $stmt->execute($productIds);

            $rows =
                $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {

                $productId =
                    (int)$row['product_id'];

                $quantity =
                    (int)(
                        $guestCart[$productId]
                        ?? 1
                    );

                if (
                    (int)$row['stock'] <= 0 ||
                    $row['status'] === 'Out of Stock'
                ) {

                    unset(
                        $_SESSION['guest_cart'][$productId]
                    );

                    continue;
                }

                $quantity = min(
                    $quantity,
                    (int)$row['stock']
                );

                $_SESSION['guest_cart'][$productId] =
                    $quantity;

                $row['quantity'] =
                    $quantity;

                $row['key'] =
                    $productId;

                $row['subtotal'] =
                    (float)$row['price'] *
                    $quantity;

                $total +=
                    $row['subtotal'];

                $cartCount +=
                    $quantity;

                $items[] =
                    $row;
            }
        }
    }

} catch (PDOException $e) {

    $error =
        'Unable to load your cart.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Shopping Cart | Gym Gear Store
    </title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

    <style>


    </style>

</head>

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/partials/navbar.php'; ?>

<main class="cart-page">

    <section class="page-header">

        <div class="eyebrow">
            Your Gear
        </div>

        <h1>
            Shopping Cart
        </h1>

        <p>
            Review your selected gym equipment before placing your order.
        </p>

    </section>

    <?php if ($message !== ''): ?>

        <div class="cart-message cart-success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="cart-message cart-error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <section class="cart-container">

        <?php if (empty($items)): ?>

            <div class="empty-cart">

                <h2>
                    Your cart is empty
                </h2>

                <p>
                    Add some equipment and start building your perfect gym setup.
                </p>

                <a
                    href="/Gym-Gear-Store/shop.php"
                    class="checkout-btn"
                >
                    Start Shopping
                </a>

            </div>

        <?php else: ?>

            <table class="cart-table">

                <thead>

                    <tr>

                        <th>Product</th>

                        <th>Price</th>

                        <th>Quantity</th>

                        <th>Subtotal</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($items as $item): ?>

                        <tr>

                            <td>

                                <div class="product-info">

                                    <?php if (!empty($item['thumbnail'])): ?>

                                        <img
                                            class="product-image"
                                            src="/Gym-Gear-Store/uploads/products/<?= htmlspecialchars($item['thumbnail']) ?>"
                                            alt="<?= htmlspecialchars($item['product_name']) ?>"
                                        >

                                    <?php else: ?>

                                        <div class="product-placeholder">
                                            No Image
                                        </div>

                                    <?php endif; ?>

                                    <div>

                                        <div class="product-name">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </div>

                                        <div class="product-status">
                                            <?= (int)$item['stock'] ?>
                                            available
                                        </div>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <span class="price">
                                    Rs.
                                    <?= number_format(
                                        (float)$item['price'],
                                        2
                                    ) ?>
                                </span>

                            </td>

                            <td>

                                <form
                                    class="quantity-form"
                                    action="/Gym-Gear-Store/Cart/update_cart.php"
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="key"
                                        value="<?= htmlspecialchars($item['key']) ?>"
                                    >

                                    <input
                                        class="quantity-input"
                                        type="number"
                                        name="quantity"
                                        value="<?= (int)$item['quantity'] ?>"
                                        min="1"
                                        max="<?= max(
                                            1,
                                            (int)$item['stock']
                                        ) ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="update-btn"
                                    >
                                        Update
                                    </button>

                                </form>

                                <?php if (
                                    (int)$item['quantity']
                                    >=
                                    (int)$item['stock']
                                ): ?>

                                    <div class="stock-warning">
                                        Maximum stock reached
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

                                <span class="subtotal">
                                    Rs.
                                    <?= number_format(
                                        (float)$item['subtotal'],
                                        2
                                    ) ?>
                                </span>

                            </td>

                            <td>

                                <a
                                    href="/Gym-Gear-Store/Cart/remove_from_cart.php?key=<?= urlencode($item['key']) ?>"
                                    class="remove-btn"
                                    onclick="return confirm('Remove this item from your cart?');"
                                >
                                    Remove
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

            <div class="cart-footer">

                <a
                    href="/Gym-Gear-Store/shop.php"
                    class="continue-shopping"
                >
                    ← Continue Shopping
                </a>

                <div class="cart-summary">

                    <div>

                        <div class="total-label">
                            Cart Total
                        </div>

                        <div class="total-price">
                            Rs.
                            <?= number_format($total, 2) ?>
                        </div>

                    </div>

                    <a
                        href="/Gym-Gear-Store/Payments/checkout.php"
                        class="checkout-btn"
                    >
                        Proceed to Order
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/partials/footer.php'; ?>

</body>
</html>