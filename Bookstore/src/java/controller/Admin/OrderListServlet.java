package controller.Admin;

import database.DonHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.DonHang;

import java.io.IOException;
import java.util.ArrayList;

@WebServlet(name = "OrderListServlet", urlPatterns = {"/admin/orders"})
public class OrderListServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String searchKeyword = request.getParameter("search");
        String statusFilter = request.getParameter("status");
        
        DonHangDAO donHangDAO = new DonHangDAO();
        ArrayList<DonHang> orders;

        // Tìm kiếm theo tên sách hoặc tác giả nếu có
        if (searchKeyword != null && !searchKeyword.trim().isEmpty()) {
            orders = donHangDAO.searchByProductOrAuthor(searchKeyword.trim());
        } else {
            orders = donHangDAO.selectAll();
        }

        // Filter by status if provided
        if (statusFilter != null && !statusFilter.isEmpty()) {
            ArrayList<DonHang> filtered = new ArrayList<>();
            for (DonHang order : orders) {
                if (statusFilter.equals(order.getTrangThai())) {
                    filtered.add(order);
                }
            }
            orders = filtered;
        }

        request.setAttribute("orders", orders);
        request.setAttribute("statusFilter", statusFilter);
        request.setAttribute("searchKeyword", searchKeyword);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/order-list.jsp");
        rd.forward(request, response);
    }
}




