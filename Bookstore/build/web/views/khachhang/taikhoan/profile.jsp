<%-- WebContent/views/khachhang/account/profile.jsp --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<link rel="stylesheet" href="${baseURL}/css/khachhang/style-account.css">
<link rel="stylesheet" href="${baseURL}/css/khachhang/style-auth.css">
<link rel="stylesheet" href="${baseURL}/css/upload-styles.css">

<title>Hồ Sơ Cá Nhân - BookStore</title>

<jsp:include page="../layout/header.jsp" />

    <main class="container">
        <h2 class="account-page-title">Tài Khoản Của Tôi</h2>

        <div class="account-layout">
            <%-- Sidebar --%>
            <aside class="account-sidebar">
                <div class="user-avatar">
                     <%-- 
                       SỬA LỖI (IMAGE): Đổi /images/avatars/ 
                       thành /assets/images/avatars/
                     --%>
                     <img src="${baseURL}/assets/images/avatars/${not empty sessionScope.user.duongDanAnh ? sessionScope.user.duongDanAnh : 'avatar-default.png'}"
                          alt="Avatar của ${sessionScope.user.hoVaTen}"
                          onerror="this.onerror=null; this.src='${baseURL}/assets/images/avatars/avatar-default.png';"> <%-- Ảnh dự phòng --%>
                    <h5>${sessionScope.user.hoVaTen}</h5>
                </div>
                <ul class="account-nav">
                    <%-- 
                      SỬA LỖI (URL): Đồng bộ URL với các Servlet
                    --%>
                    <li><a href="${baseURL}/tai-khoan/ho-so" class="active"><i class="fa-solid fa-user-edit"></i> Hồ Sơ Cá Nhân</a></li>
                    <li><a href="${baseURL}/goi-cuoc"><i class="fa-solid fa-book-open"></i> Gói Cước</a></li>
                    <li><a href="${baseURL}/tai-khoan/thay-doi-mat-khau"><i class="fa-solid fa-lock"></i> Đổi Mật Khẩu</a></li>
                    <li><a href="${baseURL}/dang-xuat"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a></li>
                </ul>
            </aside>

            <%-- Nội dung chính --%>
            <section class="account-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="margin-bottom: 0;">Hồ Sơ Cá Nhân</h3>
                    <a href="${baseURL}/trang-chu" style="font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #f5f5f5; color: #333; text-decoration: none; border-radius: 8px; transition: background 0.3s;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <p>Quản lý thông tin hồ sơ để bảo mật tài khoản.</p>
                <hr>

                <%-- Hiển thị thông báo (nếu có) từ session --%>
                <c:if test="${not empty sessionScope.errorMessage}">
                    <div class="error-message">
                        ${sessionScope.errorMessage}
                    </div>
                    <c:remove var="errorMessage" scope="session" />
                </c:if>
                <c:if test="${not empty sessionScope.successMessage}">
                    <div class="error-message" style="background-color: #d4edda; color: #155724; border-color: #c3e6cb;">
                        ${sessionScope.successMessage}
                    </div>
                    <c:remove var="successMessage" scope="session" />
                </c:if>

                <%-- 
                  SỬA LỖI (URL): Thêm action trỏ đến /tai-khoan/ho-so (HoSoCaNhanServlet)
                --%>
                <form action="${baseURL}/tai-khoan/ho-so" method="POST" class="profile-form" enctype="multipart/form-data">
                    <div class="form-group upload-container">
                        <label for="avatarInput" class="upload-label">Ảnh đại diện:</label>
                        <div class="upload-wrapper">
                            <div class="upload-preview avatar-preview">
                                <img id="avatarPreview" 
                                     src="${baseURL}/assets/images/avatars/${not empty sessionScope.user.duongDanAnh ? sessionScope.user.duongDanAnh : 'avatar-default.png'}"
                                     alt="Avatar preview"
                                     onerror="this.onerror=null; this.src='${baseURL}/assets/images/avatars/avatar-default.png';">
                            </div>
                            <div style="flex: 1;">
                                <div class="upload-input-wrapper">
                                    <label for="avatarInput" class="upload-input-label">
                                        <i class="fa-solid fa-image"></i> Chọn ảnh
                                    </label>
                                    <input type="file" id="avatarInput" name="avatar" accept="image/*">
                                </div>
                                <button type="button" id="uploadAvatarBtn" class="upload-btn">
                                    <i class="fa-solid fa-upload"></i> Tải lên
                                </button>
                                <div class="upload-help">
                                    <i class="fa-solid fa-info-circle"></i>
                                    <span>JPG, PNG, tối đa 5MB</span>
                                </div>
                                <div class="upload-error" id="avatarError"></div>
                                <div class="upload-success" id="avatarSuccess"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username">Tên đăng nhập:</label>
                        <input type="text" id="username" name="username" value="${sessionScope.user.tenDangNhap}" readonly disabled>
                        <small>Tên đăng nhập không thể thay đổi.</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="${sessionScope.user.email}">
                    </div>
                    <div class="form-group">
                        <label for="fullName">Họ và tên:</label>
                        <input type="text" id="fullName" name="fullName" value="${sessionScope.user.hoVaTen}">
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại:</label>
                        <input type="tel" id="phone" name="phone" value="${sessionScope.user.soDienThoai}">
                    </div>
                     <div class="form-group">
                        <label for="address">Địa chỉ:</label>
                        <input type="text" id="address" name="address" value="${sessionScope.user.diaChi}">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                </form>
                
                <%-- Phần Gói Cước --%>
                <div style="margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="margin-bottom: 1.5rem; color: #00467f;">
                        <i class="fa-solid fa-book-open"></i> Thông Tin Gói Cước
                    </h3>
                    
                    <c:choose>
                        <c:when test="${not empty sessionScope.user and sessionScope.user.isGoiCuocConHan()}">
                            <div style="padding: 1.5rem; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                    <i class="fa-solid fa-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                                    <div>
                                        <h4 style="margin: 0; color: #00467f;">Gói cước đang hoạt động</h4>
                                        <p style="margin: 0.5rem 0 0 0; color: #666;">Bạn có thể đọc tất cả sách</p>
                                    </div>
                                </div>
                                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee;">
                                    <p style="margin: 0.5rem 0; color: #666;">
                                        <strong>Ngày đăng ký:</strong> 
                                        <fmt:formatDate value="${sessionScope.user.ngayDangKy}" pattern="dd/MM/yyyy HH:mm" />
                                    </p>
                                    <p style="margin: 0.5rem 0; color: #666;">
                                        <strong>Ngày hết hạn:</strong> 
                                        <fmt:formatDate value="${sessionScope.user.ngayHetHan}" pattern="dd/MM/yyyy HH:mm" />
                                    </p>
                                </div>
                                <a href="${baseURL}/goi-cuoc" class="btn btn-primary" style="margin-top: 1rem; display: inline-block; text-decoration: none;">
                                    <i class="fa-solid fa-sync"></i> Gia hạn gói cước
                                </a>
                            </div>
                        </c:when>
                        <c:otherwise>
                            <div style="padding: 1.5rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <i class="fa-solid fa-exclamation-triangle" style="font-size: 2rem; color: #ffc107;"></i>
                                    <div>
                                        <h4 style="margin: 0; color: #856404;">Chưa đăng ký gói cước</h4>
                                        <p style="margin: 0.5rem 0 0 0; color: #856404;">Đăng ký ngay để đọc không giới hạn hàng nghìn cuốn sách!</p>
                                    </div>
                                </div>
                                <a href="${baseURL}/goi-cuoc" class="btn btn-primary" style="margin-top: 1rem; display: inline-block; text-decoration: none;">
                                    <i class="fa-solid fa-book-open"></i> Đăng ký gói cước ngay
                                </a>
                            </div>
                        </c:otherwise>
                    </c:choose>
                </div>
            </section>
        </div>
    </main>

<script>
// Xử lý upload avatar
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const uploadAvatarBtn = document.getElementById('uploadAvatarBtn');
    
    // Xem trước ảnh khi chọn
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Upload ảnh khi click nút
    if (uploadAvatarBtn && avatarInput) {
        uploadAvatarBtn.addEventListener('click', function() {
            const file = avatarInput.files[0];
            if (!file) {
                alert('Vui lòng chọn ảnh trước khi tải lên.');
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                alert('File phải là ảnh.');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('Kích thước ảnh không được vượt quá 5MB.');
                return;
            }
            
            const formData = new FormData();
            formData.append('avatar', file);
            
            uploadAvatarBtn.disabled = true;
            uploadAvatarBtn.innerHTML = '<span class="upload-loading"></span> Đang tải...';
            
            // Ẩn thông báo lỗi cũ
            const errorMsg = document.getElementById('avatarError');
            if (errorMsg) {
                errorMsg.classList.remove('active');
            }
            
            fetch('${baseURL}/upload-avatar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadAvatarBtn.disabled = false;
                uploadAvatarBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Tải lên';
                
                if (data.success) {
                    // Hiển thị thông báo thành công
                    const successMsg = document.getElementById('avatarSuccess');
                    if (successMsg) {
                        successMsg.innerHTML = '<i class="fa-solid fa-check-circle"></i> Tải ảnh đại diện thành công!';
                        successMsg.classList.add('active');
                        setTimeout(() => successMsg.classList.remove('active'), 3000);
                    }
                    
                    // Ẩn lỗi nếu có
                    const errorMsg = document.getElementById('avatarError');
                    if (errorMsg) {
                        errorMsg.classList.remove('active');
                    }
                    
                    // Cập nhật ảnh preview
                    if (avatarPreview && data.imageUrl) {
                        avatarPreview.src = data.imageUrl;
                    }
                    // Reload trang sau 1 giây để cập nhật avatar ở sidebar
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Hiển thị lỗi
                    const errorMsg = document.getElementById('avatarError');
                    if (errorMsg) {
                        errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> ' + (data.message || 'Không thể tải ảnh lên.');
                        errorMsg.classList.add('active');
                    } else {
                        alert(data.message || 'Không thể tải ảnh lên.');
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                uploadAvatarBtn.disabled = false;
                uploadAvatarBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Tải lên';
                
                // Hiển thị lỗi
                const errorMsg = document.getElementById('avatarError');
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Đã xảy ra lỗi khi tải ảnh lên.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Đã xảy ra lỗi khi tải ảnh lên.');
                }
            });
        });
    }
});
</script>

<jsp:include page="../layout/footer.jsp" />