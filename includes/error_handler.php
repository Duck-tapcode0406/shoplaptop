<?php
/**
 * Error Handling
 * Xử lý lỗi tập trung và an toàn
 */
require_once __DIR__ . '/config.php';

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error_message);
    
    if (DEBUG_MODE) {
        echo "<div style='background: #fee; padding: 10px; border: 1px solid #f00; margin: 10px; border-radius: 5px;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    return true;
}

set_error_handler('customErrorHandler');

/**
 * Exception handler
 */
function customExceptionHandler($exception) {
    $error_message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($error_message);
    
    http_response_code(500);
    
    if (DEBUG_MODE) {
        echo "<div style='background: #fee; padding: 20px; border: 2px solid #f00; margin: 20px; border-radius: 5px;'>";
        echo "<h2>Exception</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<pre style='background: #fff; padding: 10px; overflow: auto;'>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #f5f5f5; padding: 40px; margin: 20px; border-radius: 5px; text-align: center;'>";
        echo "<h2>Có lỗi xảy ra</h2>";
        echo "<p>Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</p>";
        echo "<p><a href='" . BASE_URL . "/index.php'>Quay về trang chủ</a></p>";
        echo "</div>";
    }
    
    exit();
}

set_exception_handler('customExceptionHandler');

/**
 * Handle application errors
 */
function handleError($message, $code = 500, $log = true) {
    if ($log) {
        error_log("Application Error [$code]: $message");
    }
    
    http_response_code($code);
    
    if (DEBUG_MODE) {
        echo "<div style='background: #fee; padding: 15px; border: 2px solid #f00; margin: 20px; border-radius: 5px;'>";
        echo "<h3>Error $code</h3>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f5f5f5; padding: 20px; margin: 20px; border-radius: 5px; text-align: center;'>";
        echo "<h2>Có lỗi xảy ra</h2>";
        echo "<p>Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</p>";
        echo "<p><a href='" . BASE_URL . "/index.php'>Quay về trang chủ</a></p>";
        echo "</div>";
    }
    
    if ($code >= 500) {
        exit();
    }
}
?>










