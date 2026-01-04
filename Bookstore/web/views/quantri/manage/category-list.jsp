<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="categories" value="${requestScope.categories}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thể loại - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-tags"></i> Quản lý thể loại</h2>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Thêm thể loại mới</h3>
                    <form method="POST" action="${baseURL}/admin/categories">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="maTheLoai">Mã thể loại *</label>
                            <input type="text" id="maTheLoai" name="maTheLoai" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="tenTheLoai">Tên thể loại *</label>
                            <input type="text" id="tenTheLoai" name="tenTheLoai" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Thêm
                        </button>
                    </form>
                </div>

                <div>
                    <h3 style="margin-bottom: 1rem;">Danh sách thể loại</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên thể loại</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <c:choose>
                                    <c:when test="${categories != null && categories.size() > 0}">
                                        <c:forEach var="category" items="${categories}">
                                            <tr>
                                                <td><strong>${category.maTheLoai}</strong></td>
                                                <td>
                                                    <form method="POST" action="${baseURL}/admin/categories" style="display: inline;">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="maTheLoai" value="${category.maTheLoai}">
                                                        <div style="display: flex; gap: 0.5rem;">
                                                            <input type="text" name="tenTheLoai" value="${category.tenTheLoai}" 
                                                                   class="form-control" style="flex: 1;">
                                                            <button type="submit" class="btn btn-sm btn-info">
                                                                <i class="fa-solid fa-save"></i>
                                                            </button>
                                                            <a href="${baseURL}/admin/categories?action=delete&id=${category.maTheLoai}" 
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Bạn có chắc muốn xóa?');">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </c:forEach>
                                    </c:when>
                                    <c:otherwise>
                                        <tr>
                                            <td colspan="3" class="text-center">Chưa có thể loại nào</td>
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
