package controller.Admin;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.KhachHang;
import util.PasswordUtil;

import java.io.IOException;

@WebServlet(name = "AdminLoginServlet", urlPatterns = {"/admin/login"})
public class AdminLoginServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        // Nếu đã đăng nhập admin, redirect về dashboard
        HttpSession session = request.getSession(false);
        if (session != null) {
            KhachHang user = (KhachHang) session.getAttribute("user");
            if (user != null && user.getRole() == 1) {
                response.sendRedirect(request.getContextPath() + "/admin/dashboard");
                return;
            }
        }

        // Redirect đến trang đăng nhập chung
        response.sendRedirect(request.getContextPath() + "/dang-nhap");
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        // Redirect POST request đến servlet đăng nhập chung
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");
        
        // Forward request đến DangNhapServlet để xử lý
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/dang-nhap");
        rd.forward(request, response);
    }
}




