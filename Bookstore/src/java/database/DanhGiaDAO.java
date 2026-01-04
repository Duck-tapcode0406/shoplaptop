package database;

import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import model.DanhGia;
import model.KhachHang;
import model.SanPham;

public class DanhGiaDAO implements DAOInterface<DanhGia> {

    // Helper để lấy đối tượng lồng nhau
    private DanhGia mapRowToDanhGia(ResultSet rs) throws SQLException {
        // Hỗ trợ cả camelCase và lowercase
        String maDanhGia, noiDung, maKhachHang, maSanPham;
        int soSao;
        Date ngayDanhGia;
        
        try {
            maDanhGia = rs.getString("maDanhGia");
        } catch (SQLException e) {
            maDanhGia = rs.getString("madanhgia");
        }
        
        try {
            soSao = rs.getInt("soSao");
        } catch (SQLException e) {
            soSao = rs.getInt("sosao");
        }
        
        try {
            noiDung = rs.getString("noiDung");
        } catch (SQLException e) {
            noiDung = rs.getString("noidung");
        }
        
        // Lấy ngày giờ đánh giá - thử Timestamp trước (có giờ), nếu không có thì dùng Date
        try {
            java.sql.Timestamp timestamp = rs.getTimestamp("ngayDanhGia");
            if (timestamp != null) {
                // Chuyển Timestamp sang Date để tương thích với model hiện tại
                ngayDanhGia = new Date(timestamp.getTime());
            } else {
                ngayDanhGia = rs.getDate("ngayDanhGia");
            }
        } catch (SQLException e) {
            try {
                java.sql.Timestamp timestamp = rs.getTimestamp("ngaydanhgia");
                if (timestamp != null) {
                    ngayDanhGia = new Date(timestamp.getTime());
                } else {
                    ngayDanhGia = rs.getDate("ngaydanhgia");
                }
            } catch (SQLException e2) {
                ngayDanhGia = rs.getDate("ngaydanhgia");
            }
        }

        // Lấy thông tin KhachHang (chỉ lấy mã)
        KhachHang kh = new KhachHang();
        try {
            maKhachHang = rs.getString("maKhachHang");
        } catch (SQLException e) {
            maKhachHang = rs.getString("makhachhang");
        }
        kh.setMaKhachHang(maKhachHang);
        
        // Lấy tên khách hàng và avatar nếu có từ JOIN
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

        // Lấy thông tin SanPham (chỉ lấy mã)
        SanPham sp = new SanPham();
        try {
            maSanPham = rs.getString("maSanPham");
        } catch (SQLException e) {
            maSanPham = rs.getString("masanpham");
        }
        sp.setMaSanPham(maSanPham);
        
        return new DanhGia(maDanhGia, kh, sp, soSao, noiDung, ngayDanhGia);
    }
    
    @Override
    public ArrayList<DanhGia> selectAll() {
        // Thường ít dùng, nhưng vẫn implement
        ArrayList<DanhGia> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM `danhgia`";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                ketQua.add(mapRowToDanhGia(rs));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    
    /**
     * Hàm quan trọng: Lấy tất cả đánh giá của MỘT sản phẩm
     */
    public ArrayList<DanhGia> selectAllBySanPham(String maSanPham) {
        ArrayList<DanhGia> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM `danhgia` WHERE `maSanPham` = ? ORDER BY `ngayDanhGia` DESC";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maSanPham);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToDanhGia(rs));
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
 
    public DanhGia selectById(DanhGia t) {
        DanhGia ketQua = null;
        String sql = "SELECT * FROM `danhgia` WHERE `maDanhGia`=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaDanhGia());
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToDanhGia(rs);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insert(DanhGia t) {
        int ketQua = 0;
        String sql = "INSERT INTO `danhgia` (`maDanhGia`, `maKhachHang`, `maSanPham`, `soSao`, `noiDung`, `ngayDanhGia`) " +
                     "VALUES (?, ?, ?, ?, ?, ?)";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaDanhGia());
            st.setString(2, t.getKhachHang().getMaKhachHang());
            st.setString(3, t.getSanPham().getMaSanPham());
            st.setInt(4, t.getSoSao());
            st.setString(5, t.getNoiDung());
            // Lưu Timestamp để có đầy đủ ngày và giờ
            // Nếu database column là DATETIME/TIMESTAMP thì sẽ lưu đầy đủ, nếu là DATE thì chỉ lưu ngày
            if (t.getNgayDanhGia() != null) {
                java.sql.Timestamp timestamp = new java.sql.Timestamp(t.getNgayDanhGia().getTime());
                try {
                    st.setTimestamp(6, timestamp);
                } catch (SQLException e) {
                    // Nếu không hỗ trợ Timestamp, dùng Date
                    st.setDate(6, t.getNgayDanhGia());
                }
            } else {
                st.setDate(6, null);
            }

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int update(DanhGia t) {
        // Thông thường không cho phép SỬA đánh giá, chỉ có XÓA. 
        // Nhưng ta vẫn implement nếu cần (Admin sửa lỗi)
        int ketQua = 0;
        String sql = "UPDATE `danhgia` SET `soSao`=?, `noiDung`=? WHERE `maDanhGia`=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setInt(1, t.getSoSao());
            st.setString(2, t.getNoiDung());
            st.setString(3, t.getMaDanhGia());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int delete(DanhGia t) {
        int ketQua = 0;
        String sql = "DELETE FROM `danhgia` WHERE `maDanhGia`=?";
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaDanhGia());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<DanhGia> arr) {
        int dem = 0;
        for (DanhGia dg : arr) {
            dem += this.insert(dg);
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<DanhGia> arr) {
        int dem = 0;
        for (DanhGia dg : arr) {
            dem += this.delete(dg);
        }
        return dem;
    }
    public ArrayList<DanhGia> selectAllByProductId(String productId) {
        ArrayList<DanhGia> ketQua = new ArrayList<>();
        // Câu SQL JOIN bảng danhgia với khachhang để lấy tên và avatar
        String sql = "SELECT dg.*, kh.`hoVaTen` AS hovaten_khachhang, kh.`duongDanAnh` AS duongdananh_khachhang " 
                   + "FROM `danhgia` dg "
                   + "JOIN `khachhang` kh ON dg.`maKhachHang` = kh.`maKhachHang` " 
                   + "WHERE dg.`maSanPham` = ? " 
                   + "ORDER BY dg.`ngayDanhGia` DESC";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, productId); // Set tham số mã sản phẩm

            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    // Sử dụng hàm mapRowToDanhGia để tạo đối tượng
                    ketQua.add(mapRowToDanhGia(rs));
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy đánh giá cho sản phẩm '" + productId + "': " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
             System.err.println("Lỗi không xác định khi lấy đánh giá cho sản phẩm '" + productId + "': " + e.getMessage());
             e.printStackTrace();
        }
        return ketQua; // Trả về danh sách (có thể rỗng)
    }

    @Override
    public DanhGia selectById(String t) {
        throw new UnsupportedOperationException("Not supported yet."); // Generated from nbfs://nbhost/SystemFileSystem/Templates/Classes/Code/GeneratedMethodBody
    }
}