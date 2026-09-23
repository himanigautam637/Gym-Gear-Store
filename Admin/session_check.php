<?php
session_name('gym_admin_session');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$idleTimeout = 2 * 60 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $idleTimeout) {
    session_unset();
    session_destroy();
    header('Location: admin_login.php?expired=1');
    exit;
}

$_SESSION['last_activity'] = time();
?>