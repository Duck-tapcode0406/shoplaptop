<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-cart-checkout.css">

<title>Giỏ Hàng - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container cart-page">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">Giỏ Hàng Của Bạn</h2>
            <a href="${baseURL}/trang-chu" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <%-- Hiển thị thông báo --%>
        <c:if test="${not empty sessionScope.successMessage}">
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                ${sessionScope.successMessage}
            </div>
            <c:remove var="successMessage" scope="session"/>
        </c:if>
        <c:if test="${not empty sessionScope.errorMessage}">
            <div class="error-message" style="margin-bottom: 1.5rem;">
                 <i class="fa-solid fa-circle-exclamation"></i> ${sessionScope.errorMessage}
            </div>
            <c:remove var="errorMessage" scope="session"/>
        </c:if>
        
        <div class="cart-layout">
            <c:choose>
                <c:when test="${empty sessionScope.cart || empty sessionScope.cart.items}">
                    <div class="cart-empty">
                        <i class="fa-solid fa-cart-arrow-down"></i>
                        <p>Giỏ hàng của bạn đang trống.</p>
                        <a href="${baseURL}/danh-sach-san-pham" class="btn btn-primary">Tiếp tục mua sắm</a>
                    </div>
                </c:when>
                
                <c:otherwise>
                    <%-- Cột bên trái: Danh sách sản phẩm --%>
                    <div class="cart-items-list">
                        <%-- Lặp qua các sản phẩm trong giỏ --%>
                        <c:forEach items="${sessionScope.cart.items}" var="entry">
                            <c:set var="item" value="${entry.value}"/>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <c:set var="imageName" value="${item.sanPham.hinhAnh}" />
                                    <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                                        <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                                    </c:if>
                                    <img src="${baseURL}/assets/images/products/${imageName}" 
                                         alt="${item.sanPham.tenSanPham}"
                                         onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                                </div>
                                <div class="cart-item-info">
                                    <a href="${baseURL}/chi-tiet-san-pham?id=${item.sanPham.maSanPham}" class="item-name">${item.sanPham.tenSanPham}</a>
                                    <p class="item-author">${item.sanPham.tacGia.hoVaTen}</p>
                                    <p class="item-price-unit">
                                        <fmt:formatNumber value="${item.sanPham.giaBan}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫
                                    </p>
                                </div>
                                <div class="cart-item-quantity">
                                    <%-- Form Cập nhật số lượng --%>
                                    <form action="${baseURL}/gio-hang" method="POST" class="update-qty-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="productId" value="${item.sanPham.maSanPham}">
                                        <input type="number" name="quantity" value="${item.soLuong}" min="1" max="${item.sanPham.soLuong}">
                                        <button type="submit" class="btn-update-qty" aria-label="Cập nhật"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                </div>
                                <div class="cart-item-price">
                                    <fmt:formatNumber value="${item.tongTien}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫
                                </div>
                                <div class="cart-item-remove">
                                    <%-- Form Xóa --%>
                                    <form action="${baseURL}/gio-hang" method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="productId" value="${item.sanPham.maSanPham}">
                                        <button type="submit" class="btn-remove" aria-label="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </div>
                            </div>
                        </c:forEach>
                    </div>

                    <%-- Cột bên phải: Tổng tiền --%>
                    <aside class="cart-summary">
                        <h4>Tổng Kết Đơn Hàng</h4>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Tạm tính</span>
                                <span><fmt:formatNumber value="${sessionScope.cart.tamTinh}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</span>
                            </div>
                            <div class="summary-row total">
                                <span>Tổng cộng</span>
                                <span><fmt:formatNumber value="${sessionScope.cart.tongTien}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</span>
                            </div>
                            
                            <%-- Đã xóa phần tích điểm --%>

                        <a href="${baseURL}/thanh-toan" class="btn btn-primary btn-checkout">
                           <i class="fa-solid fa-credit-card"></i> Tiến Hành Thanh Toán
                        </a>
                    </aside>
                </c:otherwise>
            </c:choose>
        </div>
    </main>

<jsp:include page="../layout/footer.jsp" />