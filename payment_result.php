<?php
/**
 * Payment Result Page
 * Hiển thị kết quả thanh toán
 */

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

// Lấy kết quả từ session
$payment_result = $_SESSION['payment_result'] ?? null;

// Nếu không có kết quả, redirect về trang chủ
if (!$payment_result) {
    redirect('index.php');
}

// Xóa kết quả khỏi session sau khi đọc
unset($_SESSION['payment_result']);

$status = $payment_result['status'];
$message = $payment_result['message'];
$order_id = $payment_result['order_id'];
$txn_ref = $payment_result['txn_ref'];
$transaction_no = $payment_result['transaction_no'];
$amount = $payment_result['amount'];
$bank_code = $payment_result['bank_code'];
$pay_date = $payment_result['pay_date'];

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Thanh Toán - DNQDH Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .payment-result-container {
            max-width: 600px;
            margin: var(--space-5xl) auto;
            padding: var(--space-md);
        }

        .result-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            text-align: center;
        }

        .result-header {
            padding: var(--space-3xl);
        }

        .result-header.success {
            background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            color: white;
        }

        .result-header.failed {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .result-header.invalid {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }

        .result-header.error {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
        }

        .result-icon {
            font-size: 80px;
            margin-bottom: var(--space-lg);
            animation: bounceIn 0.6s ease-out;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        .result-title {
            font-size: var(--fs-h2);
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-sm);
        }

        .result-message {
            font-size: var(--fs-large);
            opacity: 0.9;
        }

        .result-body {
            padding: var(--space-2xl);
        }

        .transaction-details {
            background: var(--bg-primary);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            margin-bottom: var(--space-2xl);
            text-align: left;
        }

        .transaction-details h4 {
            margin-bottom: var(--space-lg);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: var(--space-sm) 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .detail-value {
            font-weight: var(--fw-bold);
            color: var(--text-primary);
        }

        .detail-value.amount {
            color: var(--primary);
            font-size: var(--fs-h4);
        }

        .result-actions {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
        }

        .result-actions .btn {
            min-width: 180px;
        }

        .vnpay-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            margin-top: var(--space-lg);
            padding-top: var(--space-lg);
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .vnpay-logo img {
            height: 24px;
        }

        @media (max-width: 768px) {
            .result-actions {
                flex-direction: column;
            }

            .result-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="payment-result-container">
        <!-- Back Button -->
        <a href="index.php" class="back-button" style="margin-bottom: var(--space-lg);">
            <i class="fas fa-arrow-left"></i>
            Quay lại trang chủ
        </a>
        
        <div class="result-card">
            <div class="result-header <?php echo $status; ?>">
                <div class="result-icon">
                    <?php if ($status === 'success'): ?>
                        <i class="fas fa-check-circle"></i>
                    <?php elseif ($status === 'failed'): ?>
                        <i class="fas fa-times-circle"></i>
                    <?php elseif ($status === 'invalid'): ?>
                        <i class="fas fa-exclamation-triangle"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php endif; ?>
                </div>
                <h1 class="result-title">
                    <?php if ($status === 'success'): ?>
                        Thanh Toán Thành Công!
                    <?php elseif ($status === 'failed'): ?>
                        Thanh Toán Thất Bại
                    <?php elseif ($status === 'invalid'): ?>
                        Giao Dịch Không Hợp Lệ
                    <?php else: ?>
                        Có Lỗi Xảy Ra
                    <?php endif; ?>
                </h1>
                <p class="result-message"><?php echo htmlspecialchars($message); ?></p>
            </div>

            <div class="result-body">
                <?php if ($status === 'success' || !empty($transaction_no)): ?>
                <div class="transaction-details">
                    <h4><i class="fas fa-receipt"></i> Chi Tiết Giao Dịch</h4>
                    
                    <?php if ($order_id): ?>
                    <div class="detail-row">
                        <span class="detail-label">Mã đơn hàng</span>
                        <span class="detail-value">#<?php echo $order_id; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($transaction_no): ?>
                    <div class="detail-row">
                        <span class="detail-label">Mã giao dịch VNPay</span>
                        <span class="detail-value"><?php echo htmlspecialchars($transaction_no); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($txn_ref): ?>
                    <div class="detail-row">
                        <span class="detail-label">Mã tham chiếu</span>
                        <span class="detail-value"><?php echo htmlspecialchars($txn_ref); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($amount > 0): ?>
                    <div class="detail-row">
                        <span class="detail-label">Số tiền</span>
                        <span class="detail-value amount"><?php echo number_format($amount, 0, ',', '.'); ?> ₫</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($bank_code): ?>
                    <div class="detail-row">
                        <span class="detail-label">Ngân hàng</span>
                        <span class="detail-value"><?php echo htmlspecialchars($bank_code); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($pay_date): ?>
                    <div class="detail-row">
                        <span class="detail-label">Thời gian</span>
                        <span class="detail-value"><?php 
                            $formatted_date = DateTime::createFromFormat('YmdHis', $pay_date);
                            echo $formatted_date ? $formatted_date->format('d/m/Y H:i:s') : $pay_date; 
                        ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="result-actions">
                    <?php if ($status === 'success'): ?>
                        <a href="history.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-receipt"></i> Xem Đơn Hàng
                        </a>
                        <a href="index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Tiếp Tục Mua Sắm
                        </a>
                    <?php else: ?>
                        <a href="checkout.php?step=3" class="btn btn-primary btn-lg">
                            <i class="fas fa-redo"></i> Thử Lại
                        </a>
                        <a href="cart.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Về Giỏ Hàng
                        </a>
                    <?php endif; ?>
                </div>

                <div class="vnpay-logo">
                    <span>Thanh toán qua</span>
                    <img src="https://sandbox.vnpayment.vn/paymentv2/images/logo.svg" alt="VNPay" onerror="this.style.display='none'">
                    <span style="font-weight: bold; color: #0066b3;">VNPAY</span>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

