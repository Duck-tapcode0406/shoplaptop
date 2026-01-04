<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Gói Cước - BookStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/khachhang/main.css">
    <style>
        .subscription-info-container {
            max-width: 800px;
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
        .subscription-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 3rem;
            color: white;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .subscription-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .pro-badge-large {
            display: inline-block;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
            font-weight: 900;
            font-size: 1.5rem;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        }
        .info-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 1rem;
        }
        .info-value {
            font-weight: 700;
            color: #00467f;
            font-size: 1.1rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-active {
            background: #4caf50;
            color: white;
        }
        .status-expired {
            background: #f44336;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn-renew {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-renew:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            text-decoration: none;
            color: white;
        }
        .btn-back {
            background: #f5f5f5;
            color: #333;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        .btn-back:hover {
            background: #e0e0e0;
            text-decoration: none;
            color: #333;
        }
        .countdown {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(255,255,255,0.3);
        }
        .countdown-text {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        .countdown-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <jsp:include page="layout/header.jsp" />
    
    <main class="subscription-info-container">
        <div class="subscription-header">
            <h1><i class="fa-solid fa-crown"></i> Thông Tin Gói Cước</h1>
        </div>
        
        <c:choose>
            <c:when test="${not empty user and user.isGoiCuocConHan()}">
                <%-- Hiển thị thông tin gói cước đang dùng --%>
                <div class="subscription-card">
                    <div class="pro-badge-large">PRO</div>
                    <h2 style="margin-bottom: 1rem; font-size: 2rem;">
                        <c:choose>
                            <c:when test="${not empty goiDangDung}">
                                ${goiDangDung.tenGoi}
                            </c:when>
                            <c:otherwise>
                                Gói Cước Đang Dùng
                            </c:otherwise>
                        </c:choose>
                    </h2>
                    <p style="font-size: 1.1rem; opacity: 0.9; margin-bottom: 2rem;">
                        <c:if test="${not empty goiDangDung}">
                            ${goiDangDung.moTa}
                        </c:if>
                    </p>
                    
                    <div class="countdown">
                        <div class="countdown-text">Gói cước của bạn còn hiệu lực đến</div>
                        <div class="countdown-value">
                            <fmt:formatDate value="${user.ngayHetHan}" pattern="dd/MM/yyyy HH:mm"/>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3 style="color: #00467f; margin-bottom: 1.5rem; font-size: 1.5rem;">
                        <i class="fa-solid fa-info-circle"></i> Chi Tiết Gói Cước
                    </h3>
                    
                    <div class="info-row">
                        <span class="info-label"><i class="fa-solid fa-crown"></i> Trạng thái:</span>
                        <span class="status-badge status-active">Đang hoạt động</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label"><i class="fa-solid fa-calendar-check"></i> Ngày đăng ký:</span>
                        <span class="info-value">
                            <fmt:formatDate value="${user.ngayDangKy}" pattern="dd/MM/yyyy HH:mm"/>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label"><i class="fa-solid fa-calendar-times"></i> Ngày hết hạn:</span>
                        <span class="info-value">
                            <fmt:formatDate value="${user.ngayHetHan}" pattern="dd/MM/yyyy HH:mm"/>
                        </span>
                    </div>
                    
                    <c:if test="${not empty goiDangDung}">
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-clock"></i> Thời hạn:</span>
                            <span class="info-value">${goiDangDung.thoiHan} tháng</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label"><i class="fa-solid fa-money-bill-wave"></i> Giá gói:</span>
                            <span class="info-value">
                                <fmt:formatNumber value="${goiDangDung.giaTien}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫
                            </span>
                        </div>
                    </c:if>
                </div>
                
                <div class="action-buttons">
                    <a href="${baseURL}/goi-cuoc" class="btn-renew">
                        <i class="fa-solid fa-sync-alt"></i> Gia Hạn / Đổi Gói Cước
                    </a>
                    <a href="${baseURL}/trang-chu" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Về Trang Chủ
                    </a>
                </div>
            </c:when>
            <c:otherwise>
                <%-- Nếu không có gói cước, redirect về trang đăng ký --%>
                <div class="info-section" style="text-align: center; padding: 3rem;">
                    <h2 style="color: #00467f; margin-bottom: 1rem;">Bạn chưa có gói cước</h2>
                    <p style="color: #666; margin-bottom: 2rem;">Vui lòng đăng ký gói cước để sử dụng dịch vụ.</p>
                    <a href="${baseURL}/goi-cuoc" class="btn-renew">
                        <i class="fa-solid fa-crown"></i> Đăng Ký Gói Cước Ngay
                    </a>
                </div>
            </c:otherwise>
        </c:choose>
    </main>
    
    <jsp:include page="layout/footer.jsp" />
</body>
</html>

