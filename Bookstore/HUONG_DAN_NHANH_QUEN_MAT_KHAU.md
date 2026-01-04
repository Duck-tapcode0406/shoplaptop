# Hướng Dẫn Nhanh - Quên Mật Khẩu Gửi Email

## ✅ CÁC FILE ĐÃ TẠO

1. ✅ `src/java/util/PasswordGeneratorUtil.java` - Tạo mật khẩu ngẫu nhiên
2. ✅ `src/java/util/EmailUtil.java` - Đã thêm phương thức `createNewPasswordEmailContent()`
3. ✅ `src/java/controller/KhachHang/QuenMatKhauGuiEmailServlet.java` - Servlet xử lý
4. ✅ `web/views/khachhang/xacthuc/forgot-password-send-email.jsp` - Trang form
5. ✅ `web/css/khachhang/style-auth.css` - Đã thêm CSS cho success-message

---

## 🔧 BƯỚC CẤU HÌNH BẮT BUỘC

### ⚠️ QUAN TRỌNG: Cấu hình Email Gmail

**File cần sửa**: `src/java/util/EmailUtil.java`

**Dòng 17-18**, thay đổi:

```java
private static final String FROM_EMAIL = "your-email@gmail.com"; // ⚠️ THAY BẰNG EMAIL CỦA BẠN
private static final String FROM_PASSWORD = "your-app-password"; // ⚠️ THAY BẰNG MẬT KHẨU ỨNG DỤNG
```

### Cách lấy "Mật khẩu ứng dụng" Gmail:

1. Đăng nhập Gmail → https://myaccount.google.com/security
2. Bật **Xác minh 2 bước** (nếu chưa bật)
3. Vào **Mật khẩu ứng dụng** → Tạo mới
4. Chọn: **Thư** + **Máy tính Windows**
5. **SAO CHÉP** mật khẩu 16 ký tự (ví dụ: `abcd efgh ijkl mnop` - xóa dấu cách)

---

## 🚀 CÁCH SỬ DỤNG

### 1. URL truy cập:

```
http://localhost:8080/Bookstore/quen-mat-khau-gui-email
```

### 2. Quy trình:

1. Người dùng nhập email
2. Hệ thống kiểm tra email có tồn tại không
3. Tạo mật khẩu mới ngẫu nhiên (12 ký tự)
4. Hash và lưu vào database
5. Gửi email chứa mật khẩu mới
6. Người dùng nhận email và đăng nhập

---

## 🧪 TEST

### Test thủ công:

1. **Compile project** (Build → Clean and Build)
2. **Chạy server** (Run)
3. **Truy cập**: `http://localhost:8080/Bookstore/quen-mat-khau-gui-email`
4. **Nhập email** của user có trong database
5. **Kiểm tra**:
   - ✅ Thông báo thành công hiển thị
   - ✅ Email được gửi đến hộp thư
   - ✅ Mật khẩu trong email có thể đăng nhập được

### Test Email trực tiếp (trong EmailUtil.main):

```java
// Uncomment và chạy trong EmailUtil.java
String matKhauMoi = PasswordGeneratorUtil.generateRandomPassword();
String noiDung = EmailUtil.createNewPasswordEmailContent("Test User", matKhauMoi);
boolean success = EmailUtil.sendEmail("your-test-email@gmail.com", "Test Password", noiDung);
System.out.println("Kết quả: " + success);
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

1. **BẮT BUỘC** phải cấu hình `FROM_EMAIL` và `FROM_PASSWORD` trước khi sử dụng
2. **Mật khẩu ứng dụng** khác với mật khẩu Gmail thông thường
3. Nếu lỗi "Authentication failed" → Kiểm tra lại mật khẩu ứng dụng
4. Mật khẩu mới được **hash** trước khi lưu vào database (an toàn)

---

## 🔗 LIÊN KẾT

- **Trang quên mật khẩu (mã xác thực)**: `/quen-mat-khau`
- **Trang quên mật khẩu (gửi email)**: `/quen-mat-khau-gui-email`
- **Trang đăng nhập**: `/dang-nhap`

---

## 📝 GHI CHÚ

- Mật khẩu mới có độ dài **12 ký tự** (mặc định)
- Bao gồm: chữ hoa, chữ thường, số, ký tự đặc biệt
- Mật khẩu được tạo bằng `SecureRandom` (an toàn)
- Email được format HTML đẹp mắt

---

**Chúc bạn thành công! 🎉**




