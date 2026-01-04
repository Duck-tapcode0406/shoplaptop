<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="orders" value="${requestScope.orders}" />
<c:set var="statusFilter" value="${requestScope.statusFilter}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-shopping-cart"></i> Quản lý đơn hàng</h2>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <c:if test="${not empty sessionScope.errorMessage}">
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i> ${sessionScope.errorMessage}
                </div>
                <c:remove var="errorMessage" scope="session" />
            </c:if>

            <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <form method="GET" action="${baseURL}/admin/orders" style="display: flex; gap: 1rem; align-items: center; flex: 1; flex-wrap: wrap;">
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex: 1; min-width: 300px;">
                        <label for="search" style="white-space: nowrap;">Tìm kiếm:</label>
                        <input type="text" id="search" name="search" 
                               placeholder="Tên sách hoặc tên tác giả..." 
                               class="form-control" 
                               value="${requestScope.searchKeyword != null ? requestScope.searchKeyword : ''}"
                               style="flex: 1; min-width: 200px;">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <label for="status" style="white-space: nowrap;">Trạng thái:</label>
                        <select id="status" name="status" class="form-control" style="width: 150px;">
                            <option value="">Tất cả</option>
                            <option value="Hoàn tất" ${statusFilter == 'Hoàn tất' ? 'selected' : ''}>Hoàn tất</option>
                            <option value="Hủy" ${statusFilter == 'Hủy' ? 'selected' : ''}>Hủy</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search"></i> Tìm kiếm
                    </button>
                    <c:if test="${not empty requestScope.searchKeyword or not empty statusFilter}">
                        <a href="${baseURL}/admin/orders" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Xóa bộ lọc
                        </a>
                    </c:if>
                </form>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Gói cước</th>
                            <th>Ngày đặt</th>
                            <th>Tổng điểm</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <c:choose>
                            <c:when test="${orders != null && orders.size() > 0}">
                                <c:forEach var="order" items="${orders}">
                                    <tr>
                                        <td><strong>${order.maDonHang}</strong></td>
                                        <td>
                                            <strong>${order.khachHang.hoVaTen != null ? order.khachHang.hoVaTen : 'N/A'}</strong>
                                            <c:if test="${not empty order.khachHang.email}">
                                                <br><small style="color: #666;">${order.khachHang.email}</small>
                                            </c:if>
                                        </td>
                                        <td>${order.khachHang.soDienThoai != null ? order.khachHang.soDienThoai : 'N/A'}</td>
                                        <td>
                                            <%-- Đã xóa phần bậc hội viên, thay bằng thông tin gói cước --%>
                                            <c:choose>
                                                <c:when test="${not empty order.khachHang.maGoiCuoc and order.khachHang.isGoiCuocConHan()}">
                                                    <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                                        <i class="fa-solid fa-crown"></i> Đang dùng gói
                                                    </span>
                                                </c:when>
                                                <c:otherwise>
                                                    <span class="badge" style="background: #95a5a6; color: white;">
                                                        <i class="fa-solid fa-user"></i> Chưa đăng ký
                                                    </span>
                                                </c:otherwise>
                                            </c:choose>
                                        </td>
                                        <td><fmt:formatDate value="${order.ngayDatHang}" pattern="dd/MM/yyyy HH:mm"/></td>
                                        <td>
                                            <strong style="color: #00467f;">
                                                <fmt:formatNumber value="${order.soTienDaThanhToan}" type="currency" currencySymbol="" minFractionDigits="0" maxFractionDigits="0" groupingUsed="true"/> ₫
                                            </strong>
                                        </td>
                                        <td class="actions">
                                            <a href="${baseURL}/admin/orders/detail?id=${order.maDonHang}" class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-eye"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                </c:forEach>
                            </c:when>
                            <c:otherwise>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <c:choose>
                                            <c:when test="${not empty requestScope.searchKeyword}">
                                                Không tìm thấy đơn hàng nào với từ khóa "${requestScope.searchKeyword}"
                                            </c:when>
                                            <c:otherwise>
                                                Chưa có đơn hàng nào
                                            </c:otherwise>
                                        </c:choose>
                                    </td>
                                </tr>
                            </c:otherwise>
                        </c:choose>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
</body>
</html>
