/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.KhuyenMaiDAO;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.util.Date;
import model.Cart;
import model.KhuyenMai;

/**
 *
 * @author Acer
 */
@WebServlet(name = "ApDungKhuyenMaiServlet", urlPatterns = {"/ap-dung-khuyen-mai"})
public class ApDungKhuyenMaiServlet extends HttpServlet {

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
            out.println("<title>Servlet ApDungKhuyenMaiServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet ApDungKhuyenMaiServlet at " + request.getContextPath() + "</h1>");
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
        // Không cho phép GET, đá về trang giỏ hàng
        response.sendRedirect(request.getContextPath() + "/gio-hang");
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
        String promoCode = request.getParameter("promoCode");
        
        Cart cart = (Cart) session.getAttribute("cart");
        
        if (cart == null || cart.getItems().isEmpty()) {
            session.setAttribute("errorMessage", "Giỏ hàng rỗng!");
            response.sendRedirect(request.getContextPath() + "/gio-hang");
            return;
        }

        // Nếu promoCode rỗng, xóa khuyến mãi
        if (promoCode == null || promoCode.trim().isEmpty()) {
            cart.setKhuyenMai(null);
            session.setAttribute("cart", cart);
            session.setAttribute("successMessage", "Đã xóa mã khuyến mãi.");
            response.sendRedirect(request.getContextPath() + "/gio-hang");
            return;
        }

        KhuyenMaiDAO kmDAO = new KhuyenMaiDAO();
        KhuyenMai km = kmDAO.selectByMaKhuyenMai(promoCode.trim());
        
        Date now = new Date(System.currentTimeMillis());
        
        if (km == null) {
            session.setAttribute("errorMessage", "Mã khuyến mãi không tồn tại!");
        } else if (!km.isTrangThai()) {
            session.setAttribute("errorMessage", "Mã khuyến mãi đã bị vô hiệu hóa!");
        } else if (km.getNgayBatDau() != null && km.getNgayBatDau().after(now)) {
            session.setAttribute("errorMessage", "Mã khuyến mãi chưa đến hạn!");
        } else if (km.getNgayKetThuc() != null && km.getNgayKetThuc().before(now)) {
            session.setAttribute("errorMessage", "Mã khuyến mãi đã hết hạn!");
        } else {
            // Áp dụng thành công
            cart.setKhuyenMai(km);
            session.setAttribute("cart", cart); // Lưu lại giỏ hàng
            session.setAttribute("successMessage", "Áp dụng mã khuyến mãi thành công!");
        }
        
        response.sendRedirect(request.getContextPath() + "/gio-hang");
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
