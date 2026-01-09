<?php
/**
 * VNPay Return URL
 * Xử lý kết quả thanh toán từ VNPay
 */

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/vnpay_config.php';

$vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
$vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
$vnp_Amount = $_GET['vnp_Amount'] ?? 0;
$vnp_OrderInfo = $_GET['vnp_OrderInfo'] ?? '';
$vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
$vnp_BankCode = $_GET['vnp_BankCode'] ?? '';
$vnp_PayDate = $_GET['vnp_PayDate'] ?? '';

// Xác thực response từ VNPay
$isValidSignature = verifyVnpayResponse($_GET);

// Lấy thông tin đơn hàng từ session
$vnpay_order = $_SESSION['vnpay_order'] ?? null;

$payment_status = 'failed';
$message = '';
$order_id = null;

if ($isValidSignature) {
    if ($vnp_ResponseCode === '00') {
        // Thanh toán thành công
        $payment_status = 'success';
        $message = 'Thanh toán thành công!';
        
        // Lấy order_id từ txn_ref (format: order_id_timestamp)
        $txn_parts = explode('_', $vnp_TxnRef);
        $order_id = intval($txn_parts[0]);
        
        if ($order_id > 0) {
            try {
                $conn->begin_transaction();
                
                // Cập nhật trạng thái order_details thành 'paid'
                $update_status_sql = "UPDATE order_details SET status = 'paid' WHERE order_id = ? AND status = 'pending'";
                $update_stmt = $conn->prepare($update_status_sql);
                $update_stmt->bind_param('i', $order_id);
                $update_stmt->execute();
                
                // Lưu thông tin thanh toán vào bảng payments (nếu có)
                $check_payments_table = $conn->query("SHOW TABLES LIKE 'payments'");
                if ($check_payments_table && $check_payments_table->num_rows > 0) {
                    $insert_payment_sql = "INSERT INTO payments (order_id, transaction_no, bank_code, amount, payment_date, status, payment_method) 
                                           VALUES (?, ?, ?, ?, ?, 'success', 'vnpay')";
                    $payment_stmt = $conn->prepare($insert_payment_sql);
                    $amount = $vnp_Amount / 100; // VNPay trả về số tiền * 100
                    $pay_date = date('Y-m-d H:i:s', strtotime($vnp_PayDate));
                    $payment_stmt->bind_param('issds', $order_id, $vnp_TransactionNo, $vnp_BankCode, $amount, $pay_date);
                    $payment_stmt->execute();
                }
                
                // Cập nhật tồn kho (nếu có bảng receipt_details)
                $check_receipt_table = $conn->query("SHOW TABLES LIKE 'receipt_details'");
                if ($check_receipt_table && $check_receipt_table->num_rows > 0) {
                    $order_items_sql = "SELECT product_id, quantity FROM order_details WHERE order_id = ?";
                    $items_stmt = $conn->prepare($order_items_sql);
                    $items_stmt->bind_param('i', $order_id);
                    $items_stmt->execute();
                    $items_result = $items_stmt->get_result();
                    
                    while ($item = $items_result->fetch_assoc()) {
                        $update_stock_sql = "UPDATE receipt_details SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?";
                        $stock_stmt = $conn->prepare($update_stock_sql);
                        $stock_stmt->bind_param('iii', $item['quantity'], $item['product_id'], $item['quantity']);
                        $stock_stmt->execute();
                    }
                }
                
                $conn->commit();
                
                // Xóa order_id khỏi session để tạo đơn hàng mới
                unset($_SESSION['order_id']);
                unset($_SESSION['vnpay_order']);
                
            } catch (Exception $e) {
                $conn->rollback();
                error_log("VNPay payment processing error: " . $e->getMessage());
                $payment_status = 'error';
                $message = 'Thanh toán thành công nhưng có lỗi khi cập nhật đơn hàng. Vui lòng liên hệ hỗ trợ.';
            }
        }
    } else {
        // Thanh toán thất bại
        $payment_status = 'failed';
        $message = getVnpayResponseMessage($vnp_ResponseCode);
    }
} else {
    $payment_status = 'invalid';
    $message = 'Chữ ký không hợp lệ. Giao dịch có thể bị giả mạo.';
}

// Lưu kết quả vào session để hiển thị
$_SESSION['payment_result'] = [
    'status' => $payment_status,
    'message' => $message,
    'order_id' => $order_id,
    'txn_ref' => $vnp_TxnRef,
    'transaction_no' => $vnp_TransactionNo,
    'amount' => $vnp_Amount / 100,
    'bank_code' => $vnp_BankCode,
    'pay_date' => $vnp_PayDate
];

// Redirect đến trang kết quả
header('Location: payment_result.php');
exit();
?>



