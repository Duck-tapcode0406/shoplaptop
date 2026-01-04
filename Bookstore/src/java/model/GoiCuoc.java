package model;

import java.io.Serializable;

public class GoiCuoc implements Serializable {
    private static final long serialVersionUID = 1L;
    
    private String maGoi;
    private String tenGoi;
    private int thoiHan; // Số tháng
    private long giaTien; // Giá tiền (VND)
    private String moTa;
    private int trangThai; // 1 = Active, 0 = Inactive
    
    public GoiCuoc() {
    }
    
    public GoiCuoc(String maGoi, String tenGoi, int thoiHan, long giaTien, String moTa, int trangThai) {
        this.maGoi = maGoi;
        this.tenGoi = tenGoi;
        this.thoiHan = thoiHan;
        this.giaTien = giaTien;
        this.moTa = moTa;
        this.trangThai = trangThai;
    }
    
    // Getters and Setters
    public String getMaGoi() {
        return maGoi;
    }
    
    public void setMaGoi(String maGoi) {
        this.maGoi = maGoi;
    }
    
    public String getTenGoi() {
        return tenGoi;
    }
    
    public void setTenGoi(String tenGoi) {
        this.tenGoi = tenGoi;
    }
    
    public int getThoiHan() {
        return thoiHan;
    }
    
    public void setThoiHan(int thoiHan) {
        this.thoiHan = thoiHan;
    }
    
    public long getGiaTien() {
        return giaTien;
    }
    
    public void setGiaTien(long giaTien) {
        this.giaTien = giaTien;
    }
    
    public String getMoTa() {
        return moTa;
    }
    
    public void setMoTa(String moTa) {
        this.moTa = moTa;
    }
    
    public int getTrangThai() {
        return trangThai;
    }
    
    public void setTrangThai(int trangThai) {
        this.trangThai = trangThai;
    }
}








