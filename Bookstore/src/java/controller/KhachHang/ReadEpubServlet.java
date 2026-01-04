package controller.KhachHang;

import database.SanPhamDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.nio.charset.StandardCharsets;
import java.util.Enumeration;
import java.util.zip.ZipEntry;
import java.util.zip.ZipFile;
import model.KhachHang;
import model.SanPham;
// Jsoup không bắt buộc, sẽ dùng regex đơn giản để extract text từ HTML

/**
 * Servlet đọc nội dung từ file EPUB
 */
@WebServlet(name = "ReadEpubServlet", urlPatterns = {"/read-epub"})
public class ReadEpubServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("application/json; charset=UTF-8");
        
        HttpSession session = request.getSession(false);
        
        // Kiểm tra đăng nhập
        if (session == null || session.getAttribute("user") == null) {
            response.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
            response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng đăng nhập.\"}");
            return;
        }

        KhachHang user = (KhachHang) session.getAttribute("user");
        String productId = request.getParameter("id");

        if (productId == null || productId.isEmpty()) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"success\": false, \"message\": \"Mã sách không hợp lệ.\"}");
            return;
        }

        // Kiểm tra xem user có gói cước còn hiệu lực không (tài khoản pro)
        if (!user.isGoiCuocConHan()) {
            response.setStatus(HttpServletResponse.SC_FORBIDDEN);
            response.getWriter().write("{\"success\": false, \"message\": \"Bạn cần đăng ký gói cước để đọc sách. Vui lòng đăng ký gói cước trước.\"}");
            return;
        }

        // Lấy thông tin sách
        SanPhamDAO sanPhamDAO = new SanPhamDAO();
        SanPham tempSanPham = new SanPham();
        tempSanPham.setMaSanPham(productId);
        SanPham sach = sanPhamDAO.selectById(tempSanPham);

        if (sach == null) {
            response.setStatus(HttpServletResponse.SC_NOT_FOUND);
            response.getWriter().write("{\"success\": false, \"message\": \"Không tìm thấy sách.\"}");
            return;
        }
        
        if (sach.getFileEpub() == null || sach.getFileEpub().trim().isEmpty()) {
            response.setStatus(HttpServletResponse.SC_NOT_FOUND);
            response.getWriter().write("{\"success\": false, \"message\": \"Sách này chưa có file nội dung. Vui lòng liên hệ admin.\"}");
            return;
        }

        try {
            // Đọc nội dung từ file EPUB
            String content = readEpubContent(request, sach.getFileEpub());
            
            response.setStatus(HttpServletResponse.SC_OK);
            response.getWriter().write("{\"success\": true, \"content\": " + escapeJson(content) + "}");
            
        } catch (Exception e) {
            System.err.println("Lỗi khi đọc file: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi đọc file: " + escapeJson(e.getMessage()) + "\"}");
        }
    }
    
    /**
     * Đọc nội dung từ file EPUB hoặc TXT
     */
    private String readEpubContent(HttpServletRequest request, String fileName) throws IOException {
        if (fileName == null || fileName.trim().isEmpty()) {
            throw new IOException("Tên file không hợp lệ.");
        }
        
        String appPath = request.getServletContext().getRealPath("");
        if (appPath == null) {
            appPath = "";
        }
        String filePath = appPath + "assets/epub/" + fileName;
        
        File file = new File(filePath);
        if (!file.exists()) {
            // Log để debug
            System.err.println("File không tồn tại. Đường dẫn: " + filePath);
            System.err.println("appPath: " + appPath);
            System.err.println("fileName: " + fileName);
            throw new IOException("File không tồn tại: " + fileName + ". Vui lòng kiểm tra lại file đã được upload chưa.");
        }
        
        // Kiểm tra loại file
        String lowerFileName = fileName.toLowerCase();
        if (lowerFileName.endsWith(".txt")) {
            // Đọc file TXT
            return readTxtFile(file);
        } else if (lowerFileName.endsWith(".epub")) {
            // Đọc file EPUB
            return readEpubFile(file);
        } else {
            throw new IOException("Định dạng file không được hỗ trợ: " + fileName);
        }
    }
    
    /**
     * Đọc nội dung từ file TXT
     */
    private String readTxtFile(File txtFile) throws IOException {
        StringBuilder content = new StringBuilder();
        
        try (java.io.FileInputStream fis = new java.io.FileInputStream(txtFile);
             BufferedReader reader = new BufferedReader(
                new InputStreamReader(fis, StandardCharsets.UTF_8))) {
            
            String line;
            while ((line = reader.readLine()) != null) {
                content.append(line).append("\n");
            }
        }
        
        String result = content.toString().trim();
        return result.isEmpty() ? "Nội dung sách đang được cập nhật..." : result;
    }
    
    /**
     * Đọc nội dung từ file EPUB
     */
    private String readEpubFile(File epubFile) throws IOException {
        StringBuilder content = new StringBuilder();
        
        try (ZipFile zipFile = new ZipFile(epubFile, StandardCharsets.UTF_8)) {
            // Tìm file content.opf để xác định thứ tự các file HTML
            String opfPath = findOpfPath(zipFile);
            if (opfPath == null) {
                throw new IOException("Không tìm thấy file content.opf trong EPUB");
            }
            
            // Đọc manifest và spine từ OPF
            Enumeration<? extends ZipEntry> entries = zipFile.entries();
            while (entries.hasMoreElements()) {
                ZipEntry entry = entries.nextElement();
                String entryName = entry.getName();
                
                // Chỉ đọc các file HTML/XHTML trong thư mục OEBPS hoặc root
                if (entryName.endsWith(".html") || entryName.endsWith(".xhtml") || entryName.endsWith(".htm")) {
                    if (!entry.isDirectory()) {
                        try (InputStream is = zipFile.getInputStream(entry);
                             BufferedReader reader = new BufferedReader(
                                 new InputStreamReader(is, StandardCharsets.UTF_8))) {
                            
                            StringBuilder chapterContent = new StringBuilder();
                            String line;
                            while ((line = reader.readLine()) != null) {
                                chapterContent.append(line).append("\n");
                            }
                            
                            // Parse HTML và lấy text (không dùng Jsoup)
                            String htmlContent = chapterContent.toString();
                            // Loại bỏ các thẻ script và style
                            htmlContent = htmlContent.replaceAll("(?i)<script[^>]*>.*?</script>", "");
                            htmlContent = htmlContent.replaceAll("(?i)<style[^>]*>.*?</style>", "");
                            // Lấy text từ body (nếu có) hoặc toàn bộ
                            if (htmlContent != null && !htmlContent.trim().isEmpty()) {
                                String bodyText = extractTextFromHtml(htmlContent);
                                if (bodyText != null && !bodyText.trim().isEmpty()) {
                                    content.append(bodyText).append("\n\n");
                                }
                            }
                        }
                    }
                }
            }
        }
        
        String result = content.toString().trim();
        return result.isEmpty() ? "Nội dung sách đang được cập nhật..." : result;
    }
    
    /**
     * Tìm đường dẫn file OPF trong EPUB
     */
    private String findOpfPath(ZipFile zipFile) {
        Enumeration<? extends ZipEntry> entries = zipFile.entries();
        while (entries.hasMoreElements()) {
            ZipEntry entry = entries.nextElement();
            String name = entry.getName();
            if (name.endsWith(".opf") && name.contains("content")) {
                return name;
            }
        }
        return null;
    }
    
    /**
     * Extract text from HTML (simple regex-based approach)
     */
    private String extractTextFromHtml(String html) {
        if (html == null) return "";
        
        // Loại bỏ tất cả các thẻ HTML
        String text = html.replaceAll("<[^>]+>", " ");
        // Loại bỏ các ký tự đặc biệt HTML entities
        text = text.replace("&nbsp;", " ")
                   .replace("&amp;", "&")
                   .replace("&lt;", "<")
                   .replace("&gt;", ">")
                   .replace("&quot;", "\"")
                   .replace("&#39;", "'");
        // Loại bỏ các khoảng trắng thừa
        text = text.replaceAll("\\s+", " ").trim();
        
        return text;
    }
    
    /**
     * Escape JSON string
     */
    private String escapeJson(String str) {
        if (str == null) return "null";
        return "\"" + str.replace("\\", "\\\\")
                        .replace("\"", "\\\"")
                        .replace("\n", "\\n")
                        .replace("\r", "\\r")
                        .replace("\t", "\\t") + "\"";
    }
}

