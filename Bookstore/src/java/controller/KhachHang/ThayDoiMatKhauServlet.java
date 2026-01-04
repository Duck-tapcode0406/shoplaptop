/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.KhachHang;
import util.PasswordUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "ThayDoiMatKhauServlet", urlPatterns = {"/tai-khoan/thay-doi-mat-khau"})
public class ThayDoiMatKhauServlet extends HttpServlet {

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
            out.println("<title>Servlet ThayDoiMatKhauServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet ThayDoiMatKhauServlet at " + request.getContextPath() + "</h1>");
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
        
        // Bảo mật: Bắt buộc đăng nhập
        if (request.getSession().getAttribute("user") == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }
        
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/taikhoan/change-password.jsp");
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
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        
        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");
        
        // Bảo mật
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }

        String matKhauHienTai = request.getParameter("currentPassword");
        String matKhauMoi = request.getParameter("newPassword");
        String xacNhanMatKhau = request.getParameter("confirmPassword");
        
        String url = "/views/khachhang/taikhoan/change-password.jsp";
        
        // 1. Kiểm tra mật khẩu hiện tại có đúng không
        if (!PasswordUtil.check(matKhauHienTai, user.getMatKhau())) {
            request.setAttribute("error", "Mật khẩu hiện tại không chính xác!");
        }
        // 2. Kiểm tra mật khẩu mới có khớp không
        else if (!matKhauMoi.equals(xacNhanMatKhau)) {
            request.setAttribute("error", "Mật khẩu xác nhận không khớp!");
        }
        // 3. Thành công
        else {
            KhachHangDAO khachHangDAO = new KhachHangDAO();
            khachHangDAO.updatePassword(user.getMaKhachHang(), matKhauMoi);
            
            // Cập nhật lại mật khẩu mới trong session (quan trọng)
            user.setMatKhau(PasswordUtil.hash(matKhauMoi));
            session.setAttribute("user", user);
            
            request.setAttribute("success", "Đổi mật khẩu thành công!");
        }
        
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
        rd.forward(request, response);
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
