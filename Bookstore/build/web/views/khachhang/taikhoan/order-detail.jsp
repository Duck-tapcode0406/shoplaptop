<%-- WebContent/views/khachhang/account/order-detail.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<%-- SỬA LỖI (PATH): Cập nhật đường dẫn CSS --%>
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-account.css">
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-cart-checkout.css">

<title>Chi Tiết Đơn Hàng #${donHang.maDonHang} - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <h2 class="account-page-title">Tài Khoản Của Tôi</h2>

        <div class="account-layout">
            <%-- Sidebar --%>
            <aside class="account-sidebar">
                <div class="user-avatar">
                    <%-- 
                      SỬA LỖI (IMAGE): Đổi /images/avatars/ 
                      thành /assets/images/avatars/
                    --%>
                    <img src="${baseURL}/assets/images/avatars/${not empty sessionScope.user.duongDanAnh ? sessionScope.user.duongDanAnh : 'avatar-default.png'}" 
                         alt="Avatar"
                         onerror="this.onerror=null; this.src='${baseURL}/assets/images/avatars/avatar-default.png';">
                    <h5>${sessionScope.user.hoVaTen}</h5>
                </div>
                <ul class="account-nav">
                     <%-- 
                       SỬA LỖI (URL): Đồng bộ tất cả URL với các Servlet
                     --%>
                    <li><a href="${baseURL}/tai-khoan/ho-so"><i class="fa-solid fa-user-edit"></i> Hồ Sơ Cá Nhân</a></li>
                    <li><a href="${baseURL}/tai-khoan/lich-su-don-hang" class="active"><i class="fa-solid fa-box-archive"></i> Lịch Sử Đơn Hàng</a></li>
                    <li><a href="${baseURL}/tai-khoan/thay-doi-mat-khau"><i class="fa-solid fa-lock"></i> Đổi Mật Khẩu</a></li>
                    <li><a href="${baseURL}/dang-xuat"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a></li>
                </ul>
            </aside>
            
            <%-- Nội dung chính --%>
            <section class="account-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                     <h3 style="margin-bottom: 0;">Chi Tiết Đơn Hàng #${donHang.maDonHang}</h3>
                     <a href="${baseURL}/tai-khoan/lich-su-don-hang" style="font-size: 0.9rem;"><i class="fa-solid fa-arrow-left"></i> Quay lại Lịch sử</a>
                </div>
                 
                <p>Đặt lúc: <fmt:formatDate value="${donHang.ngayDatHang}" pattern="HH:mm, dd/MM/yyyy"/> | 
                   Trạng thái: 
                   <c:choose>
                       <c:when test="${donHang.trangThai == 'Hoàn tất'}"><span class="order-status completed">${donHang.trangThai}</span></c:when>
                       <c:when test="${donHang.trangThai == 'Đang giao'}"><span class="order-status shipped">${donHang.trangThai}</span></c:when>
                                    <c:when test="${donHang.trangThai == 'Hủy' or donHang.trangThai == 'Đã hủy'}"><span class="order-status cancelled">${donHang.trangThai}</span></c:when>
                       <c:otherwise><span class="order-status pending">${donHang.trangThai}</span></c:otherwise>
                   </c:choose>
                </p>
                <hr>

                <%-- Hiển thị thông báo (ví dụ: hủy đơn thành công/thất bại) --%>
                <c:if test="${not empty sessionScope.errorMessage}">
                    <div class="error-message"> ${sessionScope.errorMessage} </div>
                    <c:remove var="errorMessage" scope="session" />
                </c:if>
                <c:if test="${not empty sessionScope.successMessage}">
                    <div class="error-message" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
                        ${sessionScope.successMessage}
                    </div>
                    <c:remove var="successMessage" scope="session" />
                </c:if>

                <c:if test="${not empty donHang}">
                    <div class="checkout-layout">
                        <%-- Cột bên trái: Thông tin giao hàng và Danh sách SP --%>
                        <div class="checkout-details">
                            
                            <h4>Thông tin nhận hàng</h4>
                            <div class="delivery-info-box">
                                <strong>${sessionScope.user.hoVaTen}</strong>
                                <p>${sessionScope.user.soDienThoai}</p>
                                <p>${donHang.diaChiNhanHang}</p>
                            </div>

                            <h4>Danh sách sản phẩm</h4>
                            <div class="cart-items-list">
                                <c:forEach items="${requestScope.listChiTiet}" var="item">
                                    <div class="cart-item">
                                        <div class="cart-item-image">
                                             <%-- 
                                               SỬA LỖI (IMAGE): Đổi /images/products/ 
                                               thành /assets/images/products/
                                             --%>
                                             <c:choose>
                                                 <c:choose>
                                                     <c:when test="${fn:startsWith(item.sanPham.hinhAnh, '/') or fn:startsWith(item.sanPham.hinhAnh, 'assets/')}">
                                                         <img src="${baseURL}${fn:startsWith(item.sanPham.hinhAnh, '/') ? '' : '/'}${item.sanPham.hinhAnh}" 
                                                              alt="${item.sanPham.tenSanPham}"
                                                              onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                                                     </c:when>
                                                     <c:otherwise>
                                                         <c:set var="imageName" value="${item.sanPham.hinhAnh}" />
                                                         <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                                                             <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                                                         </c:if>
                                                         <img src="${baseURL}/assets/images/products/${imageName}" 
                                                              alt="${item.sanPham.tenSanPham}"
                                                              onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                                                     </c:otherwise>
                                                 </c:choose>
                                             </c:choose>
                                        </div>
                                        <div class="cart-item-info">
                                            <p class="item-name">${item.sanPham.tenSanPham}</p>
                                            <p class="item-qty">Số lượng: ${item.soLuong}</p>
                                            <p class="item-price" style="color: #666; font-size: 0.9rem;">
                                                Đơn giá: <fmt:formatNumber value="${item.giaBan}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫
                                            </p>
                                        </div>
                                        <div class="cart-item-price">
                                            <strong><fmt:formatNumber value="${item.giaBan * item.soLuong}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</strong>
                                        </div>
                                    </div>
                                </c:forEach>
                            </div>
                        </div>

                        <%-- Cột bên phải: Tổng kết và Nút Hủy --%>
                        <aside class="checkout-summary">
                            <h4>Tổng Kết Đơn Hàng</h4>
                            <div class="summary-details">
                                <div class="summary-row">
                                    <span>Tạm tính</span>
                                    <span><fmt:formatNumber value="${donHang.soTienDaThanhToan + donHang.soTienConThieu}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</span>
                                </div>
                                <div class="summary-row">
                                    <span>Phí giao hàng</span>
                                    <span>(Cần lấy từ DB)</span>
                                </div>
                                <div class="summary-row">
                                    <span>Giảm giá</span>
                                    <span>(Cần lấy từ DB)</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Tổng cộng</span>
                                    <span><fmt:formatNumber value="${donHang.soTienDaThanhToan + donHang.soTienConThieu}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫</span>
                                </div>
                            </div>
                            
                            <%-- Nút Hủy Đơn Hàng --%>
                            <c:if test="${donHang.trangThai == 'Chờ duyệt'}">
                                <form action="${baseURL}/tai-khoan/huy-don-hang" method="POST" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?\n\nSau khi hủy, số lượng sản phẩm sẽ được hoàn lại vào kho.');">
                                    <input type="hidden" name="orderId" value="${donHang.maDonHang}">
                                    <button type="submit" class="btn btn-danger btn-checkout" style="background-color: #dc3545; width: 100%; margin-top: 1rem;">
                                        <i class="fa-solid fa-ban"></i> Hủy Đơn Hàng
                                    </button>
                                </form>
                            </c:if>
                            
                            <%-- Thông báo khi đơn hàng đã hủy hoặc không thể hủy --%>
                            <c:if test="${donHang.trangThai == 'Hủy' or donHang.trangThai == 'Đã hủy'}">
                                <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-top: 1rem; text-align: center;">
                                    <i class="fa-solid fa-info-circle"></i> Đơn hàng này đã bị hủy.
                                </div>
                            </c:if>
                            
                            <c:if test="${donHang.trangThai == 'Đang giao' or donHang.trangThai == 'Hoàn tất'}">
                                <div style="background-color: #d1ecf1; color: #0c5460; padding: 1rem; border-radius: 4px; margin-top: 1rem; text-align: center;">
                                    <i class="fa-solid fa-info-circle"></i> Đơn hàng đã được xử lý, không thể hủy.
                                </div>
                            </c:if>

                        </aside>
                    </div>

                </c:if>
                <c:if test="${empty donHang}">
                    <p>Không tìm thấy thông tin đơn hàng.</p>
                </c:if>

            </section>
        </div>
    </main>

<jsp:include page="../layout/footer.jsp" />