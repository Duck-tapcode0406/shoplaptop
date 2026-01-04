package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.ArrayList;
import model.KhachHang;
import model.TraLoiDanhGia;

/**
 * DAO cho trả lời đánh giá
 */
public class TraLoiDanhGiaDAO {

    /**
     * Lấy tất cả trả lời của một đánh giá
     */
    public ArrayList<TraLoiDanhGia> selectAllByReviewId(String maDanhGia) {
        ArrayList<TraLoiDanhGia> ketQua = new ArrayList<>();
        String sql = "SELECT tl.*, kh.`hoVaTen` AS hovaten_khachhang, kh.`duongDanAnh` AS duongdananh_khachhang "
                   + "FROM `traloidanhgia` tl "
                   + "JOIN `khachhang` kh ON tl.`maKhachHang` = kh.`maKhachHang` "
                   + "WHERE tl.`maDanhGia` = ? "
                   + "ORDER BY tl.`ngayTraLoi` ASC";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maDanhGia);

            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    TraLoiDanhGia traLoi = mapRowToTraLoiDanhGia(rs);
                    ketQua.add(traLoi);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy trả lời cho đánh giá '" + maDanhGia + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Thêm trả lời mới
     */
    public int insert(TraLoiDanhGia traLoi) {
        int ketQua = 0;
        String sql = "INSERT INTO `traloidanhgia` (`maTraLoi`, `maDanhGia`, `maKhachHang`, `noiDung`, `ngayTraLoi`) "
                   + "VALUES (?, ?, ?, ?, ?)";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, traLoi.getMaTraLoi());
            st.setString(2, traLoi.getMaDanhGia());
            st.setString(3, traLoi.getKhachHang().getMaKhachHang());
            st.setString(4, traLoi.getNoiDung());
            st.setTimestamp(5, traLoi.getNgayTraLoi());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi thêm trả lời: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Xóa trả lời
     */
    public int delete(String maTraLoi, String maKhachHang) {
        int ketQua = 0;
        String sql = "DELETE FROM `traloidanhgia` WHERE `maTraLoi` = ? AND `maKhachHang` = ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maTraLoi);
            st.setString(2, maKhachHang);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi xóa trả lời: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Map ResultSet sang TraLoiDanhGia
     */
    private TraLoiDanhGia mapRowToTraLoiDanhGia(ResultSet rs) throws SQLException {
        TraLoiDanhGia traLoi = new TraLoiDanhGia();

        try {
            traLoi.setMaTraLoi(rs.getString("maTraLoi"));
        } catch (SQLException e) {
            traLoi.setMaTraLoi(rs.getString("matraloi"));
        }

        try {
            traLoi.setMaDanhGia(rs.getString("maDanhGia"));
        } catch (SQLException e) {
            traLoi.setMaDanhGia(rs.getString("madanhgia"));
        }

        try {
            traLoi.setNoiDung(rs.getString("noiDung"));
        } catch (SQLException e) {
            traLoi.setNoiDung(rs.getString("noidung"));
        }

        try {
            Timestamp timestamp = rs.getTimestamp("ngayTraLoi");
            if (timestamp != null) {
                traLoi.setNgayTraLoi(timestamp);
            } else {
                traLoi.setNgayTraLoi(rs.getTimestamp("ngaytraloi"));
            }
        } catch (SQLException e) {
            traLoi.setNgayTraLoi(rs.getTimestamp("ngaytraloi"));
        }

        // Lấy thông tin khách hàng
        KhachHang kh = new KhachHang();
        try {
            kh.setMaKhachHang(rs.getString("maKhachHang"));
        } catch (SQLException e) {
            kh.setMaKhachHang(rs.getString("makhachhang"));
        }

        try {
            String hoTen = rs.getString("hovaten_khachhang");
            if (hoTen != null) {
                kh.setHoVaTen(hoTen);
            }
        } catch (SQLException e) {
            // Không có cột này
        }

        try {
            String avatar = rs.getString("duongdananh_khachhang");
            if (avatar != null) {
                kh.setDuongDanAnh(avatar);
            }
        } catch (SQLException e) {
            // Không có cột này
        }

        traLoi.setKhachHang(kh);
        return traLoi;
    }
}

