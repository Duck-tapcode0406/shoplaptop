<%-- WebContent/views/khachhang/account/order-history.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-account.css">

<title>Lịch Sử Đơn Hàng - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <h2 class="account-page-title">Tài Khoản Của Tôi</h2>
        
        <div class="account-layout">
            
            <%-- Menu sidebar (Copy từ profile.jsp) --%>
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
                      SỬA LỖI (URL): Đồng bộ URL với các Servlet
                    --%>
                    <li><a href="${baseURL}/tai-khoan/ho-so"><i class="fa-solid fa-user-edit"></i> Hồ Sơ Cá Nhân</a></li>
                    <li><a href="${baseURL}/tai-khoan/lich-su-don-hang" class="active"><i class="fa-solid fa-receipt"></i> Lịch Sử Đơn Hàng</a></li>
                    <li><a href="${baseURL}/tai-khoan/thay-doi-mat-khau"><i class="fa-solid fa-lock"></i> Đổi Mật Khẩu</a></li>
                    <li><a href="${baseURL}/dang-xuat"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a></li>
                </ul>
            </aside>
            
            <%-- Nội dung chính --%>
            <section class="account-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="margin-bottom: 0;">Lịch Sử Đơn Hàng</h3>
                    <a href="${baseURL}/trang-chu" style="font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <p>Danh sách các đơn hàng bạn đã đặt.</p>
                <hr>
                
                <c:if test="${not empty requestScope.errorMessage}">
                    <div class="error-message">
                        ${requestScope.errorMessage}
                    </div>
                </c:if>

                <div class="book-library-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                    <c:forEach items="${requestScope.listSachDaMua}" var="sach">
                        <div class="product-card">
                            <a href="${baseURL}/doc-sach?id=${sach.maSanPham}" class="product-image-link">
                                <c:set var="imageName" value="${sach.hinhAnh}" />
                                <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                                    <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                                </c:if>
                                <img src="${baseURL}/assets/images/products/${imageName}" 
                                     alt="${sach.tenSanPham}" class="product-image"
                                     onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                            </a>
                            <a href="${baseURL}/doc-sach?id=${sach.maSanPham}" class="product-title">${sach.tenSanPham}</a>
                            <p class="product-author">${sach.tacGia.hoVaTen}</p>
                            <a href="${baseURL}/doc-sach?id=${sach.maSanPham}" class="btn btn-primary btn-add-to-cart" style="text-decoration: none; text-align: center;">
                                <i class="fa-solid fa-book-open"></i> Đọc Ngay
                            </a>
                        </div>
                    </c:forEach>
                    
                    <c:if test="${empty requestScope.listSachDaMua and empty requestScope.errorMessage}">
                        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                            <i class="fa-solid fa-book-open" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>Bạn chưa có sách nào trong kho. Hãy mua sách để bắt đầu đọc!</p>
                            <a href="${baseURL}/danh-sach-san-pham" class="btn btn-primary" style="margin-top: 1rem;">Khám Phá Sách</a>
                        </div>
                    </c:if>
                </div>
                
            </section>
            
        </div>
    </main>

<jsp:include page="../layout/footer.jsp" />