package controller.Admin;

import database.*;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.*;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

@WebServlet(name = "OrderDetailServlet", urlPatterns = {"/admin/orders/detail"})
public class OrderDetailServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String maDonHang = request.getParameter("id");
        
        if (maDonHang == null || maDonHang.isEmpty()) {
            response.sendRedirect(request.getContextPath() + "/admin/orders");
            return;
        }

        DonHangDAO donHangDAO = new DonHangDAO();
        DonHang donHang = donHangDAO.selectById(maDonHang);

        if (donHang == null) {
            response.sendRedirect(request.getContextPath() + "/admin/orders");
            return;
        }

        // Get order details - sử dụng method có sẵn trong DAO
        ChiTietDonHangDAO chiTietDAO = new ChiTietDonHangDAO();
        ArrayList<ChiTietDonHang> chiTiet = chiTietDAO.selectAllByOrderId(maDonHang);

        // Lấy thông tin số lượng tồn kho cho từng sản phẩm (để hiển thị trong UI)
        SanPhamDAO sanPhamDAO = new SanPhamDAO();
        java.util.HashMap<String, Integer> stockInfo = new java.util.HashMap<>();
        for (ChiTietDonHang ctdh : chiTiet) {
            String maSanPham = ctdh.getSanPham().getMaSanPham();
            SanPham sp = sanPhamDAO.selectById(maSanPham);
            if (sp != null) {
                stockInfo.put(maSanPham, sp.getSoLuong());
            }
        }

        request.setAttribute("donHang", donHang);
        request.setAttribute("chiTiet", chiTiet);
        request.setAttribute("stockInfo", stockInfo);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/order-detail.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String maDonHang = request.getParameter("maDonHang");
        String trangThai = request.getParameter("trangThai");
        String ghiChu = request.getParameter("ghiChu");

        if (maDonHang != null && trangThai != null) {
            DonHangDAO donHangDAO = new DonHangDAO();
            DonHang donHang = donHangDAO.selectById(maDonHang);

            if (donHang != null) {
                donHang.setTrangThai(trangThai);
                updateOrderNote(maDonHang, ghiChu);
                donHangDAO.update(donHang);
                request.getSession().setAttribute("successMessage", "Cập nhật trạng thái đơn hàng thành công.");
            }
        }

        response.sendRedirect(request.getContextPath() + "/admin/orders/detail?id=" + maDonHang);
    }


    private void updateOrderNote(String maDonHang, String ghiChu) {
        String sql = "UPDATE `donhang` SET `ghiChu` = ? WHERE `maDonHang` = ?";
        try (Connection con = database.JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            st.setString(1, ghiChu);
            st.setString(2, maDonHang);
            st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}




