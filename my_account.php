<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'my_account.php';

$isLoggedIn = isset($_SESSION['user_id']);
$loginError = '';

if (!$isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {

        $loginError = 'Please enter both fields.';

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ?
               OR full_name = ?
        ");

        $stmt->execute([
            $identifier,
            $identifier
        ]);

        $loginUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $loginUser &&
            password_verify(
                $password,
                $loginUser['password']
            )
        ) {

            $_SESSION['user_id']   = $loginUser['user_id'];
            $_SESSION['full_name'] = $loginUser['full_name'];
            $_SESSION['username']  = $loginUser['username'];

            header('Location: my_account.php');
            exit;

        } else {

            $loginError = 'Invalid username/full name or password.';
        }
    }
}

$user = null;
$orders = [];

if ($isLoggedIn) {

    $stmt = $pdo->prepare("
        SELECT
            full_name,
            email,
            phone,
            address,
            created_at
        FROM users
        WHERE user_id = ?
    ");

    $stmt->execute([
        $_SESSION['user_id']
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        session_unset();
        session_destroy();

        $isLoggedIn = false;

    } else {

        $ordersStmt = $pdo->prepare("
            SELECT
                o.order_id,
                o.order_date,
                o.total_amount,
                o.order_status,
                o.payment_status,
                (
                    SELECT COUNT(*)
                    FROM order_items oi
                    WHERE oi.order_id = o.order_id
                ) AS item_count
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC
        ");

        $ordersStmt->execute([
            $_SESSION['user_id']
        ]);

        $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$cartCount = 0;

if (isset($_SESSION['guest_cart'])) {

    foreach ($_SESSION['guest_cart'] as $qty) {
        $cartCount += (int)$qty;
    }
}

if ($isLoggedIn) {

    try {

        $cartStmt = $pdo->prepare("
            SELECT COALESCE(SUM(quantity), 0)
            FROM cart
            WHERE user_id = ?
        ");

        $cartStmt->execute([
            $_SESSION['user_id']
        ]);

        $cartCount = (int)$cartStmt->fetchColumn();

    } catch (PDOException $e) {

        $cartCount = 0;
    }
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
        My Account | Gym Gear Store
    </title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

    <style>

        .account-page {
            width: min(1100px, 100%);
            margin: auto;
            padding: 55px 25px 80px;
        }

        .guest-card {
            max-width: 440px;
            margin: 40px auto;
            background: #122a4a;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 15px;
            overflow: hidden;
        }

        .guest-head {
            padding: 30px;
            text-align: center;
            background: rgba(0,0,0,0.12);
            border-bottom: 1px solid rgba(255,255,255,0.10);
        }

        .guest-head h1 {
            color: #fff;
            font-family: "Bricolage Grotesque", sans-serif;
            font-size: 24px;
            margin-bottom: 7px;
        }

        .guest-head p {
            color: #91a4bd;
            font-size: 12px;
        }

        .badge-bar {
            width: 45px;
            height: 4px;
            margin: 12px auto 0;
            border-radius: 4px;
            background: #3f73e8;
        }

        .guest-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #d2dceb;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .form-group input {
            width: 100%;
            height: 44px;
            padding: 0 13px;
            background: #081729;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 8px;
            color: #fff;
            outline: none;
        }

        .form-group input:focus {
            border-color: #3f73e8;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            cursor: pointer;
            color: #91a4bd;
        }

        .toggle-eye svg {
            width: 19px;
            height: 19px;
            stroke: currentColor;
            fill: none;
        }

        .error-message {
            margin-bottom: 18px;
            padding: 11px 13px;
            border-radius: 8px;
            color: #ff7774;
            background: rgba(239,83,80,0.12);
            border: 1px solid rgba(239,83,80,0.25);
            font-size: 12px;
        }

        .btn-login {
            width: 100%;
            height: 45px;
            border: none;
            border-radius: 8px;
            background: #3f73e8;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #5685ed;
        }

        .register-link {
            text-align: center;
            color: #91a4bd;
            font-size: 12px;
            margin-top: 17px;
        }

        .register-link a {
            color: #3f73e8;
            font-weight: 700;
            text-decoration: none;
        }

        .account-panel {
            background: #122a4a;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 22px;
        }

        .account-panel h2 {
            color: #fff;
            font-family: "Bricolage Grotesque", sans-serif;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .profile-field .label {
            display: block;
            color: #91a4bd;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 6px;
        }

        .profile-field .val {
            color: #f5f8fc;
            font-size: 14px;
            font-weight: 600;
            word-break: break-word;
        }

        .logout-btn {
            display: inline-block;
            padding: 10px 18px;
            background: #ef5350;
            color: #fff;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }

        .order-card {
            padding: 20px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.025);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 11px;
        }

        .order-card:last-child {
            margin-bottom: 0;
        }

        .order-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .oid {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }

        .meta {
            color: #91a4bd;
            font-size: 11px;
            margin-top: 5px;
        }

        .amount {
            color: #fff;
            font-size: 15px;
            font-weight: 800;
        }

        .tracker {
            display: flex;
            align-items: flex-start;
            padding-top: 5px;
        }

        .tracker-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .tracker-step .circle {
            width: 28px;
            height: 28px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            color: #91a4bd;
            font-size: 11px;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }

        .tracker-step.done .circle {
            background: #3f73e8;
            color: #fff;
        }

        .tracker-step.current .circle {
            background: #fff;
            color: #3f73e8;
            box-shadow: 0 0 0 4px rgba(63,115,232,0.25);
        }

        .tracker-step .line {
            position: absolute;
            top: 13px;
            left: -50%;
            width: 100%;
            height: 3px;
            background: rgba(255,255,255,0.08);
            z-index: 1;
        }

        .tracker-step:first-child .line {
            display: none;
        }

        .tracker-step.done .line,
        .tracker-step.current .line {
            background: #3f73e8;
        }

        .step-label {
            display: block;
            margin-top: 8px;
            color: #91a4bd;
            font-size: 10px;
        }

        .tracker-step.done .step-label,
        .tracker-step.current .step-label {
            color: #fff;
            font-weight: 700;
        }

        .cancelled-banner {
            padding: 11px;
            border-radius: 8px;
            color: #ff7774;
            background: rgba(239,83,80,0.12);
            border: 1px solid rgba(239,83,80,0.25);
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }

        .empty-row {
            padding: 35px;
            text-align: center;
            color: #91a4bd;
            font-size: 13px;
        }

        @media(max-width: 600px) {

            .account-page {
                padding: 35px 15px 60px;
            }

            .guest-body {
                padding: 20px;
            }

            .account-panel {
                padding: 18px;
            }

            .tracker {
                min-width: 600px;
            }

            .order-card {
                overflow-x: auto;
            }
        }

    </style>

</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main class="account-page">

<?php if (!$isLoggedIn): ?>

    <div class="guest-card">

        <div class="guest-head">

            <h1>
                WELCOME BACK
            </h1>

            <p>
                Log in to view your account
            </p>

            <div class="badge-bar"></div>

        </div>

        <div class="guest-body">

            <?php if ($loginError): ?>

                <div class="error-message">
                    <?= htmlspecialchars($loginError) ?>
                </div>

            <?php endif; ?>

            <form
                method="POST"
                action="my_account.php"
                autocomplete="off"
            >

                <div class="form-group">

                    <label for="identifier">
                        Username or Full Name
                    </label>

                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        required
                        autofocus
                        autocomplete="off"
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="toggle-eye"
                            onclick="togglePassword('password')"
                        >

                            <svg
                                id="eyeIcon-password"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"
                                />

                                <circle
                                    cx="12"
                                    cy="12"
                                    r="3"
                                />

                            </svg>

                        </button>

                    </div>

                </div>

                <button
                    type="submit"
                    class="btn-login"
                >
                    Log In
                </button>

            </form>

            <div class="register-link">

                Don't have an account?

                <a href="Authentication/client_register.php">
                    Register here
                </a>

            </div>

        </div>

    </div>

<?php else: ?>

    <section class="page-header">

        <div class="eyebrow">
            Account
        </div>

        <h1>
            My Account
        </h1>

        <p>
            Manage your profile and keep track of your orders.
        </p>

    </section>

    <div class="account-panel">

        <h2>
            My Details
        </h2>

        <div class="profile-grid">

            <div class="profile-field">

                <span class="label">
                    Full Name
                </span>

                <span class="val">
                    <?= htmlspecialchars($user['full_name']) ?>
                </span>

            </div>

            <div class="profile-field">

                <span class="label">
                    Email
                </span>

                <span class="val">
                    <?= htmlspecialchars($user['email']) ?>
                </span>

            </div>

            <div class="profile-field">

                <span class="label">
                    Phone
                </span>

                <span class="val">
                    <?= htmlspecialchars($user['phone'] ?: '-') ?>
                </span>

            </div>

            <div class="profile-field">

                <span class="label">
                    Address
                </span>

                <span class="val">
                    <?= htmlspecialchars($user['address'] ?: '-') ?>
                </span>

            </div>

            <div class="profile-field">

                <span class="label">
                    Member Since
                </span>

                <span class="val">
                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                </span>

            </div>

        </div>

        <div style="margin-top:22px;">

            <a
                href="Authentication/client_logout.php"
                class="logout-btn"
            >
                Log Out
            </a>

        </div>

    </div>

    <div class="account-panel">

        <h2>
            My Orders
        </h2>

        <?php if (empty($orders)): ?>

            <div class="empty-row">
                You haven't placed any orders yet.
            </div>

        <?php else: ?>

            <?php

            $trackSteps = [
                'Pending'   => 'Order Received',
                'Confirmed' => 'Confirmed',
                'Packed'    => 'Packed',
                'Shipped'   => 'Shipped',
                'Delivered' => 'Delivered'
            ];

            $stepKeys = array_keys($trackSteps);

            ?>

            <?php foreach ($orders as $o): ?>

                <div class="order-card">

                    <div class="order-card-head">

                        <div>

                            <div class="oid">

                                Order #
                                <?= htmlspecialchars($o['order_id']) ?>

                            </div>

                            <div class="meta">

                                <?= date(
                                    'M j, Y',
                                    strtotime($o['order_date'])
                                ) ?>

                                &middot;

                                <?= (int)$o['item_count'] ?>

                                item<?= $o['item_count'] == 1 ? '' : 's' ?>

                                &middot;

                                Payment:

                                <?= htmlspecialchars($o['payment_status']) ?>

                            </div>

                        </div>

                        <div class="amount">

                            Rs.
                            <?= number_format(
                                (float)$o['total_amount'],
                                2
                            ) ?>

                        </div>

                    </div>

                    <?php if ($o['order_status'] === 'Cancelled'): ?>

                        <div class="cancelled-banner">
                            This order was cancelled.
                        </div>

                    <?php else: ?>

                        <?php

                        $currentIndex = array_search(
                            $o['order_status'],
                            $stepKeys
                        );

                        if ($currentIndex === false) {
                            $currentIndex = 0;
                        }

                        ?>

                        <div class="tracker">

                            <?php foreach ($stepKeys as $i => $key): ?>

                                <div
                                    class="
                                        tracker-step
                                        <?= $i < $currentIndex
                                            ? 'done'
                                            : (
                                                $i === $currentIndex
                                                ? 'current'
                                                : ''
                                            )
                                        ?>
                                    "
                                >

                                    <span class="line"></span>

                                    <span class="circle">

                                        <?= $i < $currentIndex
                                            ? '&#10003;'
                                            : $i + 1
                                        ?>

                                    </span>

                                    <span class="step-label">
                                        <?= $trackSteps[$key] ?>
                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

<?php endif; ?>

</main>

<footer>

    <div class="footer-top">

        <div>

            <div class="brand">

                <div class="brand-icon">
                    +
                </div>

                <div>

                    <div class="brand-name">
                        ONLINE GYM GEAR
                    </div>

                    <div class="brand-tag">
                        STORE
                    </div>

                </div>

            </div>

            <p class="footer-desc">
                Premium strength, cardio, accessories and supplements for your training journey.
            </p>

        </div>

        <div>

            <div class="footer-title">
                Shop
            </div>

            <div class="footer-links">

                <a href="/Gym-Gear-Store/shop.php">
                    Shop
                </a>

                <a href="/Gym-Gear-Store/categories.php">
                    Categories
                </a>

                <a href="/Gym-Gear-Store/Cart/cart.php">
                    My Cart
                </a>

            </div>

        </div>

        <div>

            <div class="footer-title">
                Account
            </div>

            <div class="footer-links">

                <a href="/Gym-Gear-Store/my_account.php">
                    My Account
                </a>

                <a href="/Gym-Gear-Store/Authentication/client_login.php">
                    Login
                </a>

                <a href="/Gym-Gear-Store/Authentication/client_register.php">
                    Register
                </a>

            </div>

        </div>

        <div>

            <div class="footer-title">
                Support
            </div>

            <div class="footer-links">

                <a href="/Gym-Gear-Store/about.php">
                    About Us
                </a>

                <a href="/Gym-Gear-Store/contact.php">
                    Contact Us
                </a>

            </div>

        </div>

    </div>

    <div class="footer-bottom">

        <span>

            &copy; <?= date('Y') ?>

            Online Gym Gear Store.

            All rights reserved.

        </span>

        <span>
            Cash on Delivery
        </span>

    </div>

</footer>

<script>

function togglePassword(fieldId) {

    const input = document.getElementById(fieldId);

    const icon = document.getElementById(
        'eyeIcon-' + fieldId
    );

    if (input.type === 'password') {

        input.type = 'text';

        icon.innerHTML = `
            <path
                d="M1 12s4-7 11-7
                   11 7 11 7-4 7-11 7
                   -11-7-11-7z"
            />

            <circle
                cx="12"
                cy="12"
                r="3"
            />
        `;

    } else {

        input.type = 'password';

        icon.innerHTML = `
            <path
                d="M1 12s4-7 11-7
                   11 7 11 7-4 7-11 7
                   -11-7-11-7z"
            />

            <circle
                cx="12"
                cy="12"
                r="3"
            />
        `;
    }
}

</script>

</body>

</html>