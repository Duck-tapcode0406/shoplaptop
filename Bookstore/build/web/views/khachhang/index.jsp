<%-- WebContent/views/khachhang/index.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-home.css">

<title>Trang Chủ - BookStore</title>

<jsp:include page="layout/header.jsp" />
    <main>
        <%-- Hero Banner --%>
        <section class="hero-banner">
            <h1>Thư Viện Sách Trực Tuyến</h1>
            <p>Đọc sách mọi lúc, mọi nơi với hàng ngàn đầu sách hay.</p>
            
            <form action="${baseURL}/tim-kiem" method="GET" class="home-search-bar">
                <input type="text" name="query" placeholder="Tìm kiếm tên sách, tác giả, thể loại...">
                <button type="submit" class="btn-search"><i class="fa-solid fa-search"></i></button>
            </form>
        </section>

        <div class="container">
            <%-- Sách Mới Ra Mắt --%>
            <section class="product-section">
                <div class="section-header">
                    <h2>Sách Mới Cập Nhật</h2>
                    <a href="${baseURL}/danh-sach-san-pham?sort=newest" class="view-all">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                
                <div class="product-grid">
                    <%-- Lặp qua ${danhSachSachMoi} từ TrangChuServlet --%>
                    <c:forEach items="${danhSachSachMoi}" var="sach">
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
                    
                    <c:if test="${empty danhSachSachMoi}">
                        <p>Chưa có sách mới nào.</p>
                    </c:if>
                </div>
            </section>
            
            <%-- Sách Đọc Nhiều --%>
            <section class="product-section">
                <div class="section-header">
                    <h2>Sách Đọc Nhiều Nhất</h2>
                    <a href="${baseURL}/danh-sach-san-pham?sort=popular" class="view-all">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                
                <div class="product-grid">
                     <%-- Lặp qua ${danhSachBanChay} từ TrangChuServlet --%>
                    <c:forEach items="${danhSachBanChay}" var="sach">
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
                    
                    <c:if test="${empty danhSachBanChay}">
                        <p>Chưa có sách bán chạy nào.</p>
                    </c:if>
                </div>
            </section>
        </div>
    </main>

<jsp:include page="layout/footer.jsp" />