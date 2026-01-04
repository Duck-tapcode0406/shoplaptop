 
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-promo.css">

<title>Khuyến Mãi - BookStore</title>

<jsp:include page="layout/header.jsp" />

    <main class="container promo-page">
        <h2>Tổng Hợp Khuyến Mãi</h2>
        
        <div class="promo-list">
            <div class="promo-card">
                <div class="promo-icon">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div class="promo-details">
                    <h3>Giảm 20% cho Đơn hàng đầu tiên</h3>
                    <div class="promo-code">Mã: NEWUSER20</div>
                    <p class="promo-description">Áp dụng cho khách hàng mới đăng ký tài khoản và mua đơn hàng đầu tiên.</p>
                    <p class="promo-expiry">Hết hạn: 31/12/2025</p>
                </div>
                <div class="promo-actions">
                    <a href="${baseURL}/dang-ky" class="btn btn-primary">Đăng ký ngay</a>
                </div>
            </div>
            
            <%-- Dummy Promo 2 --%>
            <div class="promo-card">
                <div class="promo-icon">
                    <i class="fa-solid fa-shipping-fast"></i>
                </div>
                <div class="promo-details">
                    <h3>Miễn Phí Vận Chuyển</h3>
                    <div class="promo-code">Mã: FREESHIP</div>
                    <p class="promo-description">Áp dụng cho mọi đơn hàng từ 300.000 ₫ trở lên.</p>
                    <p class="promo-expiry">Hết hạn: 30/11/2025</p>
                </div>
                <div class="promo-actions">
                    <a href="${baseURL}/danh-sach-san-pham" class="btn btn-primary">Mua ngay</a>
                </div>
            </div>
            
        </div>
    </main>

<jsp:include page="layout/footer.jsp" />