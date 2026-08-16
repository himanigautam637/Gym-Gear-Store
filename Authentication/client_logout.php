<?php
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['full_name']);
unset($_SESSION['username']);

header('Location: client_login.php');
exit;