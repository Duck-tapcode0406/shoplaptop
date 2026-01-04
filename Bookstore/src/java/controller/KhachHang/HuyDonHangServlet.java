/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.DonHangDAO;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.util.ArrayList;
import model.ChiTietDonHang;
import model.DonHang;
import model.KhachHang;

/**
 *
 * @author Acer
 */
@WebServlet(name = "HuyDonHangServlet", urlPatterns = {"/tai-khoan/huy-don-hang"})
public class HuyDonHangServlet extends HttpServlet {

    /**
     * Processes requests for both HTTP <code>GET</code> and <code>POST</code>
     * methods.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    protected void processRequest(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        try (PrintWriter out = response.getWriter()) {
            /* TODO output your page here. You may use following sample code. */
            out.println("<!DOCTYPE html>");
            out.println("<html>");
            out.println("<head>");
            out.println("<title>Servlet HuyDonHangServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet HuyDonHangServlet at " + request.getContextPath() + "</h1>");
            out.println("</body>");
            out.println("</html>");
        }
    }

    // <editor-fold defaultstate="collapsed" desc="HttpServlet methods. Click on the + sign on the left to edit the code.">
    /**
     * Handles the HTTP <code>GET</code> method.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        // Không cho phép GET
        response.sendRedirect(request.getContextPath() + "/tai-khoan/lich-su-don-hang");
    }

    /**
     * Handles the HTTP <code>POST</code> method.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");
        String orderId = request.getParameter("orderId");
        String urlRedirect = request.getContextPath() + "/tai-khoan/chi-tiet-don-hang?id=" + orderId; // Về trang chi tiết

        String error = null;
        String success = null;

        // --- BẢO MẬT: Kiểm tra đăng nhập ---
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap?redirect=" + urlRedirect);
            return;
        }

        // --- Validate ---
        if (orderId == null || orderId.trim().isEmpty()) {
            error = "Mã đơn hàng không hợp lệ.";
            urlRedirect = request.getContextPath() + "/tai-khoan/lich-su-don-hang"; // Về trang lịch sử nếu ko có ID
        } else {
            try {
                DonHangDAO donHangDAO = new DonHangDAO();
                DonHang donHang = donHangDAO.selectById(orderId);

                // --- BẢO MẬT: Kiểm tra đơn hàng tồn tại và thuộc về user ---
                if (donHang == null || !donHang.getKhachHang().getMaKhachHang().equals(user.getMaKhachHang())) {
                    error = "Đơn hàng không tồn tại hoặc bạn không có quyền hủy đơn hàng này.";
                     urlRedirect = request.getContextPath() + "/tai-khoan/lich-su-don-hang";
                }
                // --- Kiểm tra trạng thái có cho phép hủy không ---
                else if (!"Chờ duyệt".equals(donHang.getTrangThai())) {
                    error = "Chỉ có thể hủy đơn hàng ở trạng thái 'Chờ duyệt'. Đơn hàng hiện tại: '" + donHang.getTrangThai() + "'.";
                } else {
                    // --- TIẾN HÀNH HỦY ---
                    try {
                        // Lấy chi tiết đơn hàng để hoàn lại số lượng tồn kho
                        database.ChiTietDonHangDAO ctdhDAO = new database.ChiTietDonHangDAO();
                        ArrayList<ChiTietDonHang> items = ctdhDAO.selectAllByOrderId(orderId);
                        
                        // Sử dụng transaction để đảm bảo tính nhất quán
                        java.sql.Connection con = database.JDBCUtil.getConnection();
                        try {
                            con.setAutoCommit(false);
                            
                            // 1. Cập nhật trạng thái đơn hàng thành "Hủy" (đồng bộ với admin)
                            int result = donHangDAO.updateStatus(orderId, "Hủy");
                            
                            if (result > 0) {
                                // 2. Hoàn lại số lượng tồn kho cho từng sản phẩm
                                database.SanPhamDAO spDAO = new database.SanPhamDAO();
                                for (ChiTietDonHang item : items) {
                                    String maSanPham = item.getSanPham().getMaSanPham();
                                    int soLuong = (int) item.getSoLuong();
                                    
                                    // Tăng số lượng tồn kho
                                    String sql = "UPDATE `sanpham` SET `soLuong` = `soLuong` + ? WHERE `maSanPham` = ?";
                                    try (java.sql.PreparedStatement st = con.prepareStatement(sql)) {
                                        st.setInt(1, soLuong);
                                        st.setString(2, maSanPham);
                                        st.executeUpdate();
                                    }
                                }
                                
                                // Commit transaction
                                con.commit();
                                success = "Đã hủy đơn hàng #" + orderId + " thành công. Số lượng sản phẩm đã được hoàn lại vào kho.";
                            } else {
                                con.rollback();
                                error = "Hủy đơn hàng thất bại do lỗi hệ thống.";
                            }
                            
                        } catch (java.sql.SQLException e) {
                            con.rollback();
                            System.err.println("Lỗi SQL khi hủy đơn hàng: " + e.getMessage());
                            e.printStackTrace();
                            error = "Lỗi khi hủy đơn hàng: " + e.getMessage();
                        } finally {
                            con.setAutoCommit(true);
                            database.JDBCUtil.closeConnection(con);
                        }
                        
                    } catch (Exception e) {
                        System.err.println("Lỗi không xác định khi hủy đơn hàng: " + e.getMessage());
                        e.printStackTrace();
                        error = "Lỗi hệ thống khi hủy đơn hàng.";
                    }
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG HuyDonHangServlet doPost: " + e.getMessage());
                e.printStackTrace();
                error = "Đã xảy ra lỗi không mong muốn khi xử lý yêu cầu hủy đơn hàng.";
            }
        }

        // --- Redirect về trang chi tiết (hoặc lịch sử) với thông báo ---
        if (error != null) {
            session.setAttribute("errorMessage", error);
        }
        if (success != null) {
            session.setAttribute("successMessage", success);
        }
        response.sendRedirect(urlRedirect);
    }

    /**
     * Returns a short description of the servlet.
     *
     * @return a String containing servlet description
     */
    @Override
    public String getServletInfo() {
        return "Short description";
    }// </editor-fold>

}
