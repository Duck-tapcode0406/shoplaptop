/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.DanhGiaDAO;
import database.DonHangDAO;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.DanhGia;
import model.KhachHang;
import model.SanPham;
import util.DateUtil;
import util.MaGeneratorUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "DangDanhGiaServlet", urlPatterns = {"/dang-danh-gia"})
public class DangDanhGiaServlet extends HttpServlet {

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
            out.println("<title>Servlet DangDanhGiaServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet DangDanhGiaServlet at " + request.getContextPath() + "</h1>");
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
        // Nếu truy cập GET, redirect về trang chủ hoặc trang sản phẩm trước đó
        String referer = request.getHeader("Referer");
        if (referer != null && !referer.isEmpty()) {
            response.sendRedirect(referer);
        } else {
            response.sendRedirect(request.getContextPath() + "/trang-chu");
        }
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        // --- SỬA LỖI: Thêm Encoding ---
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");
        String productId = request.getParameter("productId");
        String urlRedirect = request.getContextPath() + "/chi-tiet-san-pham?id=" + productId + "#tab-reviews"; // Redirect về tab đánh giá

        // --- BẢO MẬT: Kiểm tra đăng nhập ---
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap?redirect=" + urlRedirect); // Yêu cầu đăng nhập và quay lại
            return;
        }

        // --- Validate cơ bản ---
        String content = request.getParameter("content");
        String ratingStr = request.getParameter("rating");
        int rating = 0;

        if (productId == null || productId.trim().isEmpty() ||
            content == null || content.trim().isEmpty() ||
            ratingStr == null || ratingStr.trim().isEmpty()) {
            session.setAttribute("errorMessage", "Vui lòng nhập đầy đủ xếp hạng và nội dung đánh giá.");
            response.sendRedirect(urlRedirect);
            return;
        }

        try {
            rating = Integer.parseInt(ratingStr);
            if (rating < 1 || rating > 5) {
                 session.setAttribute("errorMessage", "Xếp hạng không hợp lệ.");
                 response.sendRedirect(urlRedirect);
                 return;
            }

            // --- BẢO MẬT: Kiểm tra xem user đã mua sản phẩm này chưa ---
            DonHangDAO donHangDAO = new DonHangDAO();
            boolean daMuaHang = donHangDAO.checkIfCustomerBoughtProduct(user.getMaKhachHang(), productId);

            if (!daMuaHang) {
                 session.setAttribute("errorMessage", "Bạn chỉ có thể đánh giá sản phẩm đã mua.");
                 response.sendRedirect(urlRedirect);
                 return;
            }

            // --- Tạo đối tượng DanhGia ---
            DanhGia dg = new DanhGia();
            dg.setMaDanhGia(MaGeneratorUtil.generateUUID()); // Tạo ID mới
            dg.setKhachHang(user); // Gán đối tượng user

            SanPham sp = new SanPham();
            sp.setMaSanPham(productId);
            dg.setSanPham(sp); // Gán đối tượng sản phẩm (chỉ cần mã)

            dg.setSoSao(rating);
            dg.setNoiDung(content.trim()); // Trim() để xóa khoảng trắng thừa
            // Lưu Timestamp để có đầy đủ ngày và giờ
            java.sql.Timestamp timestamp = DateUtil.getCurrentSqlTimestamp();
            dg.setNgayDanhGia(new java.sql.Date(timestamp.getTime())); // Chuyển Timestamp sang Date để tương thích với model

            // --- Gọi DAO để lưu ---
            DanhGiaDAO danhGiaDAO = new DanhGiaDAO();
            int result = danhGiaDAO.insert(dg);

            if (result > 0) {
                 session.setAttribute("successMessage", "Gửi đánh giá thành công!");
            } else {
                 session.setAttribute("errorMessage", "Gửi đánh giá thất bại do lỗi hệ thống.");
            }

        } catch (NumberFormatException e) {
             session.setAttribute("errorMessage", "Xếp hạng phải là một số.");
        } catch (Exception e) {
            System.err.println("!!! LỖI TRONG DangDanhGiaServlet doPost: " + e.getMessage());
            e.printStackTrace();
            session.setAttribute("errorMessage", "Đã xảy ra lỗi không mong muốn khi gửi đánh giá.");
        }

        // --- Redirect về trang sản phẩm ---
        response.sendRedirect(urlRedirect);
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
