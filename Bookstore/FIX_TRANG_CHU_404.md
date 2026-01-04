# SỬA LỖI 404 - TRANG CHỦ KHÔNG TÌM THẤY

## 🔴 VẤN ĐỀ

Lỗi **404** khi truy cập `/Bookstore/trang-chu` do:
- Project chưa được **build/compile**
- `TrangChuServlet.class` chưa được tạo trong `build/web/WEB-INF/classes`
- Server không tìm thấy servlet

## ✅ GIẢI PHÁP

### Bước 1: Clean and Build Project

**Trong NetBeans IDE:**

1. **Clean Project:**
   - Click chuột phải vào project `Bookstore`
   - Chọn **Clean** (hoặc nhấn `Shift + F11`)

2. **Build Project:**
   - Click chuột phải vào project
   - Chọn **Build** (hoặc nhấn `F11`)

3. **Hoặc Clean and Build cùng lúc:**
   - Click chuột phải → **Clean and Build** (`Shift + F11`)

### Bước 2: Deploy Project

1. **Stop Server** (nếu đang chạy):
   - Click chuột phải vào Tomcat server
   - Chọn **Stop**

2. **Deploy Project:**
   - Click chuột phải vào project `Bookstore`
   - Chọn **Deploy** (hoặc nhấn `F6`)

3. **Start Server:**
   - Click chuột phải vào Tomcat server
   - Chọn **Start**

### Bước 3: Kiểm tra

Sau khi build, kiểm tra:

1. **File class có tồn tại:**
   ```
   build/web/WEB-INF/classes/controller/KhachHang/TrangChuServlet.class
   ```

2. **JSP file có tồn tại:**
   ```
   build/web/views/khachhang/index.jsp
   ```

3. **Truy cập:**
   - `http://localhost:8081/Bookstore/trang-chu`
   - Hoặc `http://localhost:8081/Bookstore/` (nếu có welcome file)

## 🔍 KIỂM TRA SERVLET

Servlet `TrangChuServlet` đã được cấu hình đúng:

```java
@WebServlet(name = "TrangChuServlet", urlPatterns = {"/trang-chu"})
public class TrangChuServlet extends HttpServlet {
    // ...
}
```

- ✅ URL pattern: `/trang-chu`
- ✅ Forward đến: `/views/khachhang/index.jsp`
- ✅ Code không có lỗi syntax

## 📝 LƯU Ý

1. **Luôn Clean and Build** sau khi:
   - Sửa code Java
   - Thêm/sửa servlet
   - Thay đổi annotation

2. **Restart Server** nếu:
   - Vẫn gặp lỗi 404 sau khi build
   - Thay đổi cấu hình web.xml
   - Thay đổi context path

3. **Kiểm tra Console:**
   - Xem có lỗi compile không
   - Xem có lỗi khi deploy không
   - Xem log của Tomcat

## 🎯 KẾT QUẢ MONG ĐỢI

Sau khi build thành công:
- ✅ Truy cập `http://localhost:8081/Bookstore/trang-chu` hiển thị trang chủ
- ✅ Hiển thị danh sách sách mới và sách bán chạy
- ✅ Không còn lỗi 404

## 🚨 NẾU VẪN GẶP LỖI

1. **Kiểm tra Tomcat Console:**
   - Xem có lỗi khi start server không
   - Xem có lỗi khi deploy không

2. **Kiểm tra Build Output:**
   - Xem có lỗi compile không
   - Xem có file `.class` được tạo không

3. **Kiểm tra Project Properties:**
   - Source folders đúng chưa
   - Libraries đầy đủ chưa
   - Build path đúng chưa







