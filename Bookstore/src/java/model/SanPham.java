package model;

import java.util.Objects;

public class SanPham {
    // Các trường cũ của bạn
    private String maSanPham;
    private String tenSanPham;
    private TacGia tacGia;
    private int namXuatBan;
    private double giaNhap;
    private double giaGoc;
    private double giaBan;
    private int soLuong;
    private TheLoai theLoai;
    private String ngonNgu;
    private String moTa;

    // --- CÁC TRƯỜNG BỔ SUNG QUAN TRỌNG ---
    private NhaXuatBan nhaXuatBan; // Liên kết với Nhà xuất bản
    private String hinhAnh; // URL hoặc tên file ảnh bìa
    private int trangThai; // 1 = Hiển thị, 0 = Ẩn
    private String fileEpub; // Tên file EPUB chứa nội dung sách

    public SanPham() {
    }

    // Constructor đầy đủ (Dùng cho DAO để map từ ResultSet)
    public SanPham(String maSanPham, String tenSanPham, TacGia tacGia, int namXuatBan, double giaNhap, double giaGoc,
            double giaBan, int soLuong, TheLoai theLoai, String ngonNgu, String moTa,
            NhaXuatBan nhaXuatBan, String hinhAnh, int trangThai, String fileEpub) {
        this.maSanPham = maSanPham;
        this.tenSanPham = tenSanPham;
        this.tacGia = tacGia;
        this.namXuatBan = namXuatBan;
        this.giaNhap = giaNhap;
        this.giaGoc = giaGoc;
        this.giaBan = giaBan;
        this.soLuong = soLuong;
        this.theLoai = theLoai;
        this.ngonNgu = ngonNgu;
        this.moTa = moTa;
        this.nhaXuatBan = nhaXuatBan;
        this.hinhAnh = hinhAnh;
        this.trangThai = trangThai;
        this.fileEpub = fileEpub;
    }

    // Getters and Setters cho tất cả các trường
    public String getMaSanPham() {
        return maSanPham;
    }

    public void setMaSanPham(String maSanPham) {
        this.maSanPham = maSanPham;
    }

    public String getTenSanPham() {
        return tenSanPham;
    }

    public void setTenSanPham(String tenSanPham) {
        this.tenSanPham = tenSanPham;
    }

    public TacGia getTacGia() {
        return tacGia;
    }

    public void setTacGia(TacGia tacGia) {
        this.tacGia = tacGia;
    }

    public int getNamXuatBan() {
        return namXuatBan;
    }

    public void setNamXuatBan(int namXuatBan) {
        this.namXuatBan = namXuatBan;
    }

    public double getGiaNhap() {
        return giaNhap;
    }

    public void setGiaNhap(double giaNhap) {
        this.giaNhap = giaNhap;
    }

    public double getGiaGoc() {
        return giaGoc;
    }

    public void setGiaGoc(double giaGoc) {
        this.giaGoc = giaGoc;
    }

    public double getGiaBan() {
        return giaBan;
    }

    public void setGiaBan(double giaBan) {
        this.giaBan = giaBan;
    }

    public int getSoLuong() {
        return soLuong;
    }

    public void setSoLuong(int soLuong) {
        this.soLuong = soLuong;
    }

    public TheLoai getTheLoai() {
        return theLoai;
    }

    public void setTheLoai(TheLoai theLoai) {
        this.theLoai = theLoai;
    }

    public String getNgonNgu() {
        return ngonNgu;
    }

    public void setNgonNgu(String ngonNgu) {
        this.ngonNgu = ngonNgu;
    }

    public String getMoTa() {
        return moTa;
    }

    public void setMoTa(String moTa) {
        this.moTa = moTa;
    }

    // Getters and Setters cho các trường MỚI
    public NhaXuatBan getNhaXuatBan() {
        return nhaXuatBan;
    }

    public void setNhaXuatBan(NhaXuatBan nhaXuatBan) {
        this.nhaXuatBan = nhaXuatBan;
    }

    public String getHinhAnh() {
        return hinhAnh;
    }

    public void setHinhAnh(String hinhAnh) {
        this.hinhAnh = hinhAnh;
    }

    public int getTrangThai() {
        return trangThai;
    }

    public void setTrangThai(int trangThai) {
        this.trangThai = trangThai;
    }

    public String getFileEpub() {
        return fileEpub;
    }

    public void setFileEpub(String fileEpub) {
        this.fileEpub = fileEpub;
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (obj == null || getClass() != obj.getClass())
            return false;
        SanPham that = (SanPham) obj;
        return Objects.equals(maSanPham, that.maSanPham);
    }

    @Override
    public int hashCode() {
        return Objects.hash(maSanPham);
    }
}