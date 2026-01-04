-- Tạo bảng gói cước
CREATE TABLE IF NOT EXISTS `goicuoc` (
  `maGoi` VARCHAR(50) PRIMARY KEY,
  `tenGoi` VARCHAR(100) NOT NULL,
  `thoiHan` INT NOT NULL COMMENT 'Số tháng',
  `giaTien` BIGINT NOT NULL COMMENT 'Giá tiền (VND)',
  `moTa` TEXT,
  `trangThai` INT DEFAULT 1 COMMENT '1 = Active, 0 = Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm các gói cước mặc định
INSERT INTO `goicuoc` (`maGoi`, `tenGoi`, `thoiHan`, `giaTien`, `moTa`, `trangThai`) VALUES
('GOI_1THANG', 'Gói 1 Tháng', 1, 199000, 'Đọc không giới hạn trong 1 tháng', 1),
('GOI_6THANG', 'Gói 6 Tháng', 6, 399000, 'Đọc không giới hạn trong 6 tháng - Tiết kiệm 40%', 1),
('GOI_1NAM', 'Gói 1 Năm', 12, 499000, 'Đọc không giới hạn trong 1 năm - Tiết kiệm 60%', 1);

-- Cập nhật bảng khachhang: thêm các cột gói cước
-- Kiểm tra và thêm cột nếu chưa tồn tại (MySQL không hỗ trợ IF NOT EXISTS cho ALTER TABLE)
-- Nếu cột đã tồn tại, sẽ bỏ qua lỗi

-- Thêm cột maGoiCuoc
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
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(50) NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Thêm cột ngayDangKy
SET @columnname = "ngayDangKy";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Thêm cột ngayHetHan
SET @columnname = "ngayHetHan";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Thêm foreign key (chỉ thêm nếu chưa tồn tại)
-- Kiểm tra constraint trước khi thêm
SET @constraint_name = "fk_khachhang_goicuoc";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (constraint_name = @constraint_name)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD CONSTRAINT ", @constraint_name, 
         " FOREIGN KEY (maGoiCuoc) REFERENCES goicuoc(maGoi) ON DELETE SET NULL ON UPDATE CASCADE")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

