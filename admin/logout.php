<?php
require_once __DIR__ . '/../includes/session.php';
destroySession();
header('Location: /shop/login.php');
exit();
?>
