<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="news" value="${requestScope.news}" />
<c:set var="isEdit" value="${requestScope.isEdit}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${isEdit ? 'Sửa' : 'Thêm'} tin tức - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
    <link rel="stylesheet" href="${baseURL}/css/upload-styles.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-${isEdit ? 'edit' : 'plus'}"></i> 
                    ${isEdit ? 'Sửa tin tức' : 'Thêm tin tức mới'}
                </h2>
                <a href="${baseURL}/admin/news" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <c:if test="${not empty requestScope.error}">
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i> ${requestScope.error}
                </div>
            </c:if>

            <form method="POST" action="${baseURL}/admin/news/${isEdit ? 'edit' : 'new'}" style="max-width: 800px;">
                <input type="hidden" name="maTinTuc" value="${news != null ? news.maTinTuc : ''}">

                <div class="form-group">
                    <label for="tieuDe">Tiêu đề *</label>
                    <input type="text" id="tieuDe" name="tieuDe" class="form-control" 
                           value="${news != null ? news.tieuDe : ''}" required>
                </div>

                <div class="form-group upload-container">
                    <label for="hinhAnh" class="upload-label">Ảnh tin tức *</label>
                    <div class="upload-wrapper">
                        <div style="flex: 1;">
                            <input type="text" id="hinhAnh" name="hinhAnh" class="form-control" 
                                   value="${news != null ? news.hinhAnh : ''}" 
                                   placeholder="Tên file ảnh sẽ tự động điền sau khi chọn" 
                                   readonly>
                        </div>
                        <div>
                            <div class="upload-input-wrapper">
                                <label for="newsImageInput" class="upload-input-label">
                                    <i class="fa-solid fa-image"></i> Chọn ảnh
                                </label>
                                <input type="file" id="newsImageInput" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="upload-preview" id="newsImagePreview">
                        <c:if test="${news != null && not empty news.hinhAnh}">
                            <c:choose>
                                <c:when test="${fn:startsWith(news.hinhAnh, '/') or fn:startsWith(news.hinhAnh, 'assets/')}">
                                    <img src="${pageContext.request.contextPath}${fn:startsWith(news.hinhAnh, '/') ? '' : '/'}${news.hinhAnh}" 
                                         alt="News preview" 
                                         onerror="this.style.display='none';">
                                </c:when>
                                <c:otherwise>
                                    <img src="${pageContext.request.contextPath}/assets/images/news/${news.hinhAnh}" 
                                         alt="News preview" 
                                         onerror="this.style.display='none';">
                                </c:otherwise>
                            </c:choose>
                        </c:if>
                    </div>
                    <div class="upload-help">
                        <i class="fa-solid fa-info-circle"></i>
                        <span>JPG, PNG, tối đa 10MB. Ảnh sẽ tự động tải lên khi bạn chọn.</span>
                    </div>
                    <div class="upload-error" id="newsImageError"></div>
                    <div class="upload-success" id="newsImageSuccess"></div>
                </div>

                <div class="form-group">
                    <label for="noiDung">Nội dung *</label>
                    <textarea id="noiDung" name="noiDung" class="form-control" rows="15" required>${news != null ? news.noiDung : ''}</textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> ${isEdit ? 'Cập nhật' : 'Thêm mới'}
                    </button>
                    <a href="${baseURL}/admin/news" class="btn btn-secondary">
                        <i class="fa-solid fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
    
<script>
// Xử lý upload ảnh tin tức - tự động upload khi chọn ảnh
document.addEventListener('DOMContentLoaded', function() {
    const newsImageInput = document.getElementById('newsImageInput');
    const hinhAnhInput = document.getElementById('hinhAnh');
    const newsImagePreview = document.getElementById('newsImagePreview');
    const errorMsg = document.getElementById('newsImageError');
    const successMsg = document.getElementById('newsImageSuccess');
    
    // Tự động upload khi chọn ảnh
    if (newsImageInput) {
        newsImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (!file) {
                return;
            }
            
            // Kiểm tra loại file
            if (!file.type.startsWith('image/')) {
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> File phải là ảnh.';
                    errorMsg.classList.add('active');
                } else {
                    alert('File phải là ảnh.');
                }
                newsImageInput.value = '';
                return;
            }
            
            // Kiểm tra kích thước
            if (file.size > 10 * 1024 * 1024) {
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Kích thước ảnh không được vượt quá 10MB.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Kích thước ảnh không được vượt quá 10MB.');
                }
                newsImageInput.value = '';
                return;
            }
            
            // Xem trước ảnh ngay lập tức
            const reader = new FileReader();
            reader.onload = function(e) {
                if (newsImagePreview) {
                    newsImagePreview.innerHTML = '<img src="' + e.target.result + '" alt="News preview">';
                    newsImagePreview.classList.add('has-image');
                }
            };
            reader.readAsDataURL(file);
            
            // Ẩn thông báo lỗi cũ
            if (errorMsg) {
                errorMsg.classList.remove('active');
            }
            if (successMsg) {
                successMsg.classList.remove('active');
            }
            
            // Tự động upload
            const formData = new FormData();
            formData.append('newsImage', file);
            
            // Hiển thị trạng thái đang tải
            if (hinhAnhInput) {
                hinhAnhInput.value = 'Đang tải lên...';
            }
            
            fetch('${baseURL}/admin/upload-news-image', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    if (successMsg) {
                        successMsg.innerHTML = '<i class="fa-solid fa-check-circle"></i> Tải ảnh thành công!';
                        successMsg.classList.add('active');
                        setTimeout(() => successMsg.classList.remove('active'), 3000);
                    }
                    
                    // Cập nhật input với tên file
                    if (hinhAnhInput && data.imagePath) {
                        hinhAnhInput.value = data.imagePath;
                    }
                    
                    // Cập nhật preview với URL đầy đủ
                    if (newsImagePreview && data.imageUrl) {
                        newsImagePreview.innerHTML = '<img src="' + data.imageUrl + '" alt="News preview">';
                        newsImagePreview.classList.add('has-image');
                    }
                } else {
                    // Hiển thị lỗi
                    if (errorMsg) {
                        errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> ' + (data.message || 'Không thể tải ảnh lên.');
                        errorMsg.classList.add('active');
                    } else {
                        alert(data.message || 'Không thể tải ảnh lên.');
                    }
                    
                    // Reset input
                    if (hinhAnhInput) {
                        hinhAnhInput.value = '';
                    }
                    if (newsImagePreview) {
                        newsImagePreview.innerHTML = '';
                        newsImagePreview.classList.remove('has-image');
                    }
                    newsImageInput.value = '';
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Đã xảy ra lỗi khi tải ảnh lên.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Đã xảy ra lỗi khi tải ảnh lên.');
                }
                
                // Reset input
                if (hinhAnhInput) {
                    hinhAnhInput.value = '';
                }
                if (newsImagePreview) {
                    newsImagePreview.innerHTML = '';
                    newsImagePreview.classList.remove('has-image');
                }
                newsImageInput.value = '';
            });
        });
    }
});
</script>
</body>
</html>
