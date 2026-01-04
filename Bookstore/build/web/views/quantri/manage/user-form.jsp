<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="user" value="${requestScope.user}" />
<c:set var="isEdit" value="${requestScope.isEdit}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${isEdit ? 'Sửa' : 'Thêm'} người dùng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-${isEdit ? 'edit' : 'plus'}"></i> 
                    ${isEdit ? 'Sửa người dùng' : 'Thêm người dùng mới'}
                </h2>
                <a href="${baseURL}/admin/users" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <c:if test="${not empty requestScope.error}">
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i> ${requestScope.error}
                </div>
            </c:if>

            <form method="POST" action="${baseURL}/admin/users/${isEdit ? 'edit' : 'new'}" style="max-width: 800px;">
                <input type="hidden" name="maKhachHang" value="${user != null ? user.maKhachHang : ''}">

                <div class="form-group">
                    <label for="tenDangNhap">Tên đăng nhập *</label>
                    <input type="text" id="tenDangNhap" name="tenDangNhap" class="form-control" 
                           value="${user != null ? user.tenDangNhap : ''}" 
                           ${isEdit ? 'readonly' : 'required'}>
                    <small style="color: #6c757d; font-size: 0.85rem;">${isEdit ? 'Tên đăng nhập không thể thay đổi.' : 'Tên đăng nhập phải là duy nhất.'}</small>
                </div>

                <c:if test="${!isEdit}">
                    <div class="form-group">
                        <label for="matKhau">Mật khẩu *</label>
                        <input type="password" id="matKhau" name="matKhau" class="form-control" required>
                        <small style="color: #6c757d; font-size: 0.85rem;">Mật khẩu tối thiểu 6 ký tự</small>
                    </div>
                </c:if>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="hoVaTen">Họ và tên *</label>
                        <input type="text" id="hoVaTen" name="hoVaTen" class="form-control" 
                               value="${user != null ? user.hoVaTen : ''}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="${user != null ? user.email : ''}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="soDienThoai">Số điện thoại</label>
                        <input type="tel" id="soDienThoai" name="soDienThoai" class="form-control" 
                               value="${user != null ? user.soDienThoai : ''}">
                    </div>
                    <div class="form-group">
                        <label for="gioiTinh">Giới tính</label>
                        <select id="gioiTinh" name="gioiTinh" class="form-control">
                            <option value="">-- Chọn --</option>
                            <option value="Nam" ${user != null && user.gioiTinh == 'Nam' ? 'selected' : ''}>Nam</option>
                            <option value="Nữ" ${user != null && user.gioiTinh == 'Nữ' ? 'selected' : ''}>Nữ</option>
                            <option value="Khác" ${user != null && user.gioiTinh == 'Khác' ? 'selected' : ''}>Khác</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="diaChi">Địa chỉ</label>
                    <input type="text" id="diaChi" name="diaChi" class="form-control" 
                           value="${user != null ? user.diaChi : ''}">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="ngaySinh">Ngày sinh</label>
                        <input type="date" id="ngaySinh" name="ngaySinh" class="form-control" 
                               value="${user != null && user.ngaySinh != null ? user.ngaySinh : ''}">
                    </div>
                    <div class="form-group">
                        <label for="status">Trạng thái *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="1" ${user != null && user.status == 1 ? 'selected' : ''}>Hoạt động</option>
                            <option value="0" ${user != null && user.status == 0 ? 'selected' : ''}>Đã khóa</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Vai trò *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="0" ${user != null && user.role == 0 ? 'selected' : ''}>Người dùng</option>
                        <option value="1" ${user != null && user.role == 1 ? 'selected' : ''}>Quản trị viên</option>
                    </select>
                    <small style="color: #6c757d; font-size: 0.85rem;">
                        <i class="fa-solid fa-info-circle"></i> Quản trị viên có quyền truy cập vào trang quản trị hệ thống.
                    </small>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> ${isEdit ? 'Cập nhật' : 'Thêm mới'}
                    </button>
                    <a href="${baseURL}/admin/users" class="btn btn-secondary">
                        <i class="fa-solid fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
</body>
</html>



