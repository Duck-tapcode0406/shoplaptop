package model;

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */

/**
 *
 * @author Acer
 */
import java.util.Objects;

public class NhaXuatBan {
    private String maNhaXuatBan;
    private String tenNhaXuatBan;
    private String diaChi;
    private String soDienThoai;

    public NhaXuatBan() {
    }

    public NhaXuatBan(String maNhaXuatBan, String tenNhaXuatBan, String diaChi, String soDienThoai) {
        this.maNhaXuatBan = maNhaXuatBan;
        this.tenNhaXuatBan = tenNhaXuatBan;
        this.diaChi = diaChi;
        this.soDienThoai = soDienThoai;
    }

    // Getters and Setters
    public String getMaNhaXuatBan() {
        return maNhaXuatBan;
    }

    public void setMaNhaXuatBan(String maNhaXuatBan) {
        this.maNhaXuatBan = maNhaXuatBan;
    }

    public String getTenNhaXuatBan() {
        return tenNhaXuatBan;
    }

    public void setTenNhaXuatBan(String tenNhaXuatBan) {
        this.tenNhaXuatBan = tenNhaXuatBan;
    }

    public String getDiaChi() {
        return diaChi;
    }

    public void setDiaChi(String diaChi) {
        this.diaChi = diaChi;
    }

    public String getSoDienThoai() {
        return soDienThoai;
    }

    public void setSoDienThoai(String soDienThoai) {
        this.soDienThoai = soDienThoai;
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj)
            return true;
        if (obj == null || getClass() != obj.getClass())
            return false;
        NhaXuatBan that = (NhaXuatBan) obj;
        return Objects.equals(maNhaXuatBan, that.maNhaXuatBan);
    }

    @Override
    public int hashCode() {
        return Objects.hash(maNhaXuatBan);
    }
}