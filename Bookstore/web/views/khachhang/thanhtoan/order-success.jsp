<%-- WebContent/views/khachhang/checkout/order-success.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-cart-checkout.css">

<title>Đặt Hàng Thành Công - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">

        <%-- Thông báo thành công --%>
        <div style="text-align: center; background: var(--light-color); padding: 3rem; border: 1px solid #c3e6cb; border-radius: 8px;">

            <i class="fa-solid fa-check-circle" style="font-size: 5rem; color: #155724; margin-bottom: 1.5rem;"></i>

            <h2 style="font-size: 2.2rem; color: var(--primary-color); margin-bottom: 1rem;">Đặt Hàng Thành Công!</h2>

            <p style="font-size: 1.1rem; color: #555; margin-bottom: 1.5rem;">
                Cảm ơn bạn đã mua hàng tại BookStore. Đơn hàng của bạn
                <strong>#${requestScope.maDonHang}</strong>
                đã được tiếp nhận và đang chờ xử lý.
            </p>

            <p style="margin-bottom: 2rem;">
                Một email xác nhận chi tiết đơn hàng đã được gửi đến địa chỉ <strong>${sessionScope.user.email}</strong>.
                 Vui lòng kiểm tra hộp thư của bạn (kể cả thư mục Spam).
            </p>

            <div>
                 <%-- 
                   SỬA LỖI (URL): Link xem chi tiết đơn hàng (trỏ đến ChiTietDonHangServlet)
                 --%>
                <a href="${baseURL}/tai-khoan/chi-tiet-don-hang?id=${requestScope.maDonHang}" class="btn btn-secondary" style="margin-right: 1rem;">
                   <i class="fa-solid fa-receipt"></i> Xem Chi Tiết Đơn Hàng
                </a>
                <a href="${baseURL}/trang-chu" class="btn btn-primary">
                   <i class="fa-solid fa-house"></i> Quay Về Trang Chủ
                </a>
            </div>

        </div>

    </main>

<jsp:include page="../layout/footer.jsp" />