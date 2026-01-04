<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="stats" value="${requestScope.stats}" />
<c:set var="recentOrders" value="${requestScope.recentOrders}" />
<c:set var="lowStockProducts" value="${requestScope.lowStockProducts}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="layout/header.jsp" />
    <jsp:include page="layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-chart-line"></i> Tổng quan hệ thống</h2>
            </div>
            
            <c:if test="${stats != null}">
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-header">
                            <span class="stat-title">Tổng đơn hàng</span>
                            <i class="fa-solid fa-shopping-cart stat-icon"></i>
                        </div>
                        <div class="stat-value">${stats.totalOrders}</div>
                        <div class="stat-change">
                            <i class="fa-solid fa-clock"></i> ${stats.pendingOrders} đơn chờ xử lý
                        </div>
                    </div>

                    <div class="stat-card success">
                        <div class="stat-header">
                            <span class="stat-title">Tổng doanh thu</span>
                            <i class="fa-solid fa-dollar-sign stat-icon"></i>
                        </div>
                        <div class="stat-value">
                            <fmt:formatNumber value="${stats.totalRevenue}" type="currency" currencySymbol="₫" maxFractionDigits="0"/>
                        </div>
                        <div class="stat-change positive">
                            <i class="fa-solid fa-arrow-up"></i> Hôm nay: 
                            <fmt:formatNumber value="${stats.todayRevenue}" type="currency" currencySymbol="₫" maxFractionDigits="0"/>
                        </div>
                    </div>

                    <div class="stat-card info">
                        <div class="stat-header">
                            <span class="stat-title">Khách hàng</span>
                            <i class="fa-solid fa-users stat-icon"></i>
                        </div>
                        <div class="stat-value">${stats.totalCustomers}</div>
                        <div class="stat-change">
                            <i class="fa-solid fa-user-check"></i> Tổng số tài khoản
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-header">
                            <span class="stat-title">Sản phẩm</span>
                            <i class="fa-solid fa-book stat-icon"></i>
                        </div>
                        <div class="stat-value">${stats.totalProducts}</div>
                        <div class="stat-change">
                            <i class="fa-solid fa-exclamation-triangle"></i> 
                            <c:if test="${lowStockProducts != null && lowStockProducts.size() > 0}">
                                ${lowStockProducts.size()} sản phẩm sắp hết
                            </c:if>
                        </div>
                    </div>

                    <div class="stat-card danger">
                        <div class="stat-header">
                            <span class="stat-title">Doanh thu tháng này</span>
                            <i class="fa-solid fa-chart-pie stat-icon"></i>
                        </div>
                        <div class="stat-value">
                            <fmt:formatNumber value="${stats.monthRevenue}" type="currency" currencySymbol="₫" maxFractionDigits="0"/>
                        </div>
                        <div class="stat-change positive">
                            <i class="fa-solid fa-calendar"></i> Tháng hiện tại
                        </div>
                    </div>
                </div>
            </c:if>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa-solid fa-clock"></i> Đơn hàng gần đây</h3>
                    <a href="${baseURL}/admin/orders" class="btn btn-sm btn-primary">Xem tất cả</a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Trạng thái</th>
                                <th>Tổng tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <c:choose>
                                <c:when test="${recentOrders != null && recentOrders.size() > 0}">
                                    <c:forEach var="order" items="${recentOrders}" end="5">
                                        <tr>
                                            <td><strong>${order.maDonHang}</strong></td>
                                            <td>${order.khachHang.hoVaTen}</td>
                                            <td><fmt:formatDate value="${order.ngayDatHang}" pattern="dd/MM/yyyy"/></td>
                                            <td>
                                                <span class="badge 
                                                    ${order.trangThai == 'Hoàn tất' ? 'badge-success' : ''}
                                                    ${order.trangThai == 'Chờ duyệt' ? 'badge-warning' : ''}
                                                    ${order.trangThai == 'Đang giao' ? 'badge-info' : ''}
                                                    ${order.trangThai == 'Hủy' ? 'badge-danger' : ''}
                                                ">${order.trangThai}</span>
                                            </td>
                                            <td>
                                                <fmt:formatNumber value="${order.soTienDaThanhToan}" type="currency" currencySymbol="₫" maxFractionDigits="0"/>
                                            </td>
                                        </tr>
                                    </c:forEach>
                                </c:when>
                                <c:otherwise>
                                    <tr>
                                        <td colspan="5" class="text-center">Chưa có đơn hàng nào</td>
                                    </tr>
                                </c:otherwise>
                            </c:choose>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa-solid fa-exclamation-triangle"></i> Sản phẩm sắp hết</h3>
                    <a href="${baseURL}/admin/products" class="btn btn-sm btn-primary">Xem tất cả</a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá bán</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <c:choose>
                                <c:when test="${lowStockProducts != null && lowStockProducts.size() > 0}">
                                    <c:forEach var="product" items="${lowStockProducts}" end="5">
                                        <tr>
                                            <td><strong>${product.tenSanPham}</strong></td>
                                            <td>
                                                <span class="badge ${product.soLuong < 5 ? 'badge-danger' : 'badge-warning'}">
                                                    ${product.soLuong} sản phẩm
                                                </span>
                                            </td>
                                            <td>
                                                <fmt:formatNumber value="${product.giaBan}" type="currency" currencySymbol="₫" maxFractionDigits="0"/>
                                            </td>
                                            <td>
                                                <a href="${baseURL}/admin/products/edit?id=${product.maSanPham}" class="btn btn-sm btn-info">
                                                    <i class="fa-solid fa-edit"></i> Sửa
                                                </a>
                                            </td>
                                        </tr>
                                    </c:forEach>
                                </c:when>
                                <c:otherwise>
                                    <tr>
                                        <td colspan="4" class="text-center">Tất cả sản phẩm đều đủ hàng</td>
                                    </tr>
                                </c:otherwise>
                            </c:choose>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <jsp:include page="layout/footer.jsp" />
</body>
</html>
