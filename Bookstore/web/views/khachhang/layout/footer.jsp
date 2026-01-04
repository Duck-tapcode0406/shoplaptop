<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

    <%-- ? 2. Footer --%>
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">

                <%-- Section 1: Giới thiệu --%>
                <div class="footer-section about">
                    <h4 class="logo">BookStore</h4>
                    <p>
                        Chuyên cung cấp các loại sách từ nhiều nhà xuất bản uy tín.
                        Trải nghiệm mua sắm trực tuyến nhanh chóng và tiện lợi.
                    </p>
                    <%-- Liên kết mạng xã hội --%>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Youtube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <%-- Section 2: Liên kết nhanh --%>
                <div class="footer-section links">
                    <h4>Về BookStore</h4>
                    <ul>
                        <li><a href="#">Giới thiệu cửa hàng</a></li>
                        <li><a href="#">Điều khoản sử dụng</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Hệ thống cửa hàng</a></li>
                    </ul>
                </div>

                <%-- Section 3: Hỗ trợ --%>
                <div class="footer-section links">
                    <h4>Hỗ trợ khách hàng</h4>
                    <ul>
                        <li><a href="#">Câu hỏi thường gặp (FAQ)</a></li>
                        <li><a href="#">Hướng dẫn đặt hàng online</a></li>
                        <li><a href="#">Chính sách đổi trả & hoàn tiền</a></li>
                        <li><a href="#">Phương thức vận chuyển</a></li>
                    </ul>
                </div>

                <%-- Section 4: Liên hệ --%>
                <div class="footer-section contact">
                    <h4>Thông tin liên hệ</h4>
                    <p><i class="fas fa-map-marker-alt"></i> 77 Nguyễn Huệ, TP. Huế, Việt Nam</p>
                    <p><i class="fas fa-phone"></i> 0393340406</p>
                    <p><i class="fas fa-envelope"></i> 22T1020575@husc.edu.vn</p>
                    <p><i class="fas fa-clock"></i> Giờ làm việc: 8:00 - 21:00 (T2 - CN)</p>
                </div>

            </div>

            <%-- Phần bản quyền --%>
            <div class="footer-bottom">
                &copy; 2025 BookStore | Bản quyền thuộc về Trần Đại Đức và thầy Huỳnh Bảo Quốc Dũng
            </div>
        </div>
    </footer>

    <%-- Thẻ đóng cho body và html (được mở trong header.jsp) --%>
    <%-- JavaScript Files --%>
    <script src="${baseURL}/js/KhachHang/main.js"></script>
    <script src="${baseURL}/js/notification.js"></script>
</body>
</html>