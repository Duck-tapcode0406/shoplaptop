package controller.Admin;

import database.*;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.*;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;

@WebServlet(name = "OrderCancelServlet", urlPatterns = {"/admin/orders/cancel", "/admin/orders/reject"})
public class OrderCancelServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");

        String maDonHang = request.getParameter("maDonHang");
        String ghiChu = request.getParameter("ghiChu");

        if (maDonHang == null || maDonHang.isEmpty()) {
            request.getSession().setAttribute("errorMessage", "Mã đơn hàng không hợp lệ.");
            response.sendRedirect(request.getContextPath() + "/admin/orders");
            return;
        }

        try {
            DonHangDAO donHangDAO = new DonHangDAO();
            DonHang donHang = donHangDAO.selectById(maDonHang);

            if (donHang == null) {
                request.getSession().setAttribute("errorMessage", "Không tìm thấy đơn hàng.");
                response.sendRedirect(request.getContextPath() + "/admin/orders");
                return;
            }

            // Kiểm tra trạng thái đơn hàng
            if ("Hủy".equals(donHang.getTrangThai())) {
                request.getSession().setAttribute("errorMessage", "Đơn hàng này đã bị hủy.");
                response.sendRedirect(request.getContextPath() + "/admin/orders");
                return;
            }
            
            if ("Hoàn tất".equals(donHang.getTrangThai())) {
                request.getSession().setAttribute("errorMessage", "Không thể hủy đơn hàng đã hoàn tất.");
                response.sendRedirect(request.getContextPath() + "/admin/orders");
                return;
            }

            // Nếu đơn hàng đã được duyệt (Đang giao), cần hoàn lại số lượng tồn kho
            if ("Đang giao".equals(donHang.getTrangThai())) {
                ChiTietDonHangDAO chiTietDAO = new ChiTietDonHangDAO();
                ArrayList<ChiTietDonHang> chiTiet = chiTietDAO.selectAllByOrderId(maDonHang);
                SanPhamDAO sanPhamDAO = new SanPhamDAO();

                Connection con = database.JDBCUtil.getConnection();
                try {
                    con.setAutoCommit(false);

                    // Hoàn lại số lượng tồn kho
                    for (ChiTietDonHang ctdh : chiTiet) {
                        String maSanPham = ctdh.getSanPham().getMaSanPham();
                        int soLuong = (int) ctdh.getSoLuong();
                        
                        // Tăng số lượng tồn kho
                        String sql = "UPDATE `sanpham` SET `soLuong` = `soLuong` + ? WHERE `maSanPham` = ?";
                        try (java.sql.PreparedStatement st = con.prepareStatement(sql)) {
                            st.setInt(1, soLuong);
                            st.setString(2, maSanPham);
                            st.executeUpdate();
                        }
                    }

                    // Cập nhật trạng thái đơn hàng
                    donHang.setTrangThai("Hủy");
                    donHangDAO.update(donHang);

                    // Cập nhật ghi chú nếu có
                    if (ghiChu != null && !ghiChu.trim().isEmpty()) {
                        updateOrderNote(maDonHang, ghiChu, con);
                    }

                    con.commit();
                    request.getSession().setAttribute("successMessage", "Đã hủy đơn hàng thành công. Đã hoàn lại số lượng tồn kho.");

                } catch (SQLException e) {
                    con.rollback();
                    System.err.println("Lỗi khi hủy đơn hàng: " + e.getMessage());
                    e.printStackTrace();
                    request.getSession().setAttribute("errorMessage", "Lỗi khi hủy đơn hàng: " + e.getMessage());
                } finally {
                    con.setAutoCommit(true);
                    database.JDBCUtil.closeConnection(con);
                }
            } else {
                // Đơn hàng chưa được duyệt, chỉ cần cập nhật trạng thái
                donHang.setTrangThai("Hủy");
                donHangDAO.update(donHang);
                
                if (ghiChu != null && !ghiChu.trim().isEmpty()) {
                    updateOrderNote(maDonHang, ghiChu, null);
                }
                
                request.getSession().setAttribute("successMessage", "Đã hủy đơn hàng thành công.");
            }

        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi hủy đơn hàng: " + e.getMessage());
            e.printStackTrace();
            request.getSession().setAttribute("errorMessage", "Lỗi hệ thống khi hủy đơn hàng.");
        }

        response.sendRedirect(request.getContextPath() + "/admin/orders");
    }

    private void updateOrderNote(String maDonHang, String ghiChu, Connection con) throws SQLException {
        String sql = "UPDATE `donhang` SET `ghiChu` = ? WHERE `maDonHang` = ?";
        boolean useExternalConnection = (con != null);
        Connection connection = con;
        
        try {
            if (!useExternalConnection) {
                connection = database.JDBCUtil.getConnection();
            }
            
            try (java.sql.PreparedStatement st = connection.prepareStatement(sql)) {
                st.setString(1, ghiChu);
                st.setString(2, maDonHang);
                st.executeUpdate();
            }
        } finally {
            if (!useExternalConnection && connection != null) {
                database.JDBCUtil.closeConnection(connection);
            }
        }
    }
}


