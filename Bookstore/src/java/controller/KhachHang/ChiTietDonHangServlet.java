/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.ChiTietDonHangDAO;
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
import model.ChiTietDonHang;
import model.DonHang;
import model.KhachHang;

/**
 *
 * @author Acer
 */
@WebServlet(name = "ChiTietDonHangServlet", urlPatterns = {"/tai-khoan/chi-tiet-don-hang"})
public class ChiTietDonHangServlet extends HttpServlet {

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
            out.println("<title>Servlet ChiTietDonHangServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet ChiTietDonHangServlet at " + request.getContextPath() + "</h1>");
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

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");

        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");
        String orderId = request.getParameter("id");

        // Bảo mật: Chưa đăng nhập
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }

        // Lấy thông tin đơn hàng
        DonHangDAO donHangDAO = new DonHangDAO();

        // ✅ SỬA: Dùng hàm selectById(String)
        DonHang donHang = donHangDAO.selectById(orderId);

        // Bảo mật: Đơn hàng không tồn tại hoặc không phải của user này
        if (donHang == null || !donHang.getKhachHang().getMaKhachHang().equals(user.getMaKhachHang())) {
            response.sendRedirect(request.getContextPath() + "/tai-khoan/lich-su-don-hang");
            return;
        }

        // Lấy chi tiết đơn hàng
        ChiTietDonHangDAO ctdhDAO = new ChiTietDonHangDAO();
        ArrayList<ChiTietDonHang> listChiTiet = ctdhDAO.selectAllByOrderId(orderId);

        // Gửi dữ liệu sang JSP
        request.setAttribute("donHang", donHang);
        request.setAttribute("listChiTiet", listChiTiet);

        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/taikhoan/order-detail.jsp");
        rd.forward(request, response);
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
        processRequest(request, response);
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
