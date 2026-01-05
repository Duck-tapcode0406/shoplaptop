<?php
/**
 * Configuration - đọc từ environment để tránh hardcode secrets
 * Các biến môi trường đề xuất xem thêm trong .env.example
 */

// Helper: trả về default nếu env không set hoặc rỗng
if (!function_exists('env_or_default')) {
    function env_or_default($key, $default)
    {
        $value = getenv($key);
        return ($value === false || $value === '') ? $default : $value;
    }
}

// Database Configuration
define('DB_HOST', env_or_default('SHOP_DB_HOST', 'localhost'));
define('DB_USER', env_or_default('SHOP_DB_USER', 'root'));
define('DB_PASS', env_or_default('SHOP_DB_PASS', ''));
define('DB_NAME', env_or_default('SHOP_DB_NAME', 'shop'));

// Application Configuration
define('BASE_URL', env_or_default('SHOP_BASE_URL', '/shop'));
define('ADMIN_URL', env_or_default('SHOP_ADMIN_URL', BASE_URL . '/admin'));

// Security
define('DEBUG_MODE', env_or_default('SHOP_DEBUG', '0') === '1'); // Set to false in production
define('SESSION_TIMEOUT', intval(env_or_default('SHOP_SESSION_TIMEOUT', 3600))); // 1 hour
define('SESSION_REGEN_INTERVAL', intval(env_or_default('SHOP_SESSION_REGEN_INTERVAL', 1800))); // 30 minutes

// File Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Pagination
define('PRODUCTS_PER_PAGE', intval(env_or_default('SHOP_PRODUCTS_PER_PAGE', 12)));
define('REVIEWS_PER_PAGE', intval(env_or_default('SHOP_REVIEWS_PER_PAGE', 10)));

// Rate Limiting
define('MAX_LOGIN_ATTEMPTS', intval(env_or_default('SHOP_MAX_LOGIN_ATTEMPTS', 5)));
define('LOGIN_ATTEMPT_WINDOW', intval(env_or_default('SHOP_LOGIN_ATTEMPT_WINDOW', 900))); // 15 minutes

// Timezone
date_default_timezone_set(env_or_default('SHOP_TIMEZONE', 'Asia/Ho_Chi_Minh'));
?>










