<%-- WebContent/views/khachhang/auth/reset-password.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css">

<title>Tạo Mật Khẩu Mới - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <div class="auth-container">
            <%-- Nút quay lại --%>
            <div style="margin-bottom: 1rem; text-align: center;">
                <a href="${baseURL}/dang-nhap" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        
            <div class="auth-form-box">
                <%-- 
                  SỬA LỖI (URL): Đổi action từ /reset-password 
                  thành /dat-lai-mat-khau (để khớp với DatLaiMatKhauServlet)
                --%>
                <form action="${baseURL}/dat-lai-mat-khau" method="POST">
                    <h2>Tạo Mật Khẩu Mới</h2>
                    <p style="text-align: center; margin-bottom: 1.5rem; color: #555;">
                        Vui lòng nhập mật khẩu mới cho tài khoản của bạn.
                    </p>
                    
                    <c:if test="${not empty requestScope.error}">
                        <div class="error-message">
                            <i class="fa-solid fa-circle-exclamation"></i> ${requestScope.error}
                        </div>
                    </c:if>

                    <div class="form-group">
                        <label for="newPassword">Mật khẩu mới *</label>
                        <input type="password" id="newPassword" name="newPassword" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Xác nhận mật khẩu mới *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    
                    <input type="hidden" name="email" value="${requestScope.email}">
                    <input type="hidden" name="token" value="${requestScope.token}">

                    <button type="submit" class="btn btn-primary btn-submit">Lưu Mật Khẩu</button>
                    
                </form>
            </div>
            
        </div>
    </main>

<jsp:include page="../layout/footer.jsp" />