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

@WebServlet(name = "OrderApproveServlet", urlPatterns = {"/admin/orders/approve"})
public class OrderApproveServlet extends HttpServlet {

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
            if (!"Chờ duyệt".equals(donHang.getTrangThai())) {
                request.getSession().setAttribute("errorMessage", "Chỉ có thể duyệt đơn hàng đang ở trạng thái 'Chờ duyệt'.");
                response.sendRedirect(request.getContextPath() + "/admin/orders");
                return;
            }

            // Lấy chi tiết đơn hàng để kiểm tra tồn kho
            ChiTietDonHangDAO chiTietDAO = new ChiTietDonHangDAO();
            ArrayList<ChiTietDonHang> chiTiet = chiTietDAO.selectAllByOrderId(maDonHang);

            // Kiểm tra tồn kho cho từng sản phẩm
            SanPhamDAO sanPhamDAO = new SanPhamDAO();
            ArrayList<String> outOfStockProducts = new ArrayList<>();
            
            for (ChiTietDonHang ctdh : chiTiet) {
                String maSanPham = ctdh.getSanPham().getMaSanPham();
                SanPham sp = sanPhamDAO.selectById(maSanPham);
                
                if (sp == null) {
                    outOfStockProducts.add(maSanPham + " (Không tồn tại)");
                } else if (sp.getSoLuong() < ctdh.getSoLuong()) {
                    outOfStockProducts.add(sp.getTenSanPham() + " (Còn: " + sp.getSoLuong() + ", cần: " + (int)ctdh.getSoLuong() + ")");
                }
            }

            // Nếu có sản phẩm không đủ tồn kho
            if (!outOfStockProducts.isEmpty()) {
                String errorMsg = "Không thể duyệt đơn hàng. Sản phẩm không đủ tồn kho: " + 
                                 String.join(", ", outOfStockProducts);
                request.getSession().setAttribute("errorMessage", errorMsg);
                response.sendRedirect(request.getContextPath() + "/admin/orders");
                return;
            }

            // Duyệt đơn hàng: cập nhật trạng thái và giảm số lượng tồn kho
            Connection con = null;
            try {
                con = database.JDBCUtil.getConnection();
                con.setAutoCommit(false);

                // 1. Giảm số lượng tồn kho cho từng sản phẩm (kiểm tra lại trong transaction)
                for (ChiTietDonHang ctdh : chiTiet) {
                    String maSanPham = ctdh.getSanPham().getMaSanPham();
                    int soLuongMua = (int) ctdh.getSoLuong();
                    
                    // Kiểm tra lại tồn kho trong transaction
                    SanPham sp = sanPhamDAO.selectById(maSanPham);
                    if (sp == null) {
                        throw new SQLException("Sản phẩm không tồn tại: " + maSanPham);
                    }
                    if (sp.getSoLuong() < soLuongMua) {
                        throw new SQLException("Sản phẩm '" + sp.getTenSanPham() + "' không đủ tồn kho. Còn: " + sp.getSoLuong() + ", cần: " + soLuongMua);
                    }
                    
                    // Giảm số lượng
                    if (!sanPhamDAO.decreaseQuantity(maSanPham, soLuongMua, con)) {
                        throw new SQLException("Không thể giảm số lượng cho sản phẩm: " + maSanPham);
                    }
                }

                // 2. Cập nhật trạng thái đơn hàng
                donHang.setTrangThai("Đang giao");
                donHangDAO.update(donHang);

                // 3. Cập nhật ghi chú nếu có
                if (ghiChu != null && !ghiChu.trim().isEmpty()) {
                    updateOrderNote(maDonHang, ghiChu, con);
                }

                // Commit transaction
                con.commit();
                request.getSession().setAttribute("successMessage", "Đã duyệt đơn hàng thành công. Đã cập nhật số lượng tồn kho.");

            } catch (SQLException e) {
                if (con != null) {
                    try {
                        con.rollback();
                    } catch (SQLException rollbackEx) {
                        System.err.println("Lỗi khi rollback: " + rollbackEx.getMessage());
                    }
                }
                System.err.println("Lỗi khi duyệt đơn hàng: " + e.getMessage());
                e.printStackTrace();
                request.getSession().setAttribute("errorMessage", "Lỗi khi duyệt đơn hàng: " + e.getMessage());
            } catch (Exception e) {
                if (con != null) {
                    try {
                        con.rollback();
                    } catch (SQLException rollbackEx) {
                        System.err.println("Lỗi khi rollback: " + rollbackEx.getMessage());
                    }
                }
                System.err.println("Lỗi không xác định khi duyệt đơn hàng: " + e.getMessage());
                e.printStackTrace();
                request.getSession().setAttribute("errorMessage", "Lỗi hệ thống khi duyệt đơn hàng: " + e.getMessage());
            } finally {
                if (con != null) {
                    try {
                        con.setAutoCommit(true);
                    } catch (SQLException e) {
                        System.err.println("Lỗi khi setAutoCommit: " + e.getMessage());
                    }
                    database.JDBCUtil.closeConnection(con);
                }
            }

        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi duyệt đơn hàng: " + e.getMessage());
            e.printStackTrace();
            request.getSession().setAttribute("errorMessage", "Lỗi hệ thống khi duyệt đơn hàng.");
        }

        response.sendRedirect(request.getContextPath() + "/admin/orders");
    }

    private void updateOrderNote(String maDonHang, String ghiChu, Connection con) throws SQLException {
        String sql = "UPDATE `donhang` SET `ghiChu` = ? WHERE `maDonHang` = ?";
        try (java.sql.PreparedStatement st = con.prepareStatement(sql)) {
            st.setString(1, ghiChu);
            st.setString(2, maDonHang);
            st.executeUpdate();
        }
    }
}


