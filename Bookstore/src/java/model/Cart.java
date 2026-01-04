package model;

import java.io.Serializable;
import java.util.HashMap;
import java.util.Map;

public class Cart implements Serializable {
    private static final long serialVersionUID = 1L;

    // Dùng Map để truy cập sản phẩm qua ID (String) nhanh nhất
    private Map<String, CartItem> items;
    private KhuyenMai khuyenMai;

    public Cart() {
        items = new HashMap<>();
        khuyenMai = null;
    }

    // Lấy danh sách item
    public Map<String, CartItem> getItems() {
        return items;
    }
    
    // Thêm sản phẩm vào giỏ (hoặc tăng số lượng)
    public void add(CartItem item) {
        String productId = item.getSanPham().getMaSanPham();
        
        // Nếu sản phẩm đã có trong giỏ
        if (items.containsKey(productId)) {
            CartItem existingItem = items.get(productId);
            // Cộng dồn số lượng
            existingItem.setSoLuong(existingItem.getSoLuong() + item.getSoLuong());
        } else {
            // Nếu sản phẩm chưa có, thêm mới
            items.put(productId, item);
        }
    }

    // Cập nhật số lượng
    public void update(String productId, int soLuong) {
        if (items.containsKey(productId)) {
            if (soLuong > 0) {
                items.get(productId).setSoLuong(soLuong);
            } else {
                // Nếu số lượng <= 0, xóa luôn
                remove(productId);
            }
        }
    }

    // Xóa sản phẩm
    public void remove(String productId) {
        items.remove(productId);
    }

    // Xóa toàn bộ giỏ hàng (sau khi đặt hàng)
    public void clear() {
        items.clear();
        khuyenMai = null;
    }
    
    // Đếm tổng số sản phẩm (cho badge)
    public int getSoLuongTong() {
        int count = 0;
        for (CartItem item : items.values()) {
            count += item.getSoLuong();
        }
        return count;
    }

    // Tính tạm tính (chưa giảm giá)
    public double getTamTinh() {
        double total = 0;
        for (CartItem item : items.values()) {
            total += item.getTongTien();
        }
        return total;
    }
    
    // Áp dụng khuyến mãi
    public void setKhuyenMai(KhuyenMai km) {
        this.khuyenMai = km;
    }
    
    public KhuyenMai getKhuyenMai() {
        return this.khuyenMai;
    }
    
    // Lấy số tiền được giảm
    public double getTienGiamGia() {
        if (khuyenMai == null || !khuyenMai.isTrangThai()) {
            return 0;
        }
        double tamTinh = getTamTinh();
        double tienGiam = tamTinh * khuyenMai.getPhanTramGiam();
        
        // Kiểm tra số tiền giảm tối đa
        if (tienGiam > khuyenMai.getSoTienGiamToiDa()) {
            return khuyenMai.getSoTienGiamToiDa();
        }
        return tienGiam;
    }
    
    // Lấy tổng tiền cuối cùng
    public double getTongTien() {
        return getTamTinh() - getTienGiamGia();
    }
}