<?php
/**
 * Database Connection Singleton
 * Đảm bảo chỉ có 1 kết nối database trong suốt ứng dụng
 */
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            error_log('Database connection failed: ' . $this->conn->connect_error);
            if (DEBUG_MODE) {
                die('Database connection failed: ' . $this->conn->connect_error);
            } else {
                die('Database connection failed. Please try again later.');
            }
        }
        
        $this->conn->set_charset('utf8mb4');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function để dễ sử dụng
function getDB() {
    return Database::getInstance()->getConnection();
}
?>














