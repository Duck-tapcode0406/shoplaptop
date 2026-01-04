/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.KhuyenMaiDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.util.ArrayList;
import model.KhuyenMai;

/**
 *
 * @author Acer
 */
@WebServlet(name = "KhuyenMaiServlet", urlPatterns = {"/khuyen-mai"})
public class KhuyenMaiServlet extends HttpServlet {

    /**
     * Processes requests for both HTTP <code>GET</code> and <code>POST</code>
     * methods.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    protected void processRequest(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        try (PrintWriter out = response.getWriter()) {
            /* TODO output your page here. You may use following sample code. */
            out.println("<!DOCTYPE html>");
            out.println("<html>");
            out.println("<head>");
            out.println("<title>Servlet KhuyenMaiServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet KhuyenMaiServlet at " + request.getContextPath() + "</h1>");
            out.println("</body>");
            out.println("</html>");
        }
    }

    // <editor-fold defaultstate="collapsed" desc="HttpServlet methods. Click on the + sign on the left to edit the code.">
    /**
     * Handles the HTTP <code>GET</code> method.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        KhuyenMaiDAO khuyenMaiDAO = new KhuyenMaiDAO();
        ArrayList<KhuyenMai> listKhuyenMai = null;
        String error = null;

        try {
            // Lấy tất cả khuyến mãi (có thể cần lọc các KM còn hạn/active)
            listKhuyenMai = khuyenMaiDAO.selectAll(); // Giả sử hàm này lấy tất cả
            // Hoặc tạo hàm mới: khuyenMaiDAO.selectAllActiveAndCurrent();
        } catch (Exception e) {
            System.err.println("!!! LỖI TRONG KhuyenMaiServlet doGet: " + e.getMessage());
            e.printStackTrace();
            error = "Không thể tải danh sách khuyến mãi. Vui lòng thử lại sau.";
        }

        // --- Gửi dữ liệu sang JSP ---
        if (error != null) {
            request.setAttribute("errorMessage", error);
        }
        // Gửi danh sách rỗng nếu có lỗi hoặc DAO trả về null
        request.setAttribute("listKhuyenMai", (listKhuyenMai != null) ? listKhuyenMai : new ArrayList<KhuyenMai>());

        // --- Forward đến trang JSP hiển thị khuyến mãi ---
        // Đảm bảo đường dẫn JSP đúng
        String url = "/views/khachhang/promotions.jsp"; // Hoặc tên file JSP của bạn
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
        rd.forward(request, response);
    }

    /**
     * Returns a short description of the servlet.
     *
     * @return a String containing servlet description
     */
    @Override
    public String getServletInfo() {
        return "Short description";
    }// </editor-fold>

}
