<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="publishers" value="${requestScope.publishers}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhà xuất bản - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-building"></i> Quản lý nhà xuất bản</h2>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Thêm nhà xuất bản mới</h3>
                    <form method="POST" action="${baseURL}/admin/publishers">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="maNhaXuatBan">Mã NXB *</label>
                            <input type="text" id="maNhaXuatBan" name="maNhaXuatBan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="tenNhaXuatBan">Tên nhà xuất bản *</label>
                            <input type="text" id="tenNhaXuatBan" name="tenNhaXuatBan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="diaChi">Địa chỉ</label>
                            <input type="text" id="diaChi" name="diaChi" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="soDienThoai">Số điện thoại</label>
                            <input type="text" id="soDienThoai" name="soDienThoai" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Thêm
                        </button>
                    </form>
                </div>

                <div>
                    <h3 style="margin-bottom: 1rem;">Danh sách nhà xuất bản</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên NXB</th>
                                    <th>Địa chỉ</th>
                                    <th>Số điện thoại</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${publishers != null && publishers.size() > 0}">
                                        <c:forEach var="publisher" items="${publishers}">
                                            <tr>
                                                <td><strong>${publisher.maNhaXuatBan}</strong></td>
                                                <td>${publisher.tenNhaXuatBan}</td>
                                                <td>${publisher.diaChi}</td>
                                                <td>${publisher.soDienThoai}</td>
                                                <td>
                                                    <a href="${baseURL}/admin/publishers?action=delete&id=${publisher.maNhaXuatBan}" 
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
                                            <td colspan="5" class="text-center">Chưa có nhà xuất bản nào</td>
                                        </tr>
                                    </c:otherwise>
                                </c:choose>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
</body>
</html>
