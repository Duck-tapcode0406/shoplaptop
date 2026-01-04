package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import model.TheLoai;

public class TheLoaiDAO implements DAOInterface<TheLoai> {

    @Override
    public ArrayList<TheLoai> selectAll() {
        ArrayList<TheLoai> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM theloai";
        // Sử dụng try-with-resources để đảm bảo kết nối luôn được đóng
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            
            while (rs.next()) {
                String maTheLoai = rs.getString("matheloai");
                String tenTheLoai = rs.getString("tentheloai");
                ketQua.add(new TheLoai(maTheLoai, tenTheLoai));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
 
    public TheLoai selectById(TheLoai t) {
        TheLoai ketQua = null;
        String sql = "SELECT * FROM theloai WHERE matheloai=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaTheLoai());
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    String maTheLoai = rs.getString("matheloai");
                    String tenTheLoai = rs.getString("tentheloai");
                    ketQua = new TheLoai(maTheLoai, tenTheLoai);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insert(TheLoai t) {
        int ketQua = 0;
        String sql = "INSERT INTO theloai (matheloai, tentheloai) VALUES (?, ?)";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaTheLoai());
            st.setString(2, t.getTenTheLoai());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    
    @Override
    public int update(TheLoai t) {
        int ketQua = 0;
        String sql = "UPDATE theloai SET tentheloai=? WHERE matheloai=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getTenTheLoai());
            st.setString(2, t.getMaTheLoai());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int delete(TheLoai t) {
        int ketQua = 0;
        String sql = "DELETE FROM theloai WHERE matheloai=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaTheLoai());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<TheLoai> arr) {
        int dem = 0;
        for (TheLoai tl : arr) {
            dem += this.insert(tl);
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<TheLoai> arr) {
        int dem = 0;
        for (TheLoai tl : arr) {
            dem += this.delete(tl);
        }
        return dem;
    }

    @Override
    public TheLoai selectById(String t) {
        throw new UnsupportedOperationException("Not supported yet."); // Generated from nbfs://nbhost/SystemFileSystem/Templates/Classes/Code/GeneratedMethodBody
    }
    
    /**
     * Tìm thể loại theo tên (không phân biệt hoa thường)
     * @param tenTheLoai Tên thể loại cần tìm
     * @return Danh sách thể loại khớp với tên
     */
    public ArrayList<TheLoai> searchByName(String tenTheLoai) {
        ArrayList<TheLoai> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM theloai WHERE LOWER(tentheloai) LIKE LOWER(?)";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, "%" + tenTheLoai + "%");
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    String maTheLoai = rs.getString("matheloai");
                    String ten = rs.getString("tentheloai");
                    ketQua.add(new TheLoai(maTheLoai, ten));
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
}