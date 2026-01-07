<?php
/**
 * VNPay IPN (Instant Payment Notification)
 * Xử lý callback từ VNPay server-to-server
 * 
 * URL này cần được cấu hình trên VNPay Merchant Portal
 * Ví dụ: http://yourdomain.com/vnpay_ipn.php
 */

require_once 'includes/db.php';
require_once 'includes/vnpay_config.php';

// Log IPN request for debugging
error_log("VNPay IPN received: " . json_encode($_GET));

// Response codes
$returnData = array();

try {
    // Lấy dữ liệu từ VNPay
    $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
    $inputData = array();
    
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }
    
    // Xác thực chữ ký
    unset($inputData['vnp_SecureHash']);
    unset($inputData['vnp_SecureHashType']);
    ksort($inputData);
    
    $hashData = "";
    $i = 0;
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    $secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);
    
    // Lấy thông tin giao dịch
    $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
    $vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100; // VNPay trả về số tiền * 100
    $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
    $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
    $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
    $vnp_BankCode = $inputData['vnp_BankCode'] ?? '';
    $vnp_PayDate = $inputData['vnp_PayDate'] ?? '';
    
    // Lấy order_id từ txn_ref (format: order_id_timestamp)
    $txn_parts = explode('_', $vnp_TxnRef);
    $order_id = intval($txn_parts[0]);
    
    // Kiểm tra checksum
    if ($secureHash !== $vnp_SecureHash) {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid Checksum';
    } else {
        // Kiểm tra đơn hàng tồn tại
        $order_check = $conn->prepare("SELECT id FROM `order` WHERE id = ?");
        $order_check->bind_param('i', $order_id);
        $order_check->execute();
        $order_result = $order_check->get_result();
        
        if ($order_result->num_rows === 0) {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        } else {
            // Kiểm tra số tiền
            $amount_check = $conn->prepare("SELECT SUM(od.quantity * od.price) as total FROM order_details od WHERE od.order_id = ?");
            $amount_check->bind_param('i', $order_id);
            $amount_check->execute();
            $amount_result = $amount_check->get_result();
            $order_amount = $amount_result->fetch_assoc()['total'];
            $order_amount_with_tax = $order_amount + round($order_amount * 0.1); // + VAT 10%
            
            if ($order_amount_with_tax != $vnp_Amount) {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'Invalid Amount';
            } else {
                // Kiểm tra trạng thái đơn hàng đã được xử lý chưa
                $status_check = $conn->prepare("SELECT status FROM order_details WHERE order_id = ? LIMIT 1");
                $status_check->bind_param('i', $order_id);
                $status_check->execute();
                $status_result = $status_check->get_result();
                $current_status = $status_result->fetch_assoc()['status'];
                
                if ($current_status === 'paid') {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                } else {
                    // Xử lý thanh toán
                    if ($vnp_ResponseCode === '00' && $vnp_TransactionStatus === '00') {
                        // Thanh toán thành công
                        $conn->begin_transaction();
                        
                        try {
                            // Cập nhật trạng thái đơn hàng
                            $update_order = $conn->prepare("UPDATE order_details SET status = 'paid' WHERE order_id = ? AND status = 'pending'");
                            $update_order->bind_param('i', $order_id);
                            $update_order->execute();
                            
                            // Lưu thông tin thanh toán
                            $check_payments = $conn->query("SHOW TABLES LIKE 'payments'");
                            if ($check_payments && $check_payments->num_rows > 0) {
                                $insert_payment = $conn->prepare("INSERT INTO payments (order_id, transaction_no, bank_code, amount, payment_date, status, payment_method, response_code, txn_ref, ip_address) VALUES (?, ?, ?, ?, ?, 'success', 'vnpay', ?, ?, ?)");
                                $pay_date = date('Y-m-d H:i:s', strtotime($vnp_PayDate));
                                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                                $insert_payment->bind_param('issdssss', $order_id, $vnp_TransactionNo, $vnp_BankCode, $vnp_Amount, $pay_date, $vnp_ResponseCode, $vnp_TxnRef, $ip_address);
                                $insert_payment->execute();
                            }
                            
                            // Cập nhật tồn kho
                            $check_receipt = $conn->query("SHOW TABLES LIKE 'receipt_details'");
                            if ($check_receipt && $check_receipt->num_rows > 0) {
                                $items_query = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = ?");
                                $items_query->bind_param('i', $order_id);
                                $items_query->execute();
                                $items_result = $items_query->get_result();
                                
                                while ($item = $items_result->fetch_assoc()) {
                                    $update_stock = $conn->prepare("UPDATE receipt_details SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?");
                                    $update_stock->bind_param('iii', $item['quantity'], $item['product_id'], $item['quantity']);
                                    $update_stock->execute();
                                }
                            }
                            
                            $conn->commit();
                            
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                            
                        } catch (Exception $e) {
                            $conn->rollback();
                            error_log("VNPay IPN processing error: " . $e->getMessage());
                            $returnData['RspCode'] = '99';
                            $returnData['Message'] = 'Unknown error';
                        }
                    } else {
                        // Thanh toán thất bại - ghi log
                        $check_payments = $conn->query("SHOW TABLES LIKE 'payments'");
                        if ($check_payments && $check_payments->num_rows > 0) {
                            $insert_payment = $conn->prepare("INSERT INTO payments (order_id, transaction_no, bank_code, amount, payment_date, status, payment_method, response_code, txn_ref, ip_address, response_message) VALUES (?, ?, ?, ?, NOW(), 'failed', 'vnpay', ?, ?, ?, ?)");
                            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
                            $response_message = getVnpayResponseMessage($vnp_ResponseCode);
                            $insert_payment->bind_param('issdssss', $order_id, $vnp_TransactionNo, $vnp_BankCode, $vnp_Amount, $vnp_ResponseCode, $vnp_TxnRef, $ip_address, $response_message);
                            $insert_payment->execute();
                        }
                        
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Success';
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("VNPay IPN exception: " . $e->getMessage());
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknown error';
}

// Trả về response cho VNPay
header('Content-Type: application/json');
echo json_encode($returnData);
exit();
?>

