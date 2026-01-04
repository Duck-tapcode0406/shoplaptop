-- Script sửa lỗi đăng nhập: Thêm các cột gói cước vào bảng khachhang
-- Chạy script này nếu gặp lỗi "Đã xảy ra lỗi hệ thống trong quá trình đăng nhập"
-- Script này sẽ tự động kiểm tra và chỉ thêm cột nếu chưa tồn tại

USE bookstore; -- Thay đổi tên database nếu khác

-- Kiểm tra và thêm cột maGoiCuoc (chỉ thêm nếu chưa tồn tại)
SET @dbname = DATABASE();
SET @tablename = "khachhang";
SET @columnname = "maGoiCuoc";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column maGoiCuoc already exists' AS message",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(50) NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra và thêm cột ngayDangKy (chỉ thêm nếu chưa tồn tại)
SET @columnname = "ngayDangKy";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column ngayDangKy already exists' AS message",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra và thêm cột ngayHetHan (chỉ thêm nếu chưa tồn tại)
SET @columnname = "ngayHetHan";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column ngayHetHan already exists' AS message",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Kiểm tra và thêm foreign key (chỉ thêm nếu chưa tồn tại)
SET @constraint_name = "fk_khachhang_goicuoc";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (constraint_name = @constraint_name)
  ) > 0,
  "SELECT 'Foreign key fk_khachhang_goicuoc already exists' AS message",
  CONCAT("ALTER TABLE ", @tablename, " ADD CONSTRAINT ", @constraint_name, 
         " FOREIGN KEY (maGoiCuoc) REFERENCES goicuoc(maGoi) ON DELETE SET NULL ON UPDATE CASCADE")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Hoàn thành!
SELECT 'Script đã chạy xong. Các cột đã được kiểm tra và thêm nếu cần thiết.' AS result;


