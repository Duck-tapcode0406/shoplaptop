package controller.KhachHang;

import database.DanhGiaDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import model.DanhGia;
import model.KhachHang;

/**
 * Servlet xử lý xóa đánh giá
 * Chỉ cho phép chủ sở hữu đánh giá xóa đánh giá của mình
 */
@WebServlet(name = "XoaDanhGiaServlet", urlPatterns = {"/xoa-danh-gia"})
public class XoaDanhGiaServlet extends HttpServlet {

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
        String productId = request.getParameter("productId");
        
        if (reviewId == null || reviewId.trim().isEmpty()) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"success\": false, \"message\": \"Mã đánh giá không hợp lệ.\"}");
            return;
        }
        
        try {
            DanhGiaDAO danhGiaDAO = new DanhGiaDAO();
            
            // Lấy thông tin đánh giá để kiểm tra quyền
            DanhGia danhGia = new DanhGia();
            danhGia.setMaDanhGia(reviewId);
            danhGia = danhGiaDAO.selectById(danhGia);
            
            if (danhGia == null) {
                response.setStatus(HttpServletResponse.SC_NOT_FOUND);
                response.getWriter().write("{\"success\": false, \"message\": \"Không tìm thấy đánh giá.\"}");
                return;
            }
            
            // Kiểm tra quyền: chỉ chủ sở hữu mới được xóa
            if (!danhGia.getKhachHang().getMaKhachHang().equals(user.getMaKhachHang())) {
                response.setStatus(HttpServletResponse.SC_FORBIDDEN);
                response.getWriter().write("{\"success\": false, \"message\": \"Bạn không có quyền xóa đánh giá này.\"}");
                return;
            }
            
            // Xóa đánh giá
            int result = danhGiaDAO.delete(danhGia);
            
            if (result > 0) {
                // Xóa thành công
                response.setStatus(HttpServletResponse.SC_OK);
                response.getWriter().write("{\"success\": true, \"message\": \"Đã xóa đánh giá thành công.\"}");
            } else {
                response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
                response.getWriter().write("{\"success\": false, \"message\": \"Không thể xóa đánh giá. Vui lòng thử lại.\"}");
            }
            
        } catch (Exception e) {
            System.err.println("Lỗi khi xóa đánh giá: " + e.getMessage());
            e.printStackTrace();
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            response.getWriter().write("{\"success\": false, \"message\": \"Đã xảy ra lỗi khi xóa đánh giá.\"}");
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        // Redirect về POST
        doPost(request, response);
    }
}
















