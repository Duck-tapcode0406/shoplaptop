# Hướng Dẫn Cài Đặt Chức Năng Quên Mật Khẩu - Gửi Mật Khẩu Mới Qua Email

## 📋 Tổng Quan

Tài liệu này hướng dẫn cách cài đặt chức năng quên mật khẩu, tự động tạo mật khẩu mới và gửi về email thật của người dùng.

## ⚠️ LƯU Ý BẢO MẬT

**CẢNH BÁO**: Gửi mật khẩu mới trực tiếp qua email **KHÔNG AN TOÀN** vì:
- Email có thể bị đọc bởi người khác
- Mật khẩu được lưu trữ dạng plain text trong email
- Không có cơ chế xác thực người dùng

**KHUYẾN NGHỊ**: Sử dụng phương pháp Token-based (gửi link reset) như đã có trong dự án hiện tại.

---

## ✅ CÁC THÀNH PHẦN ĐÃ CÓ SẴN

Dự án đã có sẵn các thành phần sau:

### 1. **EmailUtil.java** ✅
- **Vị trí**: `src/java/util/EmailUtil.java`
- **Chức năng**: Gửi email qua SMTP Gmail
- **Cấu hình hiện tại**:
  ```java
  private static final String FROM_EMAIL = "22T1020575@husc.edu.vn";
  private static final String FROM_PASSWORD = "111"; // ⚠️ CẦN THAY ĐỔI
  ```

### 2. **KhachHangDAO.java** ✅
- **Vị trí**: `src/java/database/KhachHangDAO.java`
- **Các phương thức có sẵn**:
  - `selectByEmail(String email)` - Tìm user theo email
  - `updatePasswordByEmail(String email, String newPassword)` - Cập nhật mật khẩu mới

### 3. **PasswordUtil.java** ✅
- **Vị trí**: `src/java/util/PasswordUtil.java`
- **Chức năng**: Hash mật khẩu (BCrypt)

### 4. **QuenMatKhauServlet.java** ✅
- **Vị trí**: `src/java/controller/KhachHang/QuenMatKhauServlet.java`
- **URL**: `/quen-mat-khau`
- **Trạng thái**: Hiện đang dùng mã xác thực 6 số

---

## 🔧 CÁC BƯỚC CÀI ĐẶT

### BƯỚC 1: Cấu Hình Email Gmail

#### 1.1. Tạo "Mật khẩu ứng dụng" cho Gmail

1. Đăng nhập vào Gmail của bạn
2. Truy cập: https://myaccount.google.com/security
3. Bật **Xác minh 2 bước** (nếu chưa bật)
4. Vào **Mật khẩu ứng dụng** (App passwords)
5. Tạo mật khẩu ứng dụng mới:
   - Chọn ứng dụng: **Thư**
   - Chọn thiết bị: **Máy tính Windows** (hoặc khác)
   - Click **Tạo**
6. **SAO CHÉP** mật khẩu 16 ký tự (ví dụ: `abcd efgh ijkl mnop`)

#### 1.2. Cập nhật EmailUtil.java

**File**: `src/java/util/EmailUtil.java`

**Thay đổi**:
```java
// Dòng 17-18
private static final String FROM_EMAIL = "your-email@gmail.com"; // ⚠️ THAY BẰNG EMAIL CỦA BẠN
private static final String FROM_PASSWORD = "your-app-password"; // ⚠️ THAY BẰNG MẬT KHẨU ỨNG DỤNG 16 KÝ TỰ
```

**Ví dụ**:
```java
private static final String FROM_EMAIL = "bookstore@gmail.com";
private static final String FROM_PASSWORD = "abcd efgh ijkl mnop"; // Không có dấu cách
```

---

### BƯỚC 2: Tạo Phương Thức Tạo Mật Khẩu Ngẫu Nhiên

**File mới**: `src/java/util/PasswordGeneratorUtil.java`

**Nội dung**:
```java
package util;

import java.security.SecureRandom;

public class PasswordGeneratorUtil {
    
    private static final String UPPERCASE = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private static final String LOWERCASE = "abcdefghijklmnopqrstuvwxyz";
    private static final String DIGITS = "0123456789";
    private static final String SPECIAL = "!@#$%^&*";
    private static final String ALL_CHARS = UPPERCASE + LOWERCASE + DIGITS + SPECIAL;
    
    private static final SecureRandom random = new SecureRandom();
    
    /**
     * Tạo mật khẩu ngẫu nhiên an toàn
     * @param length Độ dài mật khẩu (mặc định 12)
     * @return Mật khẩu ngẫu nhiên
     */
    public static String generateRandomPassword(int length) {
        if (length < 8) {
            length = 12; // Mặc định 12 ký tự
        }
        
        StringBuilder password = new StringBuilder(length);
        
        // Đảm bảo có ít nhất 1 ký tự từ mỗi loại
        password.append(UPPERCASE.charAt(random.nextInt(UPPERCASE.length())));
        password.append(LOWERCASE.charAt(random.nextInt(LOWERCASE.length())));
        password.append(DIGITS.charAt(random.nextInt(DIGITS.length())));
        password.append(SPECIAL.charAt(random.nextInt(SPECIAL.length())));
        
        // Điền các ký tự còn lại
        for (int i = password.length(); i < length; i++) {
            password.append(ALL_CHARS.charAt(random.nextInt(ALL_CHARS.length())));
        }
        
        // Trộn ngẫu nhiên các ký tự
        char[] passwordArray = password.toString().toCharArray();
        for (int i = passwordArray.length - 1; i > 0; i--) {
            int j = random.nextInt(i + 1);
            char temp = passwordArray[i];
            passwordArray[i] = passwordArray[j];
            passwordArray[j] = temp;
        }
        
        return new String(passwordArray);
    }
    
    /**
     * Tạo mật khẩu ngẫu nhiên với độ dài mặc định (12 ký tự)
     */
    public static String generateRandomPassword() {
        return generateRandomPassword(12);
    }
}
```

---

### BƯỚC 3: Tạo Phương Thức Email Gửi Mật Khẩu Mới

**File**: `src/java/util/EmailUtil.java`

**Thêm phương thức mới** (sau dòng 115):

```java
/**
 * Tạo nội dung email gửi mật khẩu mới
 * (Dùng cho QuenMatKhauServlet - gửi mật khẩu trực tiếp)
 * @param tenNguoiNhan Tên của người nhận
 * @param matKhauMoi Mật khẩu mới
 * @return Chuỗi HTML nội dung email
 */
public static String createNewPasswordEmailContent(String tenNguoiNhan, String matKhauMoi) {
    return "<!DOCTYPE html>"
         + "<html>"
         + "<head><meta charset='UTF-8'></head>"
         + "<body style='font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4; padding: 20px;'>"
         + "<div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>"
         + "<h2 style='color: #00466a;'>Xin chào " + tenNguoiNhan + ",</h2>"
         + "<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại BookStore.</p>"
         + "<p>Mật khẩu mới của bạn là:</p>"
         + "<div style='background-color: #f0f0f0; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;'>"
         + "<h3 style='color: #00466a; margin: 0; font-size: 24px; letter-spacing: 2px; font-family: monospace;'>"
         + matKhauMoi
         + "</h3>"
         + "</div>"
         + "<p style='color: #ff0000; font-weight: bold;'>⚠️ Vui lòng đăng nhập và đổi mật khẩu ngay sau khi nhận được email này để đảm bảo an toàn.</p>"
         + "<p>Nếu bạn không yêu cầu thao tác này, vui lòng liên hệ với chúng tôi ngay lập tức.</p>"
         + "<p style='margin-top: 30px;'>Trân trọng,<br><strong>Đội ngũ BookStore</strong></p>"
         + "</div>"
         + "</body>"
         + "</html>";
}
```

---

### BƯỚC 4: Tạo Servlet Mới - QuenMatKhauGuiEmailServlet

**File mới**: `src/java/controller/KhachHang/QuenMatKhauGuiEmailServlet.java`

**Nội dung**:
```java
package controller.KhachHang;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import model.KhachHang;
import util.EmailUtil;
import util.PasswordGeneratorUtil;

@WebServlet(name = "QuenMatKhauGuiEmailServlet", urlPatterns = {"/quen-mat-khau-gui-email"})
public class QuenMatKhauGuiEmailServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/xacthuc/forgot-password-send-email.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String email = request.getParameter("email");
        String url = "";

        if (email == null || email.trim().isEmpty()) {
            request.setAttribute("error", "Vui lòng nhập địa chỉ email.");
            url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
        } else {
            try {
                KhachHangDAO khachHangDAO = new KhachHangDAO();
                KhachHang user = khachHangDAO.selectByEmail(email);

                if (user == null) {
                    request.setAttribute("error", "Email không tồn tại trong hệ thống!");
                    request.setAttribute("email", email);
                    url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                } else {
                    // Tạo mật khẩu mới ngẫu nhiên
                    String matKhauMoi = PasswordGeneratorUtil.generateRandomPassword(12);

                    // Cập nhật mật khẩu mới vào database (tự động hash)
                    int updateResult = khachHangDAO.updatePasswordByEmail(email, matKhauMoi);

                    if (updateResult > 0) {
                        // Gửi email chứa mật khẩu mới
                        String noiDungEmail = EmailUtil.createNewPasswordEmailContent(user.getHoVaTen(), matKhauMoi);
                        boolean emailSent = EmailUtil.sendEmail(email, "Mật khẩu mới - BookStore", noiDungEmail);

                        if (emailSent) {
                            request.setAttribute("success", "Mật khẩu mới đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.");
                            request.setAttribute("email", email);
                            url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                        } else {
                            request.setAttribute("error", "Lỗi khi gửi email. Vui lòng thử lại sau.");
                            request.setAttribute("email", email);
                            url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                        }
                    } else {
                        request.setAttribute("error", "Lỗi hệ thống khi cập nhật mật khẩu.");
                        request.setAttribute("email", email);
                        url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                    }
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG QuenMatKhauGuiEmailServlet: " + e.getMessage());
                e.printStackTrace();
                request.setAttribute("error", "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.");
                request.setAttribute("email", email);
                url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
            }
        }

        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
        rd.forward(request, response);
    }
}
```

---

### BƯỚC 5: Tạo Trang JSP

**File mới**: `web/views/khachhang/xacthuc/forgot-password-send-email.jsp`

**Nội dung** (dựa trên cấu trúc của `forgot-password.jsp` hiện có):

```jsp
<%@page contentType="text/html" pageEncoding="UTF-8"%>
<%@taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<jsp:include page="../layout/header.jsp" />

<main class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-header">
            <h1>Quên Mật Khẩu</h1>
            <p>Nhập email của bạn để nhận mật khẩu mới</p>
            <a href="${baseURL}/dang-nhap" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Quay lại đăng nhập
            </a>
        </div>
    
        <div class="auth-form-box">
            <form action="${baseURL}/quen-mat-khau-gui-email" method="POST">
                <h2>Nhập Email</h2>
                <p style="text-align: center; margin-bottom: 1.5rem; color: #555;">
                    Chúng tôi sẽ gửi mật khẩu mới đến email của bạn.
                </p>
                
                <c:if test="${not empty requestScope.error}">
                    <div class="error-message">
                        <i class="fa-solid fa-circle-exclamation"></i> ${requestScope.error}
                    </div>
                </c:if>
                
                <c:if test="${not empty requestScope.success}">
                    <div class="success-message">
                        <i class="fa-solid fa-circle-check"></i> ${requestScope.success}
                    </div>
                </c:if>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="${requestScope.email}" 
                           placeholder="Nhập email của bạn" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-submit">
                    Gửi Mật Khẩu Mới
                </button>
                
            </form>
        </div>
    </div>
</main>

<jsp:include page="../layout/footer.jsp" />
```

---

## 📝 CHECKLIST CÀI ĐẶT

### ✅ Các Bước Bắt Buộc:

- [ ] **Bước 1**: Tạo mật khẩu ứng dụng Gmail và cập nhật `EmailUtil.java`
- [ ] **Bước 2**: Tạo file `PasswordGeneratorUtil.java`
- [ ] **Bước 3**: Thêm phương thức `createNewPasswordEmailContent()` vào `EmailUtil.java`
- [ ] **Bước 4**: Tạo file `QuenMatKhauGuiEmailServlet.java`
- [ ] **Bước 5**: Tạo file JSP `forgot-password-send-email.jsp`

### ✅ Kiểm Tra:

- [ ] Compile project không có lỗi
- [ ] Test gửi email thành công
- [ ] Mật khẩu mới được lưu vào database (đã hash)
- [ ] Email nhận được mật khẩu mới đúng định dạng

---

## 🧪 KIỂM TRA VÀ TEST

### Test Thủ Công:

1. **Truy cập**: `http://localhost:8080/Bookstore/quen-mat-khau-gui-email`
2. **Nhập email** của một user có trong database
3. **Click "Gửi Mật Khẩu Mới"**
4. **Kiểm tra**:
   - Thông báo thành công hiển thị
   - Email được gửi đến hộp thư
   - Mật khẩu trong email có thể đăng nhập được

### Test Email:

```java
// Test trong EmailUtil.main()
String matKhauMoi = PasswordGeneratorUtil.generateRandomPassword();
String noiDung = EmailUtil.createNewPasswordEmailContent("Test User", matKhauMoi);
boolean success = EmailUtil.sendEmail("your-test-email@gmail.com", "Test Password", noiDung);
System.out.println("Kết quả: " + success);
```

---

## ⚠️ CÁC VẤN ĐỀ THƯỜNG GẶP

### 1. Lỗi "Authentication failed"

**Nguyên nhân**: Mật khẩu ứng dụng Gmail sai hoặc chưa tạo

**Giải pháp**:
- Kiểm tra lại mật khẩu ứng dụng 16 ký tự
- Đảm bảo đã bật xác minh 2 bước
- Xóa dấu cách trong mật khẩu (nếu có)

### 2. Email không được gửi

**Nguyên nhân**: 
- SMTP settings sai
- Firewall chặn port 587
- Gmail chặn ứng dụng không an toàn

**Giải pháp**:
- Kiểm tra log console để xem lỗi chi tiết
- Thử dùng port 465 với SSL thay vì TLS
- Cho phép ứng dụng không an toàn trong Gmail (không khuyến nghị)

### 3. Mật khẩu không khớp khi đăng nhập

**Nguyên nhân**: Mật khẩu đã được hash trong database

**Giải pháp**: 
- Đây là hành vi đúng
- Sử dụng mật khẩu mới từ email để đăng nhập
- Mật khẩu sẽ được hash và so sánh tự động

---

## 🔐 BẢO MẬT BỔ SUNG (TÙY CHỌN)

Nếu muốn tăng cường bảo mật, có thể thêm:

1. **Rate Limiting**: Giới hạn số lần gửi email trong một khoảng thời gian
2. **IP Tracking**: Ghi log IP address khi gửi yêu cầu
3. **Email Verification**: Xác thực email trước khi gửi mật khẩu
4. **Expiry Time**: Mật khẩu mới chỉ có hiệu lực trong thời gian nhất định

---

## 📚 TÀI LIỆU THAM KHẢO

- [Gmail App Passwords](https://support.google.com/accounts/answer/185833)
- [Jakarta Mail API](https://eclipse-ee4j.github.io/mail/)
- [Secure Password Generation](https://owasp.org/www-community/vulnerabilities/Weak_Password_Requirements)

---

## ✅ KẾT LUẬN

Sau khi hoàn thành tất cả các bước trên, bạn sẽ có:

1. ✅ Chức năng quên mật khẩu hoàn chỉnh
2. ✅ Tự động tạo mật khẩu mới an toàn (12 ký tự)
3. ✅ Gửi mật khẩu mới qua email thật
4. ✅ Mật khẩu được hash và lưu vào database

**Lưu ý**: Nhớ cập nhật `FROM_EMAIL` và `FROM_PASSWORD` trong `EmailUtil.java` trước khi sử dụng!





