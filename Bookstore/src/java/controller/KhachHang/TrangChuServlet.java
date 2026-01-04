/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.SanPhamDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.util.ArrayList;
import model.SanPham;

/**
 *
 * @author Acer
 */
@WebServlet(name = "TrangChuServlet", urlPatterns = {"/trang-chu"})
public class TrangChuServlet extends HttpServlet {

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
            out.println("<title>Servlet TrangChuServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet TrangChuServlet at " + request.getContextPath() + "</h1>");
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

        // --- Thiết lập Encoding ---
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        // --- Khởi tạo DAO ---
        SanPhamDAO sanPhamDAO = new SanPhamDAO();

        // --- Lấy dữ liệu từ Database ---
        ArrayList<SanPham> danhSachSachMoi = null;
        ArrayList<SanPham> danhSachBanChay = null;
        String daoError = null; // Biến lưu lỗi nếu có

        try {
            // Lấy 5 sách mới nhất
            danhSachSachMoi = sanPhamDAO.getNewestProducts(5);

            // Lấy 5 sách bán chạy nhất
            danhSachBanChay = sanPhamDAO.getBestsellerProducts(5);

        } catch (Exception e) {
            // Ghi log lỗi chi tiết ra Console
            System.err.println("!!! LỖI NGHIÊM TRỌNG TRONG TrangChuServlet KHI GỌI DAO !!!");
            e.printStackTrace();
            daoError = "Không thể tải dữ liệu sản phẩm. Vui lòng thử lại sau.";
        }

        // --- Đặt dữ liệu (hoặc lỗi) vào request ---
        if (daoError != null) {
            request.setAttribute("daoError", daoError); // Gửi lỗi sang JSP
        } else {
            // Nếu DAO không có lỗi
            request.setAttribute("danhSachSachMoi",
                    (danhSachSachMoi != null) ? danhSachSachMoi : new ArrayList<SanPham>());

            request.setAttribute("danhSachBanChay",
                    (danhSachBanChay != null) ? danhSachBanChay : new ArrayList<SanPham>());
        }

        // --- Chuyển hướng đến JSP ---
        RequestDispatcher rd = getServletContext()
                .getRequestDispatcher("/views/khachhang/index.jsp");
        rd.forward(request, response);
    }
    

    /**
     * Handles the HTTP <code>POST</code> method.
     *
     * @param request servlet request
     * @param response servlet response
     * @throws ServletException if a servlet-specific error occurs
     * @throws IOException if an I/O error occurs
     */
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        processRequest(request, response);
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
