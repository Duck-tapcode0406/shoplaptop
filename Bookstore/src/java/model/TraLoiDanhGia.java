package model;

import java.sql.Timestamp;

/**
 * Model cho trả lời đánh giá
 */
public class TraLoiDanhGia {
    private String maTraLoi;
    private String maDanhGia;
    private KhachHang khachHang;
    private String noiDung;
    private Timestamp ngayTraLoi;

    public TraLoiDanhGia() {
    }

    public TraLoiDanhGia(String maTraLoi, String maDanhGia, KhachHang khachHang, String noiDung, Timestamp ngayTraLoi) {
        this.maTraLoi = maTraLoi;
        this.maDanhGia = maDanhGia;
        this.khachHang = khachHang;
        this.noiDung = noiDung;
        this.ngayTraLoi = ngayTraLoi;
    }

    public String getMaTraLoi() {
        return maTraLoi;
    }

    public void setMaTraLoi(String maTraLoi) {
        this.maTraLoi = maTraLoi;
    }

    public String getMaDanhGia() {
        return maDanhGia;
    }

    public void setMaDanhGia(String maDanhGia) {
        this.maDanhGia = maDanhGia;
    }

    public KhachHang getKhachHang() {
        return khachHang;
    }

    public void setKhachHang(KhachHang khachHang) {
        this.khachHang = khachHang;
    }

    public String getNoiDung() {
        return noiDung;
    }

    public void setNoiDung(String noiDung) {
        this.noiDung = noiDung;
    }

    public Timestamp getNgayTraLoi() {
        return ngayTraLoi;
    }

    public void setNgayTraLoi(Timestamp ngayTraLoi) {
        this.ngayTraLoi = ngayTraLoi;
    }
}
















