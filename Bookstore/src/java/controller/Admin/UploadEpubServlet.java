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

/**
 * Servlet xử lý upload file EPUB và TXT
 */
@WebServlet(name = "UploadEpubServlet", urlPatterns = {"/admin/upload-epub"})
@MultipartConfig(
    fileSizeThreshold = 1024 * 1024, // 1MB
    maxFileSize = 50 * 1024 * 1024, // 50MB
    maxRequestSize = 60 * 1024 * 1024 // 60MB
)
public class UploadEpubServlet extends HttpServlet {

    private static final String UPLOAD_DIR = "assets/epub/";

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("application/json; charset=UTF-8");
        
        try {
            Part filePart = request.getPart("epubFile");
            
            if (filePart == null || filePart.getSize() == 0) {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng chọn file EPUB hoặc TXT.\"}");
                return;
            }
            
            // Lấy tên file gốc
            String fileName = filePart.getSubmittedFileName();
            if (fileName == null || fileName.isEmpty()) {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"Tên file không hợp lệ.\"}");
                return;
            }
            
            // Kiểm tra định dạng file (chấp nhận EPUB và TXT)
            String lowerFileName = fileName.toLowerCase();
            String fileExtension;
            if (lowerFileName.endsWith(".epub")) {
                fileExtension = ".epub";
            } else if (lowerFileName.endsWith(".txt")) {
                fileExtension = ".txt";
            } else {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"File phải là định dạng EPUB hoặc TXT.\"}");
                return;
            }
            
            // Tạo tên file mới để tránh trùng lặp
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
            
            // Trả về chỉ tên file (không có đường dẫn) để lưu vào database
            String filePath = newFileName; // Chỉ trả về tên file
            String fileUrl = request.getContextPath() + "/" + UPLOAD_DIR + newFileName; // URL đầy đủ
            
            response.setStatus(HttpServletResponse.SC_OK);
            String fileType = fileExtension.equals(".epub") ? "EPUB" : "TXT";
            response.getWriter().write("{\"success\": true, \"message\": \"Upload file " + fileType + " thành công.\", \"filePath\": \"" + filePath + "\", \"fileUrl\": \"" + fileUrl + "\"}");
            
        } catch (Exception e) {
            System.err.println("Lỗi khi upload file: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi upload file.\"}");
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doPost(request, response);
    }
}

