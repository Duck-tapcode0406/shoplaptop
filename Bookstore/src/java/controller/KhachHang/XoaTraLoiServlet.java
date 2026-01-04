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

/**
 * Servlet xử lý xóa trả lời đánh giá
 */
@WebServlet(name = "XoaTraLoiServlet", urlPatterns = {"/xoa-tra-loi"})
public class XoaTraLoiServlet extends HttpServlet {

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
        
        String replyId = request.getParameter("replyId");
        
        if (replyId == null || replyId.trim().isEmpty()) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"success\": false, \"message\": \"Mã trả lời không hợp lệ.\"}");
            return;
        }
        
        try {
            TraLoiDanhGiaDAO traLoiDAO = new TraLoiDanhGiaDAO();
            
            // Xóa trả lời (chỉ chủ sở hữu mới được xóa)
            int result = traLoiDAO.delete(replyId, user.getMaKhachHang());
            
            if (result > 0) {
                response.setStatus(HttpServletResponse.SC_OK);
                response.getWriter().write("{\"success\": true, \"message\": \"Đã xóa trả lời thành công.\"}");
            } else {
                response.setStatus(HttpServletResponse.SC_FORBIDDEN);
                response.getWriter().write("{\"success\": false, \"message\": \"Bạn không có quyền xóa trả lời này hoặc trả lời không tồn tại.\"}");
            }
            
        } catch (Exception e) {
            System.err.println("Lỗi khi xóa trả lời: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi xóa trả lời.\"}");
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doPost(request, response);
    }
}
















