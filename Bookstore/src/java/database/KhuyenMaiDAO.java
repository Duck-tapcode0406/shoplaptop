package database;

import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import model.KhuyenMai;

public class KhuyenMaiDAO implements DAOInterface<KhuyenMai> {

    private KhuyenMai mapRowToKhuyenMai(ResultSet rs) throws SQLException {
        KhuyenMai km = new KhuyenMai();
        try {
            km.setMaKhuyenMai(rs.getString("maKhuyenMai"));
        } catch (SQLException e) {
            km.setMaKhuyenMai(rs.getString("makhuyenmai"));
        }
        try {
            km.setTenKhuyenMai(rs.getString("tenKhuyenMai"));
        } catch (SQLException e) {
            km.setTenKhuyenMai(rs.getString("tenkhuyenmai"));
        }
        try {
            km.setPhanTramGiam(rs.getDouble("phanTramGiam"));
        } catch (SQLException e) {
            km.setPhanTramGiam(rs.getDouble("phantramgiam"));
        }
        try {
            km.setSoTienGiamToiDa(rs.getDouble("soTienGiamToiDa"));
        } catch (SQLException e) {
            km.setSoTienGiamToiDa(rs.getDouble("sotiengiamtoida"));
        }
        try {
            km.setNgayBatDau(rs.getDate("ngayBatDau"));
        } catch (SQLException e) {
            km.setNgayBatDau(rs.getDate("ngaybatdau"));
        }
        try {
            km.setNgayKetThuc(rs.getDate("ngayKetThuc"));
        } catch (SQLException e) {
            km.setNgayKetThuc(rs.getDate("ngayketthuc"));
        }
        try {
            km.setTrangThai(rs.getBoolean("trangThai"));
        } catch (SQLException e) {
            km.setTrangThai(rs.getBoolean("trangthai"));
        }
        return km;
    }
    
    @Override
    public ArrayList<KhuyenMai> selectAll() {
        ArrayList<KhuyenMai> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM khuyenmai";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                ketQua.add(mapRowToKhuyenMai(rs));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    public KhuyenMai selectById(KhuyenMai t) {
        // Thường ta sẽ select bằng mã (String)
        return selectByMaKhuyenMai(t.getMaKhuyenMai());
    }

    /**
     * Dùng khi user áp dụng mã giảm giá
     */
    public KhuyenMai selectByMaKhuyenMai(String maKhuyenMai) {
        KhuyenMai ketQua = null;
        String sql = "SELECT * FROM `khuyenmai` WHERE `maKhuyenMai` = ? OR `makhuyenmai` = ?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maKhuyenMai);
            st.setString(2, maKhuyenMai);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToKhuyenMai(rs);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insert(KhuyenMai t) {
        int ketQua = 0;
        String sql = "INSERT INTO khuyenmai (makhuyenmai, tenkhuyenmai, phantramgiam, sotiengiamtoida, ngaybatdau, ngayketthuc, trangthai) " +
                     "VALUES (?, ?, ?, ?, ?, ?, ?)";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaKhuyenMai());
            st.setString(2, t.getTenKhuyenMai());
            st.setDouble(3, t.getPhanTramGiam());
            st.setDouble(4, t.getSoTienGiamToiDa());
            st.setDate(5, t.getNgayBatDau());
            st.setDate(6, t.getNgayKetThuc());
            st.setBoolean(7, t.isTrangThai());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int update(KhuyenMai t) {
        int ketQua = 0;
        String sql = "UPDATE khuyenmai SET tenkhuyenmai=?, phantramgiam=?, sotiengiamtoida=?, ngaybatdau=?, ngayketthuc=?, trangthai=? " +
                     "WHERE makhuyenmai=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getTenKhuyenMai());
            st.setDouble(2, t.getPhanTramGiam());
            st.setDouble(3, t.getSoTienGiamToiDa());
            st.setDate(4, t.getNgayBatDau());
            st.setDate(5, t.getNgayKetThuc());
            st.setBoolean(6, t.isTrangThai());
            st.setString(7, t.getMaKhuyenMai());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int delete(KhuyenMai t) {
        int ketQua = 0;
        String sql = "DELETE FROM khuyenmai WHERE makhuyenmai=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaKhuyenMai());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<KhuyenMai> arr) {
        int dem = 0;
        for (KhuyenMai km : arr) {
            dem += this.insert(km);
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<KhuyenMai> arr) {
        int dem = 0;
        for (KhuyenMai km : arr) {
            dem += this.delete(km);
        }
        return dem;
    }

    @Override
    public KhuyenMai selectById(String t) {
        throw new UnsupportedOperationException("Not supported yet."); // Generated from nbfs://nbhost/SystemFileSystem/Templates/Classes/Code/GeneratedMethodBody
    }
}