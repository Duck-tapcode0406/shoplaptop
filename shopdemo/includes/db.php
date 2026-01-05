<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'shop';

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli($host, $user, $pass, $db_name);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
