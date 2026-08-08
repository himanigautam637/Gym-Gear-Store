<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$isLoggedIn = isset($_SESSION['user_id']);
$items = [];
$total = 0;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.stock, p.status,
               (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS thumbnail
        FROM cart c
        JOIN products p ON p.product_id = c.product_id
        WHERE c.user_id = ?
        ORDER BY c.cart_id DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $row['key']      = $row['cart_id'];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
} else {
    $guestCart = $_SESSION['guest_cart'] ?? [];
    if (!empty($guestCart)) {
        $placeholders = implode(',', array_fill(0, count($guestCart), '?'));
        $stmt = $pdo->prepare("
            SELECT p.product_id, p.product_name, p.price, p.stock, p.status,
                   (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS thumbnail
            FROM products p
            WHERE p.product_id IN ($placeholders)
        ");
        $stmt->execute(array_keys($guestCart));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $row['quantity'] = $guestCart[$row['product_id']];
            $row['key']      = $row['product_id'];
            $row['subtotal'] = $row['price'] * $row['quantity'];
            $total += $row['subtotal'];
            $items[] = $row;
        }
    }
}

$message = $_GET['msg'] ?? '';
$error   = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart | Gym Gear Store</title>
<style>
    :root {
        --navy:#0C2340; --navy-deep:#081729; --card-dark:#122a4a; --orange:#FF6B35;
        --border:rgba(255,255,255,0.08); --green:#4caf50; --red:#ef5350; --muted:#93a2ba; --text:#e8edf5;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background:var(--navy-deep); min-height:100vh; color:var(--text); }

    .site-header { background:var(--navy); color:#fff; padding:16px 40px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); }
    .site-header a.brand { color:#fff; text-decoration:none; font-weight:bold; letter-spacing:1px; }
    .site-header nav a { color:#dbe4f0; text-decoration:none; font-size:14px; margin-left:20px; }
    .site-header nav a:hover { color:var(--orange); }
    .site-header nav a.account-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.1); vertical-align:middle; }
    .site-header nav a.account-icon:hover { background:var(--orange); }
    .site-header nav a.account-icon svg { width:18px; height:18px; stroke:#fff; fill:none; stroke-width:2; }

    .wrap { max-width: 900px; margin: 40px auto; padding: 0 20px; }
    h1 { color: var(--text); font-size: 22px; margin-bottom: 20px; }

    .alert { padding: 12px 18px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background: rgba(76,175,80,0.15); color: var(--green); border: 1px solid rgba(76,175,80,0.3); }
    .alert-error { background: rgba(239,83,80,0.15); color: var(--red); border: 1px solid rgba(239,83,80,0.3); }

    .panel { background:var(--card-dark); border:1px solid var(--border); border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.25); padding:24px; margin-bottom:20px; }

    table { width:100%; border-collapse:collapse; font-size:13px; }
    thead th { text-align:left; color:var(--muted); text-transform:uppercase; font-size:11px; letter-spacing:0.4px; padding:10px 6px; border-bottom:2px solid var(--border); }
    tbody td { padding:14px 6px; border-bottom:1px solid var(--border); vertical-align:middle; color:var(--text); }
    tbody tr:last-child td { border-bottom:none; }

    .thumb { width:50px; height:50px; border-radius:8px; object-fit:cover; background:rgba(255,255,255,0.06); border:1px solid var(--border); }
    .prod-name { font-weight:bold; color:var(--text); }

    .qty-form { display:flex; align-items:center; gap:6px; }
    .qty-form input[type=number] { width:55px; padding:6px; border:1px solid var(--border); border-radius:6px; font-size:13px; background:rgba(255,255,255,0.06); color:var(--text); }
    .qty-form button { background:var(--orange); color:#fff; border:none; border-radius:6px; padding:6px 10px; font-size:12px; cursor:pointer; }
    .qty-form button:hover { background:#e85a29; }

    .remove-link { color:var(--red); font-size:12px; text-decoration:none; font-weight:bold; }
    .remove-link:hover { text-decoration:underline; }

    .empty-cart { text-align:center; padding:50px 20px; color:var(--muted); }
    .empty-cart .empty-icon {
        width:80px; height:80px; border-radius:50%; background:rgba(255,107,53,0.12);
        display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:34px;
    }
    .empty-cart a.btn-checkout { color: #fff; }

    .cart-summary { display:flex; justify-content:flex-end; align-items:center; gap:20px; margin-top:10px; }
    .cart-summary .total-label { font-size:14px; color:var(--muted); }
    .cart-summary .total-value { font-size:22px; font-weight:bold; color:var(--text); }

    .btn-checkout { background:var(--orange); color:#fff; border:none; padding:13px 26px; border-radius:6px; font-size:14px; font-weight:bold; cursor:pointer; text-decoration:none; display:inline-block; }
    .btn-checkout:hover { background:#e85a29; }

    .stock-warning { color:var(--red); font-size:11px; margin-top:4px; }
</style>
</head>
<body>

<div class="site-header">
    <a class="brand" href="../index.php">GYM GEAR STORE</a>
    <nav>
        <a href="../index.php">Home</a>
        <a href="cart.php" title="Cart">🛒</a>
        <a href="../my_account.php" class="account-icon" title="My Account">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </a>
    </nav>
</div>

<div class="wrap">
    <h1>My Cart 🛒</h1>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="panel">
        <?php if (empty($items)): ?>
            <div class="empty-cart">
                <div class="empty-icon">🛒</div>
                <p style="font-size:16px;font-weight:bold;color:var(--text);margin-bottom:6px;">Your cart is empty</p>
                <p style="margin-bottom:20px;">Time to gear up. Explore our best-selling equipment.</p>
                <a class="btn-checkout" href="../index.php">Start Shopping</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img class="thumb"
                                     src="<?= $item['thumbnail'] ? '../uploads/products/' . htmlspecialchars($item['thumbnail']) : '' ?>"
                                     onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect width=%2250%22 height=%2250%22 fill=%22%23eef1f5%22/></svg>'">
                                <span class="prod-name"><?= htmlspecialchars($item['product_name']) ?></span>
                            </div>
                        </td>
                        <td>Rs. <?= number_format((float)$item['price'], 2) ?></td>
                        <td>
                            <form class="qty-form" action="update_cart.php" method="POST">
                                <input type="hidden" name="key" value="<?= $item['key'] ?>">
                                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" max="<?= (int)$item['stock'] ?>">
                                <button type="submit">Update</button>
                            </form>
                            <?php if ($item['quantity'] >= $item['stock']): ?>
                                <div class="stock-warning">Max stock reached</div>
                            <?php endif; ?>
                        </td>
                        <td>Rs. <?= number_format((float)$item['subtotal'], 2) ?></td>
                        <td>
                            <a class="remove-link" href="remove_from_cart.php?key=<?= $item['key'] ?>"
                               onclick="return confirm('Remove this item from your cart?')">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <span class="total-label">Total:</span>
                <span class="total-value">Rs. <?= number_format($total, 2) ?></span>
                <a class="btn-checkout" href="../Payments/checkout.php">Proceed to Order</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>