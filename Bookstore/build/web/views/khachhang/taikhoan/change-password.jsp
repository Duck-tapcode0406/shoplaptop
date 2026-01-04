<%-- WebContent/views/khachhang/account/change-password.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-account.css">
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css">

<title>Đổi Mật Khẩu - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <h2 class="account-page-title">Tài Khoản Của Tôi</h2>
        
        <div class="account-layout">
            
            <%-- 👤 Menu sidebar tài khoản --%>
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
                    <li><a href="${baseURL}/tai-khoan/lich-su-don-hang"><i class="fa-solid fa-box-archive"></i> Lịch Sử Đơn Hàng</a></li>
                    <li><a href="${baseURL}/tai-khoan/thay-doi-mat-khau" class="active"><i class="fa-solid fa-lock"></i> Đổi Mật Khẩu</a></li>
                    <li><a href="${baseURL}/dang-xuat"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a></li>
                </ul>
            </aside>
            
            <%-- Nội dung chính --%>
            <section class="account-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="margin-bottom: 0;">Đổi Mật Khẩu</h3>
                    <a href="${baseURL}/tai-khoan/ho-so" style="font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <p>Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác.</p>
                <hr>

                <%-- Hiển thị thông báo (từ ThayDoiMatKhauServlet) --%>
                <c:if test="${not empty requestScope.error}">
                    <div class="error-message">
                        ${requestScope.error}
                    </div>
                </c:if>
                <c:if test="${not empty requestScope.success}">
                    <div class="error-message" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
                        ${requestScope.success}
                    </div>
                </c:if>
                
                <%-- 
                  SỬA LỖI (URL): Đổi action
                  thành /tai-khoan/thay-doi-mat-khau (để khớp với ThayDoiMatKhauServlet)
                --%>
                <form action="${baseURL}/tai-khoan/thay-doi-mat-khau" method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="currentPassword">Mật khẩu hiện tại *</label>
                        <input type="password" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Mật khẩu mới *</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Xác nhận mật khẩu mới *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Lưu Mật Khẩu</button>
                </form>
            </section>
            
        </div>
    </main>

<jsp:include page="../layout/footer.jsp" />