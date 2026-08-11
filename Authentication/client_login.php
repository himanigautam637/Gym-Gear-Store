<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter both fields.';
    } else {
        // Match against either username or full_name
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR full_name = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username']  = $user['username'];
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Invalid username/full name or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Gym Gear Store</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #0C2340;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        background-color: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .login-header {
        background-color: #0C2340;
        color: #ffffff;
        padding: 30px 20px 20px;
        text-align: center;
    }

    .login-header h1 { font-size: 22px; letter-spacing: 1px; margin-top: 10px; }
    .login-header p { font-size: 13px; color: #c9d4e0; margin-top: 4px; }

    .badge-bar {
        width: 48px;
        height: 4px;
        background-color: #FF6B35;
        margin: 12px auto 0;
        border-radius: 2px;
    }

    .login-body { padding: 30px; }
    .form-group { margin-bottom: 18px; }

    label {
        display: block;
        font-weight: bold;
        color: #0C2340;
        font-size: 14px;
        margin-bottom: 6px;
    }

    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
    }

    input:focus { border-color: #FF6B35; }

    .password-wrapper { position: relative; }

    .password-wrapper input[type="password"],
    .password-wrapper input[type="text"] {
        padding-right: 40px;
    }

    .toggle-eye {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        display: flex;
        align-items: center;
    }

    .toggle-eye svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: #666;
        stroke-width: 1.8;
    }

    .btn-login {
        width: 100%;
        padding: 12px;
        background-color: #FF6B35;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 0.5px;
        cursor: pointer;
        margin-top: 8px;
    }

    .btn-login:hover { background-color: #e85a29; }

    .error-message {
        background-color: #fdecea;
        color: #b3261e;
        border: 1px solid #f5c6c2;
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .register-link {
        text-align: center;
        font-size: 13px;
        margin-top: 14px;
    }

    .register-link a {
        color: #FF6B35;
        font-weight: bold;
        text-decoration: none;
    }
</style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h1>WELCOME BACK</h1>
        <p>Gym Gear Store</p>
        <div class="badge-bar"></div>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="client_login.php" autocomplete="off">
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
            Don't have an account? <a href="client_register.php">Register here</a>
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

</body>
</html>