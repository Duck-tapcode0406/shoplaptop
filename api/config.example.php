<?php
/**
 * File cấu hình mẫu cho Location Verification API
 * 
 * Đổi tên file này thành config.php và điền thông tin API key của bạn
 */

// SerpAPI Key - Lấy từ https://serpapi.com/
// Đăng ký tài khoản miễn phí để nhận API key (250 lượt tìm kiếm/tháng miễn phí)
define('SERPAPI_KEY', 'YOUR_SERPAPI_KEY_HERE');

// SerpAPI Endpoint
define('SERPAPI_ENDPOINT', 'https://serpapi.com/search.json');

// Cấu hình tìm kiếm mặc định
define('DEFAULT_SEARCH_QUERY', 'Coffee'); // Từ khóa tìm kiếm mặc định
define('DEFAULT_SEARCH_RADIUS', '14z'); // Bán kính tìm kiếm (zoom level: 3-30, mặc định 14)

// Cấu hình ngôn ngữ và quốc gia
define('SERPAPI_LANGUAGE', 'vi'); // Ngôn ngữ: vi (tiếng Việt), en (tiếng Anh)
define('SERPAPI_COUNTRY', 'vn'); // Quốc gia: vn (Việt Nam), us (Mỹ)

// Cấu hình timeout
define('API_TIMEOUT', 10); // Thời gian chờ API (giây)

// Google Maps API Key (nếu sử dụng Google Maps JavaScript API)
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

?>










