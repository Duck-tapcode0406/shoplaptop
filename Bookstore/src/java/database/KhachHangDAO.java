package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.ArrayList;

import model.KhachHang;
import util.PasswordUtil;

public class KhachHangDAO implements DAOInterface<KhachHang> {

    private KhachHang mapRowToKhachHang(ResultSet rs) throws SQLException {
        KhachHang kh = new KhachHang();
        try {
            kh.setMaKhachHang(rs.getString("maKhachHang"));
        } catch (SQLException e) {
            kh.setMaKhachHang(rs.getString("makhachhang"));
        }
        
        try {
            kh.setTenDangNhap(rs.getString("tenDangNhap"));
        } catch (SQLException e) {
            kh.setTenDangNhap(rs.getString("tendangnhap"));
        }
        
        try {
            kh.setMatKhau(rs.getString("matKhau"));
        } catch (SQLException e) {
            kh.setMatKhau(rs.getString("matkhau"));
        }
        
        try {
            kh.setHoVaTen(rs.getString("hoVaTen"));
        } catch (SQLException e) {
            kh.setHoVaTen(rs.getString("hoten"));
        }
        
        try {
            kh.setGioiTinh(rs.getString("gioiTinh"));
        } catch (SQLException e) {
            kh.setGioiTinh(rs.getString("gioitinh"));
        }
        
        try {
            kh.setDiaChi(rs.getString("diaChi"));
        } catch (SQLException e) {
            kh.setDiaChi(rs.getString("diachi"));
        }
        
        try {
            kh.setDiaChiNhanHang(rs.getString("diaChiNhanHang"));
        } catch (SQLException e) {
            kh.setDiaChiNhanHang(rs.getString("diachinhanhang"));
        }
        
        try {
            kh.setDiaChiMuaHang(rs.getString("diaChiMuaHang"));
        } catch (SQLException e) {
            kh.setDiaChiMuaHang(rs.getString("diachimuahang"));
        }
        
        try {
            kh.setNgaySinh(rs.getDate("ngaySinh"));
        } catch (SQLException e) {
            kh.setNgaySinh(rs.getDate("ngaysinh"));
        }
        
        try {
            kh.setSoDienThoai(rs.getString("soDienThoai"));
        } catch (SQLException e) {
            kh.setSoDienThoai(rs.getString("sodienthoai"));
        }
        
        // Email - bắt buộc phải có
        try {
            kh.setEmail(rs.getString("email"));
        } catch (SQLException e) {
            kh.setEmail(null);
        }
        
        try {
            kh.setDangKyNhanBangTin(rs.getBoolean("dangKyNhanBangTin"));
        } catch (SQLException e) {
            try {
                kh.setDangKyNhanBangTin(rs.getBoolean("dangkinhanbangtin"));
            } catch (SQLException e2) {
                kh.setDangKyNhanBangTin(false);
            }
        }
        
        // MaXacThuc - có thể null
        try {
            kh.setMaXacThuc(rs.getString("maXacThuc"));
        } catch (SQLException e) {
            try {
                kh.setMaXacThuc(rs.getString("maxacthuc"));
            } catch (SQLException e2) {
                kh.setMaXacThuc(null);
            }
        }
        
        // ThoiGianHieuLucCuaMaXacThuc - có thể null
        try {
            kh.setThoiGianHieuLucCuaMaXacThuc(rs.getTimestamp("thoiGianHieuLucCuaMaXacThuc"));
        } catch (SQLException e) {
            try {
                kh.setThoiGianHieuLucCuaMaXacThuc(rs.getTimestamp("thoigianhieulucmaxacthuc"));
            } catch (SQLException e2) {
                kh.setThoiGianHieuLucCuaMaXacThuc(null);
            }
        }
        
        // Lấy trangThaiXacThuc và duongDanAnh (có kiểm tra cột tồn tại)
        if (hasColumn(rs, "trangthaixacthuc") || hasColumn(rs, "trangThaiXacThuc")) {
            try {
                kh.setTrangThaiXacThuc(rs.getBoolean("trangthaixacthuc"));
            } catch (SQLException e) {
                kh.setTrangThaiXacThuc(rs.getBoolean("trangThaiXacThuc"));
            }
        } else {
            kh.setTrangThaiXacThuc(false);
        }
        
        if (hasColumn(rs, "duongdananh") || hasColumn(rs, "duongDanAnh")) {
            try {
                kh.setDuongDanAnh(rs.getString("duongdananh"));
            } catch (SQLException e) {
                kh.setDuongDanAnh(rs.getString("duongDanAnh"));
            }
        } else {
            kh.setDuongDanAnh(null);
        }
        
        // Lấy status và role (có kiểm tra cột tồn tại)
        if (hasColumn(rs, "status") || hasColumn(rs, "Status")) {
            kh.setStatus(rs.getInt("status"));
        } else {
            kh.setStatus(1);
        }
        
        if (hasColumn(rs, "role") || hasColumn(rs, "Role")) {
            kh.setRole(rs.getInt("role"));
        } else {
            kh.setRole(0);
        }
        
        // Đã xóa phần bậc hội viên và điểm tích lũy (không còn sử dụng)
        
        // Lấy thông tin gói cước (nếu có) - Xử lý an toàn với try-catch toàn bộ
        // Khởi tạo giá trị mặc định trước
        kh.setMaGoiCuoc(null);
        kh.setNgayDangKy(null);
        kh.setNgayHetHan(null);
        
        // Xử lý an toàn: thử lấy giá trị trực tiếp, nếu lỗi thì bỏ qua
        try {
            // Thử lấy maGoiCuoc với các tên cột khác nhau
            try {
                String maGoi = rs.getString("maGoiCuoc");
                if (maGoi != null && !maGoi.trim().isEmpty()) {
                    kh.setMaGoiCuoc(maGoi);
                }
            } catch (SQLException e) {
                try {
                    String maGoi = rs.getString("magoicuoc");
                    if (maGoi != null && !maGoi.trim().isEmpty()) {
                        kh.setMaGoiCuoc(maGoi);
                    }
                } catch (SQLException e2) {
                    // Cột không tồn tại hoặc giá trị null, giữ nguyên null
                }
            }
        } catch (Exception e) {
            // Bỏ qua lỗi
        }
        
        try {
            // Thử lấy ngayDangKy với các tên cột khác nhau
            try {
                java.sql.Timestamp ngayDangKy = rs.getTimestamp("ngayDangKy");
                kh.setNgayDangKy(ngayDangKy);
            } catch (SQLException e) {
                try {
                    java.sql.Timestamp ngayDangKy = rs.getTimestamp("ngaydangky");
                    kh.setNgayDangKy(ngayDangKy);
                } catch (SQLException e2) {
                    // Cột không tồn tại hoặc giá trị null, giữ nguyên null
                }
            }
        } catch (Exception e) {
            // Bỏ qua lỗi
        }
        
        try {
            // Thử lấy ngayHetHan với các tên cột khác nhau
            try {
                java.sql.Timestamp ngayHetHan = rs.getTimestamp("ngayHetHan");
                kh.setNgayHetHan(ngayHetHan);
            } catch (SQLException e) {
                try {
                    java.sql.Timestamp ngayHetHan = rs.getTimestamp("ngayhethan");
                    kh.setNgayHetHan(ngayHetHan);
                } catch (SQLException e2) {
                    // Cột không tồn tại hoặc giá trị null, giữ nguyên null
                }
            }
        } catch (Exception e) {
            // Bỏ qua lỗi
        }
        
        return kh;
    }

    private boolean hasColumn(ResultSet rs, String columnName) throws SQLException {
        try {
            rs.findColumn(columnName);
            return true;
        } catch (SQLException sqlex) {
            return false;
        }
    }

    @Override
    public ArrayList<KhachHang> selectAll() {
        ArrayList<KhachHang> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM `khachhang`";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            
            while (rs.next()) {
                ketQua.add(mapRowToKhachHang(rs));
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectAll KhachHang: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    public KhachHang selectById(KhachHang t) {
        if (t == null || t.getMaKhachHang() == null) {
            return null;
        }
        return selectById(t.getMaKhachHang());
    }

    /**
     * Lấy khách hàng theo MÃ KHÁCH HÀNG (String)
     */
    public KhachHang selectById(String maKhachHang) {
        KhachHang ketQua = null;
        String sql = "SELECT * FROM `khachhang` WHERE `maKhachHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maKhachHang);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToKhachHang(rs);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectById KhachHang: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Tìm Khách Hàng bằng TÊN ĐĂNG NHẬP
     */
    public KhachHang selectByUsername(String username) {
        KhachHang ketQua = null;
        String sql = "SELECT * FROM `khachhang` WHERE `tenDangNhap`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, username);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToKhachHang(rs);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectByUsername: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Tìm Khách Hàng bằng EMAIL
     */
    public KhachHang selectByEmail(String email) {
        KhachHang ketQua = null;
        String sql = "SELECT * FROM `khachhang` WHERE `email`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, email);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToKhachHang(rs);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectByEmail: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Kiểm tra Tên đăng nhập đã tồn tại chưa
     */
    public boolean checkUsernameExists(String username) {
        String sql = "SELECT 1 FROM `khachhang` WHERE `tenDangNhap`=? LIMIT 1";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, username);
            try (ResultSet rs = st.executeQuery()) {
                return rs.next();
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi checkUsernameExists: " + e.getMessage());
            e.printStackTrace();
            return true; 
        }
    }

    /**
     * Kiểm tra Email đã tồn tại chưa
     */
    public boolean checkEmailExists(String email) {
        String sql = "SELECT 1 FROM `khachhang` WHERE `email`=? LIMIT 1";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, email);
            try (ResultSet rs = st.executeQuery()) {
                return rs.next();
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi checkEmailExists: " + e.getMessage());
            e.printStackTrace();
            return true;
        }
    }

    /**
     * Thêm khách hàng mới + HASH MẬT KHẨU
     */
    @Override
    public int insert(KhachHang t) {
        int ketQua = 0;
        
        if (t == null || t.getMatKhau() == null) {
            System.err.println("Lỗi insert KhachHang: Đối tượng hoặc mật khẩu null.");
            return 0;
        }

        // Hash mật khẩu TRƯỚC KHI insert
        String matKhauMaHoa = PasswordUtil.hash(t.getMatKhau());
        if (matKhauMaHoa == null) {
            System.err.println("Lỗi insert KhachHang: Không thể hash mật khẩu.");
            return 0;
        }

        String sql = "INSERT INTO `khachhang` (`maKhachHang`, `tenDangNhap`, `matKhau`, `hoVaTen`, `gioiTinh`, " +
                     "`diaChi`, `diaChiNhanHang`, `diaChiMuaHang`, `ngaySinh`, `soDienThoai`, `email`, " +
                     "`dangKyNhanBangTin`, `status`, `role`) " +
                     "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getMaKhachHang());
            st.setString(2, t.getTenDangNhap());
            st.setString(3, matKhauMaHoa); 
            st.setString(4, t.getHoVaTen());
            st.setString(5, t.getGioiTinh());
            st.setString(6, t.getDiaChi());
            st.setString(7, t.getDiaChiNhanHang());
            st.setString(8, t.getDiaChiMuaHang());
            st.setDate(9, t.getNgaySinh());
            st.setString(10, t.getSoDienThoai());
            st.setString(11, t.getEmail());
            st.setBoolean(12, t.isDangKyNhanBangTin());
            st.setInt(13, t.getStatus());
            st.setInt(14, t.getRole());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi insert KhachHang: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * UPDATE - Cập nhật thông tin hồ sơ (KHÔNG bao gồm mật khẩu, email, username)
     */
    @Override
    public int update(KhachHang t) {
        int ketQua = 0;
        
        if (t == null || t.getMaKhachHang() == null) {
            return 0;
        }

        String sql = "UPDATE `khachhang` SET `hoVaTen`=?, `gioiTinh`=?, `diaChi`=?, `diaChiNhanHang`=?, " +
                     "`diaChiMuaHang`=?, `ngaySinh`=?, `soDienThoai`=?, `dangKyNhanBangTin`=?, `duongDanAnh`=? " +
                     "WHERE `maKhachHang`=?";

        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, t.getHoVaTen());
            st.setString(2, t.getGioiTinh());
            st.setString(3, t.getDiaChi());
            st.setString(4, t.getDiaChiNhanHang());
            st.setString(5, t.getDiaChiMuaHang());
            st.setDate(6, t.getNgaySinh());
            st.setString(7, t.getSoDienThoai());
            st.setBoolean(8, t.isDangKyNhanBangTin());
            st.setString(9, t.getDuongDanAnh());
            st.setString(10, t.getMaKhachHang());

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi update KhachHang: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Cập nhật MÃ XÁC THỰC và THỜI GIAN HẾT HẠN (dùng Timestamp)
     * Dùng cho Quên mật khẩu
     */
    public int updateAuthCode(String email, String code, Timestamp expiryTimestamp) {
        int ketQua = 0;
        String sql = "UPDATE `khachhang` SET `maXacThuc`=?, `thoiGianHieuLucCuaMaXacThuc`=? WHERE `email`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {

            st.setString(1, code);
            st.setTimestamp(2, expiryTimestamp); // Dùng Timestamp
            st.setString(3, email);

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi updateAuthCode: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Tìm Khách Hàng bằng Email và Token (còn hợp lệ)
     * Dùng để xác thực mã trước khi đặt lại mật khẩu
     */
    public KhachHang selectByEmailAndToken(String email, String token) {
        KhachHang ketQua = null;
        String sql = "SELECT * FROM `khachhang` WHERE `email`=? AND `maXacThuc`=? AND `thoiGianHieuLucCuaMaXacThuc` > NOW()";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, email);
            st.setString(2, token);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToKhachHang(rs);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectByEmailAndToken: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Cập nhật MẬT KHẨU MỚI BẰNG EMAIL (Tự động hash và vô hiệu hóa mã xác thực)
     * Dùng cho Đặt lại mật khẩu
     */
    public int updatePasswordByEmail(String email, String newPassword) {
        int ketQua = 0;
        
        if (newPassword == null || newPassword.isEmpty()) {
            return 0;
        }

        String hashedPassword = PasswordUtil.hash(newPassword);
        if (hashedPassword == null) {
            return 0;
        }

        String sql = "UPDATE `khachhang` SET `matKhau`=?, `maXacThuc`=NULL, `thoiGianHieuLucCuaMaXacThuc`=NULL WHERE `email`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, hashedPassword);
            st.setString(2, email);
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi updatePasswordByEmail: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Cập nhật MẬT KHẨU BẰNG MÃ KHÁCH HÀNG (Tự động hash)
     * Dùng cho Đổi mật khẩu khi đã đăng nhập
     */
    public int updatePassword(String maKhachHang, String newPassword) {
        int ketQua = 0;
        
        if (newPassword == null || newPassword.isEmpty()) {
            return 0;
        }

        String hashedPassword = PasswordUtil.hash(newPassword);
        if (hashedPassword == null) {
            return 0;
        }

        String sql = "UPDATE `khachhang` SET `matKhau`=? WHERE `maKhachHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, hashedPassword);
            st.setString(2, maKhachHang);
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi updatePassword: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int delete(KhachHang t) {
        int ketQua = 0;
        
        if (t == null || t.getMaKhachHang() == null) {
            return 0;
        }
        
        String sql = "DELETE FROM `khachhang` WHERE `maKhachHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaKhachHang());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi delete KhachHang: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<KhachHang> arr) {
        int dem = 0;
        if (arr == null) return 0;
        
        for (KhachHang kh : arr) {
            if (kh != null) {
                dem += this.insert(kh);
            }
        }
        return dem;
    }

    @Override
    public int deleteAll(ArrayList<KhachHang> arr) {
        int dem = 0;
        if (arr == null) return 0;
        
        for (KhachHang kh : arr) {
            if (kh != null) {
                dem += this.delete(kh);
            }
        }
        return dem;
    }

    /**
     * Cập nhật STATUS (khóa/mở khóa tài khoản)
     * Dùng cho Admin quản lý người dùng
     */
    public int updateStatus(String maKhachHang, int status) {
        int ketQua = 0;
        if (maKhachHang == null || maKhachHang.isEmpty()) {
            return 0;
        }

        String sql = "UPDATE `khachhang` SET `status`=? WHERE `maKhachHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setInt(1, status);
            st.setString(2, maKhachHang);
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi updateStatus: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Lấy danh sách tất cả người dùng (không phải admin)
     */
    public ArrayList<KhachHang> selectAllUsers() {
        ArrayList<KhachHang> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM `khachhang` WHERE `role` = 0 ORDER BY `maKhachHang` DESC";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            
            while (rs.next()) {
                ketQua.add(mapRowToKhachHang(rs));
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectAllUsers: " + e.getMessage());
            e.printStackTrace();
        }
        return ketQua;
    }
}