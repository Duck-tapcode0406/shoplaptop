<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Gói Cước - BookStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/khachhang/main.css">
    <style>
        .subscription-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .subscription-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .subscription-header h1 {
            font-size: 2.5rem;
            color: #00467f;
            margin-bottom: 1rem;
        }
        .subscription-header p {
            font-size: 1.1rem;
            color: #666;
        }
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .package-card.popular {
            border: 3px solid #00467f;
            transform: scale(1.05);
        }
        .package-card.popular::before {
            content: "PHỔ BIẾN";
            position: absolute;
            top: 20px;
            right: -30px;
            background: #00467f;
            color: white;
            padding: 5px 40px;
            font-size: 0.8rem;
            font-weight: bold;
            transform: rotate(45deg);
        }
        .package-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .package-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #00467f;
            margin-bottom: 0.5rem;
        }
        .package-duration {
            color: #666;
            font-size: 1rem;
        }
        .package-price {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .price-original {
            font-size: 1.2rem;
            color: #999;
            text-decoration: line-through;
            margin-right: 0.5rem;
        }
        .price-current {
            font-size: 2.5rem;
            font-weight: bold;
            color: #00467f;
        }
        .price-unit {
            font-size: 1rem;
            color: #666;
        }
        .discount-badge {
            display: inline-block;
            background: #ff4444;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-left: 0.5rem;
        }
        .package-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        .package-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .package-features li:last-child {
            border-bottom: none;
        }
        .package-features li i {
            color: #28a745;
            font-size: 1.1rem;
        }
        .package-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .package-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #00467f 0%, #0066cc 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        .package-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,70,127,0.3);
        }
        .package-card.popular .package-button {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
        }
        .fake-notification {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .fake-notification i {
            color: #ffc107;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <jsp:include page="layout/header.jsp" />
    
    <div class="subscription-container">
        <div class="subscription-header">
            <h1><i class="fa-solid fa-book-open"></i> Đăng Ký Gói Cước Đọc Sách</h1>
            <p>Đọc không giới hạn hàng nghìn cuốn sách với gói cước ưu đãi</p>
            <c:if test="${not empty sessionScope.user and sessionScope.user.isGoiCuocConHan()}">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 2rem; border-radius: 10px; margin-top: 1.5rem; display: inline-block;">
                    <i class="fa-solid fa-info-circle"></i> Bạn đang có gói cước còn hiệu lực. 
                    <a href="${baseURL}/thong-tin-goi-cuoc" style="color: #ffd700; font-weight: bold; text-decoration: underline; margin-left: 0.5rem;">
                        Xem thông tin gói cước
                    </a>
                </div>
            </c:if>
        </div>
        
        <c:if test="${not empty sessionScope.errorMessage}">
            <div class="alert alert-danger">
                <i class="fa-solid fa-exclamation-circle"></i> ${sessionScope.errorMessage}
            </div>
            <c:remove var="errorMessage" scope="session" />
        </c:if>
        
        <c:if test="${not empty sessionScope.successMessage}">
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
            </div>
            <c:remove var="successMessage" scope="session" />
        </c:if>
        
        <div class="packages-grid">
            <c:forEach items="${listGoi}" var="goi" varStatus="status">
                <div class="package-card ${status.index == 1 ? 'popular' : ''}">
                    <div class="package-header">
                        <div class="package-name">${goi.tenGoi}</div>
                        <div class="package-duration">${goi.thoiHan} tháng</div>
                    </div>
                    
                    <c:if test="${status.index == 1}">
                        <div class="fake-notification">
                            <i class="fa-solid fa-fire"></i>
                            <strong>Ưu đãi đặc biệt!</strong> Gói này được nhiều người chọn nhất
                        </div>
                    </c:if>
                    
                    <div class="package-price">
                        <%-- Xác định giá gốc (giảm giá ảo) dựa trên thời hạn gói --%>
                        <c:choose>
                            <c:when test="${goi.thoiHan == 1}">
                                <%-- Gói 1 tháng: 300000 → 199000 --%>
                                <c:set var="giaGoc" value="300000" />
                                <c:set var="phanTramGiam" value="34" />
                            </c:when>
                            <c:when test="${goi.thoiHan == 6}">
                                <%-- Gói 6 tháng: 1800000 → 399000 --%>
                                <c:set var="giaGoc" value="1800000" />
                                <c:set var="phanTramGiam" value="78" />
                            </c:when>
                            <c:when test="${goi.thoiHan == 12}">
                                <%-- Gói 1 năm: 3600000 → 599000 --%>
                                <c:set var="giaGoc" value="3600000" />
                                <c:set var="phanTramGiam" value="83" />
                            </c:when>
                            <c:otherwise>
                                <%-- Mặc định: tính 30% giảm --%>
                                <c:set var="giaGoc" value="${goi.giaTien * 1.3}" />
                                <c:set var="phanTramGiam" value="30" />
                            </c:otherwise>
                        </c:choose>
                        <span class="price-original">
                            <fmt:formatNumber value="${giaGoc}" type="number" groupingUsed="true"/> ₫
                        </span>
                        <span class="price-current">
                            <fmt:formatNumber value="${goi.giaTien}" type="number" groupingUsed="true"/>
                        </span>
                        <span class="price-unit">₫</span>
                        <span class="discount-badge">Giảm ${phanTramGiam}%</span>
                    </div>
                    
                    <c:if test="${not empty goi.moTa}">
                        <div class="package-description">${goi.moTa}</div>
                    </c:if>
                    
                    <ul class="package-features">
                        <li><i class="fa-solid fa-check"></i> Đọc không giới hạn tất cả sách</li>
                        <li><i class="fa-solid fa-check"></i> Đọc offline trên ứng dụng</li>
                        <li><i class="fa-solid fa-check"></i> Không quảng cáo</li>
                        <li><i class="fa-solid fa-check"></i> Hỗ trợ 24/7</li>
                    </ul>
                    
                    <c:choose>
                        <c:when test="${not empty sessionScope.user}">
                            <form action="${baseURL}/dang-ky-goi-cuoc" method="POST">
                                <input type="hidden" name="action" value="subscribe">
                                <input type="hidden" name="maGoi" value="${goi.maGoi}">
                                <button type="submit" class="package-button">
                                    <i class="fa-solid fa-credit-card"></i> Đăng Ký Ngay
                                </button>
                            </form>
                        </c:when>
                        <c:otherwise>
                            <a href="${baseURL}/dang-nhap" class="package-button">
                                <i class="fa-solid fa-sign-in-alt"></i> Đăng Nhập Để Đăng Ký
                            </a>
                        </c:otherwise>
                    </c:choose>
                </div>
            </c:forEach>
        </div>
        
        <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
            <h3 style="color: #00467f; margin-bottom: 1rem;">
                <i class="fa-solid fa-shield-alt"></i> Cam Kết Bảo Mật
            </h3>
            <p style="color: #666; line-height: 1.8;">
                Thanh toán được bảo mật 100% qua VNPay. Chúng tôi không lưu trữ thông tin thẻ của bạn.
                Bạn có thể hủy gói bất cứ lúc nào.
            </p>
        </div>
        
        <%-- Nút quay lại --%>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="${baseURL}/trang-chu" class="btn-back" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ
            </a>
        </div>
    </div>
    
    <jsp:include page="layout/footer.jsp" />
</body>
</html>

