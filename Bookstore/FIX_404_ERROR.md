# SỬA LỖI 404 - SERVLET KHÔNG TÌM THẤY

## 🔴 VẤN ĐỀ

Lỗi **HTTP Status 404 - Not Found** khi truy cập `/Bookstore/dang-nhap` do:
- Project chưa được **build/compile**
- Servlet class chưa được tạo trong `build/web/WEB-INF/classes`
- Server không tìm thấy servlet

## ✅ GIẢI PHÁP

### Cách 1: Build Project trong NetBeans (Khuyến nghị)

1. **Mở NetBeans IDE**
2. **Clean and Build Project:**
   - Click chuột phải vào project `Bookstore`
   - Chọn **Clean and Build** (hoặc nhấn `Shift + F11`)
   - Hoặc chọn **Build** → **Clean and Build Project**

3. **Deploy lại project:**
   - Click chuột phải vào project
   - Chọn **Deploy** (hoặc nhấn `F6`)
   - Hoặc chọn **Run** → **Run Project**

4. **Kiểm tra:**
   - Sau khi build, kiểm tra thư mục `build/web/WEB-INF/classes/controller/KhachHang/`
   - Phải có file `DangNhapServlet.class`

### Cách 2: Restart Server

1. **Dừng Tomcat server** (nếu đang chạy)
2. **Build lại project** (theo Cách 1)
3. **Start lại Tomcat server**
4. **Truy cập lại:** `http://localhost:8081/Bookstore/dang-nhap`

### Cách 3: Kiểm tra Build Path

1. **Kiểm tra Source Folders:**
   - Click chuột phải vào project → **Properties**
   - Chọn **Sources**
   - Đảm bảo `src/java` được cấu hình đúng

2. **Kiểm tra Libraries:**
   - Trong **Properties** → **Libraries**
   - Đảm bảo có đầy đủ các thư viện:
     - Jakarta Servlet API
     - Jakarta JSP JSTL
     - MySQL Connector
     - BCrypt
     - Gson

## 🔍 KIỂM TRA SAU KHI BUILD

Sau khi build thành công, kiểm tra:

1. **File class có tồn tại:**
   ```
   build/web/WEB-INF/classes/controller/KhachHang/DangNhapServlet.class
   ```

2. **Servlet được map đúng:**
   - Annotation `@WebServlet(urlPatterns = {"/dang-nhap"})` đã có trong code
   - URL pattern: `/dang-nhap`

3. **Server log:**
   - Kiểm tra console/log của Tomcat
   - Không có lỗi khi deploy

## 📝 LƯU Ý

- **Luôn Clean and Build** sau khi sửa code Java
- **Restart server** nếu vẫn gặp lỗi sau khi build
- **Kiểm tra console** để xem có lỗi compile không

## 🎯 KẾT QUẢ MONG ĐỢI

Sau khi build thành công:
- ✅ Truy cập `http://localhost:8081/Bookstore/dang-nhap` sẽ hiển thị form đăng nhập
- ✅ POST request đến `/dang-nhap` sẽ được xử lý bởi `DangNhapServlet`
- ✅ Không còn lỗi 404







