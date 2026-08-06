<?php


$password = 'gymgearAdmin@123'; 
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Your password: " . $password . "<br>";
echo "Your hash (copy this): <br>" . $hash;
?>