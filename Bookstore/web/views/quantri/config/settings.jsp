 <%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="configMap" value="${requestScope.configMap}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình website - Admin Panel</title>
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
                <h2 class="card-title"><i class="fa-solid fa-cog"></i> Cấu hình website</h2>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <form method="POST" action="${baseURL}/admin/config/settings" style="max-width: 800px;">
                <h3 style="margin-bottom: 1rem;">Thông tin cửa hàng</h3>
                <div class="form-group">
                    <label for="site_name">Tên cửa hàng *</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" 
                           value="${configMap['site_name'].value}" required>
                </div>

                <div class="form-group upload-container">
                    <label for="site_logo" class="upload-label">Logo website</label>
                    <div class="upload-wrapper">
                        <div style="flex: 1;">
                            <input type="text" id="site_logo" name="site_logo" class="form-control" 
                                   value="${configMap['site_logo'].value}" placeholder="/assets/images/logo.png">
                        </div>
                        <div>
                            <div class="upload-input-wrapper">
                                <label for="logoInput" class="upload-input-label">
                                    <i class="fa-solid fa-image"></i> Chọn logo
                                </label>
                                <input type="file" id="logoInput" accept="image/*">
                            </div>
                            <button type="button" id="uploadLogoBtn" class="upload-btn">
                                <i class="fa-solid fa-upload"></i> Tải lên
                            </button>
                        </div>
                    </div>
                    <div class="upload-preview" id="logoPreview">
                        <c:if test="${not empty configMap['site_logo'].value}">
                            <img src="${pageContext.request.contextPath}${configMap['site_logo'].value}" 
                                 alt="Logo preview" 
                                 onerror="this.style.display='none';">
                        </c:if>
                    </div>
                    <div class="upload-help">
                        <i class="fa-solid fa-info-circle"></i>
                        <span>JPG, PNG, tối đa 5MB</span>
                    </div>
                    <div class="upload-error" id="logoError"></div>
                    <div class="upload-success" id="logoSuccess"></div>
                </div>

                <div class="form-group">
                    <label for="site_description">Mô tả website</label>
                    <textarea id="site_description" name="site_description" class="form-control" rows="3">${configMap['site_description'].value}</textarea>
                </div>

                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Thông tin liên hệ</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="site_phone">Số điện thoại *</label>
                        <input type="text" id="site_phone" name="site_phone" class="form-control" 
                               value="${configMap['site_phone'].value}" required>
                    </div>

                    <div class="form-group">
                        <label for="site_email">Email *</label>
                        <input type="email" id="site_email" name="site_email" class="form-control" 
                               value="${configMap['site_email'].value}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="site_address">Địa chỉ *</label>
                    <textarea id="site_address" name="site_address" class="form-control" rows="2" required>${configMap['site_address'].value}</textarea>
                </div>

                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Mạng xã hội</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="facebook_url">Facebook URL</label>
                        <input type="url" id="facebook_url" name="facebook_url" class="form-control" 
                               value="${configMap['facebook_url'].value}">
                    </div>

                    <div class="form-group">
                        <label for="twitter_url">Twitter URL</label>
                        <input type="url" id="twitter_url" name="twitter_url" class="form-control" 
                               value="${configMap['twitter_url'].value}">
                    </div>

                    <div class="form-group">
                        <label for="instagram_url">Instagram URL</label>
                        <input type="url" id="instagram_url" name="instagram_url" class="form-control" 
                               value="${configMap['instagram_url'].value}">
                    </div>
                </div>

                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Vận chuyển</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="shipping_fee">Phí vận chuyển (VNĐ) *</label>
                        <input type="number" id="shipping_fee" name="shipping_fee" class="form-control" 
                               value="${configMap['shipping_fee'].value}" step="1000" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="free_shipping_threshold">Ngưỡng miễn phí vận chuyển (VNĐ) *</label>
                        <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" class="form-control" 
                               value="${configMap['free_shipping_threshold'].value}" step="1000" min="0" required>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Lưu cấu hình
                    </button>
                </div>
            </form>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
    
<script>
// Xử lý upload logo
document.addEventListener('DOMContentLoaded', function() {
    const logoInput = document.getElementById('logoInput');
    const uploadLogoBtn = document.getElementById('uploadLogoBtn');
    const siteLogoInput = document.getElementById('site_logo');
    const logoPreview = document.getElementById('logoPreview');
    
    // Xem trước ảnh khi chọn
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Logo preview';
                    logoPreview.appendChild(img);
                    logoPreview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Upload logo khi click nút
    if (uploadLogoBtn && logoInput) {
        uploadLogoBtn.addEventListener('click', function() {
            const file = logoInput.files[0];
            if (!file) {
                const errorMsg = document.getElementById('logoError');
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Vui lòng chọn logo trước khi tải lên.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Vui lòng chọn logo trước khi tải lên.');
                }
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                const errorMsg = document.getElementById('logoError');
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> File phải là ảnh.';
                    errorMsg.classList.add('active');
                } else {
                    alert('File phải là ảnh.');
                }
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                const errorMsg = document.getElementById('logoError');
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Kích thước logo không được vượt quá 5MB.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Kích thước logo không được vượt quá 5MB.');
                }
                return;
            }
            
            const formData = new FormData();
            formData.append('logo', file);
            
            uploadLogoBtn.disabled = true;
            uploadLogoBtn.innerHTML = '<span class="upload-loading"></span> Đang tải...';
            
            // Ẩn thông báo lỗi cũ
            const errorMsg = document.getElementById('logoError');
            if (errorMsg) {
                errorMsg.classList.remove('active');
            }
            
            fetch('${baseURL}/admin/upload-logo', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadLogoBtn.disabled = false;
                uploadLogoBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Tải lên';
                
                if (data.success) {
                    // Hiển thị thông báo thành công
                    const successMsg = document.getElementById('logoSuccess');
                    if (successMsg) {
                        successMsg.innerHTML = '<i class="fa-solid fa-check-circle"></i> Tải logo thành công!';
                        successMsg.classList.add('active');
                        setTimeout(() => successMsg.classList.remove('active'), 3000);
                    }
                    
                    // Cập nhật input
                    if (siteLogoInput && data.imagePath) {
                        siteLogoInput.value = data.imagePath;
                    }
                    
                    // Cập nhật preview
                    if (logoPreview && data.imageUrl) {
                        logoPreview.innerHTML = '<img src="' + data.imageUrl + '" alt="Logo preview">';
                        logoPreview.classList.add('has-image');
                    }
                } else {
                    // Hiển thị lỗi
                    if (errorMsg) {
                        errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> ' + (data.message || 'Không thể tải logo lên.');
                        errorMsg.classList.add('active');
                    } else {
                        alert(data.message || 'Không thể tải logo lên.');
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                uploadLogoBtn.disabled = false;
                uploadLogoBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Tải lên';
                
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Đã xảy ra lỗi khi tải logo lên.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Đã xảy ra lỗi khi tải logo lên.');
                }
            });
        });
    }
});
</script>
</body>
</html>
