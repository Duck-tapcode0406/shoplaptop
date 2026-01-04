package controller.KhachHang;

import database.TraLoiDanhGiaDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import model.KhachHang;
import model.TraLoiDanhGia;
import util.DateUtil;
import util.MaGeneratorUtil;

/**
 * Servlet xử lý trả lời đánh giá
 */
@WebServlet(name = "TraLoiDanhGiaServlet", urlPatterns = {"/tra-loi-danh-gia"})
public class TraLoiDanhGiaServlet extends HttpServlet {

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
            response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng đăng nhập để thực hiện thao tác này.\"}");
            return;
        }
        
        String reviewId = request.getParameter("reviewId");
        String content = request.getParameter("content");
        
        if (reviewId == null || reviewId.trim().isEmpty() ||
            content == null || content.trim().isEmpty()) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"success\": false, \"message\": \"Vui lòng nhập đầy đủ thông tin.\"}");
            return;
        }
        
        if (content.trim().length() < 5) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"success\": false, \"message\": \"Nội dung trả lời phải có ít nhất 5 ký tự.\"}");
            return;
        }
        
        try {
            TraLoiDanhGiaDAO traLoiDAO = new TraLoiDanhGiaDAO();
            
            TraLoiDanhGia traLoi = new TraLoiDanhGia();
            traLoi.setMaTraLoi(MaGeneratorUtil.generateUUID());
            traLoi.setMaDanhGia(reviewId);
            traLoi.setKhachHang(user);
            traLoi.setNoiDung(content.trim());
            traLoi.setNgayTraLoi(DateUtil.getCurrentSqlTimestamp());
            
            int result = traLoiDAO.insert(traLoi);
            
            if (result > 0) {
                response.setStatus(HttpServletResponse.SC_OK);
                response.getWriter().write("{\"success\": true, \"message\": \"Đã gửi trả lời thành công.\", \"maTraLoi\": \"" + traLoi.getMaTraLoi() + "\"}");
            } else {
                response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
                response.getWriter().write("{\"success\": false, \"message\": \"Không thể gửi trả lời. Vui lòng thử lại.\"}");
            }
            
        } catch (Exception e) {
            System.err.println("Lỗi khi gửi trả lời: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi gửi trả lời.\"}");
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doPost(request, response);
    }
}
















