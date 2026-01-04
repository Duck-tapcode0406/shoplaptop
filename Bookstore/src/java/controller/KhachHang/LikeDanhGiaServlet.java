package controller.KhachHang;

import database.DanhGiaLikeDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import model.KhachHang;

/**
 * Servlet xử lý like/dislike đánh giá
 */
@WebServlet(name = "LikeDanhGiaServlet", urlPatterns = {"/like-danh-gia", "/dislike-danh-gia"})
public class LikeDanhGiaServlet extends HttpServlet {

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
        String action = request.getParameter("action"); // "like" hoặc "dislike"
        
        if (reviewId == null || reviewId.trim().isEmpty()) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"success\": false, \"message\": \"Mã đánh giá không hợp lệ.\"}");
            return;
        }
        
        // Xác định action từ URL path
        String servletPath = request.getServletPath();
        if (action == null || action.isEmpty()) {
            if (servletPath.contains("like")) {
                action = "like";
            } else if (servletPath.contains("dislike")) {
                action = "dislike";
            } else {
                action = request.getParameter("action");
            }
        }
        
        try {
            DanhGiaLikeDAO likeDAO = new DanhGiaLikeDAO();
            String maKhachHang = user.getMaKhachHang();
            
            if ("like".equals(action)) {
                // Toggle like
                boolean hasLiked = likeDAO.hasLiked(reviewId, maKhachHang);
                boolean hasDisliked = likeDAO.hasDisliked(reviewId, maKhachHang);
                
                if (hasLiked) {
                    // Đã like rồi -> bỏ like
                    likeDAO.deleteLike(reviewId, maKhachHang);
                } else {
                    // Chưa like -> thêm like
                    likeDAO.insertLike(reviewId, maKhachHang);
                    // Nếu đã dislike thì xóa dislike
                    if (hasDisliked) {
                        likeDAO.deleteDislike(reviewId, maKhachHang);
                    }
                }
                
                int likeCount = likeDAO.countLikes(reviewId);
                int dislikeCount = likeDAO.countDislikes(reviewId);
                
                response.setStatus(HttpServletResponse.SC_OK);
                response.getWriter().write("{\"success\": true, \"liked\": " + !hasLiked + ", \"likeCount\": " + likeCount + ", \"dislikeCount\": " + dislikeCount + "}");
                
            } else if ("dislike".equals(action)) {
                // Toggle dislike
                boolean hasDisliked = likeDAO.hasDisliked(reviewId, maKhachHang);
                boolean hasLiked = likeDAO.hasLiked(reviewId, maKhachHang);
                
                if (hasDisliked) {
                    // Đã dislike rồi -> bỏ dislike
                    likeDAO.deleteDislike(reviewId, maKhachHang);
                } else {
                    // Chưa dislike -> thêm dislike
                    likeDAO.insertDislike(reviewId, maKhachHang);
                    // Nếu đã like thì xóa like
                    if (hasLiked) {
                        likeDAO.deleteLike(reviewId, maKhachHang);
                    }
                }
                
                int likeCount = likeDAO.countLikes(reviewId);
                int dislikeCount = likeDAO.countDislikes(reviewId);
                
                response.setStatus(HttpServletResponse.SC_OK);
                response.getWriter().write("{\"success\": true, \"disliked\": " + !hasDisliked + ", \"likeCount\": " + likeCount + ", \"dislikeCount\": " + dislikeCount + "}");
                
            } else {
                response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                response.getWriter().write("{\"success\": false, \"message\": \"Action không hợp lệ.\"}");
            }
            
        } catch (java.sql.SQLException e) {
            System.err.println("=== LỖI SQL KHI LIKE/DISLIKE ===");
            System.err.println("Review ID: " + reviewId);
            System.err.println("Action: " + action);
            System.err.println("Error Message: " + e.getMessage());
            System.err.println("SQL State: " + e.getSQLState());
            System.err.println("Error Code: " + e.getErrorCode());
            e.printStackTrace();
            
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            String errorMessage = "Lỗi database: " + e.getMessage();
            // Nếu là lỗi SQL về table không tồn tại, thông báo rõ ràng hơn
            String errorMsg = e.getMessage() != null ? e.getMessage().toLowerCase() : "";
            if (errorMsg.contains("table") && (errorMsg.contains("doesn't exist") || errorMsg.contains("does not exist") || errorMsg.contains("unknown table"))) {
                errorMessage = "Bảng database chưa được tạo. Vui lòng chạy script database_schema_reviews.sql trong phpMyAdmin để tạo các bảng: danhgialike và danhgiadislike";
            }
            try {
                response.getWriter().write("{\"success\": false, \"message\": \"" + errorMessage.replace("\"", "\\\"").replace("\n", " ").replace("\r", " ") + "\"}");
            } catch (IOException ioException) {
                System.err.println("Lỗi khi ghi response: " + ioException.getMessage());
            }
        } catch (Exception e) {
            System.err.println("=== LỖI KHÔNG XÁC ĐỊNH KHI LIKE/DISLIKE ===");
            System.err.println("Review ID: " + reviewId);
            System.err.println("Action: " + action);
            System.err.println("Error Message: " + e.getMessage());
            System.err.println("Error Class: " + e.getClass().getName());
            e.printStackTrace();
            
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            String errorMessage = "Đã xảy ra lỗi: " + (e.getMessage() != null ? e.getMessage() : "Lỗi không xác định");
            try {
                response.getWriter().write("{\"success\": false, \"message\": \"" + errorMessage.replace("\"", "\\\"").replace("\n", " ").replace("\r", " ") + "\"}");
            } catch (IOException ioException) {
                System.err.println("Lỗi khi ghi response: " + ioException.getMessage());
            }
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doPost(request, response);
    }
}

