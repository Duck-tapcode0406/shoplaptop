<%-- WebContent/views/khachhang/checkout/checkout.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %> <%-- Thêm thư viện format --%>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-cart-checkout.css">
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css"> <%-- Tái sử dụng .form-group --%>

<title>Thanh Toán - BookStore</title>

<jsp:include page="../layout/header.jsp" />

<script>
    // Script đơn giản cho thanh toán
    document.addEventListener('DOMContentLoaded', function() {
        // Có thể thêm validation hoặc logic khác ở đây nếu cần
    });
</script>

    <main class="container">
        <%-- Nút quay lại --%>
        <div style="margin-bottom: 1.5rem;">
            <a href="${baseURL}/gio-hang" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại giỏ hàng
            </a>
        </div>

        <%-- Các bước thanh toán --%>
        <div class="checkout-steps">
            <div class="step active"><i class="fa-solid fa-credit-card"></i> 1. Phương Thức Thanh Toán</div>
            <div class="step"><i class="fa-solid fa-check-double"></i> 2. Xác Nhận</div>
        </div>

         <%-- Hiển thị lỗi nếu có từ Servlet đặt hàng --%>
         <c:if test="${not empty requestScope.errorMessage}">
            <div class="error-message" style="margin-bottom: 1.5rem;">
                 <i class="fa-solid fa-circle-exclamation"></i> ${requestScope.errorMessage}
            </div>
         </c:if>

        <div class="checkout-container">

             <%-- SỬA ACTION FORM --%>
            <form action="${baseURL}/dat-hang" method="POST" class="checkout-form">

                <%-- Bước 1: Phương thức thanh toán --%>
                <h3>1. Phương Thức Thanh Toán</h3>
                <div class="form-grid">
                </div>
                
                <div class="payment-methods" style="width: 100%;">
                    <div class="method">
                        <input type="radio" id="payment-vnpay" name="paymentMethod" value="vnpay" checked>
                        <label for="payment-vnpay"><i class="fa-solid fa-qrcode"></i> Thanh toán online qua VNPay</label>
                        <p>Thanh toán qua cổng VNPay Demo - Hỗ trợ ATM, thẻ tín dụng, ví điện tử.</p>
                        <small style="color: #666; display: block; margin-top: 0.5rem; padding-left: 2.75rem;">
                            <i class="fa-solid fa-info-circle"></i> Bạn sẽ được chuyển đến cổng thanh toán VNPay để thanh toán.
                        </small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-checkout" style="width: 100%; margin-top: 1rem; font-size: 1.2rem; padding: 1rem;">
                    <i class="fa-solid fa-lock"></i> Xác Nhận & Thanh Toán <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <%-- Tóm tắt đơn hàng --%>
            <aside class="cart-summary order-summary">
                <h3>Đơn Hàng Của Bạn</h3>
                <%-- Lặp qua items trong giỏ hàng để hiển thị tóm tắt --%>
                <c:if test="${not empty sessionScope.cart && not empty sessionScope.cart.items}">
                    <c:forEach items="${sessionScope.cart.items}" var="entry">
                        <div class="summary-row" style="font-size: 0.9rem; margin-bottom: 0.5rem; align-items: flex-start;">
                            <span style="flex-grow: 1; margin-right: 10px;">
                                ${entry.value.sanPham.tenSanPham} <br>
                                <small>x ${entry.value.soLuong}</small>
                            </span>
                            <span><fmt:formatNumber value="${entry.value.tongTien}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</span>
                        </div>
                    </c:forEach>
                    <hr style="border-top: 1px dashed #ccc; margin: 1rem 0;">
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <span><fmt:formatNumber value="${sessionScope.cart.tamTinh}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</span>
                    </div>
                    <div class="summary-row total">
                        <span><strong>Tổng cộng</strong></span>
                        <span><strong><fmt:formatNumber value="${sessionScope.cart.tongTien}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</strong></span>
                    </div>
                    <%-- Đã xóa phần tích điểm --%>
                    <div style="margin-top: 0.5rem; padding: 0.75rem; background: #f8f9fa; border-radius: 5px; font-size: 0.9rem; color: #666;">
                        <i class="fa-solid fa-info-circle"></i> Số tiền thanh toán: <strong style="color: #d32f2f;"><fmt:formatNumber value="${sessionScope.cart.tongTien}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</strong>
                    </div>
                </c:if>
                 <c:if test="${empty sessionScope.cart || empty sessionScope.cart.items}">
                    <p>Giỏ hàng của bạn đang trống.</p>
                 </c:if>
            </aside>

        </div>

    </main>

<jsp:include page="../layout/footer.jsp" />