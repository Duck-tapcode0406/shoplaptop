# Hướng Dẫn Kiểm Tra Cấu Hình Email

## 📋 CÁC CẤU HÌNH ĐÃ CẢI THIỆN

### 1. **EmailUtil.java** (TLS - Port 587) ✅
- Port: **587** (TLS)
- Protocol: **STARTTLS**
- Timeout: 5 giây
- Debug mode: Có thể bật/tắt

### 2. **EmailUtilSSL.java** (SSL - Port 465) ✅
- Port: **465** (SSL)
- Protocol: **SSL**
- Dùng khi TLS không hoạt động

---

## 🔍 CÁCH KIỂM TRA CẤU HÌNH

### Bước 1: Test EmailUtil trực tiếp

1. **Mở file**: `src/java/util/EmailUtil.java`
2. **Uncomment** hàm `main()` (đã có sẵn code test)
3. **Right-click** vào file → **Run File** (hoặc Shift+F6)
4. **Xem kết quả** trong Console:
   - ✅ Nếu thành công: `Gửi email: ✅ THÀNH CÔNG`
   - ❌ Nếu thất bại: Xem log lỗi chi tiết

### Bước 2: Xem log chi tiết

Trong Console sẽ hiển thị:
```
=== TEST EMAIL UTIL ===
FROM_EMAIL: daiducka123@gmail.com
FROM_PASSWORD: ***
========================

Đang gửi email test đến: daiducka123@gmail.com
EmailUtil: Bắt đầu gửi email đến...
EmailUtil: Đang xác thực với email: daiducka123@gmail.com
EmailUtil: Đang kết nối và gửi email...
EmailUtil: ✅ Đã gửi email thành công đến...
```

### Bước 3: Bật Debug Mode (nếu cần)

Trong `EmailUtil.java`, dòng 45:
```java
props.put("mail.debug", "true"); // Đổi từ false sang true
```

Sau đó chạy lại test, sẽ thấy log rất chi tiết về quá trình gửi email.

---

## 🛠️ CÁC LỖI THƯỜNG GẶP VÀ CÁCH SỬA

### ❌ Lỗi 1: "Authentication failed"

**Log lỗi**:
```
!!! EmailUtil LỖI: Không thể gửi email đến...
javax.mail.AuthenticationFailedException: 535-5.7.8 Username and Password not accepted
```

**Nguyên nhân**:
- Mật khẩu ứng dụng sai
- Chưa tạo mật khẩu ứng dụng
- Email không đúng

**Giải pháp**:
1. Kiểm tra lại `FROM_EMAIL` và `FROM_PASSWORD`
2. Tạo lại mật khẩu ứng dụng từ Gmail
3. Đảm bảo đã xóa dấu cách trong mật khẩu

---

### ❌ Lỗi 2: "Connection timeout"

**Log lỗi**:
```
!!! EmailUtil LỖI: java.net.SocketTimeoutException
```

**Nguyên nhân**:
- Firewall chặn port 587
- Mạng không kết nối được đến Gmail
- Port bị chặn

**Giải pháp**:
1. **Thử dùng SSL (Port 465)**:
   - Sửa `QuenMatKhauGuiEmailServlet.java`
   - Thay `EmailUtil.sendEmail()` bằng `EmailUtilSSL.sendEmail()`
   
2. **Kiểm tra firewall**:
   - Cho phép port 587 và 465

3. **Kiểm tra internet**:
   - Đảm bảo có kết nối internet

---

### ❌ Lỗi 3: "Could not connect to SMTP host"

**Log lỗi**:
```
!!! EmailUtil LỖI: Could not connect to SMTP host: smtp.gmail.com, port: 587
```

**Nguyên nhân**:
- Không kết nối được đến Gmail SMTP
- Proxy/VPN chặn

**Giải pháp**:
1. Thử dùng SSL (Port 465)
2. Kiểm tra proxy settings
3. Thử từ mạng khác

---

## 🔄 CHUYỂN TỪ TLS SANG SSL

Nếu TLS (Port 587) không hoạt động, thử SSL (Port 465):

### Cách 1: Sửa trong QuenMatKhauGuiEmailServlet

```java
// Thay đổi import
import util.EmailUtilSSL;

// Thay đổi trong doPost()
boolean emailSent = EmailUtilSSL.sendEmail(email, "Mật khẩu mới - BookStore", noiDungEmail);
```

### Cách 2: Sửa trong QuenMatKhauServlet

Tương tự, thay `EmailUtil.sendEmail()` bằng `EmailUtilSSL.sendEmail()`

---

## 📊 SO SÁNH CẤU HÌNH

| Thuộc tính | TLS (Port 587) | SSL (Port 465) |
|------------|----------------|----------------|
| Port | 587 | 465 |
| Protocol | STARTTLS | SSL |
| Tốc độ | Nhanh hơn | Chậm hơn một chút |
| Bảo mật | ✅ Tốt | ✅ Tốt |
| Firewall | Có thể bị chặn | Ít bị chặn hơn |

---

## ✅ CHECKLIST KIỂM TRA

- [ ] Đã test EmailUtil.main() và thấy log
- [ ] Đã kiểm tra FROM_EMAIL và FROM_PASSWORD đúng
- [ ] Đã xem log lỗi chi tiết trong Console
- [ ] Đã thử bật debug mode nếu cần
- [ ] Đã thử SSL nếu TLS không hoạt động

---

## 🧪 TEST NHANH

1. **Mở** `EmailUtil.java`
2. **Chạy** hàm `main()` (Right-click → Run File)
3. **Xem kết quả**:
   - ✅ Thành công → Cấu hình đúng
   - ❌ Thất bại → Xem log và sửa theo hướng dẫn trên

---

**Sau khi test, hãy cho tôi biết kết quả!**




