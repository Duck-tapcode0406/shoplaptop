package controller.Admin;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.KhachHang;

import java.io.IOException;
import java.util.ArrayList;

@WebServlet(name = "UserListServlet", urlPatterns = {"/admin/users"})
public class UserListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String search = request.getParameter("search");
        KhachHangDAO khachHangDAO = new KhachHangDAO();
        ArrayList<KhachHang> users = khachHangDAO.selectAllUsers();

        // Tìm kiếm nếu có
        if (search != null && !search.trim().isEmpty()) {
            ArrayList<KhachHang> filtered = new ArrayList<>();
            String searchLower = search.toLowerCase().trim();
            for (KhachHang user : users) {
                if ((user.getHoVaTen() != null && user.getHoVaTen().toLowerCase().contains(searchLower)) ||
                    (user.getEmail() != null && user.getEmail().toLowerCase().contains(searchLower)) ||
                    (user.getSoDienThoai() != null && user.getSoDienThoai().contains(search)) ||
                    (user.getTenDangNhap() != null && user.getTenDangNhap().toLowerCase().contains(searchLower))) {
                    filtered.add(user);
                }
            }
            users = filtered;
        }

        request.setAttribute("users", users);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/user-list.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String maKhachHang = request.getParameter("maKhachHang");

        if (action != null && maKhachHang != null) {
            KhachHangDAO khachHangDAO = new KhachHangDAO();
            
            if ("lock".equals(action)) {
                khachHangDAO.updateStatus(maKhachHang, 0);
                request.getSession().setAttribute("successMessage", "Đã khóa tài khoản thành công.");
            } else if ("unlock".equals(action)) {
                khachHangDAO.updateStatus(maKhachHang, 1);
                request.getSession().setAttribute("successMessage", "Đã mở khóa tài khoản thành công.");
            } else if ("delete".equals(action)) {
                KhachHang user = new KhachHang();
                user.setMaKhachHang(maKhachHang);
                int result = khachHangDAO.delete(user);
                if (result > 0) {
                    request.getSession().setAttribute("successMessage", "Đã xóa người dùng thành công.");
                } else {
                    request.getSession().setAttribute("errorMessage", "Không thể xóa người dùng. Có thể người dùng đang có đơn hàng.");
                }
            }
        }

        response.sendRedirect(request.getContextPath() + "/admin/users");
    }
}




