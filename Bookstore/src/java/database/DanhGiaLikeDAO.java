package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * DAO cho like/dislike đánh giá
 */
public class DanhGiaLikeDAO {

    /**
     * Kiểm tra xem user đã like đánh giá này chưa
     */
    public boolean hasLiked(String maDanhGia, String maKhachHang) throws SQLException {
        String sql = "SELECT 1 FROM `danhgialike` WHERE `maDanhGia` = ? AND `maKhachHang` = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maDanhGia);
            st.setString(2, maKhachHang);
            
            try (ResultSet rs = st.executeQuery()) {
                return rs.next();
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi kiểm tra like: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
    }

    /**
     * Kiểm tra xem user đã dislike đánh giá này chưa
     */
    public boolean hasDisliked(String maDanhGia, String maKhachHang) throws SQLException {
        String sql = "SELECT 1 FROM `danhgiadislike` WHERE `maDanhGia` = ? AND `maKhachHang` = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maDanhGia);
            st.setString(2, maKhachHang);
            
            try (ResultSet rs = st.executeQuery()) {
                return rs.next();
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi kiểm tra dislike: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
    }

    /**
     * Thêm like
     */
    public int insertLike(String maDanhGia, String maKhachHang) throws SQLException {
        int ketQua = 0;
        String sql = "INSERT INTO `danhgialike` (`maDanhGia`, `maKhachHang`) VALUES (?, ?) "
                   + "ON DUPLICATE KEY UPDATE `ngayLike` = CURRENT_TIMESTAMP";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maDanhGia);
            st.setString(2, maKhachHang);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi thêm like: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
        return ketQua;
    }

    /**
     * Xóa like
     */
    public int deleteLike(String maDanhGia, String maKhachHang) throws SQLException {
        int ketQua = 0;
        String sql = "DELETE FROM `danhgialike` WHERE `maDanhGia` = ? AND `maKhachHang` = ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maDanhGia);
            st.setString(2, maKhachHang);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi xóa like: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
        return ketQua;
    }

    /**
     * Thêm dislike
     */
    public int insertDislike(String maDanhGia, String maKhachHang) throws SQLException {
        int ketQua = 0;
        String sql = "INSERT INTO `danhgiadislike` (`maDanhGia`, `maKhachHang`) VALUES (?, ?) "
                   + "ON DUPLICATE KEY UPDATE `ngayDislike` = CURRENT_TIMESTAMP";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maDanhGia);
            st.setString(2, maKhachHang);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi thêm dislike: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
        return ketQua;
    }

    /**
     * Xóa dislike
     */
    public int deleteDislike(String maDanhGia, String maKhachHang) throws SQLException {
        int ketQua = 0;
        String sql = "DELETE FROM `danhgiadislike` WHERE `maDanhGia` = ? AND `maKhachHang` = ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maDanhGia);
            st.setString(2, maKhachHang);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi xóa dislike: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
        return ketQua;
    }

    /**
     * Đếm số lượt like của một đánh giá
     */
    public int countLikes(String maDanhGia) throws SQLException {
        String sql = "SELECT COUNT(*) as count FROM `danhgialike` WHERE `maDanhGia` = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maDanhGia);
            
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt("count");
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi đếm like: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
        return 0;
    }

    /**
     * Đếm số lượt dislike của một đánh giá
     */
    public int countDislikes(String maDanhGia) throws SQLException {
        String sql = "SELECT COUNT(*) as count FROM `danhgiadislike` WHERE `maDanhGia` = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maDanhGia);
            
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt("count");
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi đếm dislike: " + e.getMessage());
            e.printStackTrace();
            throw e; // Ném lại exception để servlet xử lý
        }
        return 0;
    }
}

