package controller.Admin;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.MultipartConfig;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.Part;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.util.UUID;

@WebServlet(name = "UploadLogoServlet", urlPatterns = {"/admin/upload-logo"})
@MultipartConfig(
    fileSizeThreshold = 1024 * 1024, // 1MB
    maxFileSize = 5 * 1024 * 1024, // 5MB
    maxRequestSize = 5 * 1024 * 1024 // 5MB
)
public class UploadLogoServlet extends HttpServlet {

    private static final String UPLOAD_DIR = "assets/images/";

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("application/json; charset=UTF-8");
        
        try {
            Part filePart = request.getPart("logo");
            
            if (filePart == null || filePart.getSize() == 0) {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng chọn file logo.\"}");
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
            String newFileName = "logo" + UUID.randomUUID().toString().substring(0, 8) + fileExtension;
            
            // Lấy đường dẫn thực tế của thư mục upload
            String appPath = request.getServletContext().getRealPath("");
            String uploadPath = appPath + UPLOAD_DIR;
            
            // Tạo thư mục nếu chưa tồn tại
            File uploadDir = new File(uploadPath);
            if (!uploadDir.exists()) {
                uploadDir.mkdirs();
            }
            
            // Xóa logo cũ nếu có
            File[] oldLogos = uploadDir.listFiles((dir, name) -> name.startsWith("logo"));
            if (oldLogos != null) {
                for (File oldLogo : oldLogos) {
                    oldLogo.delete();
                }
            }
            
            // Lưu file
            Path targetPath = Paths.get(uploadPath + newFileName);
            Files.copy(filePart.getInputStream(), targetPath, StandardCopyOption.REPLACE_EXISTING);
            
            // Trả về đường dẫn ảnh
            String imagePath = "/" + UPLOAD_DIR + newFileName;
            String imageUrl = request.getContextPath() + imagePath;
            
            response.setStatus(HttpServletResponse.SC_OK);
            response.getWriter().write("{\"success\": true, \"message\": \"Upload logo thành công.\", \"imagePath\": \"" + imagePath + "\", \"imageUrl\": \"" + imageUrl + "\"}");
            
        } catch (Exception e) {
            System.err.println("Lỗi khi upload logo: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi upload logo.\"}");
        }
    }
}



