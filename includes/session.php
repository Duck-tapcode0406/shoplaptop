<?php
/**
 * Secure Session Configuration
 * Cải thiện bảo mật session
 */
require_once __DIR__ . '/config.php';

// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
ini_set('session.cookie_samesite', 'Strict');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > SESSION_REGEN_INTERVAL) {
    // Regenerate every 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Session expired
    session_unset();
    session_destroy();
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header('Location: login.php?expired=1');
        exit();
    }
}
$_SESSION['last_activity'] = time();

/**
 * Regenerate session ID after login
 */
function regenerateSessionAfterLogin() {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
    $_SESSION['last_activity'] = time();
}

/**
 * Destroy session and redirect to login
 */
function destroySession() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}
?>












