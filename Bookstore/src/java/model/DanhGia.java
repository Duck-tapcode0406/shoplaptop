/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package model;

/**
 *
 * @author Acer
 */
import java.sql.Date;
import java.util.Objects;

public class DanhGia {
    private String maDanhGia;
    private KhachHang khachHang; // Ai đánh giá
    private SanPham sanPham; // Đánh giá sách nào
    private int soSao; // 1, 2, 3, 4, 5 sao
    private String noiDung;
    private Date ngayDanhGia;

    public DanhGia() {
    }

    public DanhGia(String maDanhGia, KhachHang khachHang, SanPham sanPham, int soSao, String noiDung, Date ngayDanhGia) {
        this.maDanhGia = maDanhGia;
        this.khachHang = khachHang;
        this.sanPham = sanPham;
        this.soSao = soSao;
        this.noiDung = noiDung;
        this.ngayDanhGia = ngayDanhGia;
    }

    // Getters and Setters
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

    public SanPham getSanPham() {
        return sanPham;
    }

    public void setSanPham(SanPham sanPham) {
        this.sanPham = sanPham;
    }

    public int getSoSao() {
        return soSao;
    }

    public void setSoSao(int soSao) {
        this.soSao = soSao;
    }

    public String getNoiDung() {
        return noiDung;
    }

    public void setNoiDung(String noiDung) {
        this.noiDung = noiDung;
    }

    public Date getNgayDanhGia() {
        return ngayDanhGia;
    }

    public void setNgayDanhGia(Date ngayDanhGia) {
        this.ngayDanhGia = ngayDanhGia;
    }
    
    @Override
    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (obj == null || getClass() != obj.getClass())
            return false;
        DanhGia that = (DanhGia) obj;
        return Objects.equals(maDanhGia, that.maDanhGia);
    }

    @Override
    public int hashCode() {
        return Objects.hash(maDanhGia);
    }
}