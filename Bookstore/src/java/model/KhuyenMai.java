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

public class KhuyenMai {
    private String maKhuyenMai; // Ví dụ: "HE2024"
    private String tenKhuyenMai; // Ví dụ: "Giảm giá hè"
    private double phanTramGiam; // Ví dụ: 0.1 (tức 10%)
    private double soTienGiamToiDa; // Ví dụ: 50000 (giảm 10% nhưng tối đa 50k)
    private Date ngayBatDau;
    private Date ngayKetThuc;
    private boolean trangThai; // True = còn hiệu lực, False = hết hiệu lực

    public KhuyenMai() {
    }

    public KhuyenMai(String maKhuyenMai, String tenKhuyenMai, double phanTramGiam, double soTienGiamToiDa, Date ngayBatDau, Date ngayKetThuc, boolean trangThai) {
        this.maKhuyenMai = maKhuyenMai;
        this.tenKhuyenMai = tenKhuyenMai;
        this.phanTramGiam = phanTramGiam;
        this.soTienGiamToiDa = soTienGiamToiDa;
        this.ngayBatDau = ngayBatDau;
        this.ngayKetThuc = ngayKetThuc;
        this.trangThai = trangThai;
    }

    // Getters and Setters
    public String getMaKhuyenMai() {
        return maKhuyenMai;
    }

    public void setMaKhuyenMai(String maKhuyenMai) {
        this.maKhuyenMai = maKhuyenMai;
    }

    public String getTenKhuyenMai() {
        return tenKhuyenMai;
    }

    public void setTenKhuyenMai(String tenKhuyenMai) {
        this.tenKhuyenMai = tenKhuyenMai;
    }

    public double getPhanTramGiam() {
        return phanTramGiam;
    }

    public void setPhanTramGiam(double phanTramGiam) {
        this.phanTramGiam = phanTramGiam;
    }

    public double getSoTienGiamToiDa() {
        return soTienGiamToiDa;
    }

    public void setSoTienGiamToiDa(double soTienGiamToiDa) {
        this.soTienGiamToiDa = soTienGiamToiDa;
    }

    public Date getNgayBatDau() {
        return ngayBatDau;
    }

    public void setNgayBatDau(Date ngayBatDau) {
        this.ngayBatDau = ngayBatDau;
    }

    public Date getNgayKetThuc() {
        return ngayKetThuc;
    }

    public void setNgayKetThuc(Date ngayKetThuc) {
        this.ngayKetThuc = ngayKetThuc;
    }

    public boolean isTrangThai() {
        return trangThai;
    }

    public void setTrangThai(boolean trangThai) {
        this.trangThai = trangThai;
    }
    
    @Override
    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (obj == null || getClass() != obj.getClass())
            return false;
        KhuyenMai that = (KhuyenMai) obj;
        return Objects.equals(maKhuyenMai, that.maKhuyenMai);
    }

    @Override
    public int hashCode() {
        return Objects.hash(maKhuyenMai);
    }
}