package controller.Admin;

import database.TinTucDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.TinTuc;

import java.io.IOException;

@WebServlet(name = "NewsListServlet", urlPatterns = {"/admin/news"})
public class NewsListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String id = request.getParameter("id");

        if (action != null && id != null && "delete".equals(action)) {
            TinTucDAO dao = new TinTucDAO();
            TinTuc news = new TinTuc();
            news.setMaTinTuc(id);
            dao.delete(news);
            request.getSession().setAttribute("successMessage", "Đã xóa tin tức thành công.");
            response.sendRedirect(request.getContextPath() + "/admin/news");
            return;
        }

        TinTucDAO dao = new TinTucDAO();
        request.setAttribute("newsList", dao.selectAll());
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/news-list.jsp");
        rd.forward(request, response);
    }
}




