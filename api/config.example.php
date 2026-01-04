<?php
/**
 * File cấu hình mẫu cho Location Verification API
 * 
 * Đổi tên file này thành config.php và điền thông tin API key của bạn
 */

// SerpAPI Key - Lấy từ https://serpapi.com/
// Đăng ký tài khoản miễn phí để nhận API key
define('SERPAPI_KEY', 'YOUR_SERPAPI_KEY_HERE');

// Cấu hình tìm kiếm mặc định
define('DEFAULT_SEARCH_QUERY', 'Coffee'); // Từ khóa tìm kiếm mặc định
define('DEFAULT_SEARCH_RADIUS', '14z'); // Bán kính tìm kiếm (zoom level)

// Cấu hình timeout
define('API_TIMEOUT', 10); // Thời gian chờ API (giây)

?>









