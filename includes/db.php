<?php
/**
 * Database Connection
 * Sử dụng Database singleton để đảm bảo chỉ có 1 kết nối
 */
require_once __DIR__ . '/database.php';

// Tạo kết nối database
$conn = getDB();
?>
