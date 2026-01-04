package controller.Admin;

import database.*;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

@WebServlet(name = "StatisticsServlet", urlPatterns = {"/admin/statistics"})
public class StatisticsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String period = request.getParameter("period");
        if (period == null || period.isEmpty()) {
            period = "month";
        }

        try {
            // Doanh thu theo ngày/tháng/năm (chỉ tính đơn đã duyệt và đã thanh toán)
            Map<String, Double> revenueByPeriod = getRevenueByPeriod(period);
            request.setAttribute("revenueByPeriod", revenueByPeriod);
            
            // Tổng doanh thu (tất cả thời gian)
            double totalRevenue = getTotalRevenue();
            request.setAttribute("totalRevenue", totalRevenue);

            // Đơn hàng theo trạng thái
            Map<String, Integer> ordersByStatus = getOrdersByStatus();
            request.setAttribute("ordersByStatus", ordersByStatus);

            // Sách bán chạy (chỉ tính đơn đã duyệt và đã thanh toán)
            ArrayList<BestSeller> bestSellers = getBestSellers(10);
            request.setAttribute("bestSellers", bestSellers);

            // Khách hàng mua nhiều nhất (chỉ tính đơn đã duyệt và đã thanh toán)
            ArrayList<TopCustomer> topCustomers = getTopCustomers(10);
            request.setAttribute("topCustomers", topCustomers);

            // Thống kê người dùng
            UserStatistics userStats = getUserStatistics();
            request.setAttribute("userStats", userStats);
            
            // Người dùng mới đăng ký theo thời gian
            Map<String, Integer> newUsersByPeriod = getNewUsersByPeriod(period);
            request.setAttribute("newUsersByPeriod", newUsersByPeriod);
            
            // Phân bố người dùng theo vai trò
            Map<String, Integer> usersByRole = getUsersByRole();
            request.setAttribute("usersByRole", usersByRole);
            
            // Người dùng có gói cước
            SubscriptionStatistics subscriptionStats = getSubscriptionStatistics();
            request.setAttribute("subscriptionStats", subscriptionStats);

            request.setAttribute("period", period);
            RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/statistics.jsp");
            rd.forward(request, response);
        } catch (Exception e) {
            e.printStackTrace();
            request.setAttribute("error", "Lỗi khi tải thống kê: " + e.getMessage());
            RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/statistics.jsp");
            rd.forward(request, response);
        }
    }

    private Map<String, Double> getRevenueByPeriod(String period) throws SQLException {
        Map<String, Double> revenue = new HashMap<>();
        Connection con = database.JDBCUtil.getConnection();
        String sql = "";

        switch (period) {
            case "day":
                sql = "SELECT DATE(ngayDatHang) as period, SUM(soTienDaThanhToan) as total " +
                      "FROM donhang " +
                      "WHERE (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                      "AND trangThaiThanhToan = 'Đã thanh toán' " +
                      "AND ngayDatHang >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) " +
                      "GROUP BY DATE(ngayDatHang) ORDER BY period DESC";
                break;
            case "month":
                sql = "SELECT DATE_FORMAT(ngayDatHang, '%Y-%m') as period, SUM(soTienDaThanhToan) as total " +
                      "FROM donhang " +
                      "WHERE (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                      "AND trangThaiThanhToan = 'Đã thanh toán' " +
                      "AND ngayDatHang >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) " +
                      "GROUP BY DATE_FORMAT(ngayDatHang, '%Y-%m') ORDER BY period DESC";
                break;
            case "year":
                sql = "SELECT YEAR(ngayDatHang) as period, SUM(soTienDaThanhToan) as total " +
                      "FROM donhang " +
                      "WHERE (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                      "AND trangThaiThanhToan = 'Đã thanh toán' " +
                      "GROUP BY YEAR(ngayDatHang) ORDER BY period DESC";
                break;
        }

        try (PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            while (rs.next()) {
                String periodKey = rs.getString("period");
                double total = rs.getDouble("total");
                // Xử lý null
                if (periodKey != null) {
                    revenue.put(periodKey, rs.wasNull() ? 0.0 : total);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy doanh thu theo kỳ: " + e.getMessage());
            e.printStackTrace();
            throw e;
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        return revenue;
    }
    
    private double getTotalRevenue() throws SQLException {
        Connection con = database.JDBCUtil.getConnection();
        String sql = "SELECT COALESCE(SUM(soTienDaThanhToan), 0) as total " +
                     "FROM donhang " +
                     "WHERE (trangThai = 'Đang giao' OR trangThai = 'Hoàn tất') " +
                     "AND trangThaiThanhToan = 'Đã thanh toán'";
        
        try (PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            if (rs.next()) {
                double total = rs.getDouble("total");
                return rs.wasNull() ? 0.0 : total;
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy tổng doanh thu: " + e.getMessage());
            e.printStackTrace();
            throw e;
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        return 0.0;
    }

    private Map<String, Integer> getOrdersByStatus() throws SQLException {
        Map<String, Integer> orders = new HashMap<>();
        Connection con = database.JDBCUtil.getConnection();
        String sql = "SELECT trangThai, COUNT(*) as count FROM donhang GROUP BY trangThai";

        try (PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            while (rs.next()) {
                String status = rs.getString("trangThai");
                int count = rs.getInt("count");
                if (status != null) {
                    orders.put(status, count);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy đơn hàng theo trạng thái: " + e.getMessage());
            e.printStackTrace();
            throw e;
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        return orders;
    }

    private ArrayList<BestSeller> getBestSellers(int limit) throws SQLException {
        ArrayList<BestSeller> bestSellers = new ArrayList<>();
        Connection con = database.JDBCUtil.getConnection();
        String sql = "SELECT sp.maSanPham, sp.tenSanPham, SUM(ctdh.soLuong) as totalSold " +
                     "FROM chitietdonhang ctdh " +
                     "JOIN donhang dh ON ctdh.maDonHang = dh.maDonHang " +
                     "JOIN sanpham sp ON ctdh.maSanPham = sp.maSanPham " +
                     "WHERE (dh.trangThai = 'Đang giao' OR dh.trangThai = 'Hoàn tất') " +
                     "AND dh.trangThaiThanhToan = 'Đã thanh toán' " +
                     "GROUP BY sp.maSanPham, sp.tenSanPham " +
                     "ORDER BY totalSold DESC LIMIT ?";

        try (PreparedStatement st = con.prepareStatement(sql)) {
            st.setInt(1, limit);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    BestSeller bs = new BestSeller();
                    bs.setMaSanPham(rs.getString("maSanPham"));
                    bs.setTenSanPham(rs.getString("tenSanPham"));
                    bs.setTotalSold(rs.getInt("totalSold"));
                    bestSellers.add(bs);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy sách bán chạy: " + e.getMessage());
            e.printStackTrace();
            throw e;
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        return bestSellers;
    }

    private ArrayList<TopCustomer> getTopCustomers(int limit) throws SQLException {
        ArrayList<TopCustomer> topCustomers = new ArrayList<>();
        Connection con = database.JDBCUtil.getConnection();
        String sql = "SELECT kh.maKhachHang, kh.hoVaTen, COUNT(dh.maDonHang) as totalOrders, " +
                     "SUM(dh.soTienDaThanhToan) as totalSpent " +
                     "FROM khachhang kh " +
                     "JOIN donhang dh ON kh.maKhachHang = dh.maKhachHang " +
                     "WHERE (dh.trangThai = 'Đang giao' OR dh.trangThai = 'Hoàn tất') " +
                     "AND dh.trangThaiThanhToan = 'Đã thanh toán' " +
                     "AND kh.role = 0 " +
                     "GROUP BY kh.maKhachHang, kh.hoVaTen " +
                     "ORDER BY totalSpent DESC LIMIT ?";

        try (PreparedStatement st = con.prepareStatement(sql)) {
            st.setInt(1, limit);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    TopCustomer tc = new TopCustomer();
                    tc.setMaKhachHang(rs.getString("maKhachHang"));
                    tc.setHoVaTen(rs.getString("hoVaTen"));
                    tc.setTotalOrders(rs.getInt("totalOrders"));
                    double totalSpent = rs.getDouble("totalSpent");
                    tc.setTotalSpent(rs.wasNull() ? 0.0 : totalSpent);
                    topCustomers.add(tc);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy khách hàng mua nhiều nhất: " + e.getMessage());
            e.printStackTrace();
            throw e;
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        return topCustomers;
    }

    private UserStatistics getUserStatistics() throws SQLException {
        UserStatistics stats = new UserStatistics();
        Connection con = database.JDBCUtil.getConnection();
        
        try {
            // Tổng số người dùng (không tính admin)
            String sql = "SELECT COUNT(*) as total FROM khachhang WHERE role = 0";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setTotalUsers(rs.getInt("total"));
                }
            }
            
            // Số người dùng active
            sql = "SELECT COUNT(*) as total FROM khachhang WHERE role = 0 AND status = 1";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setActiveUsers(rs.getInt("total"));
                }
            }
            
            // Số người dùng bị khóa
            sql = "SELECT COUNT(*) as total FROM khachhang WHERE role = 0 AND status = 0";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setLockedUsers(rs.getInt("total"));
                }
            }
            
            // Số người dùng mới trong tháng này (sử dụng ngayDangKy từ gói cước hoặc ước tính)
            // Nếu không có ngayDangKy, sẽ tính dựa trên ngày tạo tài khoản (nếu có)
            try {
                sql = "SELECT COUNT(*) as total FROM khachhang " +
                      "WHERE role = 0 AND MONTH(ngayDangKy) = MONTH(CURDATE()) " +
                      "AND YEAR(ngayDangKy) = YEAR(CURDATE())";
                try (PreparedStatement st = con.prepareStatement(sql);
                     ResultSet rs = st.executeQuery()) {
                    if (rs.next()) {
                        stats.setNewUsersThisMonth(rs.getInt("total"));
                    }
                }
            } catch (SQLException e) {
                // Nếu không có cột ngayDangKy, đặt giá trị mặc định
                stats.setNewUsersThisMonth(0);
            }
            
            // Số người dùng mới trong tuần này
            try {
                sql = "SELECT COUNT(*) as total FROM khachhang " +
                      "WHERE role = 0 AND ngayDangKy >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                try (PreparedStatement st = con.prepareStatement(sql);
                     ResultSet rs = st.executeQuery()) {
                    if (rs.next()) {
                        stats.setNewUsersThisWeek(rs.getInt("total"));
                    }
                }
            } catch (SQLException e) {
                // Nếu không có cột ngayDangKy, đặt giá trị mặc định
                stats.setNewUsersThisWeek(0);
            }
            
            // Số người dùng đã xác thực email
            sql = "SELECT COUNT(*) as total FROM khachhang WHERE role = 0 AND trangThaiXacThuc = 1";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setVerifiedUsers(rs.getInt("total"));
                }
            }
            
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        
        return stats;
    }
    
    private Map<String, Integer> getNewUsersByPeriod(String period) throws SQLException {
        Map<String, Integer> users = new HashMap<>();
        Connection con = database.JDBCUtil.getConnection();
        String sql = "";
        
        // Kiểm tra xem có cột ngayDangKy không (có thể không có trong bảng khachhang)
        // Nếu không có, sẽ trả về map rỗng
        try {
            switch (period) {
                case "day":
                    sql = "SELECT DATE(ngayDangKy) as period, COUNT(*) as total " +
                          "FROM khachhang " +
                          "WHERE role = 0 AND ngayDangKy >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) " +
                          "GROUP BY DATE(ngayDangKy) ORDER BY period DESC";
                    break;
                case "month":
                    sql = "SELECT DATE_FORMAT(ngayDangKy, '%Y-%m') as period, COUNT(*) as total " +
                          "FROM khachhang " +
                          "WHERE role = 0 AND ngayDangKy >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) " +
                          "GROUP BY DATE_FORMAT(ngayDangKy, '%Y-%m') ORDER BY period DESC";
                    break;
                case "year":
                    sql = "SELECT YEAR(ngayDangKy) as period, COUNT(*) as total " +
                          "FROM khachhang " +
                          "WHERE role = 0 " +
                          "GROUP BY YEAR(ngayDangKy) ORDER BY period DESC";
                    break;
            }
            
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    String periodKey = rs.getString("period");
                    int total = rs.getInt("total");
                    if (periodKey != null) {
                        users.put(periodKey, total);
                    }
                }
            }
        } catch (SQLException e) {
            // Nếu không có cột ngayDangKy, trả về map rỗng
            System.err.println("Lưu ý: Không thể lấy thống kê người dùng đăng ký theo thời gian: " + e.getMessage());
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        
        return users;
    }
    
    private Map<String, Integer> getUsersByRole() throws SQLException {
        Map<String, Integer> users = new HashMap<>();
        Connection con = database.JDBCUtil.getConnection();
        String sql = "SELECT role, COUNT(*) as total FROM khachhang GROUP BY role";
        
        try (PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            while (rs.next()) {
                int role = rs.getInt("role");
                int total = rs.getInt("total");
                String roleName = role == 1 ? "Quản trị viên" : "Người dùng";
                users.put(roleName, total);
            }
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        
        return users;
    }
    
    private SubscriptionStatistics getSubscriptionStatistics() throws SQLException {
        SubscriptionStatistics stats = new SubscriptionStatistics();
        Connection con = database.JDBCUtil.getConnection();
        
        try {
            // Tổng số người dùng có gói cước
            String sql = "SELECT COUNT(DISTINCT maKhachHang) as total " +
                         "FROM khachhang " +
                         "WHERE maGoiCuoc IS NOT NULL AND maGoiCuoc != ''";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setTotalSubscribers(rs.getInt("total"));
                }
            }
            
            // Số người dùng có gói cước còn hiệu lực
            sql = "SELECT COUNT(*) as total " +
                  "FROM khachhang " +
                  "WHERE maGoiCuoc IS NOT NULL AND maGoiCuoc != '' " +
                  "AND ngayHetHan IS NOT NULL AND ngayHetHan >= NOW()";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setActiveSubscribers(rs.getInt("total"));
                }
            }
            
            // Số người dùng có gói cước đã hết hạn
            sql = "SELECT COUNT(*) as total " +
                  "FROM khachhang " +
                  "WHERE maGoiCuoc IS NOT NULL AND maGoiCuoc != '' " +
                  "AND (ngayHetHan IS NULL OR ngayHetHan < NOW())";
            try (PreparedStatement st = con.prepareStatement(sql);
                 ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    stats.setExpiredSubscribers(rs.getInt("total"));
                }
            }
            
        } finally {
            database.JDBCUtil.closeConnection(con);
        }
        
        return stats;
    }

    public static class BestSeller {
        private String maSanPham;
        private String tenSanPham;
        private int totalSold;

        public String getMaSanPham() { return maSanPham; }
        public void setMaSanPham(String maSanPham) { this.maSanPham = maSanPham; }
        public String getTenSanPham() { return tenSanPham; }
        public void setTenSanPham(String tenSanPham) { this.tenSanPham = tenSanPham; }
        public int getTotalSold() { return totalSold; }
        public void setTotalSold(int totalSold) { this.totalSold = totalSold; }
    }

    public static class TopCustomer {
        private String maKhachHang;
        private String hoVaTen;
        private int totalOrders;
        private double totalSpent;

        public String getMaKhachHang() { return maKhachHang; }
        public void setMaKhachHang(String maKhachHang) { this.maKhachHang = maKhachHang; }
        public String getHoVaTen() { return hoVaTen; }
        public void setHoVaTen(String hoVaTen) { this.hoVaTen = hoVaTen; }
        public int getTotalOrders() { return totalOrders; }
        public void setTotalOrders(int totalOrders) { this.totalOrders = totalOrders; }
        public double getTotalSpent() { return totalSpent; }
        public void setTotalSpent(double totalSpent) { this.totalSpent = totalSpent; }
    }
    
    public static class UserStatistics {
        private int totalUsers;
        private int activeUsers;
        private int lockedUsers;
        private int newUsersThisMonth;
        private int newUsersThisWeek;
        private int verifiedUsers;
        
        public int getTotalUsers() { return totalUsers; }
        public void setTotalUsers(int totalUsers) { this.totalUsers = totalUsers; }
        public int getActiveUsers() { return activeUsers; }
        public void setActiveUsers(int activeUsers) { this.activeUsers = activeUsers; }
        public int getLockedUsers() { return lockedUsers; }
        public void setLockedUsers(int lockedUsers) { this.lockedUsers = lockedUsers; }
        public int getNewUsersThisMonth() { return newUsersThisMonth; }
        public void setNewUsersThisMonth(int newUsersThisMonth) { this.newUsersThisMonth = newUsersThisMonth; }
        public int getNewUsersThisWeek() { return newUsersThisWeek; }
        public void setNewUsersThisWeek(int newUsersThisWeek) { this.newUsersThisWeek = newUsersThisWeek; }
        public int getVerifiedUsers() { return verifiedUsers; }
        public void setVerifiedUsers(int verifiedUsers) { this.verifiedUsers = verifiedUsers; }
    }
    
    public static class SubscriptionStatistics {
        private int totalSubscribers;
        private int activeSubscribers;
        private int expiredSubscribers;
        
        public int getTotalSubscribers() { return totalSubscribers; }
        public void setTotalSubscribers(int totalSubscribers) { this.totalSubscribers = totalSubscribers; }
        public int getActiveSubscribers() { return activeSubscribers; }
        public void setActiveSubscribers(int activeSubscribers) { this.activeSubscribers = activeSubscribers; }
        public int getExpiredSubscribers() { return expiredSubscribers; }
        public void setExpiredSubscribers(int expiredSubscribers) { this.expiredSubscribers = expiredSubscribers; }
    }
}




