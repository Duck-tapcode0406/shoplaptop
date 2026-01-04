/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.util.Date;
import model.KhachHang;

/**
 *
 * @author Acer
 */
@WebServlet(name = "HoSoCaNhanServlet", urlPatterns = {"/tai-khoan/ho-so"})
public class HoSoCaNhanServlet extends HttpServlet {

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
            out.println("<title>Servlet HoSoCaNhanServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet HoSoCaNhanServlet at " + request.getContextPath() + "</h1>");
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

        HttpSession session = request.getSession();
        KhachHang user = (KhachHang) session.getAttribute("user");

        // --- BẢO MẬT: Kiểm tra đăng nhập ---
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }

        // --- Hiển thị thông báo từ session (nếu có) ---
        String errorMessage = (String) session.getAttribute("errorMessage");
        String successMessage = (String) session.getAttribute("successMessage");
        if (errorMessage != null) {
            request.setAttribute("errorMessage", errorMessage);
            session.removeAttribute("errorMessage");
        }
        if (successMessage != null) {
            request.setAttribute("successMessage", successMessage);
            session.removeAttribute("successMessage");
        }

        // --- Chỉ hiển thị trang profile.jsp ---
        // Không cần lấy lại user từ DB vì đã có trong session
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/taikhoan/profile.jsp");
        rd.forward(request, response);
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
        String urlRedirect = request.getContextPath() + "/tai-khoan/ho-so"; // Redirect về trang hồ sơ

        // --- BẢO MẬT: Kiểm tra đăng nhập ---
        if (user == null) {
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }

        String error = null;
        String success = null;

        try {
            // --- Lấy dữ liệu từ form ---
            String email = request.getParameter("email");
            String hoTen = request.getParameter("fullName");
            String soDienThoai = request.getParameter("phone");
            String diaChi = request.getParameter("address");
            // String gioiTinh = request.getParameter("gender"); // Lấy nếu có
            // String ngaySinhStr = request.getParameter("birthDate"); // Lấy nếu có

            // --- Validate cơ bản ---
            if (hoTen == null || hoTen.trim().isEmpty()) {
                error = "Họ tên không được để trống.";
            } else {
                KhachHangDAO khachHangDAO = new KhachHangDAO();
                
                // --- Cập nhật thông tin vào đối tượng user trong session ---
                // Lưu ý: Email không thể cập nhật qua hàm update() (bảo mật)
                // Chỉ cập nhật các trường được phép
                user.setHoVaTen(hoTen.trim());
                user.setSoDienThoai(soDienThoai != null ? soDienThoai.trim() : "");
                user.setDiaChi(diaChi != null ? diaChi.trim() : "");
                
                // Cập nhật địa chỉ nhận hàng và địa chỉ mua hàng nếu có
                if (diaChi != null && !diaChi.trim().isEmpty()) {
                    user.setDiaChiNhanHang(diaChi.trim());
                    user.setDiaChiMuaHang(diaChi.trim());
                }

                // --- Gọi DAO để cập nhật vào CSDL ---
                int result = khachHangDAO.update(user);

                if (result > 0) {
                    // Cập nhật thành công, user trong session đã là mới nhất
                    session.setAttribute("user", user); // Đặt lại session để chắc chắn
                    success = "Cập nhật hồ sơ thành công!";
                } else {
                    error = "Cập nhật hồ sơ thất bại do lỗi hệ thống.";
                }
            }

        } catch (Exception e) {
            System.err.println("!!! LỖI TRONG HoSoCaNhanServlet doPost: " + e.getMessage());
            e.printStackTrace();
            error = "Đã xảy ra lỗi không mong muốn khi cập nhật hồ sơ.";
        }

        // --- Redirect về trang hồ sơ với thông báo (dùng session) ---
        if (error != null) {
            session.setAttribute("errorMessage", error);
        }
        if (success != null) {
            session.setAttribute("successMessage", success);
        }
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
