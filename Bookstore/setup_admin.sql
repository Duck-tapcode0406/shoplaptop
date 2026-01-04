-- Script thiết lập Admin từ User hiện có
-- Sử dụng: Cập nhật role = 1 cho user muốn làm admin

-- Cách 1: Thiết lập admin dựa vào email
-- Thay 'admin@example.com' bằng email của user bạn muốn làm admin
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `email` = 'admin@example.com';

-- Cách 2: Thiết lập admin dựa vào tên đăng nhập
-- Thay 'admin' bằng tên đăng nhập của user bạn muốn làm admin
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `tenDangNhap` = 'admin';

-- Cách 3: Thiết lập admin dựa vào mã khách hàng
-- Thay 'KH001' bằng mã khách hàng của user bạn muốn làm admin
UPDATE `khachhang` 
SET `role` = 1, `status` = 1 
WHERE `maKhachHang` = 'KH001';

-- Kiểm tra kết quả
SELECT `maKhachHang`, `tenDangNhap`, `email`, `hoVaTen`, `role`, `status`
FROM `khachhang`
WHERE `role` = 1;

-- Lưu ý:
-- - role = 0: User thường
-- - role = 1: Admin
-- - status = 1: Tài khoản hoạt động
-- - status = 0: Tài khoản bị khóa







