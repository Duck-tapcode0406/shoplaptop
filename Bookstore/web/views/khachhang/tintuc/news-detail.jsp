<%-- WebContent/views/khachhang/news/news-detail.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %> <%-- Format ngày --%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/kieudang_tintuc.css">


<%-- Lấy tiêu đề động từ đối tượng ${tinTuc} --%>
<title>${not empty tinTuc ? tinTuc.tieuDe : 'Chi Tiết Tin Tức'} - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <%-- Nút quay lại --%>
        <div style="margin-bottom: 1.5rem;">
            <a href="${baseURL}/tin-tuc" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s; font-size: 0.9rem;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách tin tức
            </a>
        </div>

        <div class="news-detail-page">
            <%-- Kiểm tra xem Servlet có gửi dữ liệu ${tinTuc} không và không có lỗi --%>
            <c:if test="${not empty tinTuc and empty errorMessage}">

                <%-- Hiển thị tiêu đề động --%>
                <h1 class="news-title">${tinTuc.tieuDe}</h1>

                <p class="news-meta">
                    <%-- SỬA LỖI: Kiểm tra nguoiDang trước khi lấy hoTen --%>
                    <i class="fa-solid fa-user"></i> Đăng bởi <strong>${not empty tinTuc.nguoiDang ? tinTuc.nguoiDang.hoVaTen : 'Không rõ'}</strong>
                    | <i class="fa-solid fa-calendar-days"></i> <fmt:formatDate value="${tinTuc.ngayDang}" pattern="dd/MM/yyyy HH:mm"/>
                </p>

                <%-- 
                  Hiển thị ảnh: nếu hinhAnh đã có đường dẫn đầy đủ thì dùng trực tiếp,
                  nếu chỉ là tên file thì thêm đường dẫn
                --%>
                <c:choose>
                    <c:when test="${fn:startsWith(tinTuc.hinhAnh, '/') or fn:startsWith(tinTuc.hinhAnh, 'assets/')}">
                        <img src="${baseURL}${fn:startsWith(tinTuc.hinhAnh, '/') ? '' : '/'}${tinTuc.hinhAnh}"
                             alt="${tinTuc.tieuDe}" class="news-main-image"
                             onerror="this.onerror=null; this.src='${baseURL}/assets/images/news/default-news.png';">
                    </c:when>
                    <c:otherwise>
                        <img src="${baseURL}/assets/images/news/${tinTuc.hinhAnh}"
                             alt="${tinTuc.tieuDe}" class="news-main-image"
                             onerror="this.onerror=null; this.src='${baseURL}/assets/images/news/default-news.png';">
                    </c:otherwise>
                </c:choose>

                <%-- Hiển thị nội dung động (cho phép HTML) --%>
                <div class="news-content">
                    <c:out value="${tinTuc.noiDung}" escapeXml="false" />
                </div>

                 <%-- Phần chia sẻ hoặc bình luận (nếu có) --%>

            </c:if>
            <%-- Thông báo nếu không tìm thấy tin tức hoặc có lỗi --%>
            <c:if test="${empty tinTuc or not empty errorMessage}">
                <p style="text-align: center; color: #6c757d; margin-top: 2rem;">
                    ${not empty errorMessage ? errorMessage : 'Không tìm thấy bài viết hoặc bài viết đã bị xóa.'}
                </p>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="${baseURL}/tin-tuc" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i> Quay lại danh sách tin tức</a>
                </div>
            </c:if>
        </div>

    </main>

<jsp:include page="../layout/footer.jsp" />