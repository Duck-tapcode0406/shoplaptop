package controller.Admin;

import database.SanPhamDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.SanPham;

import java.io.IOException;
import java.util.ArrayList;

@WebServlet(name = "ProductListServlet", urlPatterns = {"/admin/products"})
public class ProductListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String action = request.getParameter("action");
        String maSanPham = request.getParameter("id");

        if (action != null && maSanPham != null) {
            SanPhamDAO sanPhamDAO = new SanPhamDAO();
            SanPham sp = new SanPham();
            sp.setMaSanPham(maSanPham);
            sp = sanPhamDAO.selectById(sp);
            
            if (sp != null) {
                if ("toggle".equals(action)) {
                    // Toggle status (ẩn/hiện)
                    sp.setTrangThai(sp.getTrangThai() == 1 ? 0 : 1);
                    sanPhamDAO.update(sp);
                    request.getSession().setAttribute("successMessage", 
                        sp.getTrangThai() == 1 ? "Đã hiển thị sản phẩm." : "Đã ẩn sản phẩm.");
                } else if ("delete".equals(action)) {
                    sanPhamDAO.delete(sp);
                    request.getSession().setAttribute("successMessage", "Đã xóa sản phẩm.");
                }
            }
            response.sendRedirect(request.getContextPath() + "/admin/products");
            return;
        }

        SanPhamDAO sanPhamDAO = new SanPhamDAO();
        ArrayList<SanPham> products = sanPhamDAO.selectAll();

        request.setAttribute("products", products);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/product-list.jsp");
        rd.forward(request, response);
    }
}




