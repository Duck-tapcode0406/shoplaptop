package controller.Admin;

import database.NhaXuatBanDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.NhaXuatBan;

import java.io.IOException;

@WebServlet(name = "PublisherListServlet", urlPatterns = {"/admin/publishers"})
public class PublisherListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String id = request.getParameter("id");

        if (action != null && id != null && "delete".equals(action)) {
            NhaXuatBanDAO dao = new NhaXuatBanDAO();
            NhaXuatBan publisher = new NhaXuatBan();
            publisher.setMaNhaXuatBan(id);
            dao.delete(publisher);
            request.getSession().setAttribute("successMessage", "Đã xóa nhà xuất bản thành công.");
            response.sendRedirect(request.getContextPath() + "/admin/publishers");
            return;
        }

        NhaXuatBanDAO dao = new NhaXuatBanDAO();
        request.setAttribute("publishers", dao.selectAll());
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/publisher-list.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String maNhaXuatBan = request.getParameter("maNhaXuatBan");
        String tenNhaXuatBan = request.getParameter("tenNhaXuatBan");
        String diaChi = request.getParameter("diaChi");
        String soDienThoai = request.getParameter("soDienThoai");

        NhaXuatBanDAO dao = new NhaXuatBanDAO();
        NhaXuatBan publisher = new NhaXuatBan(maNhaXuatBan, tenNhaXuatBan, diaChi, soDienThoai);

        if ("add".equals(action)) {
            dao.insert(publisher);
            request.getSession().setAttribute("successMessage", "Thêm nhà xuất bản thành công.");
        } else if ("update".equals(action)) {
            dao.update(publisher);
            request.getSession().setAttribute("successMessage", "Cập nhật nhà xuất bản thành công.");
        }

        response.sendRedirect(request.getContextPath() + "/admin/publishers");
    }
}




