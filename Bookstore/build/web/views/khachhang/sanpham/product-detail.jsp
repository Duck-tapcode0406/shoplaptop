<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-home.css">
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-product.css">
    
<style>
    .rating-stars { 
        display: inline-flex; 
        flex-direction: row-reverse; 
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
    }
    .rating-stars input[type=radio] { 
        display: none; 
    }
    .rating-stars label { 
        color: #ddd; 
        font-size: 2.2rem; 
        cursor: pointer; 
        transition: all 0.2s ease;
        -webkit-user-select: none; 
        user-select: none;
        padding: 4px;
        display: inline-block;
    }
    .rating-stars label:hover,
    .rating-stars label:hover ~ label { 
        color: #ffc107 !important; 
        transform: scale(1.15);
    }
    .rating-stars input[type=radio]:checked ~ label { 
        color: #f0c14b !important; 
    }
    .rating-stars input[type=radio]:checked + label {
        color: #f0c14b !important;
    }
    
    /* Hiển thị ngôi sao trong danh sách đánh giá */
    .display-stars { 
        display: inline-flex;
        gap: 3px;
        align-items: center;
    }
    .display-stars .fa-star { 
        color: #f0c14b; 
        font-size: 1.1rem;
    }
    .display-stars .fa-regular.fa-star { 
        color: #ddd; 
    }
    
    /* Form đánh giá */
    .review-form-container {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border: 1px solid #dee2e6;
    }
    .review-form-container h5 {
        margin-bottom: 1rem;
        color: #333;
    }
    .review-form-container .form-group {
        margin-bottom: 1rem;
    }
    .review-form-container label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }
    .review-form-container textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: inherit;
        resize: vertical;
    }
    
    /* Danh sách đánh giá */
    .review-item {
        padding: 1.5rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s;
    }
    .review-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .review-author {
        font-weight: 700;
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 0.5rem;
    }
    .review-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #666;
    }
    .review-content {
        color: #555;
        line-height: 1.6;
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .review-date {
        color: #999;
    }
    
    /* Animation cho đánh giá mới */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .review-item.new-review {
        animation: slideIn 0.5s ease-out;
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .review-author-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }
    .review-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .review-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .review-avatar i {
        font-size: 2rem;
        color: #999;
    }
    .review-author-details {
        flex: 1;
    }
    .review-author-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    .review-date-time {
        font-size: 0.85rem;
        color: #666;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .review-rating {
        flex-shrink: 0;
    }
    
    /* Review Actions */
    .review-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
        flex-wrap: wrap;
    }
    .review-action-btn {
        background: none;
        border: none;
        color: #666;
        font-size: 0.9rem;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
    }
    .review-action-btn:hover {
        background: #f8f9fa;
        color: #333;
    }
    .review-action-btn i {
        font-size: 1rem;
    }
    .like-btn:hover {
        color: #28a745;
    }
    .dislike-btn:hover {
        color: #dc3545;
    }
    .reply-btn:hover {
        color: #007bff;
    }
    .report-btn:hover {
        color: #ffc107;
    }
    .like-btn.active {
        color: #28a745;
    }
    .dislike-btn.active {
        color: #dc3545;
    }
    .like-count, .dislike-count {
        font-weight: 600;
        margin-left: 0.25rem;
        min-width: 1.5rem;
        display: inline-block;
        text-align: center;
    }
    .delete-btn:hover {
        color: #dc3545;
    }
    .reply-form-container {
        border-left: 3px solid #007bff;
    }
    .reply-form textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: inherit;
        resize: vertical;
    }
    .replies-list {
        margin-top: 1rem;
        padding-left: 1rem;
        border-left: 2px solid #e9ecef;
    }
    .reply-item {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .reply-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .reply-author-details {
        flex: 1;
    }
    .reply-author {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    .reply-content {
        color: #555;
        font-size: 0.9rem;
        margin: 0;
        line-height: 1.5;
    }
    .reply-date {
        font-size: 0.8rem;
        color: #999;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
</style>

<title>${not empty sanPham ? sanPham.tenSanPham : 'Chi Tiết Sách'} - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <%-- Nút quay lại --%>
        <div style="margin-bottom: 1.5rem;">
            <a href="${baseURL}/danh-sach-san-pham" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách sản phẩm
            </a>
        </div>

        <c:if test="${empty errorMessage and not empty sanPham}">
            <div class="product-detail-layout">
                <div class="product-detail-image">
                    <c:set var="imageName" value="${sanPham.hinhAnh}" />
                    <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                        <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                    </c:if>
                    <img src="${baseURL}/assets/images/products/${imageName}" 
                         alt="${sanPham.tenSanPham}" id="mainProductImage"
                         onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                    <%-- (Thêm gallery ảnh nhỏ ở đây nếu cần) --%>
                </div>

                <%-- Cột thông tin & Mua hàng --%>
                <div class="product-detail-info">
                    <h1 class="product-title">${sanPham.tenSanPham}</h1>
                    <div class="product-meta">
                        <span>Tác giả: <a href="${baseURL}/danh-sach-san-pham?author=${sanPham.tacGia.maTacGia}">${sanPham.tacGia.hoVaTen}</a></span>
                        <span>Thể loại: <a href="${baseURL}/danh-sach-san-pham?category=${sanPham.theLoai.maTheLoai}">${sanPham.theLoai.tenTheLoai}</a></span>
                    </div>

                    <div class="product-availability">
                        <c:choose>
                            <c:when test="${not empty sessionScope.user and sessionScope.user.isGoiCuocConHan()}">
                                <span style="color: green; font-weight: bold; font-size: 1.1rem;">
                                    <i class="fa-solid fa-check-circle"></i> Bạn có thể đọc sách này
                                </span>
                            </c:when>
                            <c:otherwise>
                                <span style="color: #ff6b35; font-weight: bold; font-size: 1.1rem;">
                                    <i class="fa-solid fa-info-circle"></i> Đăng ký gói cước để đọc sách
                                </span>
                            </c:otherwise>
                        </c:choose>
                    </div>
                    
                    <%-- Button đọc sách --%>
                    <div class="add-to-cart-form">
                        <c:choose>
                            <c:when test="${not empty sessionScope.user and sessionScope.user.isGoiCuocConHan()}">
                                <a href="${baseURL}/doc-sach?id=${sanPham.maSanPham}" class="btn btn-primary btn-add-to-cart-lg" style="text-decoration: none; display: block; text-align: center;">
                                    <i class="fa-solid fa-book-open"></i> Đọc sách ngay
                                </a>
                            </c:when>
                            <c:otherwise>
                                <a href="${baseURL}/goi-cuoc" class="btn btn-primary btn-add-to-cart-lg" style="text-decoration: none; display: block; text-align: center;">
                                    <i class="fa-solid fa-book-open"></i> Đọc sách
                                </a>
                            </c:otherwise>
                        </c:choose>
                    </div>

                    <div class="product-extra-meta">
                        <p><strong>Nhà xuất bản:</strong> ${sanPham.nhaXuatBan.tenNhaXuatBan}</p>
                        <p><strong>Năm xuất bản:</strong> ${sanPham.namXuatBan}</p>
                        <p><strong>Ngôn ngữ:</strong> ${sanPham.ngonNgu}</p>
                    </div>
                </div>
            </div>

            <%-- Tab Mô tả và Đánh giá --%>
            <div class="product-tabs">
                <div class="tab-headers">
                    <button class="tab-link active" data-tab="tab-description">Mô Tả Sản Phẩm</button>
                    <button class="tab-link" data-tab="tab-reviews">Đánh Giá (${fn:length(listDanhGia)})</button>
                </div>

                <div id="tab-description" class="tab-content active">
                    <h4>Mô Tả Chi Tiết</h4>
                    <div class="product-description" style="line-height: 1.8; color: #333;">
                        <c:choose>
                            <c:when test="${not empty sanPham.moTa}">
                                <div style="white-space: pre-wrap; word-wrap: break-word;">${fn:replace(fn:escapeXml(sanPham.moTa), "
", "<br/>")}</div>
                            </c:when>
                            <c:otherwise>
                                <p style="color: #999; font-style: italic;">Chưa có mô tả cho sản phẩm này.</p>
                            </c:otherwise>
                        </c:choose>
                    </div>
                    <div class="product-specs" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                        <h5 style="margin-bottom: 1rem;">Thông Tin Chi Tiết</h5>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.75rem; font-weight: 600; width: 40%;">Tác giả:</td>
                                <td style="padding: 0.75rem;">${sanPham.tacGia.hoVaTen}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.75rem; font-weight: 600;">Thể loại:</td>
                                <td style="padding: 0.75rem;">${sanPham.theLoai.tenTheLoai}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.75rem; font-weight: 600;">Nhà xuất bản:</td>
                                <td style="padding: 0.75rem;">${sanPham.nhaXuatBan.tenNhaXuatBan}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.75rem; font-weight: 600;">Năm xuất bản:</td>
                                <td style="padding: 0.75rem;">${sanPham.namXuatBan}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 0.75rem; font-weight: 600;">Ngôn ngữ:</td>
                                <td style="padding: 0.75rem;">${sanPham.ngonNgu}</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; font-weight: 600;">Số lượng còn lại:</td>
                                <td style="padding: 0.75rem;">
                                    <c:choose>
                                        <c:when test="${sanPham.soLuong > 0}">
                                            <span style="color: green; font-weight: bold;">${sanPham.soLuong} quyển</span>
                                        </c:when>
                                        <c:otherwise>
                                            <span style="color: red; font-weight: bold;">Hết hàng</span>
                                        </c:otherwise>
                                    </c:choose>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div id="tab-reviews" class="tab-content">
                    <h4>Đánh Giá Từ Khách Hàng</h4>
                    
                    <%-- Form viết đánh giá --%>
                    <c:choose>
                        <%-- Chưa đăng nhập --%>
                        <c:when test="${empty sessionScope.user}">
                            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                                <p style="margin: 0; color: #856404; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-info-circle" style="font-size: 1.2rem;"></i>
                                    <span><strong>Đăng nhập</strong> mới có thể viết đánh giá về sản phẩm.</span>
                                    <a href="${baseURL}/dang-nhap?redirect=${baseURL}/chi-tiet-san-pham?id=${sanPham.maSanPham}#tab-reviews" 
                                       style="margin-left: auto; color: #004085; text-decoration: underline; font-weight: 600;">
                                        Đăng nhập ngay
                                    </a>
                                </p>
                            </div>
                        </c:when>
                        <%-- Đã đăng nhập nhưng chưa mua --%>
                        <c:when test="${sessionScope.daMuaHang == false}">
                            <div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                                <p style="margin: 0; color: #666; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-lightbulb" style="font-size: 1.2rem;"></i>
                                    <span>Chỉ những khách hàng đã mua sản phẩm này mới có thể để lại đánh giá.</span>
                                </p>
                            </div>
                        </c:when>
                        <%-- Đã đăng nhập và đã mua --%>
                        <c:otherwise>
                            <div class="review-form-container">
                                <h5><i class="fa-solid fa-star"></i> Viết đánh giá của bạn</h5>
                                <form id="reviewForm" action="${baseURL}/dang-danh-gia" method="POST">
                                <input type="hidden" name="productId" value="${sanPham.maSanPham}">
                                <div class="form-group">
                                        <label>Xếp hạng của bạn: <span id="rating-text" style="color: #f0c14b; font-weight: normal;">Tuyệt vời</span></label>
                                    <div class="rating-stars">
                                            <input type="radio" id="star5" name="rating" value="5" checked required/>
                                            <label for="star5" title="5 sao - Tuyệt vời"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4"/>
                                            <label for="star4" title="4 sao - Rất tốt"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3"/>
                                            <label for="star3" title="3 sao - Tốt"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2"/>
                                            <label for="star2" title="2 sao - Tạm được"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1"/>
                                            <label for="star1" title="1 sao - Không hài lòng"><i class="fa-solid fa-star"></i></label>
                                        </div>
                                </div>
                                <div class="form-group">
                                    <label for="reviewContent">Nội dung đánh giá:</label>
                                        <textarea id="reviewContent" name="content" rows="4" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm này..." required></textarea>
                                        <small style="color: #666; display: block; margin-top: 0.25rem;">Tối thiểu 10 ký tự</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-paper-plane"></i> Gửi Đánh Giá
                                    </button>
                                </form>
                            </div>
                            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #dee2e6;">
                        </c:otherwise>
                    </c:choose>

                    <%-- Thống kê đánh giá --%>
                    <div id="review-stats">
                        <c:if test="${not empty listDanhGia}">
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <strong style="font-size: 1.5rem; color: #333;">${fn:length(listDanhGia)}</strong>
                                        <span style="color: #666;">đánh giá</span>
                                    </div>
                                    <c:set var="tongSao" value="0"/>
                                    <c:forEach items="${listDanhGia}" var="dg">
                                        <c:set var="tongSao" value="${tongSao + dg.soSao}"/>
                                    </c:forEach>
                                    <c:if test="${fn:length(listDanhGia) > 0}">
                                        <c:set var="diemTB" value="${tongSao / fn:length(listDanhGia)}"/>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 1.3rem; font-weight: bold; color: #f0c14b;">
                                                <fmt:formatNumber value="${diemTB}" maxFractionDigits="1" minFractionDigits="1"/>
                                            </span>
                                            <div class="display-stars">
                                                <c:forEach begin="1" end="5" varStatus="loop">
                                                    <i class="fa-${loop.index <= diemTB ? 'solid' : 'regular'} fa-star"></i>
                                                </c:forEach>
                                            </div>
                                        </div>
                                    </c:if>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                                        <i class="fa-solid fa-thumbs-up" style="color: #28a745;"></i>
                                        <span style="color: #666;">
                                            <strong id="total-likes" style="color: #333;">0</strong> lượt thích
                                        </span>
                                    </div>
                                </div>
                        </div>
                    </c:if>
                    </div>

                    <%-- Danh sách đánh giá đã có --%>
                    <div class="review-list" id="review-list">
                        <c:choose>
                            <c:when test="${not empty listDanhGia}">
                                <c:forEach items="${listDanhGia}" var="dg">
                                    <div class="review-item" data-review-id="${dg.maDanhGia}">
                                        <div class="review-header">
                                            <div class="review-author-info">
                                                <%-- Avatar --%>
                                                <div class="review-avatar">
                                                    <c:choose>
                                                        <c:when test="${not empty dg.khachHang.duongDanAnh}">
                                                            <img src="${baseURL}/assets/images/avatars/${dg.khachHang.duongDanAnh}" 
                                                                 alt="${dg.khachHang.hoVaTen}" 
                                                                 onerror="this.onerror=null; this.src='${baseURL}/assets/images/default-avatar.png'; this.outerHTML='<i class=\'fa-solid fa-user-circle\'></i>'">
                                                        </c:when>
                                                        <c:otherwise>
                                                            <i class="fa-solid fa-user-circle"></i>
                                                        </c:otherwise>
                                                    </c:choose>
                                                </div>
                                                <div class="review-author-details">
                                                    <div class="review-author-name">${dg.khachHang.hoVaTen}</div>
                                                    <div class="review-date-time">
                                                        <i class="fa-solid fa-clock"></i>
                                                        <fmt:formatDate value="${dg.ngayDanhGia}" pattern="dd/MM/yyyy HH:mm"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="review-rating">
                                                <span class="display-stars" title="${dg.soSao} sao">
                                                    <c:forEach begin="1" end="5" varStatus="loop">
                                                        <i class="fa-${loop.index <= dg.soSao ? 'solid' : 'regular'} fa-star"></i>
                                                    </c:forEach>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="review-content">${fn:escapeXml(dg.noiDung)}</p>
                                        <div class="review-actions">
                                            <c:set var="hasLikedKey" value="hasLiked_${dg.maDanhGia}" />
                                            <c:set var="hasDislikedKey" value="hasDisliked_${dg.maDanhGia}" />
                                            <c:set var="likeCountKey" value="likeCount_${dg.maDanhGia}" />
                                            <c:set var="dislikeCountKey" value="dislikeCount_${dg.maDanhGia}" />
                                            <button class="review-action-btn like-btn ${requestScope[hasLikedKey] ? 'active' : ''}" 
                                                    data-review-id="${dg.maDanhGia}" title="Thích">
                                                <i class="fa-${requestScope[hasLikedKey] ? 'solid' : 'regular'} fa-thumbs-up"></i>
                                                <span class="like-count">${requestScope[likeCountKey] != null ? requestScope[likeCountKey] : 0}</span>
                                            </button>
                                            <button class="review-action-btn dislike-btn ${requestScope[hasDislikedKey] ? 'active' : ''}" 
                                                    data-review-id="${dg.maDanhGia}" title="Không thích">
                                                <i class="fa-${requestScope[hasDislikedKey] ? 'solid' : 'regular'} fa-thumbs-down"></i>
                                                <span class="dislike-count">${requestScope[dislikeCountKey] != null ? requestScope[dislikeCountKey] : 0}</span>
                                            </button>
                                            <%-- Nút trả lời - chỉ hiển thị khi đã đăng nhập --%>
                                            <c:if test="${not empty sessionScope.user}">
                                                <button class="review-action-btn reply-btn" data-review-id="${dg.maDanhGia}" data-author-id="${dg.khachHang.maKhachHang}" title="Trả lời">
                                                    <i class="fa-regular fa-comment"></i>
                                                    Trả lời
                                                </button>
                                            </c:if>
                                            <%-- Chỉ hiện nút báo cáo nếu không phải đánh giá của mình --%>
                                            <c:if test="${empty sessionScope.user || sessionScope.user.maKhachHang != dg.khachHang.maKhachHang}">
                                                <button class="review-action-btn report-btn" data-review-id="${dg.maDanhGia}" title="Báo cáo">
                                                    <i class="fa-regular fa-flag"></i>
                                                    Báo cáo
                                                </button>
                                            </c:if>
                                            <%-- Chỉ hiện nút xóa nếu là đánh giá của mình --%>
                                            <c:if test="${not empty sessionScope.user && sessionScope.user.maKhachHang == dg.khachHang.maKhachHang}">
                                                <button class="review-action-btn delete-btn" data-review-id="${dg.maDanhGia}" title="Xóa đánh giá">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                    Xóa
                                                </button>
                                            </c:if>
                                        </div>
                                        <%-- Form trả lời (ẩn mặc định) - chỉ hiển thị khi đã đăng nhập --%>
                                        <c:if test="${not empty sessionScope.user}">
                                            <div class="reply-form-container" id="reply-form-${dg.maDanhGia}" style="display: none; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                                                <form class="reply-form" data-review-id="${dg.maDanhGia}">
                                                    <textarea class="reply-content" rows="3" placeholder="Viết câu trả lời của bạn..." required></textarea>
                                                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                                            <i class="fa-solid fa-paper-plane"></i> Gửi trả lời
                                                        </button>
                                                        <button type="button" class="btn btn-secondary cancel-reply-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                                            Hủy
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </c:if>
                                        <%-- Danh sách trả lời --%>
                                        <div class="replies-list" id="replies-${dg.maDanhGia}" data-review-id="${dg.maDanhGia}">
                                            <c:set var="repliesKey" value="replies_${dg.maDanhGia}" />
                                            <c:set var="repliesList" value="${requestScope[repliesKey]}" />
                                            <c:if test="${not empty repliesList}">
                                                <c:forEach var="reply" items="${repliesList}">
                                                    <div class="reply-item" data-reply-id="${reply.maTraLoi}">
                                                        <div class="reply-header">
                                                            <div class="review-avatar" style="width: 32px; height: 32px; font-size: 1rem;">
                                                                <c:choose>
                                                                    <c:when test="${not empty reply.khachHang.duongDanAnh}">
                                                                        <img src="${baseURL}/assets/images/avatars/${reply.khachHang.duongDanAnh}" 
                                                                             alt="${reply.khachHang.hoVaTen}" 
                                                                             onerror="this.onerror=null; this.src='${baseURL}/assets/images/default-avatar.png'; this.outerHTML='<i class=\'fa-solid fa-user-circle\'></i>'">
                                                                    </c:when>
                                                                    <c:otherwise>
                                                                        <i class="fa-solid fa-user-circle"></i>
                                                                    </c:otherwise>
                                                                </c:choose>
                                                            </div>
                                                            <div class="reply-author-details">
                                                                <div class="reply-author">${reply.khachHang.hoVaTen}</div>
                                                                <div class="reply-date">
                                                                    <i class="fa-solid fa-clock"></i>
                                                                    <fmt:formatDate value="${reply.ngayTraLoi}" pattern="dd/MM/yyyy HH:mm"/>
                                                                </div>
                                                            </div>
                                                            <c:if test="${not empty sessionScope.user && sessionScope.user.maKhachHang == reply.khachHang.maKhachHang}">
                                                                <button class="review-action-btn delete-reply-btn" data-reply-id="${reply.maTraLoi}" title="Xóa trả lời" style="margin-left: auto; font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                                                    <i class="fa-regular fa-trash-can"></i>
                                                                </button>
                                                            </c:if>
                                                        </div>
                                                        <p class="reply-content">${fn:escapeXml(reply.noiDung)}</p>
                                                    </div>
                                                </c:forEach>
                                            </c:if>
                                        </div>
                                    </div>
                                </c:forEach>
                            </c:when>
                            <c:otherwise>
                                <div style="text-align: center; padding: 2rem; color: #999;">
                                    <i class="fa-solid fa-star" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                                </div>
                            </c:otherwise>
                        </c:choose>
                    </div>
                    
                    <script>
                        // Xử lý hiển thị text khi chọn sao và đảm bảo giá trị đúng
                        const ratingTextEl = document.getElementById('rating-text');
                        if (ratingTextEl) {
                            // Set mặc định cho 5 sao
                            ratingTextEl.textContent = 'Tuyệt vời';
                            
                            document.querySelectorAll('.rating-stars input[type="radio"]').forEach(function(radio) {
                                radio.addEventListener('change', function() {
                                    const rating = parseInt(this.value);
                                    const texts = {
                                        1: 'Không hài lòng',
                                        2: 'Tạm được',
                                        3: 'Tốt',
                                        4: 'Rất tốt',
                                        5: 'Tuyệt vời'
                                    };
                                    if (ratingTextEl) {
                                        ratingTextEl.textContent = texts[rating] || '';
                                    }
                                    
                                    // Debug: Log để kiểm tra giá trị
                                    console.log('Đã chọn: ' + rating + ' sao');
                                });
                                
                                // Đảm bảo 5 sao được checked mặc định
                                if (radio.value === '5' && !radio.checked) {
                                    radio.checked = true;
                                }
                            });
                        }
                        
                        // Validate form trước khi submit và đảm bảo rating được chọn đúng
                        document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
                            const content = document.getElementById('reviewContent').value.trim();
                            if (content.length < 10) {
                                e.preventDefault();
                                alert('Vui lòng nhập ít nhất 10 ký tự cho nội dung đánh giá.');
                                return false;
                            }
                            
                            // Đảm bảo có rating được chọn (mặc định là 5)
                            const selectedRating = document.querySelector('input[name="rating"]:checked');
                            if (!selectedRating) {
                                // Nếu không có gì được chọn, set mặc định là 5
                                const star5 = document.getElementById('star5');
                                if (star5) {
                                    star5.checked = true;
                                }
                            }
                            
                            // Debug: Log giá trị rating trước khi submit
                            const ratingValue = selectedRating ? selectedRating.value : '5';
                            console.log('Submitting với rating: ' + ratingValue + ' sao');
                        });
                        
                        // Real-time refresh đánh giá mỗi 10 giây
                        let lastReviewCount = ${fn:length(listDanhGia)};
                        let lastReviewIds = new Set();
                        <c:forEach items="${listDanhGia}" var="dg">
                            lastReviewIds.add('${dg.maDanhGia}');
                        </c:forEach>
                        
                        function loadReviews() {
                            fetch('${baseURL}/chi-tiet-san-pham?id=${sanPham.maSanPham}&ajax=reviews')
                                .then(response => response.text())
                                .then(html => {
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(html, 'text/html');
                                    
                                    // Cập nhật thống kê nếu có
                                    const newStatsSection = doc.querySelector('#review-stats');
                                    if (newStatsSection) {
                                        const currentStats = document.getElementById('review-stats');
                                        if (currentStats) {
                                            currentStats.innerHTML = newStatsSection.innerHTML;
                                        }
                                    }
                                    
                                    const newReviewList = doc.querySelector('#review-list');
                                    if (newReviewList) {
                                        const currentItems = newReviewList.querySelectorAll('.review-item');
                                        const currentCount = currentItems.length;
                                        
                                        // Kiểm tra có đánh giá mới không
                                        let hasNewReview = false;
                                        const newReviewIds = [];
                                        currentItems.forEach(function(item) {
                                            const reviewId = item.getAttribute('data-review-id');
                                            newReviewIds.push(reviewId);
                                            if (!lastReviewIds.has(reviewId)) {
                                                hasNewReview = true;
                                            }
                                        });
                                        
                                        if (hasNewReview || currentCount !== lastReviewCount) {
                                            // Có đánh giá mới - cập nhật với animation
                                            const reviewListContainer = document.getElementById('review-list');
                                            
                                            // Lưu scroll position
                                            const scrollPos = reviewListContainer.scrollTop;
                                            
                                            // Cập nhật HTML
                                            reviewListContainer.innerHTML = newReviewList.innerHTML;
                                            
                                            // Không cần re-bind vì đã dùng event delegation
                                            
                                            // Thêm animation cho đánh giá mới
                                            const updatedItems = reviewListContainer.querySelectorAll('.review-item');
                                            updatedItems.forEach(function(item, index) {
                                                const reviewId = item.getAttribute('data-review-id');
                                                if (!lastReviewIds.has(reviewId)) {
                                                    item.classList.add('new-review');
                                                    // Scroll đến đánh giá mới nếu đang ở tab reviews
                                                    const reviewsTab = document.getElementById('tab-reviews');
                                                    if (reviewsTab && reviewsTab.classList.contains('active')) {
                                                        setTimeout(() => {
                                                            item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                                        }, 100);
                                                    }
                                                }
                                            });
                                            
                                            // Cập nhật lastReviewIds
                                            lastReviewIds.clear();
                                            newReviewIds.forEach(id => lastReviewIds.add(id));
                                            lastReviewCount = currentCount;
                                            
                                            // Cập nhật số lượng đánh giá trong tab
                                            const tabLink = document.querySelector('[data-tab="tab-reviews"]');
                                            if (tabLink) {
                                                tabLink.innerHTML = '<i class="fa-solid fa-star"></i> Đánh Giá (' + currentCount + ')';
                                            }
                                            
                                            // Hiển thị thông báo có đánh giá mới
                                            const reviewsTab = document.getElementById('tab-reviews');
                                            if (reviewsTab && reviewsTab.classList.contains('active') && hasNewReview) {
                                                // Hiển thị notification
                                                if (typeof showNotification === 'function') {
                                                    showNotification('info', 'Có đánh giá mới!', 'Đã có đánh giá mới được thêm vào.', 3000);
                                                }
                                            }
                                        }
                                    }
                                })
                                .catch(error => console.error('Lỗi khi tải đánh giá:', error));
                        }
                        
                        // Refresh mỗi 10 giây
                        setInterval(loadReviews, 10000);
                        
                        // Refresh ngay sau khi submit form (nếu có)
                        document.getElementById('reviewForm')?.addEventListener('submit', function() {
                            setTimeout(loadReviews, 2000); // Đợi 2 giây sau khi submit để server xử lý
                        });
                        
                        // Sử dụng Event Delegation để xử lý các nút tương tác đánh giá
                        // Event delegation cho phép xử lý events ngay cả khi HTML được thay thế
                        const reviewListContainer = document.getElementById('review-list');
                        if (reviewListContainer) {
                            // Xử lý tất cả các nút tương tác trong một event listener duy nhất
                            reviewListContainer.addEventListener('click', function(e) {
                                if (e.target.closest('.delete-btn')) {
                                    const btn = e.target.closest('.delete-btn');
                                    const reviewId = btn.getAttribute('data-review-id');
                                    const reviewItem = btn.closest('.review-item');
                                    
                                    if (confirm('Bạn có chắc muốn xóa đánh giá này? Hành động này không thể hoàn tác.')) {
                                        // Disable button để tránh click nhiều lần
                                        btn.disabled = true;
                                        const originalHTML = btn.innerHTML;
                                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xóa...';
                                        
                                        // Gửi request xóa đến server
                                        fetch('${baseURL}/xoa-danh-gia', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded',
                                            },
                                            body: 'reviewId=' + encodeURIComponent(reviewId) + '&productId=' + encodeURIComponent('${sanPham.maSanPham}')
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Xóa thành công - xóa khỏi DOM với animation
                                                if (reviewItem) {
                                                    reviewItem.style.transition = 'opacity 0.3s, transform 0.3s';
                                                    reviewItem.style.opacity = '0';
                                                    reviewItem.style.transform = 'translateX(-20px)';
                                                    setTimeout(function() {
                                                        reviewItem.remove();
                                                        
                                                        // Cập nhật số lượng đánh giá trong tab header
                                                        const tabLink = document.querySelector('[data-tab="tab-reviews"]');
                                                        if (tabLink) {
                                                            const currentCount = document.querySelectorAll('.review-item').length;
                                                            tabLink.innerHTML = 'Đánh Giá (' + currentCount + ')';
                                                        }
                                                        
                                                        // Reload lại danh sách đánh giá để đồng bộ với server
                                                        loadReviews();
                                                        
                                                        // Hiển thị thông báo thành công
                                                        if (typeof showNotification === 'function') {
                                                            showNotification('success', 'Thành công', 'Đã xóa đánh giá thành công.', 3000);
                                                        } else {
                                                            alert('Đã xóa đánh giá thành công.');
                                                        }
                                                    }, 300);
                                                }
                                            } else {
                                                // Xóa thất bại
                                                btn.disabled = false;
                                                btn.innerHTML = originalHTML;
                                                alert(data.message || 'Không thể xóa đánh giá. Vui lòng thử lại.');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Lỗi khi xóa đánh giá:', error);
                                            btn.disabled = false;
                                            btn.innerHTML = originalHTML;
                                            alert('Đã xảy ra lỗi khi xóa đánh giá. Vui lòng thử lại.');
                                        });
                                    }
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                
                                // Xử lý nút like
                                if (e.target.closest('.like-btn')) {
                                    const btn = e.target.closest('.like-btn');
                                    const reviewId = btn.getAttribute('data-review-id');
                                    const isActive = btn.classList.contains('active');
                                    
                                    // Disable button tạm thời
                                    btn.disabled = true;
                                    
                                    // Gọi backend
                                    fetch('${baseURL}/like-danh-gia', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'reviewId=' + encodeURIComponent(reviewId) + '&action=like'
                                    })
                                    .then(response => {
                                        // Kiểm tra status code
                                        if (!response.ok) {
                                            return response.text().then(text => {
                                                try {
                                                    return JSON.parse(text);
                                                } catch (e) {
                                                    return { success: false, message: 'Lỗi ' + response.status + ': ' + text };
                                                }
                                            });
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        btn.disabled = false;
                                        if (data && data.success) {
                                            // Cập nhật UI
                                            if (data.liked) {
                                                btn.classList.add('active');
                                                btn.querySelector('i').className = 'fa-solid fa-thumbs-up';
                                            } else {
                                                btn.classList.remove('active');
                                                btn.querySelector('i').className = 'fa-regular fa-thumbs-up';
                                            }
                                            
                                            const count = btn.querySelector('.like-count');
                                            if (count) {
                                                count.textContent = (data.likeCount !== undefined && data.likeCount !== null) ? data.likeCount : 0;
                                                // Đảm bảo số lượng luôn hiển thị
                                                if (count.textContent === '0' || count.textContent === '') {
                                                    count.textContent = '0';
                                                }
                                            }
                                            
                                            // Cập nhật dislike nếu có
                                            const reviewItem = btn.closest('.review-item');
                                            const dislikeBtn = reviewItem ? reviewItem.querySelector('.dislike-btn') : null;
                                            if (dislikeBtn) {
                                                if (data.liked) {
                                                    dislikeBtn.classList.remove('active');
                                                    const dislikeIcon = dislikeBtn.querySelector('i');
                                                    if (dislikeIcon) {
                                                        dislikeIcon.className = 'fa-regular fa-thumbs-down';
                                                    }
                                                }
                                                const dislikeCount = dislikeBtn.querySelector('.dislike-count');
                                                if (dislikeCount) {
                                                    dislikeCount.textContent = (data.dislikeCount !== undefined && data.dislikeCount !== null) ? data.dislikeCount : 0;
                                                    // Đảm bảo số lượng luôn hiển thị
                                                    if (dislikeCount.textContent === '0' || dislikeCount.textContent === '') {
                                                        dislikeCount.textContent = '0';
                                                    }
                                                }
                                            }
                                            
                                            if (typeof updateTotalLikes === 'function') {
                                                updateTotalLikes();
                                            }
                                        } else {
                                            const errorMsg = data && data.message ? data.message : 'Không thể thực hiện thao tác này.';
                                            alert(errorMsg);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Lỗi khi like:', error);
                                        btn.disabled = false;
                                        alert('Đã xảy ra lỗi khi thực hiện thao tác. Vui lòng kiểm tra kết nối và thử lại.');
                                    });
                                    
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                
                                // Xử lý nút dislike
                                if (e.target.closest('.dislike-btn')) {
                                    const btn = e.target.closest('.dislike-btn');
                                    const reviewId = btn.getAttribute('data-review-id');
                                    const isActive = btn.classList.contains('active');
                                    
                                    // Disable button tạm thời
                                    btn.disabled = true;
                                    
                                    // Gọi backend
                                    fetch('${baseURL}/dislike-danh-gia', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'reviewId=' + encodeURIComponent(reviewId) + '&action=dislike'
                                    })
                                    .then(response => {
                                        // Kiểm tra status code
                                        if (!response.ok) {
                                            return response.text().then(text => {
                                                try {
                                                    return JSON.parse(text);
                                                } catch (e) {
                                                    return { success: false, message: 'Lỗi ' + response.status + ': ' + text };
                                                }
                                            });
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        btn.disabled = false;
                                        if (data && data.success) {
                                            // Cập nhật UI
                                            if (data.disliked) {
                                                btn.classList.add('active');
                                                const icon = btn.querySelector('i');
                                                if (icon) {
                                                    icon.className = 'fa-solid fa-thumbs-down';
                                                }
                                            } else {
                                                btn.classList.remove('active');
                                                const icon = btn.querySelector('i');
                                                if (icon) {
                                                    icon.className = 'fa-regular fa-thumbs-down';
                                                }
                                            }
                                            
                                            const count = btn.querySelector('.dislike-count');
                                            if (count) {
                                                count.textContent = (data.dislikeCount !== undefined && data.dislikeCount !== null) ? data.dislikeCount : 0;
                                                // Đảm bảo số lượng luôn hiển thị
                                                if (count.textContent === '0' || count.textContent === '') {
                                                    count.textContent = '0';
                                                }
                                            }
                                            
                                            // Cập nhật like nếu có
                                            const reviewItem = btn.closest('.review-item');
                                            const likeBtn = reviewItem ? reviewItem.querySelector('.like-btn') : null;
                                            if (likeBtn) {
                                                if (data.disliked) {
                                                    likeBtn.classList.remove('active');
                                                    const likeIcon = likeBtn.querySelector('i');
                                                    if (likeIcon) {
                                                        likeIcon.className = 'fa-regular fa-thumbs-up';
                                                    }
                                                }
                                                const likeCount = likeBtn.querySelector('.like-count');
                                                if (likeCount) {
                                                    likeCount.textContent = (data.likeCount !== undefined && data.likeCount !== null) ? data.likeCount : 0;
                                                    // Đảm bảo số lượng luôn hiển thị
                                                    if (likeCount.textContent === '0' || likeCount.textContent === '') {
                                                        likeCount.textContent = '0';
                                                    }
                                                }
                                            }
                                            
                                            if (typeof updateTotalLikes === 'function') {
                                                updateTotalLikes();
                                            }
                                        } else {
                                            const errorMsg = data && data.message ? data.message : 'Không thể thực hiện thao tác này.';
                                            alert(errorMsg);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Lỗi khi dislike:', error);
                                        btn.disabled = false;
                                        alert('Đã xảy ra lỗi khi thực hiện thao tác. Vui lòng kiểm tra kết nối và thử lại.');
                                    });
                                    
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                
                                // Xử lý nút reply
                                if (e.target.closest('.reply-btn')) {
                                    const btn = e.target.closest('.reply-btn');
                                    const reviewId = btn.getAttribute('data-review-id');
                                    
                                    // Kiểm tra xem có form trả lời không
                                    const replyForm = document.getElementById('reply-form-' + reviewId);
                                    if (replyForm) {
                                        // Toggle hiển thị form
                                        const isHidden = replyForm.style.display === 'none' || replyForm.style.display === '';
                                        if (isHidden) {
                                            replyForm.style.display = 'block';
                                            // Focus vào textarea sau một chút để đảm bảo form đã hiển thị
                                            setTimeout(function() {
                                                const textarea = replyForm.querySelector('.reply-content');
                                                if (textarea) {
                                                    textarea.focus();
                                                }
                                            }, 100);
                                        } else {
                                            replyForm.style.display = 'none';
                                        }
                                    } else {
                                        console.warn('Không tìm thấy form trả lời cho đánh giá ID: ' + reviewId);
                                        alert('Không thể mở form trả lời. Vui lòng đảm bảo bạn đã đăng nhập.');
                                    }
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                
                                // Xử lý nút report
                                if (e.target.closest('.report-btn')) {
                                    const btn = e.target.closest('.report-btn');
                                    const reviewId = btn.getAttribute('data-review-id');
                                    if (confirm('Bạn có chắc muốn báo cáo đánh giá này?')) {
                                        alert('Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét đánh giá này.');
                                    }
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                
                                // Xử lý nút cancel reply
                                if (e.target.closest('.cancel-reply-btn')) {
                                    const btn = e.target.closest('.cancel-reply-btn');
                                    const form = btn.closest('.reply-form-container');
                                    if (form) {
                                        form.style.display = 'none';
                                        form.querySelector('.reply-content').value = '';
                                    }
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                                
                                // Xử lý xóa reply
                                if (e.target.closest('.delete-reply-btn')) {
                                    const btn = e.target.closest('.delete-reply-btn');
                                    const replyId = btn.getAttribute('data-reply-id');
                                    const replyItem = btn.closest('.reply-item');
                                    
                                    if (confirm('Bạn có chắc muốn xóa trả lời này?')) {
                                        // Gọi backend để xóa
                                        fetch('${baseURL}/xoa-tra-loi', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded',
                                            },
                                            body: 'replyId=' + encodeURIComponent(replyId)
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                replyItem.remove();
                                                if (typeof showNotification === 'function') {
                                                    showNotification('success', 'Thành công', 'Đã xóa trả lời thành công.', 3000);
                                                }
                                            } else {
                                                alert(data.message || 'Không thể xóa trả lời.');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Lỗi khi xóa trả lời:', error);
                                            alert('Đã xảy ra lỗi. Vui lòng thử lại.');
                                        });
                                    }
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                            }); // Kết thúc event listener chính cho click
                            
                            // Xử lý submit form (riêng biệt vì là submit event)
                            reviewListContainer.addEventListener('submit', function(e) {
                                if (e.target.classList.contains('reply-form')) {
                                    e.preventDefault();
                                    const form = e.target;
                                    const reviewId = form.getAttribute('data-review-id');
                                    const content = form.querySelector('.reply-content').value.trim();
                                    
                                    if (content.length < 5) {
                                        alert('Vui lòng nhập ít nhất 5 ký tự cho câu trả lời.');
                                        return;
                                    }
                                    
                                    // Disable submit button
                                    const submitBtn = form.querySelector('button[type="submit"]');
                                    const originalHTML = submitBtn.innerHTML;
                                    submitBtn.disabled = true;
                                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';
                                    
                                    // Gọi backend
                                    fetch('${baseURL}/tra-loi-danh-gia', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'reviewId=' + encodeURIComponent(reviewId) + '&content=' + encodeURIComponent(content)
                                    })
                                    .then(response => {
                                        // Kiểm tra status code
                                        if (!response.ok) {
                                            return response.text().then(text => {
                                                try {
                                                    return JSON.parse(text);
                                                } catch (e) {
                                                    return { success: false, message: 'Lỗi ' + response.status + ': ' + text };
                                                }
                                            });
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalHTML;
                                        
                                        if (data && data.success) {
                                            // Xóa form
                                            form.querySelector('.reply-content').value = '';
                                            form.closest('.reply-form-container').style.display = 'none';
                                            
                                            // Hiển thị thông báo thành công
                                            if (typeof showNotification === 'function') {
                                                showNotification('success', 'Thành công', 'Đã gửi trả lời thành công.', 3000);
                                            } else {
                                                alert('Đã gửi trả lời thành công!');
                                            }
                                            
                                            // Đợi một chút để đảm bảo server đã lưu xong, sau đó reload reviews
                                            setTimeout(function() {
                                                // Reload toàn bộ reviews để lấy reply mới từ server
                                                loadReviews();
                                                
                                                // Sau khi reload, scroll đến phần replies của review này
                                                setTimeout(function() {
                                                    const repliesList = document.getElementById('replies-' + reviewId);
                                                    if (repliesList) {
                                                        // Highlight reply mới
                                                        const newReplies = repliesList.querySelectorAll('.reply-item');
                                                        if (newReplies.length > 0) {
                                                            const lastReply = newReplies[newReplies.length - 1];
                                                            lastReply.style.backgroundColor = '#e7f3ff';
                                                            lastReply.style.transition = 'background-color 0.5s';
                                                            setTimeout(function() {
                                                                lastReply.style.backgroundColor = '';
                                                            }, 2000);
                                                            lastReply.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                                        }
                                                    }
                                                }, 500);
                                            }, 300);
                                        } else {
                                            const errorMsg = data && data.message ? data.message : 'Không thể gửi trả lời. Vui lòng thử lại.';
                                            alert(errorMsg);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Lỗi khi gửi trả lời:', error);
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalHTML;
                                        alert('Đã xảy ra lỗi khi gửi trả lời. Vui lòng kiểm tra kết nối và thử lại.');
                                    });
                                }
                            }); // Kết thúc event listener cho submit
                        } // Kết thúc if reviewListContainer
                        
                        // Hàm cập nhật tổng lượt thích
                        function updateTotalLikes() {
                            let totalLikes = 0;
                            document.querySelectorAll('.like-count').forEach(function(countEl) {
                                totalLikes += parseInt(countEl.textContent) || 0;
                            });
                            const totalLikesEl = document.getElementById('total-likes');
                            if (totalLikesEl) {
                                totalLikesEl.textContent = totalLikes;
                            }
                        }
                    </script>
                </div>
            </div>

            <%-- Sản phẩm liên quan --%>
            <section class="product-section" style="margin-top: 2rem;">
                <div class="section-header">
                    <h2>Sản Phẩm Liên Quan</h2>
                </div>
                <div class="product-grid">
                     <c:choose>
                        <c:when test="${not empty listSanPhamLienQuan}">
                            <c:forEach items="${listSanPhamLienQuan}" var="sachLQ">
                                <div class="product-card">
                                    <%-- 
                                      SỬA LỖI (IMAGE): Đổi /images/products/ 
                                      thành /assets/images/products/
                                    --%>
                                    <a href="${baseURL}/chi-tiet-san-pham?id=${sachLQ.maSanPham}" class="product-image-link">
                                        <c:set var="imageName" value="${sachLQ.hinhAnh}" />
                                        <c:if test="${fn:startsWith(imageName, 'Bookstore')}">
                                            <c:set var="imageName" value="${fn:substring(imageName, 10, fn:length(imageName))}" />
                                        </c:if>
                                        <img src="${baseURL}/assets/images/products/${imageName}" 
                                             alt="${sachLQ.tenSanPham}" class="product-image"
                                             onerror="this.onerror=null; this.src='${baseURL}/assets/images/products/default-product.png';">
                                    </a>
                                    <a href="${baseURL}/chi-tiet-san-pham?id=${sachLQ.maSanPham}" class="product-title">${sachLQ.tenSanPham}</a>
                                    <p class="product-author">${sachLQ.tacGia.hoVaTen}</p>
                                    <c:choose>
                                        <c:when test="${not empty sessionScope.user and sessionScope.user.isGoiCuocConHan()}">
                                            <a href="${baseURL}/doc-sach?id=${sachLQ.maSanPham}" class="btn btn-primary btn-add-to-cart">
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
                        </c:when>
                        <c:otherwise>
                             <p style="grid-column: 1 / -1; text-align: center; color: #6c757d;">Không tìm thấy sản phẩm liên quan.</p>
                        </c:otherwise>
                     </c:choose>
                </div>
            </section>

        </c:if>
        
        <c:if test="${not empty errorMessage or empty sanPham}">
             <p style="text-align: center; color: #6c757d; margin-top: 2rem;">
                 ${not empty errorMessage ? errorMessage : 'Không tìm thấy sản phẩm bạn yêu cầu.'}
             </p>
             <div style="text-align: center; margin-top: 1rem;">
                 <a href="${baseURL}/danh-sach-san-pham" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i> Quay lại danh mục</a>
             </div>
        </c:if>

    </main>

<jsp:include page="../layout/footer.jsp" />