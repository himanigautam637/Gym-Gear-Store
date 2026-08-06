<?php
require 'session_check.php'; 
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_connect.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';

    if ($full_name === '' || $email === '' || $username === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } else {
       
        $has_min_length = strlen($password) >= 8;
        $has_number      = false;
        $has_special     = false;

        $special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        for ($i = 0; $i < strlen($password); $i++) {
            $char = $password[$i];
            if ($char >= '0' && $char <= '9') {
                $has_number = true;
            }
            if (strpos($special_chars, $char) !== false) {
                $has_special = true;
            }
        }

        if (!$has_min_length) {
            $error = 'Password must be at least 8 characters long.';
        } elseif (!$has_number) {
            $error = 'Password must contain at least one number.';
        } elseif (!$has_special) {
            $error = 'Password must contain at least one special character (e.g. ! @ # $ %).';
        } else {
            
            $stmt = $pdo->prepare("SELECT admin_id FROM admin WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = 'Username or email is already registered.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admin (full_name, email, phone, username, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $phone, $username, $hashed]);
                $success = 'Admin account created successfully.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Admin | Gym Gear Store</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #0C2340;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .register-card {
        background-color: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        max-width: 450px;
        width: 100%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .register-header {
        background-color: #0C2340;
        color: #ffffff;
        padding: 25px 20px 18px;
        text-align: center;
    }

    .register-header h1 { font-size: 20px; letter-spacing: 1px; }

    .badge-bar {
        width: 48px;
        height: 4px;
        background-color: #FF6B35;
        margin: 10px auto 0;
        border-radius: 2px;
    }

    .register-body { padding: 26px 30px; }

    .form-group { margin-bottom: 16px; }

    label {
        display: block;
        font-weight: bold;
        color: #0C2340;
        font-size: 13px;
        margin-bottom: 5px;
    }

    input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
    }

    input:focus { border-color: #FF6B35; }

    .password-hint {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .password-hint ul { margin: 4px 0 0 18px; }

    .password-hint li.valid { color: #2e7d32; }
    .password-hint li.invalid { color: #b3261e; }

    .btn-submit {
        width: 100%;
        padding: 11px;
        background-color: #FF6B35;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 6px;
    }

    .btn-submit:hover { background-color: #e85a29; }

    .error-message {
        background-color: #fdecea;
        color: #b3261e;
        border: 1px solid #f5c6c2;
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .success-message {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 16px;
    }
</style>
</head>
<body>

<div class="register-card">
    <div class="register-header">
        <h1>REGISTER NEW ADMIN</h1>
        <div class="badge-bar"></div>
    </div>
    <div class="register-body">
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_register.php" id="registerForm">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required oninput="checkPassword()">
                <div class="password-hint">
                    Must contain:
                    <ul>
                        <li id="rule-length" class="invalid">At least 8 characters</li>
                        <li id="rule-number" class="invalid">At least one number</li>
                        <li id="rule-special" class="invalid">At least one special character</li>
                    </ul>
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Admin Account</button>
        </form>
    </div>
</div>

<script>
    function checkPassword() {
        var password = document.getElementById('password').value;
        var specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        var hasLength = password.length >= 8;
        var hasNumber = false;
        var hasSpecial = false;

        for (var i = 0; i < password.length; i++) {
            var ch = password.charAt(i);
            if (ch >= '0' && ch <= '9') {
                hasNumber = true;
            }
            if (specialChars.indexOf(ch) !== -1) {
                hasSpecial = true;
            }
        }

        setRuleState('rule-length', hasLength);
        setRuleState('rule-number', hasNumber);
        setRuleState('rule-special', hasSpecial);
    }

    function setRuleState(id, isValid) {
        var el = document.getElementById(id);
        if (isValid) {
            el.classList.remove('invalid');
            el.classList.add('valid');
        } else {
            el.classList.remove('valid');
            el.classList.add('invalid');
        }
    }
</script>

</body>
</html>