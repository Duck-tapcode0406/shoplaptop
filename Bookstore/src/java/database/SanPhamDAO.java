package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List; // Sử dụng List thay vì ArrayList cho params

import model.NhaXuatBan;
import model.SanPham;
import model.TacGia;
import model.TheLoai;

public class SanPhamDAO implements DAOInterface<SanPham> {

    /**
     * Helper: Ánh xạ ResultSet đã JOIN đầy đủ sang đối tượng SanPham.
     * **Quan trọng:** Đảm bảo lớp model SanPham có constructor khớp chính xác!
     */
    private SanPham mapRowToSanPham(ResultSet rs) throws SQLException {
        // 1. Tạo đối tượng TacGia
        TacGia tacGia = new TacGia();
        try {
            tacGia.setMaTacGia(rs.getString("maTacGia"));
        } catch (SQLException e) {
            tacGia.setMaTacGia(rs.getString("matacgia"));
        }
        
        // Kiểm tra xem cột alias có tồn tại không
        if (hasColumn(rs, "hovaten_tacgia")) {
             tacGia.setHoVaTen(rs.getString("hovaten_tacgia"));
        } else if (hasColumn(rs, "hoVaTen_tacgia")) {
             tacGia.setHoVaTen(rs.getString("hoVaTen_tacgia"));
        } else {
            try {
                tacGia.setHoVaTen(rs.getString("hoVaTen"));
            } catch (SQLException e) {
                try {
                    tacGia.setHoVaTen(rs.getString("hovaten"));
                } catch (SQLException e2) {
                    // Không có cột này
                }
            }
        }

        // 2. Tạo đối tượng TheLoai
        TheLoai theLoai = new TheLoai();
        try {
            theLoai.setMaTheLoai(rs.getString("maTheLoai"));
        } catch (SQLException e) {
            theLoai.setMaTheLoai(rs.getString("matheloai"));
        }
        
        try {
            theLoai.setTenTheLoai(rs.getString("tenTheLoai"));
        } catch (SQLException e) {
            if (hasColumn(rs, "tentheloai")) {
                theLoai.setTenTheLoai(rs.getString("tentheloai"));
            }
        }

        // 3. Tạo đối tượng NhaXuatBan
        NhaXuatBan nxb = new NhaXuatBan();
        try {
            nxb.setMaNhaXuatBan(rs.getString("maNhaXuatBan"));
        } catch (SQLException e) {
            nxb.setMaNhaXuatBan(rs.getString("manhaxuatban"));
        }
        
        try {
            nxb.setTenNhaXuatBan(rs.getString("tenNhaXuatBan"));
        } catch (SQLException e) {
            if (hasColumn(rs, "tennhaxuatban")) {
                nxb.setTenNhaXuatBan(rs.getString("tennhaxuatban"));
            }
        }

        // 4. Tạo đối tượng SanPham hoàn chỉnh
        // Hỗ trợ cả camelCase và lowercase
        String maSanPham, tenSanPham, ngonNgu, moTa, hinhAnh, fileEpub;
        int namXuatBan, soLuong, trangThai;
        double giaNhap, giaGoc, giaBan;
        
        try {
            maSanPham = rs.getString("maSanPham");
        } catch (SQLException e) {
            maSanPham = rs.getString("masanpham");
        }
        
        try {
            tenSanPham = rs.getString("tenSanPham");
        } catch (SQLException e) {
            tenSanPham = rs.getString("tensanpham");
        }
        
        try {
            namXuatBan = rs.getInt("namXuatBan");
        } catch (SQLException e) {
            namXuatBan = rs.getInt("namxuatban");
        }
        
        try {
            giaNhap = rs.getDouble("giaNhap");
        } catch (SQLException e) {
            giaNhap = rs.getDouble("gianhap");
        }
        
        try {
            giaGoc = rs.getDouble("giaGoc");
        } catch (SQLException e) {
            giaGoc = rs.getDouble("giagoc");
        }
        
        try {
            giaBan = rs.getDouble("giaBan");
        } catch (SQLException e) {
            giaBan = rs.getDouble("giaban");
        }
        
        try {
            soLuong = rs.getInt("soLuong");
        } catch (SQLException e) {
            soLuong = rs.getInt("soluong");
        }
        
        try {
            ngonNgu = rs.getString("ngonNgu");
        } catch (SQLException e) {
            ngonNgu = rs.getString("ngonngu");
        }
        
        try {
            moTa = rs.getString("moTa");
            if (moTa == null) moTa = "";
        } catch (SQLException e) {
            try {
                moTa = rs.getString("mota");
                if (moTa == null) moTa = "";
            } catch (SQLException e2) {
                moTa = "";
            }
        }
        
        try {
            hinhAnh = rs.getString("hinhAnh");
            if (hinhAnh == null) hinhAnh = "";
        } catch (SQLException e) {
            try {
                hinhAnh = rs.getString("hinhanh");
                if (hinhAnh == null) hinhAnh = "";
            } catch (SQLException e2) {
                hinhAnh = "";
            }
        }
        
        try {
            trangThai = rs.getInt("trangThai");
        } catch (SQLException e) {
            try {
                trangThai = rs.getInt("trangthai");
            } catch (SQLException e2) {
                trangThai = 1; // Mặc định
            }
        }
        
        try {
            fileEpub = rs.getString("fileEpub");
            // Cho phép null vì fileEpub là optional
        } catch (SQLException e) {
            try {
                fileEpub = rs.getString("fileepub");
            } catch (SQLException e2) {
                fileEpub = null; // Có thể null nếu cột chưa tồn tại
            }
        }
        
        // Xử lý null cho các trường String
        if (ngonNgu == null) ngonNgu = "";
        if (moTa == null) moTa = "";
        if (hinhAnh == null) hinhAnh = "";
        
        return new SanPham(
            maSanPham,
            tenSanPham,
            tacGia,
            namXuatBan,
            giaNhap,
            giaGoc,
            giaBan,
            soLuong,
            theLoai,
            ngonNgu,
            moTa,
            nxb,
            hinhAnh,
            trangThai,
            fileEpub
        );
    }

    /**
     * Helper: Kiểm tra xem ResultSet có chứa cột tên cụ thể không
     */
     private boolean hasColumn(ResultSet rs, String columnName) throws SQLException {
        try {
            rs.findColumn(columnName);
            return true;
        } catch (SQLException sqlex) {
            // Cột không tồn tại
            return false;
        }
    }
    
    /**
     * Helper: Kiểm tra xem cột có tồn tại trong bảng sanpham không
     */
    private boolean checkColumnExists(String columnName) {
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(
                 "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS " +
                 "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sanpham' AND COLUMN_NAME = ?")) {
            
            st.setString(1, columnName);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1) > 0;
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi khi kiểm tra cột '" + columnName + "': " + e.getMessage());
            // Nếu không kiểm tra được, giả định cột không tồn tại để tránh lỗi
            return false;
        }
        return false;
    }


    // Biến private final chứa câu lệnh JOIN đầy đủ, dùng chung
    private final String SELECT_ALL_SQL_JOINED =
        "SELECT sp.*, " +
        "       tg.`hoVaTen` AS hovaten_tacgia, " + // Đặt alias để tránh trùng tên cột
        "       tl.`tenTheLoai`, " +
        "       nxb.`tenNhaXuatBan` " +
        "FROM `sanpham` sp " +
        "JOIN `tacgia` tg ON sp.`maTacGia` = tg.`maTacGia` " +
        "JOIN `theloai` tl ON sp.`maTheLoai` = tl.`maTheLoai` " +
        "JOIN `nhaxuatban` nxb ON sp.`maNhaXuatBan` = nxb.`maNhaXuatBan`"; // JOIN thêm NXB

    @Override
    public ArrayList<SanPham> selectAll() {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        // Sử dụng biến JOIN và lọc trạng thái
        String sql = SELECT_ALL_SQL_JOINED + " WHERE sp.`trangThai` = 1"; // Chỉ lấy sách đang hiển thị

        // Dùng try-with-resources để tự động đóng kết nối
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                ketQua.add(mapRowToSanPham(rs)); // Dùng hàm helper
            }
        } catch (SQLException e) {
            System.err.println("Lỗi khi lấy tất cả sản phẩm: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi lấy tất cả sản phẩm: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }
 
    public SanPham selectById(SanPham t) {
        SanPham ketQua = null;
        // Sử dụng biến JOIN và thêm điều kiện WHERE
        String sql = SELECT_ALL_SQL_JOINED + " WHERE sp.`maSanPham`=?";

        if (t == null || t.getMaSanPham() == null || t.getMaSanPham().isEmpty()) {
            System.err.println("Lỗi: Mã sản phẩm không hợp lệ khi gọi selectById.");
            return null;
        }

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaSanPham());
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToSanPham(rs); // Dùng hàm helper
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi lấy sản phẩm theo ID '" + t.getMaSanPham() + "': " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi lấy sản phẩm theo ID '" + t.getMaSanPham() + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insert(SanPham t) {
        int ketQua = 0;
        
        if (t == null) {
             System.err.println("Lỗi: Đối tượng SanPham bị null khi gọi insert.");
            return 0;
        }

        // Kiểm tra xem cột fileEpub có tồn tại không
        boolean hasFileEpubColumn = checkColumnExists("fileEpub");
        
        // Xây dựng SQL động dựa trên việc cột fileEpub có tồn tại hay không
        String sql;
        if (hasFileEpubColumn) {
            sql = "INSERT INTO `sanpham` (`maSanPham`, `tenSanPham`, `maTacGia`, `namXuatBan`, `giaNhap`, `giaGoc`, `giaBan`, " +
                  "`soLuong`, `maTheLoai`, `ngonNgu`, `moTa`, `maNhaXuatBan`, `hinhAnh`, `trangThai`, `fileEpub`) " +
                  "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        } else {
            sql = "INSERT INTO `sanpham` (`maSanPham`, `tenSanPham`, `maTacGia`, `namXuatBan`, `giaNhap`, `giaGoc`, `giaBan`, " +
                  "`soLuong`, `maTheLoai`, `ngonNgu`, `moTa`, `maNhaXuatBan`, `hinhAnh`, `trangThai`) " +
                  "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaSanPham());
            st.setString(2, t.getTenSanPham());
            // Lấy ID từ các đối tượng lồng nhau, kiểm tra null trước
            st.setString(3, (t.getTacGia() != null) ? t.getTacGia().getMaTacGia() : null);
            st.setInt(4, t.getNamXuatBan());
            st.setDouble(5, t.getGiaNhap());
            st.setDouble(6, t.getGiaGoc());
            st.setDouble(7, t.getGiaBan());
            st.setInt(8, t.getSoLuong());
            st.setString(9, (t.getTheLoai() != null) ? t.getTheLoai().getMaTheLoai() : null);
            st.setString(10, t.getNgonNgu() != null ? t.getNgonNgu() : "");
            st.setString(11, t.getMoTa() != null ? t.getMoTa() : "");
            st.setString(12, (t.getNhaXuatBan() != null) ? t.getNhaXuatBan().getMaNhaXuatBan() : null);
            st.setString(13, t.getHinhAnh() != null ? t.getHinhAnh() : "");
            st.setInt(14, t.getTrangThai());
            
            // Chỉ set fileEpub nếu cột tồn tại
            if (hasFileEpubColumn) {
                String fileEpub = t.getFileEpub();
                if (fileEpub != null && fileEpub.trim().isEmpty()) {
                    fileEpub = null;
                }
                st.setString(15, fileEpub);
            }

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi thêm sản phẩm '" + t.getMaSanPham() + "': " + e.getMessage());
             System.err.println("SQL: " + sql);
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác
            System.err.println("Lỗi không xác định khi thêm sản phẩm '" + t.getMaSanPham() + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int update(SanPham t) {
        int ketQua = 0;

        if (t == null || t.getMaSanPham() == null || t.getMaSanPham().isEmpty()) {
             System.err.println("Lỗi: Đối tượng SanPham hoặc mã sản phẩm không hợp lệ khi gọi update.");
            return 0;
        }

        // Kiểm tra xem cột fileEpub có tồn tại không
        boolean hasFileEpubColumn = checkColumnExists("fileEpub");
        
        // Xây dựng SQL động dựa trên việc cột fileEpub có tồn tại hay không
        String sql;
        if (hasFileEpubColumn) {
            sql = "UPDATE `sanpham` SET " +
                  "`tenSanPham`=?, `maTacGia`=?, `namXuatBan`=?, `giaNhap`=?, `giaGoc`=?, `giaBan`=?, " +
                  "`soLuong`=?, `maTheLoai`=?, `ngonNgu`=?, `moTa`=?, " +
                  "`maNhaXuatBan`=?, `hinhAnh`=?, `trangThai`=?, `fileEpub`=? " +
                  "WHERE `maSanPham`=?";
        } else {
            sql = "UPDATE `sanpham` SET " +
                  "`tenSanPham`=?, `maTacGia`=?, `namXuatBan`=?, `giaNhap`=?, `giaGoc`=?, `giaBan`=?, " +
                  "`soLuong`=?, `maTheLoai`=?, `ngonNgu`=?, `moTa`=?, " +
                  "`maNhaXuatBan`=?, `hinhAnh`=?, `trangThai`=? " +
                  "WHERE `maSanPham`=?";
        }

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getTenSanPham());
            st.setString(2, (t.getTacGia() != null) ? t.getTacGia().getMaTacGia() : null);
            st.setInt(3, t.getNamXuatBan());
            st.setDouble(4, t.getGiaNhap());
            st.setDouble(5, t.getGiaGoc());
            st.setDouble(6, t.getGiaBan());
            st.setInt(7, t.getSoLuong());
            st.setString(8, (t.getTheLoai() != null) ? t.getTheLoai().getMaTheLoai() : null);
            st.setString(9, t.getNgonNgu() != null ? t.getNgonNgu() : "");
            st.setString(10, t.getMoTa() != null ? t.getMoTa() : "");
            st.setString(11, (t.getNhaXuatBan() != null) ? t.getNhaXuatBan().getMaNhaXuatBan() : null);
            st.setString(12, t.getHinhAnh() != null ? t.getHinhAnh() : "");
            st.setInt(13, t.getTrangThai());
            
            // Chỉ set fileEpub nếu cột tồn tại
            if (hasFileEpubColumn) {
                String fileEpub = t.getFileEpub();
                if (fileEpub != null && fileEpub.trim().isEmpty()) {
                    fileEpub = null;
                }
                st.setString(14, fileEpub);
                st.setString(15, t.getMaSanPham()); // Tham số WHERE
            } else {
                st.setString(14, t.getMaSanPham()); // Tham số WHERE
            }

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi cập nhật sản phẩm '" + t.getMaSanPham() + "': " + e.getMessage());
             System.err.println("SQL: " + sql);
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác
            System.err.println("Lỗi không xác định khi cập nhật sản phẩm '" + t.getMaSanPham() + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int delete(SanPham t) {
        int ketQua = 0;
        String sql = "DELETE FROM `sanpham` WHERE `maSanPham`=?";

        if (t == null || t.getMaSanPham() == null || t.getMaSanPham().isEmpty()) {
             System.err.println("Lỗi: Mã sản phẩm không hợp lệ khi gọi delete.");
            return 0;
        }

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaSanPham());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi xóa sản phẩm '" + t.getMaSanPham() + "': " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác
            System.err.println("Lỗi không xác định khi xóa sản phẩm '" + t.getMaSanPham() + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<SanPham> arr) {
        int dem = 0;
        if (arr == null) return 0;
        for (SanPham sp : arr) {
            // Nên thêm kiểm tra null cho sp trước khi insert
            if (sp != null) {
                 dem += this.insert(sp);
            }
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<SanPham> arr) {
        int dem = 0;
         if (arr == null) return 0;
        for (SanPham sp : arr) {
            // Nên thêm kiểm tra null cho sp trước khi delete
             if (sp != null) {
                dem += this.delete(sp);
             }
        }
        return dem;
    }

    // ----------------- CÁC HÀM NGHIỆP VỤ -----------------

    /**
     * Giảm số lượng sản phẩm khi bán hàng
     * @param maSanPham Mã sản phẩm
     * @param soLuongGiam Số lượng cần giảm
     * @param con Connection (nếu null sẽ tạo mới, nếu có thì dùng chung cho transaction)
     * @return true nếu thành công, false nếu thất bại
     */
    public boolean decreaseQuantity(String maSanPham, int soLuongGiam, Connection con) {
        boolean useExternalConnection = (con != null);
        Connection connection = con;
        PreparedStatement checkSt = null;
        PreparedStatement updateSt = null;
        ResultSet rs = null;
        
        try {
            if (!useExternalConnection) {
                connection = JDBCUtil.getConnection();
            }
            
            // Kiểm tra số lượng hiện tại
            String checkSql = "SELECT `soLuong` FROM `sanpham` WHERE `maSanPham` = ? FOR UPDATE";
            checkSt = connection.prepareStatement(checkSql);
            checkSt.setString(1, maSanPham);
            rs = checkSt.executeQuery();
            
            if (rs.next()) {
                int soLuongHienTai = rs.getInt("soLuong");
                if (soLuongHienTai < soLuongGiam) {
                    System.err.println("Không đủ số lượng. Hiện có: " + soLuongHienTai + ", cần: " + soLuongGiam);
                    return false;
                }
                
                // Giảm số lượng
                String updateSql = "UPDATE `sanpham` SET `soLuong` = `soLuong` - ? WHERE `maSanPham` = ?";
                updateSt = connection.prepareStatement(updateSql);
                updateSt.setInt(1, soLuongGiam);
                updateSt.setString(2, maSanPham);
                
                int result = updateSt.executeUpdate();
                
                return result > 0;
            } else {
                System.err.println("Không tìm thấy sản phẩm với mã: " + maSanPham);
                return false;
            }
            
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi giảm số lượng sản phẩm '" + maSanPham + "': " + e.getMessage());
            e.printStackTrace();
            return false;
        } finally {
            // Đóng resources
            try {
                if (rs != null) rs.close();
                if (checkSt != null) checkSt.close();
                if (updateSt != null) updateSt.close();
                if (!useExternalConnection && connection != null) {
                    connection.close();
                }
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }
    }
    
    /**
     * Overload: Giảm số lượng sản phẩm (tự tạo connection)
     */
    public boolean decreaseQuantity(String maSanPham, int soLuongGiam) {
        return decreaseQuantity(maSanPham, soLuongGiam, null);
    }

    /**
     * Dùng cho Trang Chủ: Lấy sách mới nhất
     */
    public ArrayList<SanPham> getNewestProducts(int limit) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        // Sử dụng biến JOIN của class
        String sql = SELECT_ALL_SQL_JOINED + " WHERE sp.`trangThai` = 1 ORDER BY sp.`namXuatBan` DESC LIMIT ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setInt(1, limit);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs));
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi lấy sách mới nhất: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi lấy sách mới nhất: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Dùng cho Trang Chủ: Lấy sách bán chạy
     * (Logic giả định: sách tồn kho ít nhất và còn hàng)
     */
    public ArrayList<SanPham> getBestsellerProducts(int limit) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        // Sử dụng biến JOIN của class
        String sql = SELECT_ALL_SQL_JOINED + " WHERE sp.`trangThai` = 1 AND sp.`soLuong` > 0 ORDER BY sp.`soLuong` ASC LIMIT ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setInt(1, limit);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs));
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi lấy sách bán chạy: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi lấy sách bán chạy: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Dùng cho Trang Chi Tiết: Lấy sách liên quan (cùng thể loại)
     */
    public ArrayList<SanPham> getRelatedProducts(String categoryId, String currentProductId, int limit) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        // Sử dụng biến JOIN của class
        String sql = SELECT_ALL_SQL_JOINED + " WHERE sp.`trangThai` = 1 AND sp.`maTheLoai` = ? AND sp.`maSanPham` != ? LIMIT ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, categoryId);
            st.setString(2, currentProductId);
            st.setInt(3, limit); // Đảm bảo đúng vị trí tham số

            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs));
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi lấy sách liên quan: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi lấy sách liên quan: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

     /**
     * Dùng cho Trang Tìm Kiếm: Tìm sách theo tên (LIKE)
     */
    public ArrayList<SanPham> searchByName(String query) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        String searchQuery = "%" + query + "%"; // Thêm ký tự đại diện %
        // Sử dụng biến JOIN của class
        String sql = SELECT_ALL_SQL_JOINED + " WHERE sp.`trangThai` = 1 AND sp.`tenSanPham` LIKE ?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, searchQuery);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs));
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi tìm kiếm sản phẩm: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi tìm kiếm sản phẩm: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }


    /**
     * Dùng cho Trang Danh Sách Sản Phẩm (Lọc và Sắp xếp)
     * Xây dựng câu SQL động dựa trên các tham số lọc.
     */
    public ArrayList<SanPham> selectAllWithFilter(String categoryId, String authorId, String publisherId, String sortOrder) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        // Sử dụng List thay vì ArrayList cho params
        List<Object> params = new ArrayList<>();

        // Bắt đầu câu SQL (sử dụng biến JOIN của class)
        StringBuilder sql = new StringBuilder(SELECT_ALL_SQL_JOINED);
        sql.append(" WHERE sp.`trangThai` = 1"); // Luôn lọc sách hiển thị

        // Thêm điều kiện lọc và lưu tham số
        if (categoryId != null && !categoryId.isEmpty()) {
            sql.append(" AND sp.`maTheLoai` = ?");
            params.add(categoryId);
        }
        if (authorId != null && !authorId.isEmpty()) {
            sql.append(" AND sp.`maTacGia` = ?");
            params.add(authorId);
        }
        if (publisherId != null && !publisherId.isEmpty()) {
            sql.append(" AND sp.`maNhaXuatBan` = ?");
            params.add(publisherId);
        }

        // Thêm sắp xếp
        if ("price-asc".equals(sortOrder)) {
            sql.append(" ORDER BY sp.`giaBan` ASC");
        } else if ("price-desc".equals(sortOrder)) {
            sql.append(" ORDER BY sp.`giaBan` DESC");
        } else if ("newest".equals(sortOrder)) {
            sql.append(" ORDER BY sp.`namXuatBan` DESC");
        } else {
            // Mặc định sắp xếp theo tên sách
            sql.append(" ORDER BY sp.`tenSanPham` ASC");
        }

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql.toString())) {

            // Set các tham số động vào PreparedStatement
            for (int i = 0; i < params.size(); i++) {
                st.setObject(i + 1, params.get(i)); // Dùng setObject cho an toàn
            }

            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs));
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi lọc/sắp xếp sản phẩm: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) { // Bắt các lỗi khác, ví dụ NullPointer khi map
            System.err.println("Lỗi không xác định khi lọc/sắp xếp sản phẩm: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    public ArrayList<SanPham> searchByKeywordCaseInsensitive(String keyword) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        // Chuẩn bị từ khóa với dấu % và chuyển thành chữ thường
        String searchQuery = "%" + keyword.toLowerCase() + "%";
        // Câu SQL dùng LOWER() trên các cột cần tìm kiếm
        String sql = SELECT_ALL_SQL_JOINED // Biến này đã JOIN sẵn các bảng sp, tg, tl, nxb
                   + " WHERE sp.`trangThai` = 1 " // Chỉ tìm sách đang hiển thị
                   // So sánh chữ thường của cột với chữ thường của từ khóa
                   + " AND (LOWER(sp.`tenSanPham`) LIKE ? OR LOWER(tg.`hoVaTen`) LIKE ? OR LOWER(tl.`tenTheLoai`) LIKE ?)";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            // Set giá trị searchQuery (đã là chữ thường) cho cả 3 tham số (?)
            st.setString(1, searchQuery); // Cho LOWER(sp.tensanpham) LIKE ?
            st.setString(2, searchQuery); // Cho LOWER(tg.hovaten) LIKE ?
            st.setString(3, searchQuery); // Cho LOWER(tl.tentheloai) LIKE ?

            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs)); // Dùng hàm map đã có
                }
            }
        } catch (SQLException e) {
             System.err.println("Lỗi SQL khi tìm kiếm (case-insensitive) sản phẩm theo từ khóa '" + keyword + "': " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi tìm kiếm (case-insensitive) sản phẩm theo từ khóa '" + keyword + "': " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public SanPham selectById(String t) {
        if (t == null || t.isEmpty()) {
            System.err.println("Lỗi: Mã sản phẩm không hợp lệ khi gọi selectById(String).");
            return null;
        }
        
        SanPham sp = new SanPham();
        sp.setMaSanPham(t);
        return selectById(sp);
    }
    
    /**
     * Lấy danh sách sách đã mua của khách hàng (dùng cho Kho Sách Của Tôi)
     * Chỉ lấy sách từ đơn hàng đã thanh toán và không bị hủy
     */
    public ArrayList<SanPham> getPurchasedBooksByCustomerId(String maKhachHang) {
        ArrayList<SanPham> ketQua = new ArrayList<>();
        String sql = "SELECT DISTINCT sp.*, tg.`maTacGia`, tg.`hoVaTen` as hoVaTen_tacgia, " +
                     "tl.`maTheLoai`, tl.`tenTheLoai`, nxb.`maNhaXuatBan`, nxb.`tenNhaXuatBan` " +
                     "FROM `sanpham` sp " +
                     "JOIN `chitietdonhang` ctdh ON sp.`maSanPham` = ctdh.`maSanPham` " +
                     "JOIN `donhang` dh ON ctdh.`maDonHang` = dh.`maDonHang` " +
                     "LEFT JOIN `tacgia` tg ON sp.`maTacGia` = tg.`maTacGia` " +
                     "LEFT JOIN `theloai` tl ON sp.`maTheLoai` = tl.`maTheLoai` " +
                     "LEFT JOIN `nhaxuatban` nxb ON sp.`maNhaXuatBan` = nxb.`maNhaXuatBan` " +
                     "WHERE dh.`maKhachHang` = ? " +
                     "AND dh.`trangThaiThanhToan` = 'Đã thanh toán' " +
                     "AND dh.`trangThai` != 'Hủy' " +
                     "ORDER BY dh.`ngayDatHang` DESC";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maKhachHang);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToSanPham(rs));
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi lấy sách đã mua: " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
            System.err.println("Lỗi không xác định khi lấy sách đã mua: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }
}