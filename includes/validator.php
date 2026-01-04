<?php
/**
 * Input Validation Class
 * Validate và sanitize user input
 */
class Validator {
    /**
     * Validate username
     */
    public static function username($value) {
        $value = trim($value);
        if (empty($value)) {
            return ['valid' => false, 'message' => 'Username không được để trống'];
        }
        if (strlen($value) < 3) {
            return ['valid' => false, 'message' => 'Username phải có ít nhất 3 ký tự'];
        }
        if (strlen($value) > 50) {
            return ['valid' => false, 'message' => 'Username không được quá 50 ký tự'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            return ['valid' => false, 'message' => 'Username chỉ chứa chữ, số và dấu gạch dưới'];
        }
        return ['valid' => true];
    }
    
    /**
     * Validate email
     */
    public static function email($value) {
        $value = trim($value);
        if (empty($value)) {
            return ['valid' => false, 'message' => 'Email không được để trống'];
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Email không hợp lệ'];
        }
        if (strlen($value) > 255) {
            return ['valid' => false, 'message' => 'Email quá dài'];
        }
        return ['valid' => true];
    }
    
    /**
     * Validate password
     */
    public static function password($value, $requireStrength = false) {
        if (empty($value)) {
            return ['valid' => false, 'message' => 'Mật khẩu không được để trống'];
        }
        if (strlen($value) < 8) {
            return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự'];
        }
        
        if ($requireStrength) {
            if (!preg_match('/[A-Z]/', $value)) {
                return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ hoa'];
            }
            if (!preg_match('/[a-z]/', $value)) {
                return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ thường'];
            }
            if (!preg_match('/[0-9]/', $value)) {
                return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 số'];
            }
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate phone (Vietnamese format)
     */
    public static function phone($value) {
        $value = trim($value);
        if (empty($value)) {
            return ['valid' => false, 'message' => 'Số điện thoại không được để trống'];
        }
        // Vietnamese phone format: 0xxxxxxxxx or +84xxxxxxxxx
        if (!preg_match('/^(0|\+84)[0-9]{9,10}$/', $value)) {
            return ['valid' => false, 'message' => 'Số điện thoại không hợp lệ'];
        }
        return ['valid' => true];
    }
    
    /**
     * Sanitize input
     */
    public static function sanitize($value, $type = 'string') {
        if ($value === null) {
            return null;
        }
        
        $value = trim($value);
        
        switch ($type) {
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            case 'string':
            default:
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate product ID
     */
    public static function productId($value) {
        $id = intval($value);
        if ($id <= 0) {
            return ['valid' => false, 'message' => 'ID sản phẩm không hợp lệ'];
        }
        return ['valid' => true, 'value' => $id];
    }
    
    /**
     * Validate rating (1-5)
     */
    public static function rating($value) {
        $rating = intval($value);
        if ($rating < 1 || $rating > 5) {
            return ['valid' => false, 'message' => 'Đánh giá phải từ 1 đến 5 sao'];
        }
        return ['valid' => true, 'value' => $rating];
    }
}
?>





