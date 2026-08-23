<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$isLoggedIn = isset($_SESSION['user_id']);
$loginError = '';

if (!$isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $stmt = $pdo->prepare("SELECT full_name, email, phone, address, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_unset();
        session_destroy();
        $isLoggedIn = false;
    } else {
        $ordersStmt = $pdo->prepare("
            SELECT o.order_id, o.order_date, o.total_amount, o.order_status, o.payment_status,
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS item_count
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC
        ");
        $ordersStmt->execute([$_SESSION['user_id']]);
        $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Account | Online Gym Gear Store</title>
<link rel="stylesheet" href="/Gym-Gear-Store/partials/site.css">
<style>
    .account-wrap { max-width: 900px; margin: 40px auto; padding: 0 20px; }

    .guest-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        max-width: 400px;
        margin: 50px auto;
        overflow: hidden;
    }
    .guest-card .head { background: var(--bg); color: #fff; padding: 28px 20px; text-align: center; }
    .guest-card .head h1 { font-size: 20px; letter-spacing: 1px; }
    .guest-card .head p { font-size: 13px; color: var(--text-dim); margin-top: 6px; }
    .guest-card .badge-bar { width: 48px; height: 4px; background-color: var(--accent); margin: 10px auto 0; border-radius: 2px; }
    .guest-card .body { padding: 28px 30px; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-weight: bold; color: var(--bg); font-size: 14px; margin-bottom: 6px; }
    .form-group input[type="text"], .form-group input[type="password"] {
        width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none;
    }
    .form-group input:focus { border-color: var(--accent); }

    .password-wrapper { position: relative; }
    .password-wrapper input { padding-right: 40px; }
    .toggle-eye {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        cursor: pointer; background: none; border: none; padding: 0; display: flex; align-items: center;
    }
    .toggle-eye svg { width: 20px; height: 20px; fill: none; stroke: #666; stroke-width: 1.8; }

    .error-message {
        background-color: #fdecea; color: #b3261e; border: 1px solid #f5c6c2;
        padding: 10px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 16px;
    }

    .register-link { text-align: center; font-size: 13px; margin-top: 16px; color: #333; }
    .register-link a { color: var(--accent); font-weight: bold; text-decoration: none; }
    .register-link a:hover { text-decoration: underline; }

    .btn-login {
        width: 100%; padding: 12px; background-color: var(--accent); color: #fff;
        border: none; border-radius: 6px; font-size: 15px; font-weight: bold;
        letter-spacing: 0.5px; cursor: pointer; margin-top: 4px;
    }
    .btn-login:hover { background-color: var(--accent-light); }

    .account-panel { background: var(--panel); border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.25); padding: 26px; margin-bottom: 24px; }
    .account-panel h2 { color: var(--text); font-size: 17px; margin-bottom: 18px; }

    .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; }
    .profile-field span.label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 4px; }
    .profile-field span.val { font-size: 15px; color: var(--text); font-weight: bold; }

    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
    .badge.Pending    { background: rgba(255,152,0,0.18); color: #ffb74d; }
    .badge.Confirmed  { background: rgba(0,150,136,0.18); color: #4db6ac; }
    .badge.Packed     { background: rgba(255,193,7,0.18); color: #ffd54f; }
    .badge.Shipped    { background: rgba(103,58,183,0.2); color: #b39ddb; }
    .badge.Delivered  { background: rgba(76,175,80,0.18); color: var(--success); }
    .badge.Cancelled  { background: rgba(239,83,80,0.18); color: var(--danger); }

    .order-card { border: 1px solid var(--border); border-radius: 10px; padding: 18px 20px; margin-bottom: 16px; background: rgba(255,255,255,0.02); }
    .order-card:last-child { margin-bottom: 0; }

    .order-card-head {
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 10px; margin-bottom: 18px;
    }
    .order-card-head .oid { font-weight: bold; color: var(--text); font-size: 15px; }
    .order-card-head .meta { font-size: 12px; color: var(--text-muted); }
    .order-card-head .amount { font-weight: bold; color: var(--text); font-size: 15px; }

    .tracker { display: flex; align-items: flex-start; padding: 6px 4px 0; }
    .tracker-step { flex: 1; text-align: center; position: relative; }
    .tracker-step .circle {
        width: 26px; height: 26px; border-radius: 50%; background: rgba(255,255,255,0.08); color: var(--text-muted);
        display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;
        font-size: 12px; font-weight: bold; position: relative; z-index: 2;
    }
    .tracker-step.done .circle { background: var(--accent); color: #fff; }
    .tracker-step.current .circle { background: #fff; color: var(--bg); box-shadow: 0 0 0 4px rgba(63,115,232,0.25); }
    .tracker-step .line {
        position: absolute; top: 13px; left: -50%; width: 100%; height: 3px;
        background: rgba(255,255,255,0.08); z-index: 1;
    }
    .tracker-step:first-child .line { display: none; }
    .tracker-step.done .line, .tracker-step.current .line { background: var(--accent); }
    .tracker-step .step-label { font-size: 11px; color: var(--text-muted); }
    .tracker-step.done .step-label, .tracker-step.current .step-label { color: var(--text); font-weight: bold; }

    .cancelled-banner {
        background: rgba(239,83,80,0.15); color: var(--danger); border: 1px solid rgba(239,83,80,0.3);
        padding: 10px 14px; border-radius: 6px; font-size: 13px; font-weight: bold; text-align: center;
    }

    .empty-row { text-align: center; color: var(--text-muted); padding: 24px 0; font-style: italic; }

    .logout-btn { background: var(--danger); color: #fff; border: none; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; }
    .logout-btn:hover { background: #c1352f; }
</style>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/partials/navbar.php'; ?>

<div class="account-wrap">

<?php if (!$isLoggedIn): ?>

    <div class="guest-card">
        <div class="head">
            <h1>WELCOME BACK</h1>
            <p>Log in to view your account</p>
            <div class="badge-bar"></div>
        </div>
        <div class="body">
            <?php if ($loginError): ?>
                <div class="error-message"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>

            <form method="POST" action="my_account.php" autocomplete="off">
                <div class="form-group">
                    <label for="identifier">Username or Full Name</label>
                    <input type="text" id="identifier" name="identifier" required autofocus autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <button type="button" class="toggle-eye" onclick="togglePassword('password')">
                            <svg id="eyeIcon-password" viewBox="0 0 24 24">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-login">Log In</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="Authentication/client_register.php">Register here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            var input = document.getElementById(fieldId);
            var icon = document.getElementById('eyeIcon-' + fieldId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a21.6 21.6 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23" stroke="#666" stroke-width="1.8"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
    </script>

<?php else: ?>

    <div class="account-panel">
        <h2>My Details</h2>
        <div class="profile-grid">
            <div class="profile-field">
                <span class="label">Full Name</span>
                <span class="val"><?= htmlspecialchars($user['full_name']) ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Email</span>
                <span class="val"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Phone</span>
                <span class="val"><?= htmlspecialchars($user['phone'] ?: '-') ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Address</span>
                <span class="val"><?= htmlspecialchars($user['address'] ?: '-') ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Member Since</span>
                <span class="val"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>
        <div style="margin-top:22px;">
            <a href="Authentication/client_logout.php"><button class="logout-btn">Log Out</button></a>
        </div>
    </div>

    <div class="account-panel">
        <h2>My Orders</h2>
        <?php if (empty($orders)): ?>
            <div class="empty-row">You haven't placed any orders yet.</div>
        <?php else: ?>
            <?php
            $trackSteps = ['Pending' => 'Order Received', 'Confirmed' => 'Confirmed', 'Packed' => 'Packed', 'Shipped' => 'Shipped', 'Delivered' => 'Delivered'];
            $stepKeys = array_keys($trackSteps);
            ?>
            <?php foreach ($orders as $o): ?>
                <div class="order-card">
                    <div class="order-card-head">
                        <div>
                            <div class="oid">Order #<?= htmlspecialchars($o['order_id']) ?></div>
                            <div class="meta"><?= date('M j, Y', strtotime($o['order_date'])) ?> &middot; <?= (int)$o['item_count'] ?> item<?= $o['item_count'] == 1 ? '' : 's' ?> &middot; Payment: <?= htmlspecialchars($o['payment_status']) ?></div>
                        </div>
                        <div class="amount">Rs. <?= number_format((float)$o['total_amount'], 2) ?></div>
                    </div>

                    <?php if ($o['order_status'] === 'Cancelled'): ?>
                        <div class="cancelled-banner">This order was cancelled.</div>
                    <?php else: ?>
                        <?php $currentIndex = array_search($o['order_status'], $stepKeys); ?>
                        <div class="tracker">
                            <?php foreach ($stepKeys as $i => $key): ?>
                                <div class="tracker-step <?= $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'current' : '') ?>">
                                    <span class="line"></span>
                                    <span class="circle"><?= $i < $currentIndex ? '&#10003;' : $i + 1 ?></span>
                                    <span class="step-label"><?= $trackSteps[$key] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php endif; ?>

</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/partials/footer.php'; ?>

<script>
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>