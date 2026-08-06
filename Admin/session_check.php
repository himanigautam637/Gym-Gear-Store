<?php
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
session_set_cookie_params(30 * 24 * 60 * 60);
session_start();


if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}


$timeout = 30 * 24 * 60 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
   
    session_unset();
    session_destroy();
    header('Location: admin_login.php?expired=1');
    exit;
}


$_SESSION['last_activity'] = time();
?>