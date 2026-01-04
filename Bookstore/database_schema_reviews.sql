-- ============================================
-- DATABASE SCHEMA CHO REVIEWS (ĐÁNH GIÁ)
-- ============================================
-- Chạy script này trong phpMyAdmin để tạo các bảng cho replies, likes, dislikes

-- Bảng trả lời đánh giá
CREATE TABLE IF NOT EXISTS `traloidanhgia` (
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

-- Bảng like đánh giá
CREATE TABLE IF NOT EXISTS `danhgialike` (
  `maDanhGia` VARCHAR(50) NOT NULL,
  `maKhachHang` VARCHAR(50) NOT NULL,
  `ngayLike` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maDanhGia`, `maKhachHang`),
  FOREIGN KEY (`maDanhGia`) REFERENCES `danhgia`(`maDanhGia`) ON DELETE CASCADE,
  FOREIGN KEY (`maKhachHang`) REFERENCES `khachhang`(`maKhachHang`) ON DELETE CASCADE,
  INDEX `idx_maDanhGia` (`maDanhGia`),
  INDEX `idx_maKhachHang` (`maKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng dislike đánh giá
CREATE TABLE IF NOT EXISTS `danhgiadislike` (
  `maDanhGia` VARCHAR(50) NOT NULL,
  `maKhachHang` VARCHAR(50) NOT NULL,
  `ngayDislike` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maDanhGia`, `maKhachHang`),
  FOREIGN KEY (`maDanhGia`) REFERENCES `danhgia`(`maDanhGia`) ON DELETE CASCADE,
  FOREIGN KEY (`maKhachHang`) REFERENCES `khachhang`(`maKhachHang`) ON DELETE CASCADE,
  INDEX `idx_maDanhGia` (`maDanhGia`),
  INDEX `idx_maKhachHang` (`maKhachHang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

