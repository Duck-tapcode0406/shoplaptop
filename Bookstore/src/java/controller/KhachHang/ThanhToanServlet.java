/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.Cart;
import model.KhachHang;

/**
 *
 * @author Acer
 */
@WebServlet(name = "ThanhToanServlet", urlPatterns = {"/thanh-toan"})
public class ThanhToanServlet extends HttpServlet {

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
            out.println("<title>Servlet ThanhToanServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet ThanhToanServlet at " + request.getContextPath() + "</h1>");
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
        HttpSession session = request.getSession(false); // Lấy session, không tạo mới

        // --- 1. KIỂM TRA ĐĂNG NHẬP ---
        if (session == null || session.getAttribute("user") == null) {
            // Chưa đăng nhập, chuyển về trang đăng nhập
            // Lưu lại trang thanh toán để redirect sau
            String redirectUrl = request.getContextPath() + "/thanh-toan";
            session = request.getSession(); // Tạo session mới để lưu
            session.setAttribute("redirectAfterLogin", redirectUrl);
            session.setAttribute("errorMessage", "Vui lòng đăng nhập để tiến hành thanh toán!");
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return; // Dừng xử lý
        }

        // --- 1.5. KIỂM TRA NẾU LÀ ADMIN ---
        KhachHang user = (KhachHang) session.getAttribute("user");
        if (user != null && user.getRole() == 1) {
            session.setAttribute("errorMessage", "Quản trị viên không thể mua hàng. Vui lòng sử dụng tài khoản khách hàng để mua hàng.");
            response.sendRedirect(request.getContextPath() + "/trang-chu");
            return;
        }

        // --- 2. KIỂM TRA GIỎ HÀNG ---
        Cart cart = (session != null) ? (Cart) session.getAttribute("cart") : null;
        if (cart == null || cart.getItems() == null || cart.getItems().isEmpty()) {
            // Giỏ hàng rỗng, chuyển về trang giỏ hàng với thông báo
            session.setAttribute("errorMessage", "Giỏ hàng của bạn đang rỗng, không thể thanh toán!");
            response.sendRedirect(request.getContextPath() + "/gio-hang"); // URL của trang giỏ hàng
            return; // Dừng xử lý
        }

        // --- ĐỦ ĐIỀU KIỆN -> HIỂN THỊ TRANG CHECKOUT ---
        // (Không cần lấy lại thông tin user hay cart vì JSP có thể lấy từ session)
        String url = "/views/khachhang/thanhtoan/checkout.jsp";
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
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
