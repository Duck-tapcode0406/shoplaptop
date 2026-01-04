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
import model.KhachHang;

/**
 *
 * @author Acer
 */
@WebServlet(name = "DatLaiMatKhauServlet", urlPatterns = {"/dat-lai-mat-khau"})
public class DatLaiMatKhauServlet extends HttpServlet {

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
            out.println("<title>Servlet DatLaiMatKhauServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet DatLaiMatKhauServlet at " + request.getContextPath() + "</h1>");
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
        // Không cho phép truy cập GET, đá về trang quên mật khẩu
        response.sendRedirect(request.getContextPath() + "/quen-mat-khau");
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
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");

        String email = request.getParameter("email");
        String token = request.getParameter("token");
        String matKhauMoi = request.getParameter("newPassword");
        String xacNhanMatKhau = request.getParameter("confirmPassword");

        String url = "/views/khachhang/xacthuc/reset-password.jsp";
        String error = "";

        KhachHangDAO khachHangDAO = new KhachHangDAO();

        // --- VALIDATION ---
        if (email == null || email.trim().isEmpty() || token == null || token.trim().isEmpty() ||
            matKhauMoi == null || matKhauMoi.isEmpty() ||
            xacNhanMatKhau == null || xacNhanMatKhau.isEmpty())
        {
            error = "Yêu cầu không hợp lệ hoặc thiếu thông tin.";
        }
        else if (!matKhauMoi.equals(xacNhanMatKhau)) {
            error = "Mật khẩu xác nhận không khớp!";
        }
        else if (matKhauMoi.length() < 6) {
            error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        }
        else {
            email = email.trim();
            token = token.trim();
            try {
                // ✅ Kiểm tra token còn hợp lệ
                KhachHang user = khachHangDAO.selectByEmailAndToken(email, token);

                if (user != null) {
                    // Token hợp lệ -> Đổi mật khẩu
                    int result = khachHangDAO.updatePasswordByEmail(email, matKhauMoi);

                    if (result > 0) {
                        request.getSession().setAttribute("successMessage", "Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại.");
                        response.sendRedirect(request.getContextPath() + "/dang-nhap");
                        return;
                    } else {
                        error = "Đặt lại mật khẩu thất bại do lỗi hệ thống khi cập nhật.";
                    }
                } else {
                    error = "Yêu cầu đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng thử lại quy trình quên mật khẩu.";
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG DatLaiMatKhauServlet: " + e.getMessage());
                e.printStackTrace();
                error = "Đã xảy ra lỗi không mong muốn khi đặt lại mật khẩu.";
            }
        }

        // Nếu có lỗi -> Forward về trang reset password
        request.setAttribute("error", error);
        if (!error.contains("hết hạn") && !error.contains("không hợp lệ")) {
            request.setAttribute("email", email);
            request.setAttribute("token", token);
        }
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
