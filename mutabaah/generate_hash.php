<?php
$password_baru = 'adminmtq'; // Kata sandi yang Anda inginkan
$hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
echo "Hash baru untuk 'password123' adalah: " . $hash_baru;
?>