<?php
/**
 * Helper Functions
 * Các hàm tiện ích dùng chung
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'admin';
}

/**
 * Require user to be admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

/**
 * Format price
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₫';
}

/**
 * Format date
 */
function formatDate($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Check login attempts (rate limiting)
 */
function checkLoginAttempts($username) {
    require_once __DIR__ . '/config.php';
    $key = 'login_attempts_' . md5($username);
    $attempts = $_SESSION[$key] ?? 0;
    $last_attempt = $_SESSION[$key . '_time'] ?? 0;
    
    // Reset if window expired
    if (time() - $last_attempt > LOGIN_ATTEMPT_WINDOW) {
        $_SESSION[$key] = 0;
        $attempts = 0;
    }
    
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        return ['allowed' => false, 'message' => 'Bạn đã vượt quá số lần đăng nhập. Vui lòng thử lại sau ' . (LOGIN_ATTEMPT_WINDOW / 60) . ' phút.'];
    }
    
    return ['allowed' => true];
}

/**
 * Increment login attempts
 */
function incrementLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username);
    $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
    $_SESSION[$key . '_time'] = time();
}

/**
 * Reset login attempts
 */
function resetLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username);
    unset($_SESSION[$key]);
    unset($_SESSION[$key . '_time']);
}

/**
 * Get cart count
 */
function getCartCount($user_id) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT SUM(od.quantity) as total 
                           FROM `order` o 
                           JOIN `order_details` od ON o.id = od.order_id 
                           WHERE o.customer_id = ? AND od.status = 'pending'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?: 0;
}
?>





