/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.TinTucDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.TinTuc;

/**
 *
 * @author Acer
 */
@WebServlet(name = "ChiTietTinTucServlet", urlPatterns = {"/chi-tiet-tin-tuc"})
public class ChiTietTinTucServlet extends HttpServlet {

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
            out.println("<title>Servlet ChiTietTinTucServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet ChiTietTinTucServlet at " + request.getContextPath() + "</h1>");
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

        // --- 1. Lấy ID Tin tức từ URL ---
        String newsId = request.getParameter("id");
        String error = null;
        TinTuc tinTuc = null; // Biến để lưu trữ đối tượng TinTuc

        // --- 2. Kiểm tra ID ---
        if (newsId == null || newsId.trim().isEmpty()) {
            error = "Mã bài viết không hợp lệ hoặc bị thiếu.";
        } else {
            try {
                // --- 3. Khởi tạo DAO và Gọi hàm lấy chi tiết ---
                TinTucDAO tinTucDAO = new TinTucDAO();
                // Gọi hàm selectById(String) đã tạo/sửa trong DAO
                tinTuc = tinTucDAO.selectById(newsId);

                // --- 4. Kiểm tra kết quả ---
                if (tinTuc == null) {
                    error = "Không tìm thấy bài viết hoặc bài viết không tồn tại.";
                }
                // (Không cần kiểm tra nguoiDang ở đây vì DAO đã xử lý)

            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG ChiTietTinTucServlet - Lỗi DAO khi gọi selectById cho ID '" + newsId + "': " + e.getMessage());
                e.printStackTrace();
                error = "Đã xảy ra lỗi hệ thống khi tải dữ liệu bài viết.";
            }
        }

        // --- 5. Đặt thuộc tính vào request ---
        if (error != null) {
            request.setAttribute("errorMessage", error);
            System.out.println("Forwarding error to JSP: " + error); // Debug lỗi
        }
        // Đặt tên attribute khớp với ${tinTuc} trong news-detail.jsp
        request.setAttribute("tinTuc", tinTuc);
        // Debug xem đối tượng tinTuc có null hay không
        System.out.println("Forwarding tinTuc to JSP: " + (tinTuc != null ? tinTuc.getTieuDe() : "NULL"));

        // --- 6. Forward đến JSP ---
        String url = "/views/khachhang/tintuc/news-detail.jsp";
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
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
