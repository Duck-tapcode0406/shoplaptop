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
import java.util.Calendar;

@WebServlet(name = "AdminDashboardServlet", urlPatterns = {"/admin/dashboard"})
public class AdminDashboardServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        try {
            // Thống kê tổng quan
            DashboardStats stats = getDashboardStats();
            request.setAttribute("stats", stats);

            // Đơn hàng gần đây
            DonHangDAO donHangDAO = new DonHangDAO();
            ArrayList<DonHang> recentOrders = donHangDAO.selectAll();
            if (recentOrders.size() > 10) {
                recentOrders = new ArrayList<>(recentOrders.subList(0, 10));
            }
            request.setAttribute("recentOrders", recentOrders);

            // Sản phẩm sắp hết hàng
            SanPhamDAO sanPhamDAO = new SanPhamDAO();
            ArrayList<SanPham> lowStockProducts = getLowStockProducts();
            request.setAttribute("lowStockProducts", lowStockProducts);

            // Thống kê doanh thu 7 ngày gần đây
            ArrayList<DailyRevenue> dailyRevenues = getDailyRevenues(7);
            request.setAttribute("dailyRevenues", dailyRevenues);

            RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/dashboard.jsp");
            rd.forward(request, response);
        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("error", "Lỗi khi tải dashboard: " + e.getMessage());
            RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/dashboard.jsp");
            rd.forward(request, response);
        }
    }

    private DashboardStats getDashboardStats() throws SQLException {
        DashboardStats stats = new DashboardStats();
        Connection con = database.JDBCUtil.getConnection();

        try {
            // Tổng số đơn hàng
            String sql = "SELECT COUNT(*) as total FROM donhang";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setTotalOrders(rs.getInt("total"));
                }
            }

            // Tổng doanh thu (từ các đơn đã duyệt và đã thanh toán)
            sql = "SELECT COALESCE(SUM(soTienDaThanhToan), 0) as total " +
                  "FROM donhang " +
                  "WHERE (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                  "AND trangThaiThanhToan = 'Đã thanh toán'";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    double revenue = rs.getDouble("total");
                    stats.setTotalRevenue(rs.wasNull() ? 0.0 : revenue);
                } else {
                    stats.setTotalRevenue(0.0);
                }
            }

            // Tổng số khách hàng
            sql = "SELECT COUNT(*) as total FROM khachhang WHERE role = 0";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setTotalCustomers(rs.getInt("total"));
                }
            }

            // Tổng số sản phẩm
            sql = "SELECT COUNT(*) as total FROM sanpham";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setTotalProducts(rs.getInt("total"));
                }
            }

            // Đơn hàng chờ xử lý
            sql = "SELECT COUNT(*) as total FROM donhang WHERE trangThai = 'Chờ duyệt'";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setPendingOrders(rs.getInt("total"));
                }
            }

            // Doanh thu hôm nay (chỉ tính đơn đã duyệt và đã thanh toán)
            sql = "SELECT COALESCE(SUM(soTienDaThanhToan), 0) as total " +
                  "FROM donhang " +
                  "WHERE DATE(ngayDatHang) = CURDATE() " +
                  "AND (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                  "AND trangThaiThanhToan = 'Đã thanh toán'";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    double revenue = rs.getDouble("total");
                    stats.setTodayRevenue(rs.wasNull() ? 0.0 : revenue);
                } else {
                    stats.setTodayRevenue(0.0);
                }
            }

            // Doanh thu tháng này (chỉ tính đơn đã duyệt và đã thanh toán)
            sql = "SELECT COALESCE(SUM(soTienDaThanhToan), 0) as total " +
                  "FROM donhang " +
                  "WHERE MONTH(ngayDatHang) = MONTH(CURDATE()) " +
                  "AND YEAR(ngayDatHang) = YEAR(CURDATE()) " +
                  "AND (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                  "AND trangThaiThanhToan = 'Đã thanh toán'";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    double revenue = rs.getDouble("total");
                    stats.setMonthRevenue(rs.wasNull() ? 0.0 : revenue);
                } else {
                    stats.setMonthRevenue(0.0);
                }
            }

        } finally {
            database.JDBCUtil.closeConnection(con);
        }

        return stats;
    }

    private ArrayList<SanPham> getLowStockProducts() {
        ArrayList<SanPham> lowStock = new ArrayList<>();
        SanPhamDAO dao = new SanPhamDAO();
        ArrayList<SanPham> allProducts = dao.selectAll();
        
        for (SanPham sp : allProducts) {
            if (sp.getSoLuong() < 10 && sp.getSoLuong() > 0) {
                lowStock.add(sp);
            }
        }
        
        return lowStock;
    }

    private ArrayList<DailyRevenue> getDailyRevenues(int days) throws SQLException {
        ArrayList<DailyRevenue> revenues = new ArrayList<>();
        Connection con = database.JDBCUtil.getConnection();

        try {
            String sql = "SELECT DATE(ngayDatHang) as ngay, SUM(soTienDaThanhToan) as doanhThu " +
                        "FROM donhang " +
                        "WHERE (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                        "AND trangThaiThanhToan = 'Đã thanh toán' " +
                        "AND ngayDatHang >= DATE_SUB(CURDATE(), INTERVAL ? DAY) " +
                        "GROUP BY DATE(ngayDatHang) " +
                        "ORDER BY ngay DESC";
            
            try (PreparedStatement st = con.prepareStatement(sql)) {
                st.setInt(1, days);
                try (ResultSet rs = st.executeQuery()) {
                    while (rs.next()) {
                        DailyRevenue dr = new DailyRevenue();
                        dr.setDate(rs.getDate("ngay"));
                        dr.setRevenue(rs.getDouble("doanhThu"));
                        revenues.add(dr);
                    }
                }
            }
        } finally {
            database.JDBCUtil.closeConnection(con);
        }

        return revenues;
    }

    // Inner classes for statistics
    public static class DashboardStats {
        private int totalOrders;
        private double totalRevenue;
        private int totalCustomers;
        private int totalProducts;
        private int pendingOrders;
        private double todayRevenue;
        private double monthRevenue;

        // Getters and Setters
        public int getTotalOrders() { return totalOrders; }
        public void setTotalOrders(int totalOrders) { this.totalOrders = totalOrders; }
        public double getTotalRevenue() { return totalRevenue; }
        public void setTotalRevenue(double totalRevenue) { this.totalRevenue = totalRevenue; }
        public int getTotalCustomers() { return totalCustomers; }
        public void setTotalCustomers(int totalCustomers) { this.totalCustomers = totalCustomers; }
        public int getTotalProducts() { return totalProducts; }
        public void setTotalProducts(int totalProducts) { this.totalProducts = totalProducts; }
        public int getPendingOrders() { return pendingOrders; }
        public void setPendingOrders(int pendingOrders) { this.pendingOrders = pendingOrders; }
        public double getTodayRevenue() { return todayRevenue; }
        public void setTodayRevenue(double todayRevenue) { this.todayRevenue = todayRevenue; }
        public double getMonthRevenue() { return monthRevenue; }
        public void setMonthRevenue(double monthRevenue) { this.monthRevenue = monthRevenue; }
    }

    public static class DailyRevenue {
        private java.sql.Date date;
        private double revenue;

        public java.sql.Date getDate() { return date; }
        public void setDate(java.sql.Date date) { this.date = date; }
        public double getRevenue() { return revenue; }
        public void setRevenue(double revenue) { this.revenue = revenue; }
    }
}




