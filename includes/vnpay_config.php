<?php
/**
 * VNPay Configuration
 * Cấu hình thanh toán VNPay
 */

// VNPay Sandbox Configuration
define('VNPAY_TMN_CODE', '7Y9OS5NF'); // Mã website tại VNPAY
define('VNPAY_HASH_SECRET', 'GKI8ULARL5POMO05UH1FWBMEIJ67MZML'); // Chuỗi bí mật
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'); // URL thanh toán sandbox

// Return URL - URL nhận kết quả thanh toán
define('VNPAY_RETURN_URL', 'http://localhost/shop/vnpay_return.php');

// VNPay API Version
define('VNPAY_VERSION', '2.1.0');

// VNPay Command
define('VNPAY_COMMAND', 'pay');

// Currency
define('VNPAY_CURRENCY', 'VND');

// Locale
define('VNPAY_LOCALE', 'vn');

/**
 * Tạo URL thanh toán VNPay
 * 
 * @param array $orderData Thông tin đơn hàng
 * @return string URL thanh toán
 */
function createVnpayUrl($orderData) {
    $vnp_TxnRef = $orderData['order_id']; // Mã đơn hàng
    $vnp_OrderInfo = $orderData['order_desc'] ?? 'Thanh toan don hang #' . $vnp_TxnRef;
    $vnp_OrderType = $orderData['order_type'] ?? 'billpayment';
    $vnp_Amount = $orderData['amount'] * 100; // VNPay yêu cầu số tiền * 100
    $vnp_Locale = $orderData['language'] ?? VNPAY_LOCALE;
    $vnp_BankCode = $orderData['bank_code'] ?? '';
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
    
    // Thời gian hết hạn thanh toán (15 phút)
    $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));
    
    $inputData = array(
        "vnp_Version" => VNPAY_VERSION,
        "vnp_TmnCode" => VNPAY_TMN_CODE,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => VNPAY_COMMAND,
        "vnp_CreateDate" => date('YmdHis'),
        "vnp_CurrCode" => VNPAY_CURRENCY,
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => VNPAY_RETURN_URL,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_ExpireDate" => $vnp_ExpireDate
    );
    
    // Thêm thông tin billing nếu có
    if (!empty($orderData['billing_mobile'])) {
        $inputData['vnp_Bill_Mobile'] = $orderData['billing_mobile'];
    }
    if (!empty($orderData['billing_email'])) {
        $inputData['vnp_Bill_Email'] = $orderData['billing_email'];
    }
    if (!empty($orderData['billing_fullname'])) {
        $fullName = trim($orderData['billing_fullname']);
        $name = explode(' ', $fullName);
        $inputData['vnp_Bill_FirstName'] = array_shift($name);
        $inputData['vnp_Bill_LastName'] = implode(' ', $name);
    }
    if (!empty($orderData['billing_address'])) {
        $inputData['vnp_Bill_Address'] = $orderData['billing_address'];
    }
    if (!empty($orderData['billing_city'])) {
        $inputData['vnp_Bill_City'] = $orderData['billing_city'];
    }
    $inputData['vnp_Bill_Country'] = 'VN';
    
    // Thêm bank code nếu có
    if (!empty($vnp_BankCode)) {
        $inputData['vnp_BankCode'] = $vnp_BankCode;
    }
    
    // Sắp xếp dữ liệu theo key
    ksort($inputData);
    
    // Tạo query string và hash data
    $query = "";
    $hashdata = "";
    $i = 0;
    
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    
    // Tạo URL thanh toán
    $vnp_Url = VNPAY_URL . "?" . $query;
    
    // Tạo secure hash
    if (VNPAY_HASH_SECRET) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
    }
    
    return $vnp_Url;
}

/**
 * Xác thực response từ VNPay
 * 
 * @param array $vnpData Dữ liệu từ VNPay
 * @return bool
 */
function verifyVnpayResponse($vnpData) {
    $vnp_SecureHash = $vnpData['vnp_SecureHash'] ?? '';
    
    // Loại bỏ các tham số không cần thiết
    unset($vnpData['vnp_SecureHash']);
    unset($vnpData['vnp_SecureHashType']);
    
    // Sắp xếp dữ liệu
    ksort($vnpData);
    
    // Tạo hash data
    $hashdata = "";
    $i = 0;
    foreach ($vnpData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    // Tạo secure hash để so sánh
    $secureHash = hash_hmac('sha512', $hashdata, VNPAY_HASH_SECRET);
    
    return $secureHash === $vnp_SecureHash;
}

/**
 * Lấy mô tả response code từ VNPay
 * 
 * @param string $responseCode Mã response
 * @return string Mô tả
 */
function getVnpayResponseMessage($responseCode) {
    $messages = array(
        '00' => 'Giao dịch thành công',
        '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
        '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
        '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
        '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
        '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
        '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
        '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
        '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
        '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
        '75' => 'Ngân hàng thanh toán đang bảo trì.',
        '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
        '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)'
    );
    
    return $messages[$responseCode] ?? 'Lỗi không xác định';
}
?>



