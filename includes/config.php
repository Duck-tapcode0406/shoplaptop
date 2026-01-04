<?php
/**
 * Configuration File
 * Tất cả cấu hình tập trung ở đây
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shop');

// Application Configuration
define('BASE_URL', '/shop');
define('ADMIN_URL', BASE_URL . '/admin');

// Security
define('DEBUG_MODE', false); // Set to false in production
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_REGEN_INTERVAL', 1800); // 30 minutes

// File Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('REVIEWS_PER_PAGE', 10);

// Rate Limiting
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 900); // 15 minutes

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>





