<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="revenueByPeriod" value="${requestScope.revenueByPeriod}" />
<c:set var="ordersByStatus" value="${requestScope.ordersByStatus}" />
<c:set var="bestSellers" value="${requestScope.bestSellers}" />
<c:set var="topCustomers" value="${requestScope.topCustomers}" />
<c:set var="period" value="${requestScope.period}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="layout/header.jsp" />
    <jsp:include page="layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-chart-bar"></i> Thống kê và Báo cáo</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="${baseURL}/admin/statistics?period=day" class="btn btn-sm ${period == 'day' ? 'btn-primary' : 'btn-secondary'}">Ngày</a>
                    <a href="${baseURL}/admin/statistics?period=month" class="btn btn-sm ${period == 'month' ? 'btn-primary' : 'btn-secondary'}">Tháng</a>
                    <a href="${baseURL}/admin/statistics?period=year" class="btn btn-sm ${period == 'year' ? 'btn-primary' : 'btn-secondary'}">Năm</a>
                </div>
            </div>

            <c:if test="${not empty requestScope.error}">
                <div class="alert alert-danger" style="margin-bottom: 2rem;">
                    <i class="fa-solid fa-exclamation-circle"></i> ${requestScope.error}
                </div>
            </c:if>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin-bottom: 0;">Doanh thu theo ${period == 'day' ? 'ngày' : period == 'month' ? 'tháng' : 'năm'}</h3>
                        <div style="background: #28a745; color: white; padding: 0.5rem 1rem; border-radius: 4px;">
                            <strong>Tổng: <fmt:formatNumber value="${requestScope.totalRevenue}" type="currency" currencySymbol="₫" maxFractionDigits="0"/></strong>
                        </div>
                    </div>
                    <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 1rem;">
                        <i class="fa-solid fa-info-circle"></i> 
                        Chỉ tính đơn hàng đã duyệt (Đang giao/Hoàn tất) và đã thanh toán
                    </p>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>${period == 'day' ? 'Ngày' : period == 'month' ? 'Tháng' : 'Năm'}</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${not empty revenueByPeriod}">
                                        <c:forEach var="entry" items="${revenueByPeriod}">
                                            <tr>
                                                <td>${entry.key}</td>
                                                <td><strong><fmt:formatNumber value="${entry.value}" type="currency" currencySymbol="₫" maxFractionDigits="0"/></strong></td>
                                            </tr>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <tr>
                                            <td colspan="2" style="text-align: center; color: #6c757d;">Chưa có dữ liệu</td>
                                        </tr>
                                    </c:otherwise>
                                </c:choose>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Đơn hàng theo trạng thái</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Trạng thái</th>
                                    <th>Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${not empty ordersByStatus}">
                                        <c:forEach var="entry" items="${ordersByStatus}">
                                            <tr>
                                                <td>${entry.key}</td>
                                                <td><strong>${entry.value}</strong></td>
                                            </tr>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <tr>
                                            <td colspan="2" style="text-align: center; color: #6c757d;">Chưa có dữ liệu</td>
                                        </tr>
                                    </c:otherwise>
                                </c:choose>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Sách bán chạy</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã SP</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Số lượng bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${not empty bestSellers}">
                                        <c:forEach var="seller" items="${bestSellers}">
                                            <tr>
                                                <td><strong>${seller.maSanPham}</strong></td>
                                                <td>${seller.tenSanPham}</td>
                                                <td><strong>${seller.totalSold}</strong></td>
                                            </tr>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <tr>
                                            <td colspan="3" style="text-align: center; color: #6c757d;">Chưa có dữ liệu</td>
                                        </tr>
                                    </c:otherwise>
                                </c:choose>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Khách hàng mua nhiều nhất</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã KH</th>
                                    <th>Họ tên</th>
                                    <th>Số đơn</th>
                                    <th>Tổng chi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${not empty topCustomers}">
                                        <c:forEach var="customer" items="${topCustomers}">
                                            <tr>
                                                <td><strong>${customer.maKhachHang}</strong></td>
                                                <td>${customer.hoVaTen}</td>
                                                <td>${customer.totalOrders}</td>
                                                <td><strong><fmt:formatNumber value="${customer.totalSpent}" type="currency" currencySymbol="₫" maxFractionDigits="0"/></strong></td>
                                            </tr>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: #6c757d;">Chưa có dữ liệu</td>
                                        </tr>
                                    </c:otherwise>
                                </c:choose>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <%-- Thống kê Người dùng --%>
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title"><i class="fa-solid fa-users"></i> Thống kê Người dùng</h2>
                    <a href="${baseURL}/admin/users" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-user-cog"></i> Quản lý Người dùng
                    </a>
                </div>
                
                <c:if test="${not empty userStats}">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.9rem; opacity: 0.9;">Tổng người dùng</span>
                                <i class="fa-solid fa-users" style="font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: bold;">${userStats.totalUsers}</div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.9rem; opacity: 0.9;">Đang hoạt động</span>
                                <i class="fa-solid fa-user-check" style="font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: bold;">${userStats.activeUsers}</div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #f44336 0%, #e91e63 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.9rem; opacity: 0.9;">Đã khóa</span>
                                <i class="fa-solid fa-user-lock" style="font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: bold;">${userStats.lockedUsers}</div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.9rem; opacity: 0.9;">Mới tháng này</span>
                                <i class="fa-solid fa-user-plus" style="font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: bold;">${userStats.newUsersThisMonth}</div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.9rem; opacity: 0.9;">Mới tuần này</span>
                                <i class="fa-solid fa-user-clock" style="font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: bold;">${userStats.newUsersThisWeek}</div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.9rem; opacity: 0.9;">Đã xác thực</span>
                                <i class="fa-solid fa-user-shield" style="font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: bold;">${userStats.verifiedUsers}</div>
                        </div>
                    </div>
                </c:if>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <%-- Người dùng đăng ký theo thời gian --%>
                    <div class="card">
                        <h3 style="margin-bottom: 1rem;">
                            <i class="fa-solid fa-chart-line"></i> Người dùng đăng ký theo ${period == 'day' ? 'ngày' : period == 'month' ? 'tháng' : 'năm'}
                        </h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>${period == 'day' ? 'Ngày' : period == 'month' ? 'Tháng' : 'Năm'}</th>
                                        <th>Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <c:choose>
                                        <c:when test="${not empty newUsersByPeriod}">
                                            <c:forEach var="entry" items="${newUsersByPeriod}">
                                                <tr>
                                                    <td>${entry.key}</td>
                                                    <td><strong>${entry.value}</strong></td>
                                                </tr>
                                            </c:forEach>
                                        </c:when>
                                        <c:otherwise>
                                            <tr>
                                                <td colspan="2" style="text-align: center; color: #6c757d;">Chưa có dữ liệu</td>
                                            </tr>
                                        </c:otherwise>
                                    </c:choose>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <%-- Phân bố theo vai trò --%>
                    <div class="card">
                        <h3 style="margin-bottom: 1rem;">
                            <i class="fa-solid fa-user-tag"></i> Phân bố theo vai trò
                        </h3>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vai trò</th>
                                        <th>Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <c:choose>
                                        <c:when test="${not empty usersByRole}">
                                            <c:forEach var="entry" items="${usersByRole}">
                                                <tr>
                                                    <td>
                                                        <c:choose>
                                                            <c:when test="${entry.key == 'Quản trị viên'}">
                                                                <span class="badge badge-warning" style="background-color: #ffc107; color: #000;">
                                                                    <i class="fa-solid fa-user-shield"></i> ${entry.key}
                                                                </span>
                                                            </c:when>
                                                            <c:otherwise>
                                                                <span class="badge badge-secondary" style="background-color: #6c757d; color: #fff;">
                                                                    <i class="fa-solid fa-user"></i> ${entry.key}
                                                                </span>
                                                            </c:otherwise>
                                                        </c:choose>
                                                    </td>
                                                    <td><strong>${entry.value}</strong></td>
                                                </tr>
                                            </c:forEach>
                                        </c:when>
                                        <c:otherwise>
                                            <tr>
                                                <td colspan="2" style="text-align: center; color: #6c757d;">Chưa có dữ liệu</td>
                                            </tr>
                                        </c:otherwise>
                                    </c:choose>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <%-- Thống kê Gói cước --%>
                <c:if test="${not empty subscriptionStats}">
                    <div class="card" style="margin-top: 2rem;">
                        <h3 style="margin-bottom: 1rem;">
                            <i class="fa-solid fa-crown"></i> Thống kê Gói cước
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #667eea;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #666; font-size: 0.9rem;">Tổng người dùng có gói</span>
                                    <i class="fa-solid fa-users" style="color: #667eea; font-size: 1.5rem;"></i>
                                </div>
                                <div style="font-size: 2rem; font-weight: bold; color: #667eea;">${subscriptionStats.totalSubscribers}</div>
                            </div>
                            
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #28a745;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #666; font-size: 0.9rem;">Gói còn hiệu lực</span>
                                    <i class="fa-solid fa-check-circle" style="color: #28a745; font-size: 1.5rem;"></i>
                                </div>
                                <div style="font-size: 2rem; font-weight: bold; color: #28a745;">${subscriptionStats.activeSubscribers}</div>
                            </div>
                            
                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #f44336;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #666; font-size: 0.9rem;">Gói đã hết hạn</span>
                                    <i class="fa-solid fa-times-circle" style="color: #f44336; font-size: 1.5rem;"></i>
                                </div>
                                <div style="font-size: 2rem; font-weight: bold; color: #f44336;">${subscriptionStats.expiredSubscribers}</div>
                            </div>
                        </div>
                    </div>
                </c:if>
            </div>
        </div>
    </div>

    <jsp:include page="layout/footer.jsp" />
</body>
</html>




