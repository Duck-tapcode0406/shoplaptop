<?php
/**
 * Input Validation Class
 * Validate và sanitize user input
 */
class Validator {
    // Minimum password length
    const MIN_PASSWORD_LENGTH = 3;
    const MIN_USERNAME_LENGTH = 3;
    const MAX_USERNAME_LENGTH = 50;
    
    /**
     * Validate username
     */
    public static function username($value) {
        $value = trim($value);
        if (empty($value)) {
            return ['valid' => false, 'message' => 'Tên đăng nhập không được để trống'];
        }
        if (strlen($value) < self::MIN_USERNAME_LENGTH) {
            return ['valid' => false, 'message' => 'Tên đăng nhập phải có ít nhất ' . self::MIN_USERNAME_LENGTH . ' ký tự'];
        }
        if (strlen($value) > self::MAX_USERNAME_LENGTH) {
            return ['valid' => false, 'message' => 'Tên đăng nhập không được quá ' . self::MAX_USERNAME_LENGTH . ' ký tự'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            return ['valid' => false, 'message' => 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới'];
        }
        return ['valid' => true, 'value' => $value];
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
            return ['valid' => false, 'message' => 'Email không hợp lệ (ví dụ: example@gmail.com)'];
        }
        if (strlen($value) > 255) {
            return ['valid' => false, 'message' => 'Email quá dài (tối đa 255 ký tự)'];
        }
        return ['valid' => true, 'value' => $value];
    }
    
    /**
     * Validate password
     */
    public static function password($value, $requireStrength = false) {
        if (empty($value)) {
            return ['valid' => false, 'message' => 'Mật khẩu không được để trống'];
        }
        if (strlen($value) < self::MIN_PASSWORD_LENGTH) {
            return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất ' . self::MIN_PASSWORD_LENGTH . ' ký tự'];
        }
        if (strlen($value) > 128) {
            return ['valid' => false, 'message' => 'Mật khẩu quá dài (tối đa 128 ký tự)'];
        }
        
        if ($requireStrength) {
            if (!preg_match('/[A-Z]/', $value)) {
                return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ hoa'];
            }
            if (!preg_match('/[a-z]/', $value)) {
                return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ thường'];
            }
            if (!preg_match('/[0-9]/', $value)) {
                return ['valid' => false, 'message' => 'Mật khẩu phải có ít nhất 1 chữ số'];
            }
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate phone (Vietnamese format) - Optional field
     */
    public static function phone($value, $required = false) {
        $value = trim($value);
        
        // If not required and empty, it's valid
        if (empty($value)) {
            if ($required) {
                return ['valid' => false, 'message' => 'Số điện thoại không được để trống'];
            }
            return ['valid' => true, 'value' => ''];
        }
        
        // Remove spaces and dashes
        $cleanPhone = preg_replace('/[\s\-]/', '', $value);
        
        // Vietnamese phone format: 0xxxxxxxxx or +84xxxxxxxxx (9-10 digits after prefix)
        if (!preg_match('/^(0|\+84|84)[0-9]{9,10}$/', $cleanPhone)) {
            return ['valid' => false, 'message' => 'Số điện thoại không hợp lệ (VD: 0912345678 hoặc +84912345678)'];
        }
        
        return ['valid' => true, 'value' => $cleanPhone];
    }
    
    /**
     * Validate name (họ, tên)
     */
    public static function name($value, $fieldName = 'Tên') {
        $value = trim($value);
        if (empty($value)) {
            return ['valid' => false, 'message' => $fieldName . ' không được để trống'];
        }
        if (strlen($value) < 1) {
            return ['valid' => false, 'message' => $fieldName . ' phải có ít nhất 1 ký tự'];
        }
        if (strlen($value) > 100) {
            return ['valid' => false, 'message' => $fieldName . ' không được quá 100 ký tự'];
        }
        // Allow letters, spaces, and Vietnamese characters
        if (!preg_match('/^[\p{L}\s]+$/u', $value)) {
            return ['valid' => false, 'message' => $fieldName . ' chỉ được chứa chữ cái và khoảng trắng'];
        }
        return ['valid' => true, 'value' => $value];
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
    
    /**
     * Validate quantity
     */
    public static function quantity($value, $min = 1, $max = 999) {
        $qty = intval($value);
        if ($qty < $min) {
            return ['valid' => false, 'message' => 'Số lượng phải ít nhất là ' . $min];
        }
        if ($qty > $max) {
            return ['valid' => false, 'message' => 'Số lượng không được vượt quá ' . $max];
        }
        return ['valid' => true, 'value' => $qty];
    }
    
    /**
     * Validate address
     */
    public static function address($value, $required = true) {
        $value = trim($value);
        if (empty($value)) {
            if ($required) {
                return ['valid' => false, 'message' => 'Địa chỉ không được để trống'];
            }
            return ['valid' => true, 'value' => ''];
        }
        if (strlen($value) < 10) {
            return ['valid' => false, 'message' => 'Địa chỉ phải có ít nhất 10 ký tự'];
        }
        if (strlen($value) > 500) {
            return ['valid' => false, 'message' => 'Địa chỉ không được quá 500 ký tự'];
        }
        return ['valid' => true, 'value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8')];
    }
}
?>












