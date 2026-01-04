<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="promotions" value="${requestScope.promotions}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khuyến mãi - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-percent"></i> Quản lý khuyến mãi</h2>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <div style="margin-bottom: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Thêm khuyến mãi mới</h3>
                    <form method="POST" action="${baseURL}/admin/promotions">
                        <input type="hidden" name="action" value="add">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="maKhuyenMai">Mã khuyến mãi *</label>
                                <input type="text" id="maKhuyenMai" name="maKhuyenMai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="tenKhuyenMai">Tên khuyến mãi *</label>
                                <input type="text" id="tenKhuyenMai" name="tenKhuyenMai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="phanTramGiam">Phần trăm giảm (%) *</label>
                                <input type="number" id="phanTramGiam" name="phanTramGiam" class="form-control" 
                                       step="0.01" min="0" max="100" required>
                            </div>
                            <div class="form-group">
                                <label for="soTienGiamToiDa">Số tiền giảm tối đa (₫) *</label>
                                <input type="number" id="soTienGiamToiDa" name="soTienGiamToiDa" class="form-control" 
                                       step="1000" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="ngayBatDau">Ngày bắt đầu *</label>
                                <input type="date" id="ngayBatDau" name="ngayBatDau" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="ngayKetThuc">Ngày kết thúc *</label>
                                <input type="date" id="ngayKetThuc" name="ngayKetThuc" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="trangThai">Trạng thái</label>
                                <select id="trangThai" name="trangThai" class="form-control">
                                    <option value="true">Hoạt động</option>
                                    <option value="false">Không hoạt động</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Thêm khuyến mãi
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã KM</th>
                            <th>Tên khuyến mãi</th>
                            <th>Giảm giá</th>
                            <th>Giảm tối đa</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <c:choose>
                            <c:when test="${promotions != null && promotions.size() > 0}">
                                <c:forEach var="promotion" items="${promotions}">
                                    <tr>
                                        <td><strong>${promotion.maKhuyenMai}</strong></td>
                                        <td>${promotion.tenKhuyenMai}</td>
                                        <td>${promotion.phanTramGiam}%</td>
                                        <td><fmt:formatNumber value="${promotion.soTienGiamToiDa}" type="currency" currencySymbol="₫"/></td>
                                        <td><fmt:formatDate value="${promotion.ngayBatDau}" pattern="dd/MM/yyyy"/></td>
                                        <td><fmt:formatDate value="${promotion.ngayKetThuc}" pattern="dd/MM/yyyy"/></td>
                                        <td>
                                            <span class="badge ${promotion.trangThai ? 'badge-success' : 'badge-danger'}">
                                                ${promotion.trangThai ? 'Hoạt động' : 'Không hoạt động'}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="${baseURL}/admin/promotions?action=delete&id=${promotion.maKhuyenMai}" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bạn có chắc muốn xóa?');">
                                                <i class="fa-solid fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                </c:forEach>
                            </c:when>
                            <c:otherwise>
                                <tr>
                                    <td colspan="8" class="text-center">Chưa có khuyến mãi nào</td>
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
