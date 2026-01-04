<%-- WebContent/views/khachhang/news/news-list.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/kieudang_tintuc.css">

<title>Tin Tức & Bài Viết - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container news-list-page">
        <h2><i class="fa-solid fa-newspaper"></i> Tin Tức & Bài Viết</h2>

        <c:if test="${not empty errorMessage}">
            <div class="error-message" style="margin-bottom: 2rem;">
                 <i class="fa-solid fa-circle-exclamation"></i> ${errorMessage}
            </div>
        </c:if>

        <div class="news-list">
            <c:forEach items="${listTinTuc}" var="tin">
                <div class="news-card">
                    <div class="news-card-image">
                        <a href="${baseURL}/chi-tiet-tin-tuc?id=${tin.maTinTuc}">
                            <%-- 
                              Hiển thị ảnh: nếu hinhAnh đã có đường dẫn đầy đủ thì dùng trực tiếp,
                              nếu chỉ là tên file thì thêm đường dẫn
                            --%>
                            <c:choose>
                                <c:when test="${fn:startsWith(tin.hinhAnh, '/') or fn:startsWith(tin.hinhAnh, 'assets/')}">
                                    <img src="${baseURL}${fn:startsWith(tin.hinhAnh, '/') ? '' : '/'}${tin.hinhAnh}" 
                                         alt="${tin.tieuDe}"
                                         onerror="this.onerror=null; this.src='${baseURL}/assets/images/news/default-news.png';">
                                </c:when>
                                <c:otherwise>
                                    <img src="${baseURL}/assets/images/news/${tin.hinhAnh}" 
                                         alt="${tin.tieuDe}"
                                         onerror="this.onerror=null; this.src='${baseURL}/assets/images/news/default-news.png';">
                                </c:otherwise>
                            </c:choose>
                        </a>
                    </div>
                    <div class="news-card-content">
                        <p class="news-date"><i class="fa-solid fa-calendar-alt"></i> <fmt:formatDate value="${tin.ngayDang}" pattern="dd/MM/yyyy"/></p>
                        <h3 class="news-title">
                            <a href="${baseURL}/chi-tiet-tin-tuc?id=${tin.maTinTuc}">${tin.tieuDe}</a>
                        </h3>
                        <p class="news-excerpt">
                             <c:set var="plainTextContent" value="${fn:replace(tin.noiDung, '<[^>]*>', '')}" />
                             ${fn:substring(plainTextContent, 0, 150)}...
                        </p>
                        <p class="news-author"><i class="fa-solid fa-user-pen"></i> ${not empty tin.nguoiDang ? tin.nguoiDang.hoVaTen : 'Không rõ'}</p>
                        <a href="${baseURL}/chi-tiet-tin-tuc?id=${tin.maTinTuc}" class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.9rem;">
                           <i class="fa-solid fa-book-open-reader"></i> Đọc Tiếp
                        </a>
                    </div>
                </div>
            </c:forEach>

            <c:if test="${empty listTinTuc and empty errorMessage}">
                 <p style="grid-column: 1 / -1; text-align: center; color: #6c757d; margin-top: 2rem;">
                     Hiện chưa có bài viết nào.
                 </p>
            </c:if>
        </div>

    </main>

<jsp:include page="../layout/footer.jsp" />