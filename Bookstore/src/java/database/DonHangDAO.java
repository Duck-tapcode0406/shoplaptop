package database;

import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

import model.ChiTietDonHang; // Cần import
import model.DonHang;
import model.KhachHang;

public class DonHangDAO implements DAOInterface<DonHang> {

    /**
     * Helper: Ánh xạ 1 dòng ResultSet sang 1 đối tượng DonHang
     * (Dựa trên file DonHangDAO.java gốc của bạn)
     */
    private DonHang mapRowToDonHang(ResultSet rs) throws SQLException {
        String maDonHang = rs.getString("maDonHang");
        String maKhachHang = rs.getString("maKhachHang");
        String diaChiNguoiMua = rs.getString("diaChiMuaHang");
        String diaChiNhanHang = rs.getString("diaChiNhanHang");
        String trangThai = rs.getString("trangThai");
        String hinhThucThanhToan = rs.getString("hinhThucThanhToan");
        String trangThaiThanhToan = rs.getString("trangThaiThanhToan");
        double soTienDaThanhToan = rs.getDouble("soTienDaThanhToan");
        double soTienConThieu = rs.getDouble("soTienConThieu");
        Date ngayDatHang = rs.getDate("ngayDatHang");
        Date ngayGiaoHang = rs.getDate("ngayGiaoHang");

        KhachHang kh = new KhachHang();
        kh.setMaKhachHang(maKhachHang);
        
        // Chuyển đổi tên cột CSDL sang tên thuộc tính Model
        // (Model: diaChiMuaHang, file CSDL: diachinguoimua)
        return new DonHang(maDonHang, kh, diaChiNguoiMua, diaChiNhanHang, trangThai,
                           hinhThucThanhToan, trangThaiThanhToan, soTienDaThanhToan,
                           soTienConThieu, ngayDatHang, ngayGiaoHang);
    }

    @Override
    public ArrayList<DonHang> selectAll() {
        ArrayList<DonHang> ketQua = new ArrayList<>();
        // JOIN với khachhang để lấy đầy đủ thông tin khách hàng
        String sql = "SELECT dh.*, kh.`hoVaTen`, kh.`email`, kh.`soDienThoai` " +
                     "FROM `donhang` dh " +
                     "LEFT JOIN `khachhang` kh ON dh.`maKhachHang` = kh.`maKhachHang` " +
                     "ORDER BY dh.`ngayDatHang` DESC";
        
        // Nâng cấp: Dùng try-with-resources để tự động đóng kết nối
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                DonHang dh = mapRowToDonHang(rs);
                
                // Load đầy đủ thông tin khách hàng từ JOIN
                if (dh.getKhachHang() != null) {
                    try {
                        dh.getKhachHang().setHoVaTen(rs.getString("hoVaTen"));
                    } catch (SQLException e) {
                        try {
                            dh.getKhachHang().setHoVaTen(rs.getString("hovaten"));
                        } catch (SQLException e2) {}
                    }
                    
                    try {
                        dh.getKhachHang().setEmail(rs.getString("email"));
                    } catch (SQLException e) {}
                    
                    try {
                        dh.getKhachHang().setSoDienThoai(rs.getString("soDienThoai"));
                    } catch (SQLException e) {
                        try {
                            dh.getKhachHang().setSoDienThoai(rs.getString("sodienthoai"));
                        } catch (SQLException e2) {}
                    }
                    
                    // Đã xóa phần bậc hội viên (không còn sử dụng)
                }
                
                ketQua.add(dh);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    
    /**
     * Tìm kiếm đơn hàng theo tên sách hoặc tên tác giả
     */
    public ArrayList<DonHang> searchByProductOrAuthor(String searchKeyword) {
        ArrayList<DonHang> ketQua = new ArrayList<>();
        String searchPattern = "%" + searchKeyword + "%";
        
        String sql = "SELECT DISTINCT dh.*, kh.`hoVaTen`, kh.`email`, kh.`soDienThoai` " +
                     "FROM `donhang` dh " +
                     "LEFT JOIN `khachhang` kh ON dh.`maKhachHang` = kh.`maKhachHang` " +
                     "JOIN `chitietdonhang` ctdh ON dh.`maDonHang` = ctdh.`maDonHang` " +
                     "JOIN `sanpham` sp ON ctdh.`maSanPham` = sp.`maSanPham` " +
                     "LEFT JOIN `tacgia` tg ON sp.`maTacGia` = tg.`maTacGia` " +
                     "WHERE sp.`tenSanPham` LIKE ? OR tg.`hoVaTen` LIKE ? " +
                     "ORDER BY dh.`ngayDatHang` DESC";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, searchPattern);
            st.setString(2, searchPattern);
            
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    DonHang dh = mapRowToDonHang(rs);
                    
                    // Load đầy đủ thông tin khách hàng từ JOIN
                    if (dh.getKhachHang() != null) {
                        try {
                            dh.getKhachHang().setHoVaTen(rs.getString("hoVaTen"));
                            dh.getKhachHang().setEmail(rs.getString("email"));
                            dh.getKhachHang().setSoDienThoai(rs.getString("soDienThoai"));
                            // Đã xóa phần bậc hội viên (không còn sử dụng)
                        } catch (SQLException e) {}
                    }
                    
                    ketQua.add(dh);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
 
    public DonHang selectById(String t) {
        DonHang ketQua = null;
        // JOIN với khachhang để lấy đầy đủ thông tin khách hàng
        String sql = "SELECT dh.*, kh.`hoVaTen`, kh.`email`, kh.`soDienThoai` " +
                     "FROM `donhang` dh " +
                     "LEFT JOIN `khachhang` kh ON dh.`maKhachHang` = kh.`maKhachHang` " +
                     "WHERE dh.`maDonHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToDonHang(rs);
                    
                    // Load đầy đủ thông tin khách hàng từ JOIN
                    if (ketQua.getKhachHang() != null) {
                        try {
                            ketQua.getKhachHang().setHoVaTen(rs.getString("hoVaTen"));
                        } catch (SQLException e) {
                            try {
                                ketQua.getKhachHang().setHoVaTen(rs.getString("hovaten"));
                            } catch (SQLException e2) {
                                // Bỏ qua nếu không có
                            }
                        }
                        
                        try {
                            ketQua.getKhachHang().setEmail(rs.getString("email"));
                        } catch (SQLException e) {
                            // Bỏ qua nếu không có
                        }
                        
                        try {
                            ketQua.getKhachHang().setSoDienThoai(rs.getString("soDienThoai"));
                        } catch (SQLException e) {
                            try {
                                ketQua.getKhachHang().setSoDienThoai(rs.getString("sodienthoai"));
                            } catch (SQLException e2) {
                                // Bỏ qua nếu không có
                            }
                        }
                    }
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectById DonHang: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Phương thức insert() GỐC.
     * Tự mở và đóng Connection.
     * Được nâng cấp để gọi hàm insert(t, con) nạp chồng.
     */
    @Override
    public int insert(DonHang t) {
        int ketQua = 0;
        try (Connection con = JDBCUtil.getConnection()) {
            ketQua = this.insert(t, con); // Gọi hàm nạp chồng
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * [NÂNG CẤP] Phương thức insert() NẠP CHỒNG (Overloaded)
     * Nhận Connection từ bên ngoài để phục vụ Giao dịch (Transaction).
     * Sẽ được gọi bởi insertDonHangVaChiTiet()
     */
    public int insert(DonHang t, Connection con) throws SQLException {
        // Dùng tên cột CSDL chính xác từ schema
        String sql = "INSERT INTO donhang (maDonHang, maKhachHang, diaChiMuaHang, diaChiNhanHang, trangThai, " +
                     "hinhThucThanhToan, trangThaiThanhToan, soTienDaThanhToan, soTienConThieu, ngayDatHang) " +
                     "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        int ketQua = 0;
        // Không dùng try-with-resources cho Connection (vì nó được truyền vào)
        // PreparedStatement vẫn nên dùng try-with-resources
        try (PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaDonHang());
            st.setString(2, t.getKhachHang().getMaKhachHang());
            st.setString(3, t.getDiaChiMuaHang());
            st.setString(4, t.getDiaChiNhanHang());
            st.setString(5, t.getTrangThai());
            st.setString(6, t.getHinhThucThanhToan());
            st.setString(7, t.getTrangThaiThanhToan());
            st.setDouble(8, t.getSoTienDaThanhToan());
            st.setDouble(9, t.getSoTienConThieu());
            st.setDate(10, t.getNgayDatHang());
            
            ketQua = st.executeUpdate();
        }
        // Không được đóng Connection (con.close()) ở đây
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<DonHang> arr) {
        int dem = 0;
        for (DonHang donHang : arr) {
            dem += this.insert(donHang);
        }
        return dem;
    }

    @Override
    public int delete(DonHang t) {
        int ketQua = 0;
        String sql = "DELETE FROM donhang WHERE maDonHang = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaDonHang());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int deleteAll(ArrayList<DonHang> arr) {
        int dem = 0;
        for (DonHang donHang : arr) {
            dem += this.delete(donHang);
        }
        return dem;
    }

    @Override
    public int update(DonHang t) {
        int ketQua = 0;
        // Dùng tên cột CSDL chính xác từ schema
        String sql = "UPDATE donhang SET maKhachHang=?, diaChiMuaHang=?, diaChiNhanHang=?, " +
                     "trangThai=?, hinhThucThanhToan=?, trangThaiThanhToan=?, soTienDaThanhToan=?, soTienConThieu=?, " +
                     "ngayDatHang=?, ngayGiaoHang=? WHERE maDonHang=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getKhachHang().getMaKhachHang());
            st.setString(2, t.getDiaChiMuaHang());
            st.setString(3, t.getDiaChiNhanHang());
            st.setString(4, t.getTrangThai());
            st.setString(5, t.getHinhThucThanhToan());
            st.setString(6, t.getTrangThaiThanhToan());
            st.setDouble(7, t.getSoTienDaThanhToan());
            st.setDouble(8, t.getSoTienConThieu());
            st.setDate(9, t.getNgayDatHang());
            st.setDate(10, t.getNgayGiaoHang());
            st.setString(11, t.getMaDonHang()); // Tham số WHERE

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    
    // ----------------- HÀM NÂNG CẤP QUAN TRỌNG NHẤT -----------------

    /**
     * [HÀM MỚI] Thực thi Giao dịch (Transaction)
     * Thêm Đơn hàng VÀ tất cả Chi tiết đơn hàng.
     * Đảm bảo cả hai cùng thành công, hoặc cả hai cùng thất bại.
     * Đây là hàm mà DatHangServlet sẽ gọi.
     */
    public boolean insertDonHangVaChiTiet(DonHang dh, ArrayList<ChiTietDonHang> listCTDH) {
        Connection con = null;
        try {
            // Lấy 1 kết nối chung
            con = JDBCUtil.getConnection();
            
            // --- BẮT ĐẦU GIAO DỊCH ---
            // 1. Tắt chế độ tự động commit
            con.setAutoCommit(false);
            
            // 2. Thêm Đơn Hàng (dùng hàm insert nạp chồng)
            DonHangDAO donHangDAO = new DonHangDAO();
            donHangDAO.insert(dh, con); // Truyền kết nối (con) vào
            
            // 3. Thêm TẤT CẢ Chi Tiết Đơn Hàng (dùng hàm insert nạp chồng)
            ChiTietDonHangDAO ctdhDAO = new ChiTietDonHangDAO();
            for (ChiTietDonHang ctdh : listCTDH) {
                ctdhDAO.insert(ctdh, con); // Truyền cùng kết nối (con) vào
            }
            
            // 4. Cập nhật số lượng sản phẩm (giảm số lượng tồn kho)
            database.SanPhamDAO sanPhamDAO = new database.SanPhamDAO();
            for (ChiTietDonHang ctdh : listCTDH) {
                String maSanPham = ctdh.getSanPham().getMaSanPham();
                int soLuongMua = (int) ctdh.getSoLuong(); // Ép kiểu từ double sang int
                boolean updateSuccess = sanPhamDAO.decreaseQuantity(maSanPham, soLuongMua, con);
                if (!updateSuccess) {
                    throw new SQLException("Không thể cập nhật số lượng cho sản phẩm: " + maSanPham);
                }
            }
            
            // 5. Nếu không có lỗi, commit giao dịch
            con.commit();
            return true;
            // --- KẾT THÚC GIAO DỊCH ---
            
        } catch (SQLException e) {
            System.err.println("LỖI GIAO DỊCH: Đang thực hiện Rollback...");
            e.printStackTrace();
            // 5. Nếu có bất kỳ lỗi nào, rollback (hoàn tác)
            try {
                if (con != null) {
                    con.rollback();
                }
            } catch (SQLException e1) {
                e1.printStackTrace();
            }
            return false;
            
        } finally {
            // 6. Luôn luôn phải đóng kết nối và bật lại AutoCommit
            try {
                if (con != null) {
                    con.setAutoCommit(true); // Trả lại trạng thái mặc định
                    JDBCUtil.closeConnection(con); // Đóng kết nối
                }
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }
    }
    
    /**
     * [NẠP CHỒNG] Thực thi Giao dịch với Connection từ bên ngoài
     * Dùng khi cần kết hợp với các thao tác khác trong cùng transaction
     */
    public boolean insertDonHangVaChiTiet(DonHang dh, ArrayList<ChiTietDonHang> listCTDH, Connection con) throws SQLException {
        // 1. Thêm Đơn Hàng
        this.insert(dh, con);
        
        // 2. Thêm TẤT CẢ Chi Tiết Đơn Hàng
        ChiTietDonHangDAO ctdhDAO = new ChiTietDonHangDAO();
        for (ChiTietDonHang ctdh : listCTDH) {
            ctdhDAO.insert(ctdh, con);
        }
        
        // 3. Cập nhật số lượng sản phẩm (giảm số lượng tồn kho)
        database.SanPhamDAO sanPhamDAO = new database.SanPhamDAO();
        for (ChiTietDonHang ctdh : listCTDH) {
            String maSanPham = ctdh.getSanPham().getMaSanPham();
            int soLuongMua = (int) ctdh.getSoLuong();
            boolean updateSuccess = sanPhamDAO.decreaseQuantity(maSanPham, soLuongMua, con);
            if (!updateSuccess) {
                throw new SQLException("Không thể cập nhật số lượng cho sản phẩm: " + maSanPham);
            }
        }
        
        return true;
    }
    // Thêm 3 phương thức này vào file database/DonHangDAO.java

    /**
     * [MỚI] Lấy tất cả đơn hàng của 1 khách hàng
     * Dùng cho trang LichSuDonHangServlet
     */
    public ArrayList<DonHang> selectAllByCustomerId(String maKhachHang) {
        ArrayList<DonHang> ketQua = new ArrayList<>();
        // Sử dụng hàm mapRowToDonHang đã có
        String sql = "SELECT * FROM donhang WHERE maKhachHang = ? ORDER BY ngayDatHang DESC";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maKhachHang);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    ketQua.add(mapRowToDonHang(rs)); // Dùng hàm mapRow đã có
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    
    /**
     * [MỚI] Cập nhật trạng thái đơn hàng (ví dụ: "Đã hủy")
     * Dùng cho HuyDonHangServlet
     */
    public int updateStatus(String maDonHang, String trangThai) {
        int ketQua = 0;
        String sql = "UPDATE donhang SET trangThai = ? WHERE maDonHang = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, trangThai);
            st.setString(2, maDonHang);
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    
    /**
     * [MỚI] Kiểm tra xem khách hàng đã mua sản phẩm này chưa
     * Cho phép đánh giá khi đơn hàng đã được thanh toán (không cần chờ giao hàng)
     * Dùng cho ChiTietSanPhamServlet và DangDanhGiaServlet (Bảo mật)
     */
    public boolean checkIfCustomerBoughtProduct(String maKhachHang, String maSanPham) {
        // Cập nhật điều kiện để phù hợp với logic mới: đơn hàng tự động hoàn tất với trangThai = 'Hoàn tất'
        String sql = "SELECT 1 FROM donhang dh " +
                     "JOIN chitietdonhang ctdh ON dh.maDonHang = ctdh.maDonHang " +
                     "WHERE dh.maKhachHang = ? AND ctdh.maSanPham = ? " +
                     "AND (dh.trangThai = 'Hoàn tất' OR dh.trangThai = 'Đã giao' OR " +
                     "     (dh.trangThaiThanhToan = 'Đã thanh toán' AND dh.trangThai != 'Hủy' AND dh.trangThai != 'Đã hủy'))";
                     
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maKhachHang);
            st.setString(2, maSanPham);
            
            try (ResultSet rs = st.executeQuery()) {
                return rs.next(); // Trả về true nếu tìm thấy (đã mua)
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi kiểm tra khách hàng đã mua sản phẩm: " + e.getMessage());
            e.printStackTrace();
        }
        return false;
    }

    public DonHang selectById(DonHang donHang) {
        throw new UnsupportedOperationException("Not supported yet."); // Generated from nbfs://nbhost/SystemFileSystem/Templates/Classes/Code/GeneratedMethodBody
    }
}