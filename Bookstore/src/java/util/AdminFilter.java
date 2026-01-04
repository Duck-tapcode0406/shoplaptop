package util;

import jakarta.servlet.*;
import jakarta.servlet.annotation.WebFilter;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.KhachHang;

import java.io.IOException;

@WebFilter(urlPatterns = {"/admin/*"})
public class AdminFilter implements Filter {

    @Override
    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {
        HttpServletRequest httpRequest = (HttpServletRequest) request;
        HttpServletResponse httpResponse = (HttpServletResponse) response;

        String path = httpRequest.getRequestURI().substring(httpRequest.getContextPath().length());

        // Cho phép truy cập trang login mà không cần đăng nhập
        if (path.equals("/admin/login")) {
            chain.doFilter(request, response);
            return;
        }

        HttpSession session = httpRequest.getSession(false);
        if (session == null || session.getAttribute("user") == null) {
            // Chưa đăng nhập, redirect về trang đăng nhập chung
            httpResponse.sendRedirect(httpRequest.getContextPath() + "/dang-nhap");
            return;
        }

        KhachHang user = (KhachHang) session.getAttribute("user");
        if (user == null || user.getRole() != 1) {
            // Không phải admin, redirect về trang chủ
            httpResponse.sendRedirect(httpRequest.getContextPath() + "/trang-chu");
            return;
        }

        // Cho phép tiếp tục
        chain.doFilter(request, response);
    }
}




