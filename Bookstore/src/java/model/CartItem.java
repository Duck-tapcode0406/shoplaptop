package model;

import java.io.Serializable;

// Serializable là cần thiết để lưu an toàn trong Session
public class CartItem implements Serializable {
    private static final long serialVersionUID = 1L;
    
    private SanPham sanPham;
    private int soLuong;

    public CartItem() {
    }

    public CartItem(SanPham sanPham, int soLuong) {
        this.sanPham = sanPham;
        this.soLuong = soLuong;
    }

    public SanPham getSanPham() {
        return sanPham;
    }

    public void setSanPham(SanPham sanPham) {
        this.sanPham = sanPham;
    }

    public int getSoLuong() {
        return soLuong;
    }

    public void setSoLuong(int soLuong) {
        this.soLuong = soLuong;
    }

    //tính tổng tiền cho 1 line
    public double getTongTien() {
        if (sanPham != null) {
            return sanPham.getGiaBan() * soLuong;
        }
        return 0;
    }
}