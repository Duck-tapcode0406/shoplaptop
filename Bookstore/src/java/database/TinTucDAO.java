package database; // Hoặc package của bạn

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp; // Sử dụng Timestamp
import java.util.ArrayList;
// import java.util.Date; // Không cần java.util.Date nữa nếu Model dùng Timestamp

import model.KhachHang; // Hoặc model người đăng tương ứng
import model.TinTuc;

public class TinTucDAO implements DAOInterface<TinTuc> {

    // --- Hàm Helper Map Row -> TinTuc (Đã sửa lỗi Timestamp) ---
    private TinTuc mapRowToTinTuc(ResultSet rs) throws SQLException {
        TinTuc tt = new TinTuc();
        
        // Hỗ trợ cả camelCase và lowercase
        try {
            tt.setMaTinTuc(rs.getString("maTinTuc"));
        } catch (SQLException e) {
            tt.setMaTinTuc(rs.getString("matintuc"));
        }
        
        try {
            tt.setTieuDe(rs.getString("tieuDe"));
        } catch (SQLException e) {
            tt.setTieuDe(rs.getString("tieude"));
        }
        
        try {
            tt.setNoiDung(rs.getString("noiDung"));
        } catch (SQLException e) {
            tt.setNoiDung(rs.getString("noidung"));
        }
        
        try {
            tt.setHinhAnh(rs.getString("hinhAnh"));
        } catch (SQLException e) {
            tt.setHinhAnh(rs.getString("hinhanh"));
        }

        // Lấy tác giả (tacGia)
        try {
            tt.setTacGia(rs.getString("tacGia"));
        } catch (SQLException e) {
            try {
                tt.setTacGia(rs.getString("tacgia"));
            } catch (SQLException e2) {
                // Không có cột này, để null
                tt.setTacGia(null);
            }
        }

        // Lấy Timestamp
        try {
            tt.setNgayDang(rs.getTimestamp("ngayDang"));
        } catch (SQLException e) {
            tt.setNgayDang(rs.getTimestamp("ngaydang"));
        }

        // Tạo đối tượng nguoiDang (KhachHang)
        KhachHang nguoiDang = new KhachHang();
        String maNguoiDang = null;
        try {
            maNguoiDang = rs.getString("maNguoiDang");
        } catch (SQLException e) {
            maNguoiDang = rs.getString("manguoidang");
        }
        nguoiDang.setMaKhachHang(maNguoiDang);

        // Lấy tên người đăng từ JOIN (có kiểm tra null)
        String hoTenNguoiDang = null;
        try {
            hoTenNguoiDang = rs.getString("hovaten_nguoidang");
        } catch (SQLException e) {
            try {
                hoTenNguoiDang = rs.getString("hoVaTen_nguoidang");
            } catch (SQLException e2) {
                // Không có cột này
            }
        }
        
        if (hoTenNguoiDang != null) {
            nguoiDang.setHoVaTen(hoTenNguoiDang);
        } else if (maNguoiDang != null) {
            nguoiDang.setHoVaTen("Người dùng #" + maNguoiDang);
        } else {
             nguoiDang.setHoVaTen("Không rõ");
        }

        tt.setNguoiDang(nguoiDang);

        return tt;
    }

    // Helper kiểm tra cột tồn tại (giữ nguyên)
    private boolean hasColumn(ResultSet rs, String columnName) throws SQLException {
        try {
            rs.findColumn(columnName);
            return true;
        } catch (SQLException sqlex) {
            return false;
        }
    }


    // --- Câu lệnh SQL JOIN dùng chung (giữ nguyên) ---
    private final String SELECT_ALL_JOINED =
        "SELECT tt.*, kh.`hoVaTen` AS hovaten_nguoidang " +
        "FROM `tintuc` tt " +
        "LEFT JOIN `khachhang` kh ON tt.`maNguoiDang` = kh.`maKhachHang`";

    // --- Các phương thức CRUD (giữ nguyên logic, chỉ đảm bảo dùng Timestamp) ---

    @Override
    public ArrayList<TinTuc> selectAll() {
        ArrayList<TinTuc> ketQua = new ArrayList<>();
        String sql = SELECT_ALL_JOINED + " ORDER BY tt.`ngayDang` DESC";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                ketQua.add(mapRowToTinTuc(rs)); // Gọi hàm map đã sửa
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy tất cả tin tức: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi lấy tất cả tin tức: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

     public TinTuc selectById(String maTinTuc) {
        TinTuc ketQua = null;
        String sql = SELECT_ALL_JOINED + " WHERE tt.`maTinTuc` = ?";

         if (maTinTuc == null || maTinTuc.trim().isEmpty()) {
             System.err.println("Lỗi: Mã tin tức không hợp lệ khi gọi selectById(String).");
             return null;
         }

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, maTinTuc);

            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToTinTuc(rs); // Gọi hàm map đã sửa
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy tin tức theo ID '" + maTinTuc + "': " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi lấy tin tức theo ID '" + maTinTuc + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
     }
 
    public TinTuc selectById(TinTuc t) {
        if (t != null && t.getMaTinTuc() != null) {
            return selectById(t.getMaTinTuc());
        }
        return null;
    }


    @Override
    public int insert(TinTuc t) {
        int ketQua = 0;
        // Bỏ cột tacGia vì không tồn tại trong database
        String sql = "INSERT INTO `tintuc` (`maTinTuc`, `tieuDe`, `noiDung`, `hinhAnh`, `ngayDang`, `maNguoiDang`) " +
                     "VALUES (?, ?, ?, ?, ?, ?)";

        if (t == null) return 0;

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaTinTuc());
            st.setString(2, t.getTieuDe());
            st.setString(3, t.getNoiDung());
            st.setString(4, t.getHinhAnh());
            // Dùng thẳng Timestamp từ Model
            st.setTimestamp(5, t.getNgayDang()); // Gán trực tiếp Timestamp
            st.setString(6, (t.getNguoiDang() != null) ? t.getNguoiDang().getMaKhachHang() : null);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi thêm tin tức: " + e.getMessage());
            e.printStackTrace();
            // Wrap SQLException vào RuntimeException để không phải sửa interface
            throw new RuntimeException("Lỗi SQL khi thêm tin tức: " + e.getMessage(), e);
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi thêm tin tức: " + e.getMessage());
            e.printStackTrace();
            throw new RuntimeException("Lỗi không xác định khi thêm tin tức: " + e.getMessage(), e);
        }
        return ketQua;
    }

    @Override
    public int update(TinTuc t) {
        int ketQua = 0;
        // Bỏ cột tacGia vì không tồn tại trong database
        String sql = "UPDATE `tintuc` SET `tieuDe`=?, `noiDung`=?, `hinhAnh`=?, `ngayDang`=?, `maNguoiDang`=? " +
                     "WHERE `maTinTuc`=?";

         if (t == null || t.getMaTinTuc() == null || t.getMaTinTuc().isEmpty()) return 0;

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getTieuDe());
            st.setString(2, t.getNoiDung());
            st.setString(3, t.getHinhAnh());
            st.setTimestamp(4, t.getNgayDang()); // Gán trực tiếp Timestamp
            st.setString(5, (t.getNguoiDang() != null) ? t.getNguoiDang().getMaKhachHang() : null);
            st.setString(6, t.getMaTinTuc());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi cập nhật tin tức '" + t.getMaTinTuc() + "': " + e.getMessage());
            e.printStackTrace();
            // Wrap SQLException vào RuntimeException để không phải sửa interface
            throw new RuntimeException("Lỗi SQL khi cập nhật tin tức: " + e.getMessage(), e);
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi cập nhật tin tức '" + t.getMaTinTuc() + "': " + e.getMessage());
            e.printStackTrace();
            throw new RuntimeException("Lỗi không xác định khi cập nhật tin tức: " + e.getMessage(), e);
        }
        return ketQua;
    }

    @Override
    public int delete(TinTuc t) {
        int ketQua = 0;
        String sql = "DELETE FROM `tintuc` WHERE `maTinTuc`=?";

         if (t == null || t.getMaTinTuc() == null || t.getMaTinTuc().isEmpty()) return 0;

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaTinTuc());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi xóa tin tức '" + t.getMaTinTuc() + "': " + e.getMessage()); e.printStackTrace();
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi xóa tin tức '" + t.getMaTinTuc() + "': " + e.getMessage()); e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<TinTuc> arr) {
        int dem = 0;
        if (arr == null) return 0;
        for (TinTuc tt : arr) {
             if (tt != null) dem += this.insert(tt);
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<TinTuc> arr) {
         int dem = 0;
        if (arr == null) return 0;
        for (TinTuc tt : arr) {
             if (tt != null) dem += this.delete(tt);
        }
        return dem;
    }
}