<?php
session_start();
session_destroy(); // Hủy session đăng nhập
header('Location: ../login.php'); // Điều hướng về login.php ngoài thư mục admin
exit();
?>
