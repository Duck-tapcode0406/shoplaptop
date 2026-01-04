-- Script kiểm tra và tạo lại các bảng nếu cần
-- Chạy script này trong phpMyAdmin để đảm bảo các bảng được tạo đúng

-- Kiểm tra và tạo bảng danh_gia_like
CREATE TABLE IF NOT EXISTS `danh_gia_like` (
  `maDanhGia` VARCHAR(50) NOT NULL,
  `maKhachHang` VARCHAR(50) NOT NULL,
  `ngayLike` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maDanhGia`, `maKhachHang`),
  FOREIGN KEY (`maDanhGia`) REFERENCES `danhgia`(`maDanhGia`) ON DELETE CASCADE,
  FOREIGN KEY (`maKhachHang`) REFERENCES `khachhang`(`maKhachHang`) ON DELETE CASCADE,
  INDEX `idx_maDanhGia` (`maDanhGia`),
  INDEX `idx_maKhachHang` (`maKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kiểm tra và tạo bảng danh_gia_dislike
CREATE TABLE IF NOT EXISTS `danh_gia_dislike` (
  `maDanhGia` VARCHAR(50) NOT NULL,
  `maKhachHang` VARCHAR(50) NOT NULL,
  `ngayDislike` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maDanhGia`, `maKhachHang`),
  FOREIGN KEY (`maDanhGia`) REFERENCES `danhgia`(`maDanhGia`) ON DELETE CASCADE,
  FOREIGN KEY (`maKhachHang`) REFERENCES `khachhang`(`maKhachHang`) ON DELETE CASCADE,
  INDEX `idx_maDanhGia` (`maDanhGia`),
  INDEX `idx_maKhachHang` (`maKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kiểm tra và tạo bảng tra_loi_danh_gia
CREATE TABLE IF NOT EXISTS `tra_loi_danh_gia` (
  `maTraLoi` VARCHAR(50) NOT NULL PRIMARY KEY,
  `maDanhGia` VARCHAR(50) NOT NULL,
  `maKhachHang` VARCHAR(50) NOT NULL,
  `noiDung` TEXT NOT NULL,
  `ngayTraLoi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`maDanhGia`) REFERENCES `danhgia`(`maDanhGia`) ON DELETE CASCADE,
  FOREIGN KEY (`maKhachHang`) REFERENCES `khachhang`(`maKhachHang`) ON DELETE CASCADE,
  INDEX `idx_maDanhGia` (`maDanhGia`),
  INDEX `idx_maKhachHang` (`maKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kiểm tra các bảng đã tồn tại
SELECT 'danh_gia_like' as table_name, COUNT(*) as row_count FROM `danh_gia_like`
UNION ALL
SELECT 'danh_gia_dislike' as table_name, COUNT(*) as row_count FROM `danh_gia_dislike`
UNION ALL
SELECT 'tra_loi_danh_gia' as table_name, COUNT(*) as row_count FROM `tra_loi_danh_gia`;
















