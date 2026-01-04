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

// ✅ SỬA LỖI 1: Import đúng thư viện Timestamp
import java.sql.Timestamp; 

import java.util.Date;
import model.KhachHang;
import util.DateUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "XacThucMaServlet", urlPatterns = {"/xac-thuc-ma"})
public class XacThucMaServlet extends HttpServlet {

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
            out.println("<title>Servlet XacThucMaServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet XacThucMaServlet at " + request.getContextPath() + "</h1>");
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
        /**
         * Xử lý POST request: Nhận email và mã xác thực từ form verify-code.jsp,
         * kiểm tra tính hợp lệ và chuyển hướng đến trang đặt lại mật khẩu nếu đúng.
         */
        protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String email = request.getParameter("email");
        String maXacThucNhap = request.getParameter("verificationCode");
        String url = "";
        String error = "";

        if (email == null || email.trim().isEmpty() || maXacThucNhap == null || maXacThucNhap.trim().isEmpty()) {
            error = "Vui lòng nhập đầy đủ email và mã xác thực.";
            // Không có email để trả về trang nhập mã, nên về trang quên MK
            url = "/views/khachhang/xacthuc/forgot-password.jsp";
            request.setAttribute("error", error);
        } else {
            try {
                KhachHangDAO khachHangDAO = new KhachHangDAO();
                KhachHang user = khachHangDAO.selectByEmail(email);

                if (user == null) {
                    error = "Email không tồn tại.";
                    url = "/views/khachhang/xacthuc/forgot-password.jsp";
                    request.setAttribute("error", error);
                } else {
                    String dbCode = user.getMaXacThuc();
                    // SỬA LỖI 2: Lấy Timestamp từ user
                    Timestamp expiryTime = user.getThoiGianHieuLucCuaMaXacThuc();
                    // SỬA LỖI 3: Lấy Timestamp hiện tại
                    Timestamp now = DateUtil.getCurrentSqlTimestamp();

                    if (!maXacThucNhap.equals(dbCode)) {
                        error = "Mã xác thực không chính xác!";
                    } else if (expiryTime == null || expiryTime.before(now)) { // So sánh Timestamp
                        error = "Mã xác thực đã hết hạn hoặc không hợp lệ. Vui lòng yêu cầu mã mới.";
                         // Xóa mã hết hạn khỏi DB cho an toàn
                         khachHangDAO.updateAuthCode(email, null, null);
                    }

                    if (!error.isEmpty()) {
                        // Nếu lỗi, quay lại trang nhập mã
                        request.setAttribute("error", error);
                        request.setAttribute("email", email); // Gửi lại email để JSP hiển thị
                        url = "/views/khachhang/xacthuc/verify-code.jsp";
                    } else {
                        // Xác thực thành công -> Chuyển sang trang đặt lại mật khẩu
                        request.setAttribute("email", email);
                        // Gửi mã đi như một "token" để trang reset biết là đã xác thực
                        request.setAttribute("token", maXacThucNhap);
                        url = "/views/khachhang/xacthuc/reset-password.jsp";
                        
                        // Cập nhật trạng thái đã xác thực (tùy chọn, nếu cần)
                        // khachHangDAO.updateVerificationStatus(email, true);
                        
                         // Xóa mã đã sử dụng khỏi DB
                         khachHangDAO.updateAuthCode(email, null, null);
                    }
                }
            } catch (Exception e) {
                 System.err.println("!!! LỖI TRONG XacThucMaServlet doPost: " + e.getMessage());
                 e.printStackTrace();
                 error = "Đã xảy ra lỗi không mong muốn khi xác thực mã.";
                 request.setAttribute("error", error);
                 request.setAttribute("email", email);
                 url = "/views/khachhang/xacthuc/verify-code.jsp";
            }
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