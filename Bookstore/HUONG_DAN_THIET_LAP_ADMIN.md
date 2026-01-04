# Hướng Dẫn Thiết Lập Admin

## Cách 1: Sử dụng SQL Script (Nhanh nhất)

### Bước 1: Mở file `setup_admin.sql`
File này đã được tạo sẵn trong thư mục gốc của project.

### Bước 2: Chọn một trong các cách sau:

#### Cách A: Thiết lập admin dựa vào Email
```sql
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `email` = 'your-email@example.com';
```

#### Cách B: Thiết lập admin dựa vào Tên đăng nhập
```sql
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `tenDangNhap` = 'your-username';
```

#### Cách C: Thiết lập admin dựa vào Mã khách hàng
```sql
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `maKhachHang` = 'KH001';
```

### Bước 3: Kiểm tra kết quả
```sql
SELECT `maKhachHang`, `tenDangNhap`, `email`, `hoVaTen`, `role`, `status`
FROM `khachhang`
WHERE `role` = 1;
```

---

## Cách 2: Sử dụng Trang Quản Trị (Khuyến nghị)

### Bước 1: Đăng nhập với tài khoản admin hiện có
- Nếu chưa có admin, sử dụng **Cách 1** để tạo admin đầu tiên.

### Bước 2: Truy cập trang quản lý User
- URL: `/admin/users`
- Hoặc click vào menu "Quản lý User" trong admin panel.

### Bước 3: Chỉnh sửa User
1. Tìm user bạn muốn thiết lập làm admin
2. Click nút **"Sửa"** (icon bút chì)
3. Trong form chỉnh sửa:
   - Tìm trường **"Vai trò"**
   - Chọn **"Quản trị viên"** (thay vì "Người dùng")
4. Click **"Cập nhật"**

### Bước 4: Xác nhận
- User đã được cập nhật thành admin
- Cột "Vai trò" trong danh sách sẽ hiển thị badge **"Admin"** màu vàng

---

## Cách 3: Tạo User Mới với Quyền Admin

### Bước 1: Truy cập trang tạo User mới
- URL: `/admin/users/new`
- Hoặc click nút **"Thêm mới"** trong trang quản lý User

### Bước 2: Điền thông tin
- Điền đầy đủ thông tin user
- Trong trường **"Vai trò"**, chọn **"Quản trị viên"**

### Bước 3: Lưu
- Click **"Thêm mới"**
- User mới sẽ được tạo với quyền admin

---

## Lưu ý Quan Trọng

### Về Role:
- **role = 0**: Người dùng thường (User)
- **role = 1**: Quản trị viên (Admin)

### Về Status:
- **status = 1**: Tài khoản hoạt động
- **status = 0**: Tài khoản bị khóa

### Bảo mật:
- ⚠️ **KHÔNG** thiết lập quá nhiều tài khoản admin
- Chỉ thiết lập admin cho những người thực sự cần quyền quản trị
- Admin có quyền truy cập vào tất cả chức năng quản trị hệ thống

### Kiểm tra Admin:
Sau khi thiết lập, admin có thể:
- Truy cập `/admin/dashboard`
- Quản lý sản phẩm, đơn hàng, user, tin tức, v.v.
- Thấy icon **"Admin Panel"** (user-shield) trong header khi đăng nhập

---

## Troubleshooting

### Vấn đề: Không thể đăng nhập với tài khoản admin
**Giải pháp:**
1. Kiểm tra `role = 1` trong database
2. Kiểm tra `status = 1` (tài khoản phải hoạt động)
3. Xóa session cũ và đăng nhập lại

### Vấn đề: Không thấy menu Admin
**Giải pháp:**
1. Đảm bảo `role = 1` trong database
2. Đăng xuất và đăng nhập lại
3. Kiểm tra `AdminFilter.java` có hoạt động đúng không

### Vấn đề: User không thể truy cập `/admin/*`
**Giải pháp:**
- Đây là hành vi bình thường
- Chỉ user có `role = 1` mới có thể truy cập trang admin
- User thường (`role = 0`) sẽ bị redirect về trang chủ

---

## Script SQL Mẫu

```sql
-- Thiết lập admin đầu tiên (thay đổi email/username theo nhu cầu)
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `email` = 'admin@bookstore.com';

-- Xem danh sách tất cả admin
SELECT `maKhachHang`, `tenDangNhap`, `email`, `hoVaTen`, `role`, `status`
FROM `khachhang`
WHERE `role` = 1;

-- Chuyển admin về user thường
UPDATE `khachhang` 
SET `role` = 0 
WHERE `email` = 'old-admin@bookstore.com';
```







