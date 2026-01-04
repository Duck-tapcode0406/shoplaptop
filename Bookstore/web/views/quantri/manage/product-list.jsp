<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="products" value="${requestScope.products}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-book"></i> Quản lý sản phẩm</h2>
                <a href="${baseURL}/admin/products/new" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Thêm sản phẩm mới
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
                            <th>Mã SP</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Tác giả</th>
                            <th>Thể loại</th>
                            <th>Giá bán</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <c:choose>
                            <c:when test="${products != null && products.size() > 0}">
                                <c:forEach var="product" items="${products}">
                                    <tr>
                                        <td><strong>${product.maSanPham}</strong></td>
                                        <td>
                                            <c:if test="${not empty product.hinhAnh}">
                                                <c:choose>
                                                    <%-- Nếu hinhAnh đã có đường dẫn đầy đủ (bắt đầu bằng / hoặc assets/) --%>
                                                    <c:when test="${fn:startsWith(product.hinhAnh, '/') or fn:startsWith(product.hinhAnh, 'assets/')}">
                                                        <img src="${baseURL}${fn:startsWith(product.hinhAnh, '/') ? '' : '/'}${product.hinhAnh}" 
                                                             alt="${product.tenSanPham}" 
                                                             style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;"
                                                             onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                                                    </c:when>
                                                    <%-- Nếu hinhAnh chỉ là tên file (có thể có tiền tố Bookstore) --%>
                                                    <c:otherwise>
                                                        <c:set var="imageName" value="${product.hinhAnh}" />
                                                        <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                                                            <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                                                        </c:if>
                                                        <img src="${baseURL}/assets/images/products/${imageName}" 
                                                             alt="${product.tenSanPham}" 
                                                             style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;"
                                                             onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                                                    </c:otherwise>
                                                </c:choose>
                                            </c:if>
                                            <c:if test="${empty product.hinhAnh}">
                                                <div style="width: 50px; height: 70px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fa-solid fa-image" style="color: #ccc;"></i>
                                                </div>
                                            </c:if>
                                        </td>
                                        <td>${product.tenSanPham}</td>
                                        <td>${product.tacGia.hoVaTen}</td>
                                        <td>${product.theLoai.tenTheLoai}</td>
                                        <td>
                                            <fmt:formatNumber value="${product.giaBan}" type="currency" currencySymbol="₫" maxFractionDigits="0"/>
                                        </td>
                                        <td>
                                            <c:choose>
                                                <c:when test="${product.trangThai == 1}">
                                                    <span class="badge badge-success">Hiển thị</span>
                                                </c:when>
                                                <c:otherwise>
                                                    <span class="badge badge-danger">Ẩn</span>
                                                </c:otherwise>
                                            </c:choose>
                                        </td>
                                        <td class="actions">
                                            <a href="${baseURL}/admin/products/edit?id=${product.maSanPham}" class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-edit"></i> Sửa
                                            </a>
                                            <a href="${baseURL}/admin/products?action=toggle&id=${product.maSanPham}" 
                                               class="btn btn-sm ${product.trangThai == 1 ? 'btn-warning' : 'btn-success'}"
                                               onclick="return confirm('Bạn có chắc muốn ${product.trangThai == 1 ? 'ẩn' : 'hiển thị'} sản phẩm này?');">
                                                <i class="fa-solid fa-eye${product.trangThai == 1 ? '-slash' : ''}"></i> 
                                                ${product.trangThai == 1 ? 'Ẩn' : 'Hiện'}
                                            </a>
                                            <a href="${baseURL}/admin/products?action=delete&id=${product.maSanPham}" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                                <i class="fa-solid fa-trash"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                </c:forEach>
                            </c:when>
                            <c:otherwise>
                                <tr>
                                    <td colspan="8" class="text-center">Chưa có sản phẩm nào</td>
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
