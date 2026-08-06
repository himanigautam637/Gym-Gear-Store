<?php

$host    = 'localhost';
$dbname  = 'gymgear_store'; 
$db_user = 'root';          
$db_pass = 'dalli 23@';               
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);

    // Make errors throw exceptions instead of failing silently
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>