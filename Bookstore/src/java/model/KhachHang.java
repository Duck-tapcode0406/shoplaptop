package model;

import java.sql.Date;
import java.util.Objects;
import java.sql.Timestamp; // Import này là đúng

public class KhachHang {
    // Các trường cũ của bạn
    private String maKhachHang;
    private String tenDangNhap;
    private String matKhau;
    private String hoVaTen;
    private String gioiTinh;
    private String diaChi; // xa, huyen, tinh
    private String diaChiNhanHang;
    private String diaChiMuaHang;
    private Date ngaySinh;
    private String soDienThoai;
    private String email;
    private boolean dangKyNhanBangTin;
    private String maXacThuc;
    
    // ✅ SỬA LỖI (Dòng 19): Đã đổi từ "Date" sang "Timestamp"
    private Timestamp thoiGianHieuLucCuaMaXacThuc;
    
    private boolean trangThaiXacThuc;
    private String duongDanAnh;

    // --- CÁC TRƯỜNG BỔ SUNG QUAN TRỌNG ---
    private int role; // 0 = User, 1 = Admin (hoặc vai trò khác)
    private int status; // 1 = Active, 0 = Locked/Inactive
    
    // Thông tin gói cước
    private String maGoiCuoc; // Mã gói cước đang sử dụng
    private Timestamp ngayDangKy; // Ngày đăng ký gói cước
    private Timestamp ngayHetHan; // Ngày hết hạn gói cước

    public KhachHang() {
    }

    // Constructor đầy đủ (Dùng cho DAO để map từ ResultSet)
    public KhachHang(String maKhachHang, String tenDangNhap, String matKhau, String hoVaTen, String gioiTinh,
            String diaChi, String diaChiNhanHang, String diaChiMuaHang, Date ngaySinh, String soDienThoai, String email,
            boolean dangKyNhanBangTin, String maXacThuc, 
            
            // ✅ SỬA LỖI (Dòng 30): Đã đổi tham số từ "Date" sang "Timestamp"
            Timestamp thoiGianHieuLucCuaMaXacThuc,
            
            boolean trangThaiXacThuc, String duongDanAnh, int role, int status) {
        this.maKhachHang = maKhachHang;
        this.tenDangNhap = tenDangNhap;
        this.matKhau = matKhau;
        this.hoVaTen = hoVaTen;
        this.gioiTinh = gioiTinh;
        this.diaChi = diaChi;
        this.diaChiNhanHang = diaChiNhanHang;
        this.diaChiMuaHang = diaChiMuaHang;
        this.ngaySinh = ngaySinh;
        this.soDienThoai = soDienThoai;
        this.email = email;
        this.dangKyNhanBangTin = dangKyNhanBangTin;
        this.maXacThuc = maXacThuc;
        this.thoiGianHieuLucCuaMaXacThuc = thoiGianHieuLucCuaMaXacThuc; // (Giờ đã khớp kiểu)
        this.trangThaiXacThuc = trangThaiXacThuc;
        this.duongDanAnh = duongDanAnh;
        this.role = role;
        this.status = status;
    }
    
    // Getters and Setters cho tất cả các trường
    public String getMaKhachHang() {
        return maKhachHang;
    }

    public void setMaKhachHang(String maKhachHang) {
        this.maKhachHang = maKhachHang;
    }

    public String getTenDangNhap() {
        return tenDangNhap;
    }

    public void setTenDangNhap(String tenDangNhap) {
        this.tenDangNhap = tenDangNhap;
    }

    public String getMatKhau() {
        return matKhau;
    }

    public void setMatKhau(String matKhau) {
        this.matKhau = matKhau;
    }

    public String getHoVaTen() {
        return hoVaTen;
    }

    public void setHoVaTen(String hoVaTen) {
        this.hoVaTen = hoVaTen;
    }

    public String getGioiTinh() {
        return gioiTinh;
    }

    public void setGioiTinh(String gioiTinh) {
        this.gioiTinh = gioiTinh;
    }

    public String getDiaChi() {
        return diaChi;
    }

    public void setDiaChi(String diaChi) {
        this.diaChi = diaChi;
    }

    public String getDiaChiNhanHang() {
        return diaChiNhanHang;
    }

    public void setDiaChiNhanHang(String diaChiNhanHang) {
        this.diaChiNhanHang = diaChiNhanHang;
    }

    public String getDiaChiMuaHang() {
        return diaChiMuaHang;
    }

    public void setDiaChiMuaHang(String diaChiMuaHang) {
        this.diaChiMuaHang = diaChiMuaHang;
    }

    public Date getNgaySinh() {
        return ngaySinh;
    }

    public void setNgaySinh(Date ngaySinh) {
        this.ngaySinh = ngaySinh;
    }

    public String getSoDienThoai() {
        return soDienThoai;
    }

    public void setSoDienThoai(String soDienThoai) {
        this.soDienThoai = soDienThoai;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public boolean isDangKyNhanBangTin() {
        return dangKyNhanBangTin;
    }

    public void setDangKyNhanBangTin(boolean dangKyNhanBangTin) {
        this.dangKyNhanBangTin = dangKyNhanBangTin;
    }

    public String getMaXacThuc() {
        return maXacThuc;
    }

    public void setMaXacThuc(String maXacThuc) {
        this.maXacThuc = maXacThuc;
    }

    // (Hàm này giờ đã khớp với biến (field) ở Dòng 19)
    public void setThoiGianHieuLucCuaMaXacThuc(Timestamp thoiGianHieuLucCuaMaXacThuc) {
        this.thoiGianHieuLucCuaMaXacThuc = thoiGianHieuLucCuaMaXacThuc;
    }

    public Timestamp getThoiGianHieuLucCuaMaXacThuc() {
        return thoiGianHieuLucCuaMaXacThuc;
    }

    public boolean isTrangThaiXacThuc() {
        return trangThaiXacThuc;
    }

    public void setTrangThaiXacThuc(boolean trangThaiXacThuc) {
        this.trangThaiXacThuc = trangThaiXacThuc;
    }

    public String getDuongDanAnh() {
        return duongDanAnh;
    }

    public void setDuongDanAnh(String duongDanAnh) {
        this.duongDanAnh = duongDanAnh;
    }

    // Getters and Setters cho các trường MỚI
    public int getRole() {
        return role;
    }

    public void setRole(int role) {
        this.role = role;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }
    
    // Getters and Setters cho gói cước
    public String getMaGoiCuoc() {
        return maGoiCuoc;
    }
    
    public void setMaGoiCuoc(String maGoiCuoc) {
        this.maGoiCuoc = maGoiCuoc;
    }
    
    public Timestamp getNgayDangKy() {
        return ngayDangKy;
    }
    
    public void setNgayDangKy(Timestamp ngayDangKy) {
        this.ngayDangKy = ngayDangKy;
    }
    
    public Timestamp getNgayHetHan() {
        return ngayHetHan;
    }
    
    public void setNgayHetHan(Timestamp ngayHetHan) {
        this.ngayHetHan = ngayHetHan;
    }
    
    /**
     * Kiểm tra xem gói cước có còn hiệu lực không
     */
    public boolean isGoiCuocConHan() {
        if (ngayHetHan == null) {
            return false;
        }
        return ngayHetHan.after(new Timestamp(System.currentTimeMillis()));
    }
    
    /**
     * Getter method cho EL expression (JSP)
     * EL expression sẽ tự động tìm getter method này
     */
    public boolean getGoiCuocConHan() {
        return isGoiCuocConHan();
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (obj == null || getClass() != obj.getClass())
            return false;
        KhachHang that = (KhachHang) obj;
        return Objects.equals(maKhachHang, that.maKhachHang);
    }

    @Override
    public int hashCode() {
        return Objects.hash(maKhachHang);
    }
    
    // (Tôi đã xóa hàm "getHoTen()" bị lỗi của bạn ở đây vì nó thừa và sai)
}