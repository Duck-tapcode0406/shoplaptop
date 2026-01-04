package database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

import model.ChiTietDonHang;
import model.DonHang;
import model.SanPham;

public class ChiTietDonHangDAO implements DAOInterface<ChiTietDonHang> {

    /**
     * Helper: Ánh xạ 1 dòng ResultSet sang 1 đối tượng ChiTietDonHang
     * (Dựa trên file ChiTietDonHangDAO.java gốc của bạn)
     */
    private ChiTietDonHang mapRowToCTDH(ResultSet rs) throws SQLException {
        // Sử dụng tên cột chính xác từ database schema (camelCase)
        String maChiTietDonHang = null;
        String madonhang = null;
        String masanpham = null;
        
        try {
            maChiTietDonHang = rs.getString("maChiTietDonHang");
        } catch (SQLException e) {
            maChiTietDonHang = rs.getString("machitietdonhang");
        }
        
        try {
            madonhang = rs.getString("maDonHang");
        } catch (SQLException e) {
            madonhang = rs.getString("madonhang");
        }
        
        try {
            masanpham = rs.getString("maSanPham");
        } catch (SQLException e) {
            masanpham = rs.getString("masanpham");
        }
        
        double soluong = 0;
        double giagoc = 0;
        double giamgia = 0;
        double giaban = 0;
        double thuevat = 0;
        double tongtien = 0;
        
        try {
            soluong = rs.getDouble("soLuong");
        } catch (SQLException e) {
            soluong = rs.getDouble("soluong");
        }
        
        try {
            giagoc = rs.getDouble("giaGoc");
        } catch (SQLException e) {
            giagoc = rs.getDouble("giagoc");
        }
        
        try {
            giamgia = rs.getDouble("giamGia");
        } catch (SQLException e) {
            giamgia = rs.getDouble("giamgia");
        }
        
        try {
            giaban = rs.getDouble("giaBan");
        } catch (SQLException e) {
            giaban = rs.getDouble("giaban");
        }
        
        try {
            thuevat = rs.getDouble("thueVAT");
        } catch (SQLException e) {
            thuevat = rs.getDouble("thuevat");
        }
        
        try {
            tongtien = rs.getDouble("tongTien");
        } catch (SQLException e) {
            tongtien = rs.getDouble("tongtien");
        }

        DonHang dh = new DonHang();
        dh.setMaDonHang(madonhang);

        SanPham sp = new SanPham();
        sp.setMaSanPham(masanpham);

        return new ChiTietDonHang(maChiTietDonHang, dh, sp, soluong, giagoc, 
                                  giamgia, giaban, thuevat, tongtien);
    }

    @Override
    public ArrayList<ChiTietDonHang> selectAll() {
        ArrayList<ChiTietDonHang> ketQua = new ArrayList<>();
        String sql = "SELECT * FROM `chitietdonhang`";
        
        // Nâng cấp: Dùng try-with-resources
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {

            while (rs.next()) {
                ketQua.add(mapRowToCTDH(rs));
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
 
    public ChiTietDonHang selectById(ChiTietDonHang t) {
        ChiTietDonHang ketQua = null;
        String sql = "SELECT * FROM `chitietdonhang` WHERE `maChiTietDonHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaChiTietDonHang());
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    ketQua = mapRowToCTDH(rs);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * Phương thức insert() GỐC.
     * Tự mở và đóng Connection.
     * Được nâng cấp để gọi hàm insert(t, con) nạp chồng.
     */
    @Override
    public int insert(ChiTietDonHang t) {
        int ketQua = 0;
        try (Connection con = JDBCUtil.getConnection()) {
            ketQua = this.insert(t, con); // Gọi hàm nạp chồng
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    /**
     * [NÂNG CẤP] Phương thức insert() NẠP CHỒNG (Overloaded)
     * Nhận Connection từ bên ngoài để phục vụ Giao dịch (Transaction).
     * Sẽ được gọi bởi DonHangDAO.insertDonHangVaChiTiet()
     */
    public int insert(ChiTietDonHang t, Connection con) throws SQLException {
        // Dùng tên cột CSDL chính xác từ schema (camelCase)
        String sql = "INSERT INTO `chitietdonhang` (`maChiTietDonHang`, `maDonHang`, `maSanPham`, `soLuong`, " +
                     "`giaGoc`, `giamGia`, `giaBan`, `thueVAT`, `tongTien`) " +
                     "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        int ketQua = 0;
        // Không dùng try-with-resources cho Connection (vì nó được truyền vào)
        try (PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaChiTietDonHang());
            st.setString(2, t.getDonHang().getMaDonHang());
            st.setString(3, t.getSanPham().getMaSanPham());
            st.setDouble(4, t.getSoLuong());
            st.setDouble(5, t.getGiaGoc());
            st.setDouble(6, t.getGiamGia());
            st.setDouble(7, t.getGiaBan());
            st.setDouble(8, t.getThueVAT());
            st.setDouble(9, t.getTongTien());

            ketQua = st.executeUpdate();
        }
        // Không được đóng Connection (con.close()) ở đây
        return ketQua;
    }

    @Override
    public int insertAll(ArrayList<ChiTietDonHang> arr) {
        int dem = 0;
        for (ChiTietDonHang ctdh : arr) {
            dem += this.insert(ctdh);
        }
        return dem;
    }

    @Override
    public int delete(ChiTietDonHang t) {
        int ketQua = 0;
        String sql = "DELETE FROM `chitietdonhang` WHERE `maChiTietDonHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getMaChiTietDonHang());
            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public int deleteAll(ArrayList<ChiTietDonHang> arr) {
        int dem = 0;
        for (ChiTietDonHang ctdh : arr) {
            dem += this.delete(ctdh);
        }
        return dem;
    }

    @Override
    public int update(ChiTietDonHang t) {
        int ketQua = 0;
        String sql = "UPDATE `chitietdonhang` SET `maDonHang`=?, `maSanPham`=?, `soLuong`=?, `giaGoc`=?, " +
                     "`giamGia`=?, `giaBan`=?, `thueVAT`=?, `tongTien`=? " +
                     "WHERE `maChiTietDonHang`=?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, t.getDonHang().getMaDonHang());
            st.setString(2, t.getSanPham().getMaSanPham());
            st.setDouble(3, t.getSoLuong());
            st.setDouble(4, t.getGiaGoc());
            st.setDouble(5, t.getGiamGia());
            st.setDouble(6, t.getGiaBan());
            st.setDouble(7, t.getThueVAT());
            st.setDouble(8, t.getTongTien());
            st.setString(9, t.getMaChiTietDonHang()); // Tham số WHERE

            ketQua = st.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }
    // Thêm 1 phương thức này vào file database/ChiTietDonHangDAO.java

    /**
     * [MỚI] Lấy tất cả chi tiết của 1 đơn hàng (kèm thông tin sản phẩm)
     * Dùng cho ChiTietDonHangServlet
     */
    public ArrayList<ChiTietDonHang> selectAllByOrderId(String maDonHang) {
        ArrayList<ChiTietDonHang> ketQua = new ArrayList<>();
        // Cần JOIN để lấy tên, ảnh sản phẩm cho JSP
        String sql = "SELECT ctdh.*, sp.`tenSanPham`, sp.`hinhAnh` FROM `chitietdonhang` ctdh " +
                     "JOIN `sanpham` sp ON ctdh.`maSanPham` = sp.`maSanPham` " +
                     "WHERE ctdh.`maDonHang` = ?";
                     
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, maDonHang);
            try (ResultSet rs = st.executeQuery()) {
                while (rs.next()) {
                    // Dùng hàm mapRowToCTDH đã có
                    ChiTietDonHang ctdh = mapRowToCTDH(rs); 
                    
                    // Thêm thông tin từ JOIN vào đối tượng SanPham lồng nhau
                    try {
                        ctdh.getSanPham().setTenSanPham(rs.getString("tenSanPham"));
                    } catch (SQLException e) {
                        ctdh.getSanPham().setTenSanPham(rs.getString("tensanpham"));
                    }
                    try {
                        ctdh.getSanPham().setHinhAnh(rs.getString("hinhAnh"));
                    } catch (SQLException e) {
                        ctdh.getSanPham().setHinhAnh(rs.getString("hinhanh"));
                    }
                    
                    ketQua.add(ctdh);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return ketQua;
    }

    @Override
    public ChiTietDonHang selectById(String t) {
        throw new UnsupportedOperationException("Not supported yet."); // Generated from nbfs://nbhost/SystemFileSystem/Templates/Classes/Code/GeneratedMethodBody
    }
}