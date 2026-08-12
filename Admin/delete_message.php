<?php
require 'session_check.php';
require '../db_connect.php'; 

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: manage_messages.php');
    exit;
}

try {
    $pdo->prepare("DELETE FROM contact_messages WHERE message_id = ?")->execute([$id]);
    header('Location: manage_messages.php?msg=' . urlencode('Message deleted.'));
} catch (PDOException $e) {
    header('Location: manage_messages.php');
}
exit;