<%-- WebContent/views/khachhang/auth/forgot-password.jsp --%>
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
                 <%-- SỬA ACTION trỏ đến /quen-mat-khau --%>
                <form action="${baseURL}/quen-mat-khau" method="POST">
                    <h2>Đặt Lại Mật Khẩu</h2>
                    <p style="text-align: center; margin-bottom: 1.5rem; color: #555;">
                        Nhập địa chỉ email đã đăng ký tài khoản của bạn. Chúng tôi sẽ gửi một mã xác thực để bạn có thể đặt lại mật khẩu.
                    </p>

                    <%-- Hiển thị lỗi từ requestScope --%>
                    <c:if test="${not empty error}">
                        <div class="error-message">
                            <i class="fa-solid fa-circle-exclamation"></i> <c:out value="${error}" />
                        </div>
                    </c:if>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <%-- Giữ lại email nếu có lỗi khi gửi lại mã --%>
                        <input type="email" id="email" name="email" value="<c:out value="${email}" />" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-submit">
                       <i class="fa-solid fa-paper-plane"></i> Gửi Mã Xác Thực
                    </button>

                    <div class="auth-switch-link">
                        Nhớ lại mật khẩu? <a href="${baseURL}/dang-nhap">Đăng nhập</a>
                    </div>
                </form>
            </div>

        </div>
    </main>

<jsp:include page="../layout/footer.jsp" />