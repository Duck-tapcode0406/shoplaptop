package controller.Admin;

import database.TacGiaDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.TacGia;

import java.io.IOException;
import java.sql.Date;

@WebServlet(name = "AuthorListServlet", urlPatterns = {"/admin/authors"})
public class AuthorListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String id = request.getParameter("id");

        if (action != null && id != null && "delete".equals(action)) {
            TacGiaDAO dao = new TacGiaDAO();
            TacGia author = new TacGia();
            author.setMaTacGia(id);
            dao.delete(author);
            request.getSession().setAttribute("successMessage", "Đã xóa tác giả thành công.");
            response.sendRedirect(request.getContextPath() + "/admin/authors");
            return;
        }

        TacGiaDAO dao = new TacGiaDAO();
        request.setAttribute("authors", dao.selectAll());
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/author-list.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String maTacGia = request.getParameter("maTacGia");
        String hoVaTen = request.getParameter("hoVaTen");
        String ngaySinhStr = request.getParameter("ngaySinh");
        String tieuSu = request.getParameter("tieuSu");

        TacGiaDAO dao = new TacGiaDAO();
        TacGia author = new TacGia();
        author.setMaTacGia(maTacGia);
        author.setHoVaTen(hoVaTen);
        author.setTieuSu(tieuSu);
        
        if (ngaySinhStr != null && !ngaySinhStr.isEmpty()) {
            author.setNgaySinh(Date.valueOf(ngaySinhStr));
        }

        if ("add".equals(action)) {
            dao.insert(author);
            request.getSession().setAttribute("successMessage", "Thêm tác giả thành công.");
        } else if ("update".equals(action)) {
            dao.update(author);
            request.getSession().setAttribute("successMessage", "Cập nhật tác giả thành công.");
        }

        response.sendRedirect(request.getContextPath() + "/admin/authors");
    }
}




