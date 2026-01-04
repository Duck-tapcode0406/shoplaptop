# Hướng Dẫn Debug Lỗi Email

## 🔍 CÁCH XEM LOG LỖI CHI TIẾT

### Bước 1: Xem Console Log trong NetBeans

1. Mở **Output** tab ở dưới cùng NetBeans
2. Hoặc xem **Console** tab
3. Tìm dòng có chữ: `!!! LỖI TRONG QuenMatKhauServlet doPost:`

### Bước 2: Các lỗi thường gặp

#### ❌ Lỗi 1: "Authentication failed"

```
!!! EmailUtil LỖI: Không thể gửi email đến...
javax.mail.AuthenticationFailedException
```

**Nguyên nhân**: Mật khẩu email sai hoặc không phải App Password

**Giải pháp**:

1. Kiểm tra lại `FROM_PASSWORD` trong `EmailUtil.java`
2. Đảm bảo đã tạo **Mật khẩu ứng dụng** (App Password) từ Gmail
3. Xóa dấu cách trong mật khẩu (nếu có)

#### ❌ Lỗi 2: "Connection timeout"

```
java.net.SocketTimeoutException
```

**Nguyên nhân**: Không kết nối được đến Gmail SMTP

**Giải pháp**:

1. Kiểm tra kết nối internet
2. Kiểm tra firewall có chặn port 587 không
3. Thử đổi port sang 465 với SSL

#### ❌ Lỗi 3: "Database connection failed"

```
java.sql.SQLException
```

**Nguyên nhân**: Lỗi kết nối database

**Giải pháp**:

1. Kiểm tra database đang chạy
2. Kiểm tra cấu hình JDBC trong `JDBCUtil.java`

---

## 🛠️ SỬA LỖI NHANH

### Nếu lỗi do Email:

1. **Mở file**: `src/java/util/EmailUtil.java`
2. **Kiểm tra dòng 17-18**:

```java
private static final String FROM_EMAIL = "daiducka123@gmail.com";
private static final String FROM_PASSWORD = "matkhaula2468";
```

3. **Nếu mật khẩu `matkhaula2468` là mật khẩu Gmail thông thường**:
   - ❌ Sẽ KHÔNG hoạt động
   - ✅ Cần tạo **Mật khẩu ứng dụng** (16 ký tự)

### Cách tạo Mật khẩu ứng dụng:

1. Truy cập: https://myaccount.google.com/security
2. Bật **Xác minh 2 bước** (nếu chưa bật)
3. Vào **Mật khẩu ứng dụng** → **Tạo mới**
4. Chọn: **Thư** + **Máy tính Windows**
5. **Sao chép** mật khẩu 16 ký tự (ví dụ: `abcd efgh ijkl mnop`)
6. **Cập nhật** `FROM_PASSWORD` với mật khẩu mới (xóa dấu cách)

---

## 🧪 TEST NHANH

### Test 1: Kiểm tra EmailUtil

Thêm vào `EmailUtil.main()`:

```java
public static void main(String[] args) {
    String matKhauMoi = PasswordGeneratorUtil.generateRandomPassword();
    String noiDung = EmailUtil.createNewPasswordEmailContent("Test User", matKhauMoi);
    boolean success = EmailUtil.sendEmail("daiducka123@gmail.com", "Test Password", noiDung);
    System.out.println("Kết quả: " + success);
}
```

Chạy và xem kết quả trong console.

### Test 2: Kiểm tra Database

Đảm bảo có user với email trong database:

```sql
SELECT * FROM khachhang WHERE email = 'test-email@gmail.com';
```

---

## 📝 CHECKLIST DEBUG

- [ ] Đã xem log trong NetBeans Console
- [ ] Đã kiểm tra `FROM_EMAIL` và `FROM_PASSWORD` đúng chưa
- [ ] Đã tạo Mật khẩu ứng dụng (nếu chưa)
- [ ] Đã test kết nối database
- [ ] Đã kiểm tra user có email trong database

---

**Nếu vẫn lỗi, hãy copy toàn bộ log lỗi từ Console và gửi lại!**




