<?php
/**
 * VNPay Create Payment
 * Tạo URL thanh toán VNPay
 */

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/vnpay_config.php';

// Require login
requireLogin();

// Validate CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handleError("Phương thức không hợp lệ.", 405);
}
validateCSRFPost();

$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng từ giỏ hàng
$cart_sql = "SELECT o.id AS order_id, SUM(od.quantity * od.price) AS total_amount
             FROM `order` o
             JOIN `order_details` od ON o.id = od.order_id
             WHERE o.customer_id = ? AND od.status = 'pending'
             GROUP BY o.id";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param('i', $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

if ($cart_result->num_rows === 0) {
    redirect('cart.php?error=empty');
}

$cart_data = $cart_result->fetch_assoc();
$order_id = $cart_data['order_id'];
$subtotal = $cart_data['total_amount'];

// Tính thuế VAT 10%
$tax = round($subtotal * 0.1);
$total_amount = $subtotal + $tax;

// Lấy thông tin user
$user_sql = "SELECT username, email, phone, familyname, firstname FROM user WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Lấy thông tin shipping từ form
$shipping_name = $_POST['shipping_name'] ?? ($user_data['familyname'] . ' ' . $user_data['firstname']);
$shipping_phone = $_POST['shipping_phone'] ?? $user_data['phone'];
$shipping_email = $_POST['shipping_email'] ?? $user_data['email'];
$shipping_address = $_POST['shipping_address'] ?? '';
$shipping_city = $_POST['shipping_city'] ?? '';
$bank_code = $_POST['bank_code'] ?? '';

// Tạo mã giao dịch unique (order_id + timestamp)
$vnp_TxnRef = $order_id . '_' . time();

// Lưu thông tin thanh toán vào session để xử lý sau
$_SESSION['vnpay_order'] = [
    'order_id' => $order_id,
    'txn_ref' => $vnp_TxnRef,
    'amount' => $total_amount,
    'shipping_name' => $shipping_name,
    'shipping_phone' => $shipping_phone,
    'shipping_email' => $shipping_email,
    'shipping_address' => $shipping_address,
    'shipping_city' => $shipping_city
];

// Tạo dữ liệu đơn hàng cho VNPay
$orderData = [
    'order_id' => $vnp_TxnRef,
    'order_desc' => 'Thanh toan don hang #' . $order_id . ' tai DNQDH Shop',
    'order_type' => 'billpayment',
    'amount' => $total_amount,
    'language' => 'vn',
    'bank_code' => $bank_code,
    'billing_mobile' => $shipping_phone,
    'billing_email' => $shipping_email,
    'billing_fullname' => $shipping_name,
    'billing_address' => $shipping_address,
    'billing_city' => $shipping_city
];

// Tạo URL thanh toán
$vnpay_url = createVnpayUrl($orderData);

// Redirect đến VNPay
header('Location: ' . $vnpay_url);
exit();
?>

