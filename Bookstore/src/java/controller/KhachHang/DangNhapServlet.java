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
import model.KhachHang;
import util.PasswordUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "DangNhapServlet", urlPatterns = {"/dang-nhap"})
public class DangNhapServlet extends HttpServlet {

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
            out.println("<title>Servlet DangNhapServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet DangNhapServlet at " + request.getContextPath() + "</h1>");
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

        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/xacthuc/login.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String tenDangNhap = request.getParameter("username");
        String matKhauNhap = request.getParameter("password");

        KhachHangDAO khachHangDAO = new KhachHangDAO();
        KhachHang user = null;
        String error = "";
        String urlForwardOnError = "/views/khachhang/xacthuc/login.jsp";

        if (tenDangNhap == null || tenDangNhap.trim().isEmpty() || matKhauNhap == null || matKhauNhap.isEmpty()) {
            error = "Vui lòng nhập tên đăng nhập và mật khẩu.";
        } else {
            tenDangNhap = tenDangNhap.trim();
            try {
                user = khachHangDAO.selectByUsername(tenDangNhap);
                
                // Đảm bảo thông tin gói cước được khởi tạo đúng (tránh NullPointerException)
                if (user != null) {
                    // Khởi tạo giá trị mặc định nếu null
                    try {
                        if (user.getMaGoiCuoc() == null) {
                            user.setMaGoiCuoc(null);
                        }
                        if (user.getNgayDangKy() == null) {
                            user.setNgayDangKy(null);
                        }
                        if (user.getNgayHetHan() == null) {
                            user.setNgayHetHan(null);
                        }
                    } catch (Exception e) {
                        // Nếu có lỗi khi truy cập các trường, đặt giá trị null
                        System.err.println("Lỗi khi khởi tạo thông tin gói cước: " + e.getMessage());
                        user.setMaGoiCuoc(null);
                        user.setNgayDangKy(null);
                        user.setNgayHetHan(null);
                    }
                }

                if (user != null) {
                    String hashedPasswordFromDB = user.getMatKhau();

                    // --- Kiểm tra Mật khẩu ---
                    if (hashedPasswordFromDB != null && !hashedPasswordFromDB.isEmpty()
                        && PasswordUtil.check(matKhauNhap, hashedPasswordFromDB))
                    {
                        // Mật khẩu ĐÚNG
                        if (user.getStatus() == 1) {
                            // ĐĂNG NHẬP THÀNH CÔNG!
                            HttpSession oldSession = request.getSession(false);
                            if (oldSession != null) {
                                oldSession.invalidate();
                            }

                            HttpSession session = request.getSession(true);
                            session.setAttribute("user", user);
                            
                            // Nếu là admin, thêm attribute riêng và xóa giỏ hàng (nếu có)
                            if (user.getRole() == 1) {
                                session.setAttribute("admin", user);
                                // Xóa giỏ hàng của admin để không thể mua hàng
                                session.removeAttribute("cart");
                                session.removeAttribute("cartCount");
                            }
                            
                            session.setMaxInactiveInterval(30 * 60); 

                            // --- Chuyển hướng theo Role ---
                            String redirectUrl;
                            
                            if (user.getRole() == 1) {
                                // Admin - redirect đến trang chủ (giống người dùng)
                                System.out.println("Admin login successful: " + tenDangNhap);
                                redirectUrl = request.getContextPath() + "/trang-chu";
                            } else {
                                // Customer - redirect đến trang chủ
                                System.out.println("User login successful: " + tenDangNhap);
                                redirectUrl = request.getContextPath() + "/trang-chu";
                                
                                // Kiểm tra redirectAfterLogin từ session (chỉ cho customer)
                                String redirectAfterLogin = (String) session.getAttribute("redirectAfterLogin");
                                if (redirectAfterLogin != null && !redirectAfterLogin.isEmpty()) {
                                    redirectUrl = redirectAfterLogin;
                                    session.removeAttribute("redirectAfterLogin");
                                }
                                
                                // Thêm thông báo đăng nhập thành công cho customer
                                redirectUrl += "?notification=success&title=" + 
                                             java.net.URLEncoder.encode("Đăng nhập thành công", "UTF-8") +
                                             "&message=" + java.net.URLEncoder.encode("Chào mừng bạn quay trở lại!", "UTF-8");
                            }
                            
                            response.sendRedirect(redirectUrl);
                            return; 
                        } else {
                            // Tài khoản bị khóa
                            System.out.println("Login failed: Account locked for " + tenDangNhap);
                            error = "Tài khoản của bạn hiện đang bị khóa.";
                        }
                    } else {
                        // Mật khẩu SAI
                        System.out.println("Login failed: Incorrect password for " + tenDangNhap);
                        error = "Tên đăng nhập hoặc mật khẩu không chính xác.";
                    }
                } else {
                    // User KHÔNG tồn tại
                    System.out.println("Login failed: Username not found: " + tenDangNhap);
                    error = "Tên đăng nhập hoặc mật khẩu không chính xác.";
                }

            } catch (Exception e) {
                System.err.println("!!! LỖI HỆ THỐNG trong DangNhapServlet doPost: " + e.getMessage());
                e.printStackTrace();
                error = "Đã xảy ra lỗi hệ thống trong quá trình đăng nhập.";
            }
        }

        // --- Xử lý khi Đăng nhập Thất bại ---
        System.out.println("Forwarding back to login page with error: " + error);
        request.setAttribute("error", error);
        request.setAttribute("username", tenDangNhap);
        RequestDispatcher rd = getServletContext().getRequestDispatcher(urlForwardOnError);
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
