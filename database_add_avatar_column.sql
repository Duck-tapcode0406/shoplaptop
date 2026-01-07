-- Thêm cột avatar vào bảng user
-- Chạy file này để thêm chức năng ảnh đại diện

ALTER TABLE `user` 
ADD COLUMN IF NOT EXISTS `avatar` VARCHAR(255) DEFAULT NULL AFTER `email`;

-- Tạo thư mục uploads/avatars nếu chưa có (thực hiện thủ công hoặc qua code)



