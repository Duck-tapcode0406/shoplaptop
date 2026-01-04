<%-- WebContent/views/khachhang/product/product-list.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-home.css">
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-product.css">

<title>Danh Mục Sách ${not empty searchQuery ? '- Tìm kiếm: ' : ''}${searchQuery} - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <%-- Hiển thị tiêu đề tìm kiếm nếu có --%>
        <c:if test="${not empty searchQuery}">
            <h2 style="margin-bottom: 1.5rem;">Kết quả tìm kiếm cho: "${searchQuery}"</h2>
        </c:if>

        <div class="product-page-layout">

            <%-- Sidebar Bộ lọc --%>
            <aside class="filter-sidebar">
                 <c:if test="${not empty selectedCategory || not empty selectedAuthor || not empty selectedPublisher || not empty searchQuery}">
                    <a href="${baseURL}/danh-sach-san-pham" class="clear-filters-btn"><i class="fa-solid fa-times"></i> Xóa tất cả bộ lọc</a>
                 </c:if>
                
                <h4><i class="fa-solid fa-filter"></i> Bộ Lọc Sản Phẩm</h4>
                
                <div class="filter-group">
                    <h5>Theo Thể Loại</h5>
                    <ul>
                        <c:forEach items="${listTheLoai}" var="tl">
                            <li>
                                <a href="${baseURL}/danh-sach-san-pham?category=${tl.maTheLoai}"
                                   class="${tl.maTheLoai == selectedCategory ? 'active-filter' : ''}">
                                    ${tl.tenTheLoai}
                                </a>
                            </li>
                        </c:forEach>
                    </ul>
                </div>
                
                <div class="filter-group">
                    <h5>Theo Tác Giả</h5>
                    <ul>
                         <c:forEach items="${listTacGia}" var="tg">
                            <li>
                                <a href="${baseURL}/danh-sach-san-pham?author=${tg.maTacGia}"
                                   class="${tg.maTacGia == selectedAuthor ? 'active-filter' : ''}">
                                   ${tg.hoVaTen}
                                </a>
                            </li>
                        </c:forEach>
                    </ul>
                </div>
                
                 <div class="filter-group">
                    <h5>Theo Nhà Xuất Bản</h5>
                    <ul>
                         <c:forEach items="${listNXB}" var="nxb">
                            <li>
                                <a href="${baseURL}/danh-sach-san-pham?publisher=${nxb.maNhaXuatBan}"
                                   class="${nxb.maNhaXuatBan == selectedPublisher ? 'active-filter' : ''}">
                                   ${nxb.tenNhaXuatBan}
                                </a>
                            </li>
                        </c:forEach>
                    </ul>
                </div>
            </aside>

            <%-- Danh sách sản phẩm --%>
            <section class="product-list-content">
                <div class="sort-bar">
                    <span>Hiển thị ${fn:length(listSanPham)} sản phẩm</span>
                    <%-- (Thêm Dropdown Sắp xếp ở đây nếu cần) --%>
                </div>

                <div class="product-grid">
                    <c:forEach items="${listSanPham}" var="sach">
                        <div class="product-card">
                            <%-- 
                              SỬA LỖI (IMAGE): Đổi /images/products/ 
                              thành /assets/images/products/
                            --%>
                            <a href="${baseURL}/chi-tiet-san-pham?id=${sach.maSanPham}" class="product-image-link">
                                <c:set var="imageName" value="${sach.hinhAnh}" />
                                <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                                    <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                                </c:if>
                                <img src="${baseURL}/assets/images/products/${imageName}" 
                                     alt="${sach.tenSanPham}" class="product-image"
                                     onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                            </a>
                            <a href="${baseURL}/chi-tiet-san-pham?id=${sach.maSanPham}" class="product-title">${sach.tenSanPham}</a>
                            <p class="product-author">${sach.tacGia.hoVaTen}</p>
                            <c:choose>
                                <c:when test="${not empty sessionScope.user and sessionScope.user.isGoiCuocConHan()}">
                                    <a href="${baseURL}/doc-sach?id=${sach.maSanPham}" class="btn btn-primary btn-add-to-cart">
                                        <i class="fa-solid fa-book-open"></i> Đọc sách
                                    </a>
                                </c:when>
                                <c:otherwise>
                                    <a href="${baseURL}/goi-cuoc" class="btn btn-primary btn-add-to-cart">
                                        <i class="fa-solid fa-book-open"></i> Đọc sách
                                    </a>
                                </c:otherwise>
                            </c:choose>
                        </div>
                    </c:forEach>

                    <c:if test="${empty listSanPham}">
                         <p style="grid-column: 1 / -1; text-align: center; color: #6c757d; margin-top: 2rem;">
                            <c:choose>
                                <c:when test="${not empty searchQuery}">Không tìm thấy sản phẩm nào khớp với "${searchQuery}".</c:when>
                                <c:otherwise>Không có sản phẩm nào trong danh mục này.</c:otherwise>
                            </c:choose>
                         </p>
                    </c:if>
                </div>
            </section>
        </div>
    </main>

    <style>
        .filter-sidebar ul li a.active-filter {
            color: var(--primary-color);
            font-weight: bold;
        }
    </style>

<jsp:include page="../layout/footer.jsp" />