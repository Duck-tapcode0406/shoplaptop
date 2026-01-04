package controller.KhachHang;

import database.DonHangDAO;
import database.GoiCuocDAO;
import database.KhachHangDAO;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.Timestamp;
import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;
import model.GoiCuoc;
import model.KhachHang;
import util.VNPayUtil;

/**
 * Servlet xử lý callback từ VNPay sau khi thanh toán
 * Dựa trên vnpay_return.jsp từ vnpay_jsp
 */
@WebServlet(name = "VNPayCallbackServlet", urlPatterns = {"/vnpay-callback"})
public class VNPayCallbackServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        
        // Lấy tất cả parameters từ VNPay (servlet container đã decode rồi)
        Map<String, String> vnp_Params = new HashMap<>();
        java.util.Enumeration<String> paramNames = request.getParameterNames();
        while (paramNames.hasMoreElements()) {
            String fieldName = paramNames.nextElement();
            String fieldValue = request.getParameter(fieldName);
            if (fieldValue != null && fieldValue.length() > 0) {
                vnp_Params.put(fieldName, fieldValue);
            }
        }
        
        String vnp_SecureHash = request.getParameter("vnp_SecureHash");
        String vnp_ResponseCode = request.getParameter("vnp_ResponseCode");
        String vnp_TxnRef = request.getParameter("vnp_TxnRef"); // Mã đơn hàng
        String vnp_Amount = request.getParameter("vnp_Amount");
        String vnp_TransactionStatus = request.getParameter("vnp_TransactionStatus");
        
        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");
        
        String redirectUrl = request.getContextPath() + "/goi-cuoc";
        
        // Kiểm tra chữ ký
        boolean isValid = false;
        if (vnp_SecureHash != null && !vnp_SecureHash.isEmpty()) {
            isValid = VNPayUtil.verifySignature(vnp_Params, vnp_SecureHash);
        }
        
        // Kiểm tra ResponseCode và TransactionStatus
        // ResponseCode = "00" và TransactionStatus = "00" nghĩa là thanh toán thành công
        if (isValid && "00".equals(vnp_ResponseCode) && "00".equals(vnp_TransactionStatus) && vnp_TxnRef != null) {
            try {
                // Kiểm tra xem có phải thanh toán gói cước không
                GoiCuoc pendingSubscription = (GoiCuoc) session.getAttribute("pendingSubscription");
                String pendingOrderId = (String) session.getAttribute("pendingSubscriptionOrderId");
                
                if (pendingSubscription != null && pendingOrderId != null && pendingOrderId.equals(vnp_TxnRef)) {
                    // Xử lý thanh toán gói cước
                    if (user != null) {
                        java.sql.Connection con = null;
                        try {
                            con = database.JDBCUtil.getConnection();
                            con.setAutoCommit(false);
                            
                            // Tính ngày hết hạn
                            Calendar cal = Calendar.getInstance();
                            Timestamp ngayDangKy = new Timestamp(cal.getTimeInMillis());
                            cal.add(Calendar.MONTH, pendingSubscription.getThoiHan());
                            Timestamp ngayHetHan = new Timestamp(cal.getTimeInMillis());
                            
                            // Cập nhật thông tin gói cước cho khách hàng
                            String updateSQL = "UPDATE khachhang SET maGoiCuoc = ?, ngayDangKy = ?, ngayHetHan = ? WHERE maKhachHang = ?";
                            try (java.sql.PreparedStatement st = con.prepareStatement(updateSQL)) {
                                st.setString(1, pendingSubscription.getMaGoi());
                                st.setTimestamp(2, ngayDangKy);
                                st.setTimestamp(3, ngayHetHan);
                                st.setString(4, user.getMaKhachHang());
                                st.executeUpdate();
                            }
                            
                            con.commit();
                            
                            // Cập nhật session
                            user.setMaGoiCuoc(pendingSubscription.getMaGoi());
                            user.setNgayDangKy(ngayDangKy);
                            user.setNgayHetHan(ngayHetHan);
                            session.setAttribute("user", user);
                            
                            // Xóa session tạm
                            session.removeAttribute("pendingSubscription");
                            session.removeAttribute("pendingSubscriptionOrderId");
                            
                            String successMsg = "Đăng ký gói cước thành công! Bạn có thể đọc tất cả sách trong " + pendingSubscription.getThoiHan() + " tháng.";
                            session.setAttribute("successMessage", successMsg);
                            
                            redirectUrl = request.getContextPath() + "/goi-cuoc";
                        } catch (Exception e) {
                            if (con != null) {
                                try {
                                    con.rollback();
                                } catch (java.sql.SQLException ex) {
                                    ex.printStackTrace();
                                }
                            }
                            e.printStackTrace();
                            session.setAttribute("errorMessage", "Có lỗi xảy ra khi đăng ký gói cước: " + e.getMessage());
                        } finally {
                            if (con != null) {
                                try {
                                    con.setAutoCommit(true);
                                    con.close();
                                } catch (java.sql.SQLException e) {
                                    e.printStackTrace();
                                }
                            }
                        }
                    } else {
                        session.setAttribute("errorMessage", "Vui lòng đăng nhập để đăng ký gói cước.");
                        redirectUrl = request.getContextPath() + "/dang-nhap";
                    }
                } else {
                    // Xử lý đơn hàng cũ (nếu còn tồn tại)
                    model.DonHang pendingOrder = (model.DonHang) session.getAttribute("pendingOrder");
                    java.util.Map<String, model.CartItem> pendingItems = 
                        (java.util.Map<String, model.CartItem>) session.getAttribute("pendingOrderItems");
                    
                    if (pendingOrder != null && pendingItems != null) {
                        // Cập nhật trạng thái thanh toán
                        long amount = vnp_Amount != null ? Long.parseLong(vnp_Amount) / 100 : 0;
                        
                        pendingOrder.setTrangThaiThanhToan("Đã thanh toán");
                        pendingOrder.setSoTienDaThanhToan(amount);
                        pendingOrder.setSoTienConThieu(0);
                        
                        // Insert đơn hàng và chi tiết
                        DonHangDAO donHangDAO = new DonHangDAO();
                        java.util.ArrayList<model.ChiTietDonHang> listCTDH = new java.util.ArrayList<>();
                        
                        for (model.CartItem item : pendingItems.values()) {
                            model.ChiTietDonHang ctdh = new model.ChiTietDonHang();
                            ctdh.setMaChiTietDonHang(util.MaGeneratorUtil.generateUUID());
                            ctdh.setDonHang(pendingOrder);
                            ctdh.setSanPham(item.getSanPham());
                            ctdh.setSoLuong(item.getSoLuong());
                            ctdh.setGiaGoc(item.getSanPham().getGiaGoc());
                            ctdh.setGiaBan(item.getSanPham().getGiaBan());
                            ctdh.setGiamGia(0);
                            ctdh.setThueVAT(0);
                            ctdh.setTongTien(item.getTongTien());
                            listCTDH.add(ctdh);
                        }
                        
                        // Transaction: Tạo đơn hàng (giữ lại cho tương thích ngược)
                        java.sql.Connection con = null;
                        try {
                            con = database.JDBCUtil.getConnection();
                            con.setAutoCommit(false);
                            
                            // Tạo đơn hàng và chi tiết
                            boolean success = donHangDAO.insertDonHangVaChiTiet(pendingOrder, listCTDH, con);
                            
                            if (success) {
                                con.commit();
                                
                                // Xóa giỏ hàng và session tạm
                                model.Cart cart = (model.Cart) session.getAttribute("cart");
                                if (cart != null) {
                                    cart.clear();
                                    session.setAttribute("cart", cart);
                                    session.setAttribute("cartCount", 0);
                                }
                                session.removeAttribute("pendingOrder");
                                session.removeAttribute("pendingOrderItems");
                                
                                String successMsg = "Thanh toán thành công!";
                                redirectUrl = request.getContextPath() + "/trang-chu";
                                session.setAttribute("successMessage", successMsg);
                            } else {
                                con.rollback();
                                session.setAttribute("errorMessage", "Có lỗi xảy ra khi lưu đơn hàng.");
                            }
                        } catch (Exception e) {
                            if (con != null) {
                                try {
                                    con.rollback();
                                } catch (java.sql.SQLException ex) {
                                    ex.printStackTrace();
                                }
                            }
                            e.printStackTrace();
                            session.setAttribute("errorMessage", "Có lỗi xảy ra khi xử lý đơn hàng: " + e.getMessage());
                        } finally {
                            if (con != null) {
                                try {
                                    con.setAutoCommit(true);
                                    con.close();
                                } catch (java.sql.SQLException e) {
                                    e.printStackTrace();
                                }
                            }
                        }
                    } else {
                        redirectUrl = request.getContextPath() + "/goi-cuoc";
                        session.setAttribute("errorMessage", "Không tìm thấy thông tin thanh toán.");
                    }
                }
            } catch (Exception e) {
                System.err.println("Lỗi xử lý callback VNPay: " + e.getMessage());
                e.printStackTrace();
                session.setAttribute("errorMessage", "Có lỗi xảy ra khi xử lý thanh toán: " + e.getMessage());
            }
        } else {
            // Thanh toán thất bại hoặc chữ ký không hợp lệ
            String errorMsg = "Thanh toán thất bại.";
            if (!isValid) {
                errorMsg += " Chữ ký không hợp lệ.";
            } else if (!"00".equals(vnp_ResponseCode)) {
                errorMsg += " Mã lỗi: " + (vnp_ResponseCode != null ? vnp_ResponseCode : "Không xác định");
            }
            session.setAttribute("errorMessage", errorMsg);
        }
        
        response.sendRedirect(redirectUrl);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doGet(request, response);
    }
}

