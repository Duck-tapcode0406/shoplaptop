# Hướng Dẫn Test Quên Mật Khẩu

## ✅ TRANG ĐÃ LOAD ĐƯỢC

Version đơn giản (`forgot-password-simple.jsp`) đã hoạt động - không còn StackOverflowError!

## 🔍 KIỂM TRA LỖI KHI SUBMIT

### Bước 1: Xem Log trong NetBeans

1. Mở **Output** tab trong NetBeans
2. Submit form với email `daiducka123@gmail.com`
3. Tìm dòng: `!!! ========================================`
4. Copy toàn bộ log từ đó

### Bước 2: Các lỗi có thể gặp

#### ❌ Lỗi 1: "Authentication failed" (Email)
```
!!! Loại lỗi: javax.mail.AuthenticationFailedException
!!! Thông báo: 535-5.7.8 Username and Password not accepted
```

**Giải pháp**: 
- Kiểm tra lại mật khẩu ứng dụng trong `EmailUtil.java`
- Đảm bảo đã xóa dấu cách

#### ❌ Lỗi 2: "SQL Exception" (Database)
```
!!! Loại lỗi: java.sql.SQLException
!!! Thông báo: Unknown column 'maXacThuc' in 'field list'
```

**Giải pháp**: 
- Kiểm tra bảng `khachhang` có cột `maXacThuc` và `thoiGianHieuLucCuaMaXacThuc` không
- Chạy SQL script để thêm cột nếu thiếu

#### ❌ Lỗi 3: "NullPointerException"
```
!!! Loại lỗi: java.lang.NullPointerException
```

**Giải pháp**: 
- Có thể do `user.getHoVaTen()` null
- Hoặc do `mapRowToKhachHang()` gặp lỗi

---

## 🛠️ SỬA LỖI NHANH

### Nếu lỗi do Database (thiếu cột):

Chạy SQL script này:

```sql
-- Kiểm tra cột có tồn tại không
SHOW COLUMNS FROM `khachhang` LIKE 'maXacThuc';
SHOW COLUMNS FROM `khachhang` LIKE 'thoiGianHieuLucCuaMaXacThuc';

-- Nếu không có, thêm cột:
ALTER TABLE `khachhang` 
ADD COLUMN IF NOT EXISTS `maXacThuc` VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS `thoiGianHieuLucCuaMaXacThuc` TIMESTAMP NULL;
```

### Nếu lỗi do Email:

1. Test EmailUtil trực tiếp:
   - Mở `EmailUtil.java`
   - Uncomment hàm `main()`
   - Run File
   - Xem kết quả

2. Kiểm tra mật khẩu ứng dụng:
   - Đảm bảo đúng 16 ký tự
   - Không có dấu cách

---

## 📝 CHECKLIST

- [ ] Đã xem log lỗi trong NetBeans Console
- [ ] Đã copy log lỗi đầy đủ
- [ ] Đã kiểm tra database có cột `maXacThuc` và `thoiGianHieuLucCuaMaXacThuc`
- [ ] Đã test EmailUtil.main() trực tiếp
- [ ] Đã kiểm tra mật khẩu ứng dụng đúng

---

**Vui lòng copy log lỗi từ NetBeans Console và gửi lại để tôi có thể sửa chính xác!**


