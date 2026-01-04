<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="newsList" value="${requestScope.newsList}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin tức - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-newspaper"></i> Quản lý tin tức</h2>
                <a href="${baseURL}/admin/news/new" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Thêm tin tức mới
                </a>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Tiêu đề</th>
                            <th>Tác giả</th>
                            <th>Ngày đăng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <c:choose>
                            <c:when test="${newsList != null && newsList.size() > 0}">
                                <c:forEach var="news" items="${newsList}">
                                    <tr>
                                        <td><strong>${news.maTinTuc}</strong></td>
                                        <td>
                                            <a href="${baseURL}/chi-tiet-tin-tuc?id=${news.maTinTuc}" target="_blank" style="color: #667eea; text-decoration: none;">
                                                ${news.tieuDe}
                                            </a>
                                        </td>
                                        <td>${news.tacGia != null ? news.tacGia : (news.nguoiDang != null ? news.nguoiDang.hoVaTen : 'Không rõ')}</td>
                                        <td><fmt:formatDate value="${news.ngayDang}" pattern="dd/MM/yyyy HH:mm"/></td>
                                        <td>
                                            <span class="badge badge-success">Đã đăng</span>
                                        </td>
                                        <td class="actions">
                                            <a href="${baseURL}/admin/news/edit?id=${news.maTinTuc}" class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-edit"></i> Sửa
                                            </a>
                                            <a href="${baseURL}/admin/news?action=delete&id=${news.maTinTuc}" 
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
                                    <td colspan="6" class="text-center">Chưa có tin tức nào</td>
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
