/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.DonHangDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.util.ArrayList;
import java.sql.Connection;
import java.sql.SQLException;
import model.Cart;
import model.CartItem;
import model.ChiTietDonHang;
import model.DonHang;
import model.KhachHang;
import model.SanPham;
import util.DateUtil;
import util.MaGeneratorUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "DatHangServlet", urlPatterns = {"/dat-hang"})
public class DatHangServlet extends HttpServlet {

    /**
     * Processes requests for both HTTP <code>GET</code> and <code>POST</code>
     * methods.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    protected void processRequest(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        try (PrintWriter out = response.getWriter()) {
            /* TODO output your page here. You may use following sample code. */
            out.println("<!DOCTYPE html>");
            out.println("<html>");
            out.println("<head>");
            out.println("<title>Servlet DatHangServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet DatHangServlet at " + request.getContextPath() + "</h1>");
            out.println("</body>");
            out.println("</html>");
        }
    }

    // <editor-fold defaultstate="collapsed" desc="HttpServlet methods. Click on the + sign on the left to edit the code.">
    /**
     * Handles the HTTP <code>GET</code> method.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        // Không cho phép GET, đá về trang chủ
        response.sendRedirect(request.getContextPath() + "/");
    }

    /**
     * Handles the HTTP <code>POST</code> method.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        
        HttpSession session = request.getSession();
        
        // 1. Kiểm tra Lỗi (Bảo mật)
        KhachHang user = (KhachHang) session.getAttribute("user");
        Cart cart = (Cart) session.getAttribute("cart");
        
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }
        
        // Kiểm tra nếu là admin thì không cho phép đặt hàng
        if (user.getRole() == 1) {
            session.setAttribute("errorMessage", "Quản trị viên không thể mua hàng. Vui lòng sử dụng tài khoản khách hàng để mua hàng.");
            response.sendRedirect(request.getContextPath() + "/trang-chu");
            return;
        }
        
        if (cart == null || cart.getItems().isEmpty()) {
            response.sendRedirect(request.getContextPath() + "/gio-hang");
            return;
        }

        // 2. Lấy thông tin từ form
        String phuongThucTT = request.getParameter("paymentMethod");
        
        // Validate phương thức thanh toán - CHỈ cho phép VNPay
        if (phuongThucTT == null || !phuongThucTT.equals("vnpay")) {
            request.setAttribute("errorMessage", "Vui lòng chọn phương thức thanh toán VNPay.");
            RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/thanhtoan/checkout.jsp");
            rd.forward(request, response);
            return;
        }
        
        // Kiểm tra xem đã mua sách chưa (ngăn mua lại)
        DonHangDAO donHangDAO = new DonHangDAO();
        for (CartItem item : cart.getItems().values()) {
            if (donHangDAO.checkIfCustomerBoughtProduct(user.getMaKhachHang(), item.getSanPham().getMaSanPham())) {
                request.setAttribute("errorMessage", "Bạn đã mua sách '" + item.getSanPham().getTenSanPham() + "' rồi. Vui lòng kiểm tra trong kho sách của tôi.");
                RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/thanhtoan/checkout.jsp");
                rd.forward(request, response);
                return;
            }
        }
        
        // Tính tổng tiền từ giỏ hàng (tính bằng VNĐ)
        double tongTien = cart.getTongTien();
        String maDonHang = MaGeneratorUtil.generateOrderCode();
        
        // 3. Tạo đối tượng Đơn Hàng tạm (sẽ được tạo sau khi thanh toán thành công)
        DonHang dh = new DonHang();
        dh.setMaDonHang(maDonHang);
        dh.setKhachHang(user);
        dh.setDiaChiNhanHang("Đọc trực tuyến");
        dh.setDiaChiMuaHang("Đọc trực tuyến");
        dh.setTrangThai("Hoàn tất"); // Tự động hoàn tất sau khi thanh toán
        dh.setHinhThucThanhToan("VNPay");
        dh.setNgayDatHang(DateUtil.getCurrentSqlDate());
        dh.setTrangThaiThanhToan("Chờ thanh toán");
        dh.setSoTienConThieu((long)Math.round(tongTien));
        dh.setSoTienDaThanhToan(0);
        
        // Lưu đơn hàng tạm vào session để sau khi thanh toán xong sẽ insert
        session.setAttribute("pendingOrder", dh);
        session.setAttribute("pendingOrderItems", cart.getItems());
        
        // 4. Tạo URL thanh toán VNPay
        String orderInfo = "Thanh toan don hang " + maDonHang;
        String baseUrl = request.getScheme() + "://" + request.getServerName() + 
                       (request.getServerPort() != 80 && request.getServerPort() != 443 ? 
                        ":" + request.getServerPort() : "") + 
                       request.getContextPath();
        String returnUrl = baseUrl + "/vnpay-callback";
        String ipAddr = util.VNPayUtil.getIpAddress(request);
        String vnpayUrl = util.VNPayUtil.createPaymentUrl(maDonHang, (long)Math.round(tongTien), orderInfo, returnUrl, ipAddr);
        
        // Redirect đến VNPay
        response.sendRedirect(vnpayUrl);
    }

    /**
     * Returns a short description of the servlet.
     *
     * @return a String containing servlet description
     */
    @Override
    public String getServletInfo() {
        return "Short description";
    }// </editor-fold>

}
