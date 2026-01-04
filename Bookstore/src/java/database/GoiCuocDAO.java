package database;

import model.GoiCuoc;
import java.sql.*;
import java.util.ArrayList;

public class GoiCuocDAO {
    
    /**
     * Lấy tất cả các gói cước đang hoạt động
     */
    public ArrayList<GoiCuoc> getAllActivePackages() {
        ArrayList<GoiCuoc> list = new ArrayList<>();
        String sql = "SELECT * FROM goicuoc WHERE trangThai = 1 ORDER BY thoiHan ASC";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            
            while (rs.next()) {
                GoiCuoc goi = new GoiCuoc();
                goi.setMaGoi(rs.getString("maGoi"));
                goi.setTenGoi(rs.getString("tenGoi"));
                goi.setThoiHan(rs.getInt("thoiHan"));
                goi.setGiaTien(rs.getLong("giaTien"));
                goi.setMoTa(rs.getString("moTa"));
                goi.setTrangThai(rs.getInt("trangThai"));
                list.add(goi);
            }
        } catch (SQLException e) {
            System.err.println("Lỗi khi lấy danh sách gói cước: " + e.getMessage());
            e.printStackTrace();
        }
        
        return list;
    }
    
    /**
     * Lấy gói cước theo mã
     */
    public GoiCuoc getByMaGoi(String maGoi) {
        String sql = "SELECT * FROM goicuoc WHERE maGoi = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maGoi);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    GoiCuoc goi = new GoiCuoc();
                    goi.setMaGoi(rs.getString("maGoi"));
                    goi.setTenGoi(rs.getString("tenGoi"));
                    goi.setThoiHan(rs.getInt("thoiHan"));
                    goi.setGiaTien(rs.getLong("giaTien"));
                    goi.setMoTa(rs.getString("moTa"));
                    goi.setTrangThai(rs.getInt("trangThai"));
                    return goi;
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi khi lấy gói cước: " + e.getMessage());
            e.printStackTrace();
        }
        
        return null;
    }
}








