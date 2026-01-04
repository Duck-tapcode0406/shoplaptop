package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import model.NhaXuatBan;

public class NhaXuatBanDAO implements DAOInterface<NhaXuatBan> {

    private NhaXuatBan mapRowToNhaXuatBan(ResultSet rs) throws SQLException {
        String maNXB = rs.getString("manhaxuatban");
        String tenNXB = rs.getString("tennhaxuatban");
        String diaChi = rs.getString("diachi");
        String soDienThoai = rs.getString("sodienthoai");
        return new NhaXuatBan(maNXB, tenNXB, diaChi, soDienThoai);
    }

    @Override
    public ArrayList<NhaXuatBan> selectAll() {
        ArrayList<NhaXuatBan> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM nhaxuatban";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                ketQua.add(mapRowToNhaXuatBan(rs));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
 
    public NhaXuatBan selectById(NhaXuatBan t) {
        NhaXuatBan ketQua = null;
        String sql = "SELECT * FROM nhaxuatban WHERE manhaxuatban=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaNhaXuatBan());
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToNhaXuatBan(rs);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insert(NhaXuatBan t) {
        int ketQua = 0;
        String sql = "INSERT INTO nhaxuatban (manhaxuatban, tennhaxuatban, diachi, sodienthoai) VALUES (?, ?, ?, ?)";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaNhaXuatBan());
            st.setString(2, t.getTenNhaXuatBan());
            st.setString(3, t.getDiaChi());
            st.setString(4, t.getSoDienThoai());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int update(NhaXuatBan t) {
        int ketQua = 0;
        String sql = "UPDATE nhaxuatban SET tennhaxuatban=?, diachi=?, sodienthoai=? WHERE manhaxuatban=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getTenNhaXuatBan());
            st.setString(2, t.getDiaChi());
            st.setString(3, t.getSoDienThoai());
            st.setString(4, t.getMaNhaXuatBan());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int delete(NhaXuatBan t) {
        int ketQua = 0;
        String sql = "DELETE FROM nhaxuatban WHERE manhaxuatban=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaNhaXuatBan());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<NhaXuatBan> arr) {
        int dem = 0;
        for (NhaXuatBan nxb : arr) {
            dem += this.insert(nxb);
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<NhaXuatBan> arr) {
        int dem = 0;
        for (NhaXuatBan nxb : arr) {
            dem += this.delete(nxb);
        }
        return dem;
    }

    @Override
    public NhaXuatBan selectById(String t) {
        throw new UnsupportedOperationException("Not supported yet."); // Generated from nbfs://nbhost/SystemFileSystem/Templates/Classes/Code/GeneratedMethodBody
    }
    
    /**
     * Tìm nhà xuất bản theo tên (không phân biệt hoa thường)
     * @param tenNXB Tên nhà xuất bản cần tìm
     * @return Danh sách nhà xuất bản khớp với tên
     */
    public ArrayList<NhaXuatBan> searchByName(String tenNXB) {
        ArrayList<NhaXuatBan> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM nhaxuatban WHERE LOWER(tennhaxuatban) LIKE LOWER(?)";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, "%" + tenNXB + "%");
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToNhaXuatBan(rs));
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
}