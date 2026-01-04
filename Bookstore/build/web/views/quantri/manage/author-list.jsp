<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="authors" value="${requestScope.authors}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tác giả - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-user-pen"></i> Quản lý tác giả</h2>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Thêm tác giả mới</h3>
                    <form method="POST" action="${baseURL}/admin/authors">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="maTacGia">Mã tác giả *</label>
                            <input type="text" id="maTacGia" name="maTacGia" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="hoVaTen">Họ và tên *</label>
                            <input type="text" id="hoVaTen" name="hoVaTen" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="ngaySinh">Ngày sinh</label>
                            <input type="date" id="ngaySinh" name="ngaySinh" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="tieuSu">Tiểu sử</label>
                            <textarea id="tieuSu" name="tieuSu" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Thêm
                        </button>
                    </form>
                </div>

                <div>
                    <h3 style="margin-bottom: 1rem;">Danh sách tác giả</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Họ tên</th>
                                    <th>Ngày sinh</th>
                                    <th>Tiểu sử</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${authors != null && authors.size() > 0}">
                                        <c:forEach var="author" items="${authors}">
                                            <tr>
                                                <td><strong>${author.maTacGia}</strong></td>
                                                <td>${author.hoVaTen}</td>
                                                <td><fmt:formatDate value="${author.ngaySinh}" pattern="dd/MM/yyyy"/></td>
                                                <td>${author.tieuSu}</td>
                                                <td>
                                                    <form method="POST" action="${baseURL}/admin/authors" style="display: inline;">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="maTacGia" value="${author.maTacGia}">
                                                        <input type="hidden" name="hoVaTen" value="${author.hoVaTen}">
                                                        <input type="hidden" name="ngaySinh" value="${author.ngaySinh}">
                                                        <input type="hidden" name="tieuSu" value="${author.tieuSu}">
                                                        <a href="${baseURL}/admin/authors?action=delete&id=${author.maTacGia}" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Bạn có chắc muốn xóa?');">
                                                            <i class="fa-solid fa-trash"></i> Xóa
                                                        </a>
                                                    </form>
                                                </td>
                                            </tr>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <tr>
                                            <td colspan="5" class="text-center">Chưa có tác giả nào</td>
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
