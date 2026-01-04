<%-- WebContent/views/khachhang/auth/register.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css">

<title>Đăng Ký Tài Khoản - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <div class="auth-container">
            <%-- Nút quay lại --%>
            <div style="margin-bottom: 1rem; text-align: center;">
                <a href="${baseURL}/trang-chu" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>
        
            <div class="auth-form-box">
                <form action="${baseURL}/dang-ky" method="POST">
                    <h2>Đăng Ký</h2>
                    
                    <%-- 🧭 7. Thông báo lỗi (từ Servlet) --%>
                    <c:if test="${not empty requestScope.error}">
                        <div class="error-message">
                            <i class="fa-solid fa-circle-exclamation"></i> ${requestScope.error}
                        </div>
                    </c:if>

                    <div class="form-group">
                        <label for="username">Tên đăng nhập *</label>
                        <input type="text" id="username" name="username" value="${param.username}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="${param.email}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fullName">Họ và tên *</label>
                        <input type="text" id="fullName" name="fullName" value="${param.fullName}" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Nhập lại mật khẩu *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-submit">Đăng Ký</button>
                    
                    <div class="auth-switch-link">
                        Bạn đã có tài khoản? <a href="${baseURL}/dang-nhap">Đăng nhập ngay</a>
                    </div>
                </form>
            </div>
            
        </div>
    </main>

<%-- Import Footer --%>
<jsp:include page="../layout/footer.jsp" />