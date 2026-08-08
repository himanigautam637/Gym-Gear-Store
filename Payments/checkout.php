<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$isLoggedIn = isset($_SESSION['user_id']);
$loginError = '';
$user = null;

/* If a session claims to be logged in, verify the user still actually exists
   (covers the case where the account was deleted or the session is stale) */
if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, phone, address FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_unset();
        session_destroy();
        session_start();
        $isLoggedIn = false;
    }
}

/* Handle the inline login form on this page */
if (!$isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier'])) {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $loginError = 'Please enter both fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR full_name = ?");
        $stmt->execute([$identifier, $identifier]);
        $loginUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($loginUser && password_verify($password, $loginUser['password'])) {
            $_SESSION['user_id']   = $loginUser['user_id'];
            $_SESSION['full_name'] = $loginUser['full_name'];
            $_SESSION['username']  = $loginUser['username'];

            /* Merge the guest session cart into their real cart in the DB */
            if (!empty($_SESSION['guest_cart'])) {
                foreach ($_SESSION['guest_cart'] as $productId => $qty) {
                    $stockStmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
                    $stockStmt->execute([$productId]);
                    $stock = (int) $stockStmt->fetchColumn();
                    if ($stock <= 0) continue;
                    $qty = min($qty, $stock);

                    $existing = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $existing->execute([$loginUser['user_id'], $productId]);
                    $row = $existing->fetch(PDO::FETCH_ASSOC);

                    if ($row) {
                        $newQty = min($row['quantity'] + $qty, $stock);
                        $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?")->execute([$newQty, $row['cart_id']]);
                    } else {
                        $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")
                            ->execute([$loginUser['user_id'], $productId, $qty]);
                    }
                }
                unset($_SESSION['guest_cart']);
            }

            header('Location: checkout.php');
            exit;
        } else {
            $loginError = 'Invalid username/full name or password.';
        }
    }
}

/* -------- Load cart items for the order summary (only reached once logged in) -------- */
$items = [];
$total = 0;

if ($isLoggedIn) {
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.stock
        FROM cart c
        JOIN products p ON p.product_id = c.product_id
        WHERE c.user_id = ?
        ORDER BY c.cart_id DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Gym Gear Store</title>
<style>
    :root {
        --navy:#0C2340; --navy-deep:#081729; --card-dark:#122a4a; --orange:#FF6B35;
        --border:rgba(255,255,255,0.08); --green:#4caf50; --red:#ef5350; --muted:#93a2ba; --text:#e8edf5;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background:var(--navy-deep); min-height:100vh; color:var(--text); }

    .site-header { background:var(--navy); color:#fff; padding:16px 40px; border-bottom:1px solid var(--border); }
    .site-header a.brand { color:#fff; text-decoration:none; font-weight:bold; letter-spacing:1px; }

    .wrap { max-width: 800px; margin: 40px auto; padding: 0 20px; }
    h1 { color: var(--text); font-size: 22px; margin-bottom: 20px; }

    /* ---------- Login-gate card (kept bright for form readability) ---------- */
    .guest-card { background:#fff; border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.35); max-width:400px; margin:30px auto; overflow:hidden; }
    .guest-card .head { background:var(--navy); color:#fff; padding:28px 20px; text-align:center; }
    .guest-card .head h1 { font-size:19px; letter-spacing:1px; margin:0; }
    .guest-card .head p { font-size:13px; color:#c9d4e0; margin-top:6px; }
    .guest-card .badge-bar { width:48px; height:4px; background:var(--orange); margin:10px auto 0; border-radius:2px; }
    .guest-card .body { padding:28px 30px; }

    .form-group { margin-bottom:18px; }
    .form-group label { display:block; font-weight:bold; color:var(--navy); font-size:14px; margin-bottom:6px; }
    .form-group input[type=text], .form-group input[type=password] { width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; outline:none; }
    .form-group input:focus { border-color: var(--orange); }

    .error-message { background:#fdecea; color:#b3261e; border:1px solid #f5c6c2; padding:10px 12px; border-radius:6px; font-size:13px; margin-bottom:16px; }
    .info-message { background:#e3f2fd; color:#1565c0; border:1px solid #bbdefb; padding:10px 12px; border-radius:6px; font-size:13px; margin-bottom:16px; }

    .btn-login { width:100%; padding:12px; background:var(--orange); color:#fff; border:none; border-radius:6px; font-size:15px; font-weight:bold; cursor:pointer; }
    .btn-login:hover { background:#e85a29; }

    .register-link { text-align:center; font-size:13px; margin-top:16px; }
    .register-link a { color:var(--orange); font-weight:bold; text-decoration:none; }

    /* ---------- Order summary ---------- */
    .panel { background:var(--card-dark); border:1px solid var(--border); border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.25); padding:24px; margin-bottom:20px; }
    .panel h2 { font-size:16px; color:var(--text); margin-bottom:16px; }

    table { width:100%; border-collapse:collapse; font-size:13px; }
    thead th { text-align:left; color:var(--muted); text-transform:uppercase; font-size:11px; padding:8px 6px; border-bottom:2px solid var(--border); }
    tbody td { padding:10px 6px; border-bottom:1px solid var(--border); color:var(--text); }

    .ship-grid { display:grid; grid-template-columns: 1fr 1fr; gap:14px; font-size:13px; }
    .ship-grid .label { color:var(--muted); font-size:11px; text-transform:uppercase; margin-bottom:3px; }
    .ship-grid .val { font-weight:bold; color:var(--text); }

    .total-row { display:flex; justify-content:flex-end; gap:14px; margin-top:14px; font-size:16px; }
    .total-row .amount { font-weight:bold; color:var(--text); font-size:20px; }

    .btn-confirm { width:100%; padding:14px; background:var(--orange); color:#fff; border:none; border-radius:6px; font-size:15px; font-weight:bold; cursor:pointer; margin-top:10px; }
    .btn-confirm:hover { background:#e85a29; }

    .pay-note { font-size:12px; color:var(--muted); text-align:center; margin-top:10px; }
</style>
</head>
<body>

<div class="site-header"><a class="brand" href="../index.php">GYM GEAR STORE</a></div>

<div class="wrap">

<?php if (!$isLoggedIn): ?>

    <div class="guest-card">
        <div class="head">
            <h1>LOG IN TO CONTINUE</h1>
            <p>Please log in or create an account to place your order</p>
            <div class="badge-bar"></div>
        </div>
        <div class="body">
            <div class="info-message">Your cart items are saved — log in and you'll come right back here to confirm your order.</div>
            <?php if ($loginError): ?>
                <div class="error-message"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>

            <form method="POST" action="checkout.php" autocomplete="off">
                <div class="form-group">
                    <label for="identifier">Username or Full Name</label>
                    <input type="text" id="identifier" name="identifier" required autofocus autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-login">Log In &amp; Continue</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="../Authentication/client_register.php">Register here</a>
            </div>
        </div>
    </div>

<?php elseif (empty($items)): ?>

    <div class="panel">
        <h2>Your cart is empty</h2>
        <p style="color:var(--muted);font-size:13px;">Add some products before checking out.</p>
        <a href="../index.php" style="color:var(--orange);font-weight:bold;font-size:13px;">Browse products</a>
    </div>

<?php else: ?>

    <h1>Checkout</h1>

    <div class="panel">
        <h2>Shipping Details</h2>
        <div class="ship-grid">
            <div><div class="label">Name</div><div class="val"><?= htmlspecialchars($user['full_name']) ?></div></div>
            <div><div class="label">Phone</div><div class="val"><?= htmlspecialchars($user['phone'] ?: '-') ?></div></div>
            <div style="grid-column:1/-1;"><div class="label">Delivery Address</div><div class="val"><?= htmlspecialchars($user['address'] ?: '-') ?></div></div>
        </div>
        <?php if (!$user['address']): ?>
            <div class="error-message" style="margin-top:14px;">Please add your address in <a href="../my_account.php">My Account</a> before placing an order.</div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2>Order Summary</h2>
        <table>
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td>Rs. <?= number_format((float)$item['price'], 2) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td>Rs. <?= number_format((float)$item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-row">
            <span>Total:</span>
            <span class="amount">Rs. <?= number_format($total, 2) ?></span>
        </div>

        <form action="place_order.php" method="POST">
            <button type="submit" class="btn-confirm" <?= !$user['address'] ? 'disabled' : '' ?>>Confirm Order (Cash on Delivery)</button>
        </form>
        <div class="pay-note">Payment method: Cash on Delivery only</div>
    </div>

<?php endif; ?>

</div>

</body>
</html>