<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
destroySession();
// Redirect về trang login người dùng (không phải admin login)
// Sử dụng BASE_URL từ config
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = defined('BASE_URL') ? BASE_URL : '/shop';
header('Location: ' . $protocol . '://' . $host . $base_url . '/login.php');
exit();
?>
