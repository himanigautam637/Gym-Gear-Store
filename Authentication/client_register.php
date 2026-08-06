<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';

    if ($full_name === '' || $email === '' || $username === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($full_name) < 3) {
        $error = 'Full name must be at least 3 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters long.';
    } else {
        
        $username_valid = true;
        for ($i = 0; $i < strlen($username); $i++) {
            $ch = $username[$i];
            $is_letter = ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z');
            $is_digit  = ($ch >= '0' && $ch <= '9');
            $is_underscore = ($ch === '_');
            if (!$is_letter && !$is_digit && !$is_underscore) {
                $username_valid = false;
                break;
            }
        }

        if (!$username_valid) {
            $error = 'Username can only contain letters, numbers, and underscores.';
        } else {
            
            $has_min_length = strlen($password) >= 8;
            $has_number  = false;
            $has_special = false;
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
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);

                if ($stmt->fetch()) {
                    $error = 'Username or email is already registered.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, address, username, password) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $email, $phone, $address, $username, $hashed]);
                    $success = 'Account created successfully.';
                }
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
<title>Register | Gym Gear Store</title>
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

    .password-wrapper { position: relative; }

    .password-wrapper input[type="password"],
    .password-wrapper input[type="text"] {
        padding-right: 40px;
    }

    .toggle-eye {
        position: absolute;
        right: 10px;
        top: 9px;
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

    .password-hint {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .password-hint ul { margin: 4px 0 0 18px; }

    .password-hint li.valid { color: #2e7d32; }
    .password-hint li.invalid { color: #b3261e; }

    textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        resize: vertical;
        min-height: 60px;
        font-family: inherit;
    }

    textarea:focus { border-color: #FF6B35; }

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

    .success-message a {
        color: #2e7d32;
        font-weight: bold;
        text-decoration: underline;
        margin-left: 6px;
    }

    .login-link {
        text-align: center;
        font-size: 13px;
        margin-top: 14px;
    }

    .login-link a {
        color: #FF6B35;
        font-weight: bold;
        text-decoration: none;
    }
</style>
</head>
<body>

<div class="register-card">
    <div class="register-header">
        <h1>CREATE ACCOUNT</h1>
        <div class="badge-bar"></div>
    </div>
    <div class="register-body">
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
                <a href="client_login.php">Click here to log in</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="client_register.php" id="registerForm" autocomplete="off">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"></textarea>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required oninput="checkPassword()" autocomplete="new-password">
                    <button type="button" class="toggle-eye" onclick="togglePassword('password')">
                        <svg id="eyeIcon-password" viewBox="0 0 24 24">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div class="password-hint">
                    Must contain:
                    <ul>
                        <li id="rule-length" class="invalid">At least 8 characters</li>
                        <li id="rule-number" class="invalid">At least one number</li>
                        <li id="rule-special" class="invalid">At least one special character</li>
                    </ul>
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Account</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="client_login.php">Log in</a>
        </div>
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