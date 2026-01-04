<%-- WebContent/views/khachhang/xacthuc/forgot-password-send-email.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css">

<title>Quên Mật Khẩu - BookStore</title>

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
            <form action="${baseURL}/quen-mat-khau-gui-email" method="POST">
                <h2>Quên Mật Khẩu</h2>
                <p style="text-align: center; margin-bottom: 1.5rem; color: #555;">
                    Nhập địa chỉ email đã đăng ký tài khoản của bạn. Chúng tôi sẽ gửi mật khẩu mới đến email của bạn.
                </p>

                <%-- Hiển thị lỗi từ requestScope --%>
                <c:if test="${not empty error}">
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i> <c:out value="${error}" />
                    </div>
                </c:if>

                <%-- Hiển thị thông báo thành công --%>
                <c:if test="${not empty success}">
                    <div class="success-message">
                        <i class="fa-solid fa-circle-check"></i> <c:out value="${success}" />
                    </div>
                </c:if>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <%-- Giữ lại email nếu có lỗi khi gửi lại --%>
                    <input type="email" id="email" name="email" 
                           value="<c:out value="${email}" />" 
                           placeholder="Nhập email của bạn" 
                           required 
                           autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fa-solid fa-envelope"></i> Gửi Mật Khẩu Mới
                </button>

                <div class="auth-switch-link">
                    Nhớ lại mật khẩu? <a href="${baseURL}/dang-nhap">Đăng nhập</a>
                </div>
                
                <div class="auth-switch-link" style="margin-top: 0.5rem;">
                    Hoặc sử dụng <a href="${baseURL}/quen-mat-khau">mã xác thực</a> để đặt lại mật khẩu
                </div>
            </form>
        </div>

    </div>
</main>

<jsp:include page="../layout/footer.jsp" />

