package model;

import java.sql.Timestamp; // *** Đảm bảo import đúng java.sql.Timestamp ***
// import java.util.Date; // Không dùng cái này nếu DAO trả về Timestamp

public class TinTuc {
    private String maTinTuc;
    private String tieuDe;
    private String noiDung;
    private String hinhAnh;
    private Timestamp ngayDang; // *** Kiểu dữ liệu phải là Timestamp ***
    private String tacGia; // Tên tác giả (String)
    private KhachHang nguoiDang; // Hoặc NguoiDung/NhanVien

    // Constructor rỗng
    public TinTuc() {
    }

    // Constructor đầy đủ (nếu có)
    public TinTuc(String maTinTuc, String tieuDe, String noiDung, String hinhAnh, Timestamp ngayDang, KhachHang nguoiDang) {
        this.maTinTuc = maTinTuc;
        this.tieuDe = tieuDe;
        this.noiDung = noiDung;
        this.hinhAnh = hinhAnh;
        this.ngayDang = ngayDang;
        this.nguoiDang = nguoiDang;
    }

    // --- Getters and Setters ---
    // ... (Đảm bảo có đủ getter/setter cho tất cả thuộc tính)

    public String getMaTinTuc() { return maTinTuc; }
    public void setMaTinTuc(String maTinTuc) { this.maTinTuc = maTinTuc; }

    public String getTieuDe() { return tieuDe; }
    public void setTieuDe(String tieuDe) { this.tieuDe = tieuDe; }

    public String getNoiDung() { return noiDung; }
    public void setNoiDung(String noiDung) { this.noiDung = noiDung; }

    public String getHinhAnh() { return hinhAnh; }
    public void setHinhAnh(String hinhAnh) { this.hinhAnh = hinhAnh; }

    public Timestamp getNgayDang() { return ngayDang; } // *** Getter trả về Timestamp ***
    public void setNgayDang(Timestamp ngayDang) { this.ngayDang = ngayDang; } // *** Setter nhận Timestamp ***

    public KhachHang getNguoiDang() { return nguoiDang; }
    public void setNguoiDang(KhachHang nguoiDang) { this.nguoiDang = nguoiDang; }

    public String getTacGia() { return tacGia; }
    public void setTacGia(String tacGia) { this.tacGia = tacGia; }
}