package controller.Admin;

import database.TheLoaiDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.TheLoai;

import java.io.IOException;

@WebServlet(name = "CategoryListServlet", urlPatterns = {"/admin/categories"})
public class CategoryListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String id = request.getParameter("id");

        if (action != null && id != null) {
            TheLoaiDAO dao = new TheLoaiDAO();
            TheLoai category = new TheLoai();
            category.setMaTheLoai(id);
            
            if ("delete".equals(action)) {
                dao.delete(category);
                request.getSession().setAttribute("successMessage", "Đã xóa thể loại thành công.");
            }
            response.sendRedirect(request.getContextPath() + "/admin/categories");
            return;
        }

        TheLoaiDAO dao = new TheLoaiDAO();
        request.setAttribute("categories", dao.selectAll());
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/category-list.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String maTheLoai = request.getParameter("maTheLoai");
        String tenTheLoai = request.getParameter("tenTheLoai");

        TheLoaiDAO dao = new TheLoaiDAO();
        TheLoai category = new TheLoai();
        category.setMaTheLoai(maTheLoai);
        category.setTenTheLoai(tenTheLoai);

        if ("add".equals(action)) {
            dao.insert(category);
            request.getSession().setAttribute("successMessage", "Thêm thể loại thành công.");
        } else if ("update".equals(action)) {
            dao.update(category);
            request.getSession().setAttribute("successMessage", "Cập nhật thể loại thành công.");
        }

        response.sendRedirect(request.getContextPath() + "/admin/categories");
    }
}




