package controller.Admin;

import database.KhuyenMaiDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.KhuyenMai;

import java.io.IOException;
import java.sql.Date;

@WebServlet(name = "PromotionListServlet", urlPatterns = {"/admin/promotions"})
public class PromotionListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String id = request.getParameter("id");

        if (action != null && id != null && "delete".equals(action)) {
            KhuyenMaiDAO dao = new KhuyenMaiDAO();
            KhuyenMai promotion = new KhuyenMai();
            promotion.setMaKhuyenMai(id);
            dao.delete(promotion);
            request.getSession().setAttribute("successMessage", "Đã xóa khuyến mãi thành công.");
            response.sendRedirect(request.getContextPath() + "/admin/promotions");
            return;
        }

        KhuyenMaiDAO dao = new KhuyenMaiDAO();
        request.setAttribute("promotions", dao.selectAll());
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/promotion-list.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String maKhuyenMai = request.getParameter("maKhuyenMai");
        String tenKhuyenMai = request.getParameter("tenKhuyenMai");
        double phanTramGiam = Double.parseDouble(request.getParameter("phanTramGiam"));
        double soTienGiamToiDa = Double.parseDouble(request.getParameter("soTienGiamToiDa"));
        Date ngayBatDau = Date.valueOf(request.getParameter("ngayBatDau"));
        Date ngayKetThuc = Date.valueOf(request.getParameter("ngayKetThuc"));
        boolean trangThai = "true".equals(request.getParameter("trangThai"));

        KhuyenMaiDAO dao = new KhuyenMaiDAO();
        KhuyenMai promotion = new KhuyenMai(maKhuyenMai, tenKhuyenMai, phanTramGiam, 
                soTienGiamToiDa, ngayBatDau, ngayKetThuc, trangThai);

        if ("add".equals(action)) {
            dao.insert(promotion);
            request.getSession().setAttribute("successMessage", "Thêm khuyến mãi thành công.");
        } else if ("update".equals(action)) {
            dao.update(promotion);
            request.getSession().setAttribute("successMessage", "Cập nhật khuyến mãi thành công.");
        }

        response.sendRedirect(request.getContextPath() + "/admin/promotions");
    }
}




