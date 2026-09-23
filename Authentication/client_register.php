<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [
    'full_name' => '',
    'email' => '',
    'username' => '',
    'password' => ''
];

$values = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'username' => ''
];

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

    $values['full_name'] = trim($_POST['full_name'] ?? '');
    $values['email']     = trim($_POST['email'] ?? '');
    $values['phone']     = trim($_POST['phone'] ?? '');
    $values['address']   = trim($_POST['address'] ?? '');
    $values['username']  = trim($_POST['username'] ?? '');
    $password            = $_POST['password'] ?? '';

    if ($values['full_name'] === '') {
        $errors['full_name'] = 'Full name is required.';
    } elseif (!preg_match('/^[A-Za-z\s\'\.\-]+$/', $values['full_name'])) {
        $errors['full_name'] = 'Only letters, spaces, hyphens, apostrophes, and periods are allowed.';
    } elseif (strlen($values['full_name']) < 2 || strlen($values['full_name']) > 100) {
        $errors['full_name'] = 'Full name must be between 2 and 100 characters.';
    }

    if ($values['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (strlen($values['email']) > 254) {
        $errors['email'] = 'Email address is too long.';
    } elseif (strpos($values['email'], ' ') !== false) {
        $errors['email'] = 'Email address cannot contain spaces.';
    } elseif (!preg_match('/^[A-Za-z][A-Za-z0-9_+-]*(\.[A-Za-z0-9_+-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/', $values['email'])) {
        $errors['email'] = 'Enter a valid email address (e.g. name@example.com).';
    }

    if ($values['username'] === '') {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($values['username']) < 4) {
        $errors['username'] = 'Username must be at least 4 characters long.';
    } else {
        $firstChar = $values['username'][0];
        $starts_with_letter = ($firstChar >= 'a' && $firstChar <= 'z') || ($firstChar >= 'A' && $firstChar <= 'Z');

        if (!$starts_with_letter) {
            $errors['username'] = 'Username must start with a letter.';
        } else {
            $username_valid = true;
            for ($i = 0; $i < strlen($values['username']); $i++) {
                $ch = $values['username'][$i];
                $is_letter = ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z');
                $is_digit  = ($ch >= '0' && $ch <= '9');
                $is_underscore = ($ch === '_');
                if (!$is_letter && !$is_digit && !$is_underscore) {
                    $username_valid = false;
                    break;
                }
            }
            if (!$username_valid) {
                $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
            }
        }
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } else {
        $has_min_length = strlen($password) >= 8;
        $has_upper = false;
        $has_lower = false;
        $has_number = false;
        $has_special = false;
        $special_chars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        for ($i = 0; $i < strlen($password); $i++) {
            $char = $password[$i];
            if ($char >= 'A' && $char <= 'Z') $has_upper = true;
            if ($char >= 'a' && $char <= 'z') $has_lower = true;
            if ($char >= '0' && $char <= '9') $has_number = true;
            if (strpos($special_chars, $char) !== false) $has_special = true;
        }

        if (!$has_min_length) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        } elseif (!$has_upper) {
            $errors['password'] = 'Password must contain at least one uppercase letter.';
        } elseif (!$has_lower) {
            $errors['password'] = 'Password must contain at least one lowercase letter.';
        } elseif (!$has_number) {
            $errors['password'] = 'Password must contain at least one number.';
        } elseif (!$has_special) {
            $errors['password'] = 'Password must contain at least one special character (e.g. ! @ # $ %).';
        }
    }

    $hasErrors = $errors['full_name'] || $errors['email'] || $errors['username'] || $errors['password'];

    if (!$hasErrors) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$values['username'], $values['email']]);

        if ($stmt->fetch()) {
            $errors['username'] = 'Username or email is already registered.';
            $errors['email'] = 'Username or email is already registered.';
            $values['username'] = '';
            $values['email'] = '';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, address, username, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$values['full_name'], $values['email'], $values['phone'], $values['address'], $values['username'], $hashed]);
            $success = 'Account created successfully.';
            $values = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => '', 'username' => ''];
        }
    }

    if ($errors['full_name']) $values['full_name'] = '';
    if ($errors['email']) $values['email'] = '';
    if ($errors['username']) $values['username'] = '';
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

    input.field-error { border-color: #b3261e; }

    input:focus { border-color: #FF6B35; }

    .field-error-msg {
        font-size: 12px;
        color: #b3261e;
        margin-top: 5px;
    }

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
        position: relative;
        z-index: 100;
    }

    .login-link a {
        color: #FF6B35;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
        padding: 8px 12px;
        position: relative;
        z-index: 100;
        pointer-events: auto;
    }

    .login-link a:hover {
        text-decoration: underline;
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
        <?php if ($success): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
                <a href="client_login.php">Click here to log in</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="client_register.php" id="registerForm" autocomplete="off">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="<?= $errors['full_name'] ? 'field-error' : '' ?>" value="<?= htmlspecialchars($values['full_name']) ?>" autocomplete="off" oninput="filterName(this)" onkeypress="return blockNameKey(event)">
                <?php if ($errors['full_name']): ?><div class="field-error-msg"><?= htmlspecialchars($errors['full_name']) ?></div><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="<?= $errors['email'] ? 'field-error' : '' ?>" value="<?= htmlspecialchars($values['email']) ?>" autocomplete="off">
                <?php if ($errors['email']): ?><div class="field-error-msg"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($values['phone']) ?>" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?= htmlspecialchars($values['address']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="<?= $errors['username'] ? 'field-error' : '' ?>" value="<?= htmlspecialchars($values['username']) ?>" autocomplete="off" oninput="filterUsername(this)">
                <?php if ($errors['username']): ?><div class="field-error-msg"><?= htmlspecialchars($errors['username']) ?></div><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="<?= $errors['password'] ? 'field-error' : '' ?>" oninput="checkPassword()" autocomplete="new-password">
                    <button type="button" class="toggle-eye" onclick="togglePassword('password')">
                        <svg id="eyeIcon-password" viewBox="0 0 24 24">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <?php if ($errors['password']): ?><div class="field-error-msg"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
                <div class="password-hint">
                    Must contain:
                    <ul>
                        <li id="rule-length" class="invalid">At least 8 characters</li>
                        <li id="rule-upper" class="invalid">At least one uppercase letter</li>
                        <li id="rule-lower" class="invalid">At least one lowercase letter</li>
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
    function blockNameKey(event) {
        var ch = String.fromCharCode(event.which || event.keyCode);
        return /[A-Za-z\s'.-]/.test(ch);
    }

    function filterName(el) {
        el.value = el.value.replace(/[^A-Za-z\s'.-]/g, '');
    }

    function filterUsername(el) {
        el.value = el.value.replace(/[^A-Za-z0-9_]/g, '');
        if (el.value.length > 0 && !/^[A-Za-z]/.test(el.value)) {
            el.value = el.value.replace(/^[^A-Za-z]+/, '');
        }
    }

    function checkPassword() {
        var password = document.getElementById('password').value;
        var specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        var hasLength = password.length >= 8;
        var hasUpper = false;
        var hasLower = false;
        var hasNumber = false;
        var hasSpecial = false;

        for (var i = 0; i < password.length; i++) {
            var ch = password.charAt(i);
            if (ch >= 'A' && ch <= 'Z') {
                hasUpper = true;
            }
            if (ch >= 'a' && ch <= 'z') {
                hasLower = true;
            }
            if (ch >= '0' && ch <= '9') {
                hasNumber = true;
            }
            if (specialChars.indexOf(ch) !== -1) {
                hasSpecial = true;
            }
        }

        setRuleState('rule-length', hasLength);
        setRuleState('rule-upper', hasUpper);
        setRuleState('rule-lower', hasLower);
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