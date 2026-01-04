<%-- WebContent/views/khachhang/auth/verify-code.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />


<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css">

<title>Xác Thực Mã - BookStore</title>

<%-- Import Header --%>
<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <div class="auth-container">
            <%-- Nút quay lại --%>
            <div style="margin-bottom: 1rem; text-align: center;">
                <a href="${baseURL}/quen-mat-khau" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>
        
            <div class="auth-form-box">
                <form action="${baseURL}/xac-thuc-ma" method="POST">
                    <h2>Xác Thực Tài Khoản</h2>
                    <p style="text-align: center; margin-bottom: 1.5rem; color: #555;">
                        Một mã xác thực gồm 6 chữ số đã được gửi đến email
                        <%-- Giả sử email được lưu trong request để hiển thị lại --%>
                        <strong>${requestScope.email}</strong>.
                    </p>
                    
                    <%-- 🧭 7. Thông báo lỗi (ví dụ: Mã sai hoặc hết hạn) --%>
                    <c:if test="${not empty requestScope.error}">
                        <div class="error-message">
                            <i class="fa-solid fa-circle-exclamation"></i> ${requestScope.error}
                        </div>
                    </c:if>

                    <div class="form-group">
                        <label for="verificationCode">Mã xác thực *</label>
                        <input type="text" id="verificationCode" name="verificationCode" required
                               placeholder="Nhập 6 chữ số" maxlength="6">
                        
                        <%-- Cần truyền email đi để Servlet biết xác thực cho ai --%>
                        <input type="hidden" name="email" value="${requestScope.email}">
                    </div>

                    <button type="submit" class="btn btn-primary btn-submit">Xác Nhận</button>
                    
                    <div class="auth-switch-link">
                        Không nhận được mã? <a href="${baseURL}/thay-doi-mat-khau?action=resend&email=${requestScope.email}">Gửi lại</a>
                    </div>
                </form>
            </div>
            
        </div>
    </main>

<%-- Import Footer --%>
<jsp:include page="../layout/footer.jsp" />