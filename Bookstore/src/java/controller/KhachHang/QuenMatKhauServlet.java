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
import util.EmailUtil;
import util.MaGeneratorUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "QuenMatKhauServlet", urlPatterns = {"/quen-mat-khau"})
public class QuenMatKhauServlet extends HttpServlet {

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
            out.println("<title>Servlet QuenMatKhauServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet QuenMatKhauServlet at " + request.getContextPath() + "</h1>");
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
        // Tạm thời dùng version đơn giản để test
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/xacthuc/forgot-password-simple.jsp");
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

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String email = request.getParameter("email");
        String url = ""; // URL để forward

        if (email == null || email.trim().isEmpty()) {
            request.setAttribute("error", "Vui lòng nhập địa chỉ email.");
            url = "/views/khachhang/xacthuc/forgot-password-simple.jsp";
        } else {
            try {
                KhachHangDAO khachHangDAO = new KhachHangDAO();
                KhachHang user = khachHangDAO.selectByEmail(email);

                if (user == null) {
                    request.setAttribute("error", "Email không tồn tại trong hệ thống!");
                    request.setAttribute("email", email); // Giữ lại email đã nhập
                    url = "/views/khachhang/xacthuc/forgot-password-simple.jsp";
                } else {
                    // Tạo mã xác thực
                    String maXacThuc = MaGeneratorUtil.generateRandomCode(6);

                    // SỬA LỖI 2: Dùng hàm mới trong DateUtil để lấy Timestamp sau 10 phút
                    Timestamp thoiGianHieuLuc = DateUtil.getSqlTimestampAfterMinutes(10); // 10 phút hiệu lực

                    // Cập nhật mã và thời gian vào CSDL
                    int updateResult = khachHangDAO.updateAuthCode(email, maXacThuc, thoiGianHieuLuc);

                    if (updateResult > 0) {
                        // Gửi email
                        String tenNguoiNhan = (user.getHoVaTen() != null && !user.getHoVaTen().trim().isEmpty()) 
                                            ? user.getHoVaTen() 
                                            : "Người dùng";
                        String noiDungEmail = EmailUtil.createVerificationEmailContent(tenNguoiNhan, maXacThuc);
                        System.out.println("QuenMatKhauServlet: Đang gửi email đến " + email);
                        boolean emailSent = EmailUtil.sendEmail(email, "Yêu cầu đặt lại mật khẩu BookStore", noiDungEmail);
                        System.out.println("QuenMatKhauServlet: Kết quả gửi email: " + emailSent);

                        if (emailSent) {
                            // Chuyển sang trang nhập mã
                            request.setAttribute("email", email);
                            url = "/views/khachhang/xacthuc/verify-code.jsp";
                        } else {
                             request.setAttribute("error", "Lỗi khi gửi email xác thực. Vui lòng thử lại.");
                             request.setAttribute("email", email);
                             url = "/views/khachhang/xacthuc/forgot-password-simple.jsp";
                        }
                    } else {
                         request.setAttribute("error", "Lỗi hệ thống khi cập nhật mã xác thực.");
                         request.setAttribute("email", email);
                         url = "/views/khachhang/xacthuc/forgot-password-simple.jsp";
                    }
                }
            } catch (Exception e) {
                 System.err.println("!!! ========================================");
                 System.err.println("!!! LỖI TRONG QuenMatKhauServlet doPost");
                 System.err.println("!!! Email: " + email);
                 System.err.println("!!! Loại lỗi: " + e.getClass().getName());
                 System.err.println("!!! Thông báo: " + e.getMessage());
                 System.err.println("!!! Chi tiết lỗi:");
                 e.printStackTrace();
                 System.err.println("!!! ========================================");
                 
                 // Hiển thị lỗi chi tiết hơn
                 String errorMessage = "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.";
                 
                 if (e.getMessage() != null) {
                     String msg = e.getMessage().toLowerCase();
                     if (msg.contains("authentication") || msg.contains("535")) {
                         errorMessage = "Lỗi xác thực email. Vui lòng kiểm tra cấu hình email hoặc liên hệ quản trị viên.";
                     } else if (msg.contains("sql") || msg.contains("database") || msg.contains("connection")) {
                         errorMessage = "Lỗi kết nối database. Vui lòng thử lại sau.";
                     } else if (msg.contains("nullpointer")) {
                         errorMessage = "Lỗi hệ thống: Dữ liệu không hợp lệ. Vui lòng thử lại.";
                     } else if (msg.contains("timeout") || msg.contains("connection timeout")) {
                         errorMessage = "Lỗi kết nối. Vui lòng kiểm tra internet và thử lại.";
                     }
                 }
                 
                 request.setAttribute("error", errorMessage);
                 if (email != null) {
                     request.setAttribute("email", email);
                 }
                 url = "/views/khachhang/xacthuc/forgot-password-simple.jsp";
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