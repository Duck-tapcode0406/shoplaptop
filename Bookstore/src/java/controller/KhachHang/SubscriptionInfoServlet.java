package controller.KhachHang;

import database.GoiCuocDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import model.GoiCuoc;
import model.KhachHang;

/**
 * Servlet hiển thị thông tin gói cước của user
 */
@WebServlet(name = "SubscriptionInfoServlet", urlPatterns = {"/thong-tin-goi-cuoc"})
public class SubscriptionInfoServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        HttpSession session = request.getSession(false);
        
        // Kiểm tra đăng nhập
        if (session == null || session.getAttribute("user") == null) {
            session = request.getSession();
            session.setAttribute("redirectAfterLogin", request.getRequestURI());
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }

        KhachHang user = (KhachHang) session.getAttribute("user");
        
        // Lấy thông tin gói cước đang dùng
        GoiCuocDAO goiCuocDAO = new GoiCuocDAO();
        GoiCuoc goiDangDung = null;
        if (user.getMaGoiCuoc() != null && !user.getMaGoiCuoc().isEmpty()) {
            goiDangDung = goiCuocDAO.getByMaGoi(user.getMaGoiCuoc());
        }
        
        request.setAttribute("user", user);
        request.setAttribute("goiDangDung", goiDangDung);
        
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/subscription-info.jsp");
        rd.forward(request, response);
    }
}







