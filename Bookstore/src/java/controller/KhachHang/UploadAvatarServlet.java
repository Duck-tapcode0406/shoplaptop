package controller.KhachHang;

import database.KhachHangDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.MultipartConfig;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import jakarta.servlet.http.Part;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.util.UUID;
import model.KhachHang;

/**
 * Servlet xử lý upload ảnh đại diện
 */
@WebServlet(name = "UploadAvatarServlet", urlPatterns = {"/upload-avatar"})
@MultipartConfig(
    fileSizeThreshold = 1024 * 1024, // 1MB
    maxFileSize = 5 * 1024 * 1024, // 5MB
    maxRequestSize = 10 * 1024 * 1024 // 10MB
)
public class UploadAvatarServlet extends HttpServlet {

    private static final String UPLOAD_DIR = "assets/images/avatars/";

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("application/json; charset=UTF-8");
        
        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");
        
        // Kiểm tra đăng nhập
        if (user == null) {
            response.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
            response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng đăng nhập.\"}");
            return;
        }
        
        try {
            Part filePart = request.getPart("avatar");
            
            if (filePart == null || filePart.getSize() == 0) {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng chọn file ảnh.\"}");
                return;
            }
            
            // Kiểm tra loại file
            String contentType = filePart.getContentType();
            if (contentType == null || !contentType.startsWith("image/")) {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"File phải là ảnh.\"}");
                return;
            }
            
            // Lấy tên file gốc
            String fileName = filePart.getSubmittedFileName();
            if (fileName == null || fileName.isEmpty()) {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"Tên file không hợp lệ.\"}");
                return;
            }
            
            // Tạo tên file mới để tránh trùng lặp
            String fileExtension = fileName.substring(fileName.lastIndexOf("."));
            String newFileName = UUID.randomUUID().toString() + fileExtension;
            
            // Lấy đường dẫn thực tế của thư mục upload
            String appPath = request.getServletContext().getRealPath("");
            String uploadPath = appPath + UPLOAD_DIR;
            
            // Tạo thư mục nếu chưa tồn tại
            File uploadDir = new File(uploadPath);
            if (!uploadDir.exists()) {
                uploadDir.mkdirs();
            }
            
            // Lưu file
            Path targetPath = Paths.get(uploadPath + newFileName);
            Files.copy(filePart.getInputStream(), targetPath, StandardCopyOption.REPLACE_EXISTING);
            
            // Xóa ảnh cũ nếu có (trừ ảnh mặc định)
            String oldAvatar = user.getDuongDanAnh();
            if (oldAvatar != null && !oldAvatar.isEmpty() && !oldAvatar.equals("avatar-default.png")) {
                File oldFile = new File(uploadPath + oldAvatar);
                if (oldFile.exists()) {
                    oldFile.delete();
                }
            }
            
            // Cập nhật đường dẫn ảnh trong database
            user.setDuongDanAnh(newFileName);
            KhachHangDAO khachHangDAO = new KhachHangDAO();
            int result = khachHangDAO.update(user);
            
            if (result > 0) {
                // Cập nhật session
                session.setAttribute("user", user);
                
                // Trả về đường dẫn ảnh mới
                String imageUrl = request.getContextPath() + "/" + UPLOAD_DIR + newFileName;
                response.setStatus(HttpServletResponse.SC_OK);
                response.getWriter().write("{\"success\": true, \"message\": \"Upload ảnh đại diện thành công.\", \"imageUrl\": \"" + imageUrl + "\"}");
            } else {
                // Xóa file vừa upload nếu cập nhật DB thất bại
                Files.deleteIfExists(targetPath);
                response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
                response.getWriter().write("{\"success\": false, \"message\": \"Không thể cập nhật ảnh đại diện.\"}");
            }
            
        } catch (Exception e) {
            System.err.println("Lỗi khi upload avatar: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi upload ảnh.\"}");
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doPost(request, response);
    }
}
















