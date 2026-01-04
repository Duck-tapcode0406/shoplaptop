<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="users" value="${requestScope.users}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
            <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-users"></i> Quản lý người dùng</h2>
                <a href="${baseURL}/admin/users/new" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-plus"></i> Thêm người dùng
                </a>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <c:if test="${not empty sessionScope.errorMessage}">
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    ${sessionScope.errorMessage}
                </div>
                <c:remove var="errorMessage" scope="session" />
            </c:if>

            <div style="margin-bottom: 1.5rem;">
                <form method="GET" action="${baseURL}/admin/users" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm kiếm theo tên, email, số điện thoại..." 
                           value="${param.search}" 
                           style="flex: 1; min-width: 250px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-search"></i> Tìm kiếm
                    </button>
                    <c:if test="${not empty param.search}">
                        <a href="${baseURL}/admin/users" class="btn btn-secondary">
                            <i class="fa-solid fa-times"></i> Xóa bộ lọc
                        </a>
                    </c:if>
                </form>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã KH</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <c:choose>
                            <c:when test="${users != null && users.size() > 0}">
                                <c:forEach var="user" items="${users}">
                                    <tr>
                                        <td><strong>${user.maKhachHang}</strong></td>
                                        <td>${user.tenDangNhap}</td>
                                        <td>${user.hoVaTen}</td>
                                        <td>${user.email}</td>
                                        <td>${user.soDienThoai}</td>
                                        <td>
                                            <c:choose>
                                                <c:when test="${user.role == 1}">
                                                    <span class="badge badge-warning" style="background-color: #ffc107; color: #000;">
                                                        <i class="fa-solid fa-user-shield"></i> Admin
                                                    </span>
                                                </c:when>
                                                <c:otherwise>
                                                    <span class="badge badge-secondary" style="background-color: #6c757d; color: #fff;">
                                                        <i class="fa-solid fa-user"></i> User
                                                    </span>
                                                </c:otherwise>
                                            </c:choose>
                                        </td>
                                        <td>
                                            <c:choose>
                                                <c:when test="${user.status == 1}">
                                                    <span class="badge badge-success">Hoạt động</span>
                                                </c:when>
                                                <c:otherwise>
                                                    <span class="badge badge-danger">Đã khóa</span>
                                                </c:otherwise>
                                            </c:choose>
                                        </td>
                                        <td class="actions">
                                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <a href="${baseURL}/admin/users/edit?id=${user.maKhachHang}" class="btn btn-sm btn-info">
                                                    <i class="fa-solid fa-edit"></i> Sửa
                                                </a>
                                                <c:choose>
                                                    <c:when test="${user.status == 1}">
                                                        <form method="POST" action="${baseURL}/admin/users" style="display: inline;">
                                                            <input type="hidden" name="action" value="lock">
                                                            <input type="hidden" name="maKhachHang" value="${user.maKhachHang}">
                                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                                    onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?');">
                                                                <i class="fa-solid fa-lock"></i> Khóa
                                                            </button>
                                                        </form>
                                                    </c:when>
                                                    <c:otherwise>
                                                        <form method="POST" action="${baseURL}/admin/users" style="display: inline;">
                                                            <input type="hidden" name="action" value="unlock">
                                                            <input type="hidden" name="maKhachHang" value="${user.maKhachHang}">
                                                            <button type="submit" class="btn btn-sm btn-success" 
                                                                    onclick="return confirm('Bạn có chắc muốn mở khóa tài khoản này?');">
                                                                <i class="fa-solid fa-unlock"></i> Mở khóa
                                                            </button>
                                                        </form>
                                                    </c:otherwise>
                                                </c:choose>
                                                <form method="POST" action="${baseURL}/admin/users" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="maKhachHang" value="${user.maKhachHang}">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Bạn có chắc muốn xóa người dùng này? Hành động này không thể hoàn tác!');">
                                                        <i class="fa-solid fa-trash"></i> Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                </c:forEach>
                            </c:when>
                            <c:otherwise>
                                <tr>
                                    <td colspan="7" class="text-center">Chưa có người dùng nào</td>
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
