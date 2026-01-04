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
import java.sql.Date;
import model.KhachHang;
import util.MaGeneratorUtil;
import util.PasswordUtil;

/**
 *
 * @author Acer
 */
@WebServlet(name = "DangKyServlet", urlPatterns = {"/dang-ky"})
public class DangKyServlet extends HttpServlet {

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
            out.println("<title>Servlet DangKyServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet DangKyServlet at " + request.getContextPath() + "</h1>");
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
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/xacthuc/register.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");

        String tenDangNhap = request.getParameter("username");
        String email = request.getParameter("email");
        String hoTen = request.getParameter("fullName");
        String matKhau = request.getParameter("password");
        String xacNhanMatKhau = request.getParameter("confirmPassword");

        String url = "/views/khachhang/xacthuc/register.jsp"; 
        String error = "";

        KhachHangDAO khachHangDAO = new KhachHangDAO();

        if (tenDangNhap == null || tenDangNhap.trim().isEmpty() ||
            email == null || email.trim().isEmpty() ||
            hoTen == null || hoTen.trim().isEmpty() ||
            matKhau == null || matKhau.isEmpty() ||
            xacNhanMatKhau == null || xacNhanMatKhau.isEmpty())
        {
             error = "Vui lòng nhập đầy đủ thông tin bắt buộc.";
        }
        else if (!matKhau.equals(xacNhanMatKhau)) {
            error = "Mật khẩu xác nhận không khớp!";
        }
        else if (matKhau.length() < 6) {
             error = "Mật khẩu phải có ít nhất 6 ký tự.";
        }
        else if (!email.matches("^[\\w-\\+]+(\\.[\\w]+)*@[\\w-]+(\\.[\\w]+)*(\\.[a-z]{2,})$")) {
             error = "Định dạng email không hợp lệ.";
        }
        else {
            tenDangNhap = tenDangNhap.trim();
            email = email.trim();
            hoTen = hoTen.trim();
            try {
                if (khachHangDAO.checkUsernameExists(tenDangNhap)) {
                    error = "Tên đăng nhập '" + tenDangNhap + "' đã tồn tại!";
                }
                else if (khachHangDAO.checkEmailExists(email)) {
                    error = "Email '" + email + "' đã được sử dụng!";
                }
            } catch (Exception e) {
                 System.err.println("Lỗi DAO khi kiểm tra tồn tại user/email: " + e.getMessage());
                 e.printStackTrace();
                 error = "Lỗi hệ thống khi kiểm tra thông tin. Vui lòng thử lại.";
            }
        }

        // --- Xử lý Kết quả Validation ---
        if (!error.isEmpty()) {
            // Có lỗi -> Forward về trang đăng ký
            request.setAttribute("error", error);
            request.setAttribute("username", tenDangNhap);
            request.setAttribute("email", email);
            request.setAttribute("fullName", hoTen);
            RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
            rd.forward(request, response);
        } else {
            // Không có lỗi -> Tiến hành đăng ký
            try {
                String maKhachHang = MaGeneratorUtil.generateCustomerCode();
                KhachHang kh = new KhachHang();
                kh.setMaKhachHang(maKhachHang);
                kh.setTenDangNhap(tenDangNhap);
                kh.setMatKhau(matKhau);
                kh.setHoVaTen(hoTen);
                kh.setEmail(email);

                kh.setGioiTinh("Nam"); 
                kh.setDiaChi("");
                kh.setDiaChiNhanHang("");
                kh.setDiaChiMuaHang("");
                kh.setNgaySinh(new Date(System.currentTimeMillis())); 
                kh.setSoDienThoai("");
                kh.setDangKyNhanBangTin(false); 
                kh.setStatus(1); 
                kh.setRole(0);  

                int result = khachHangDAO.insert(kh); 

                if (result > 0) {
                    // Đăng ký thành công
                    request.getSession().setAttribute("successMessage", "Đăng ký thành công! Vui lòng đăng nhập.");
                    response.sendRedirect(request.getContextPath() + "/dang-nhap");
                } else {
                     // Lỗi không xác định khi insert
                     request.setAttribute("error", "Đăng ký thất bại do lỗi hệ thống khi lưu dữ liệu.");
                     request.setAttribute("username", tenDangNhap);
                     request.setAttribute("email", email);
                     request.setAttribute("fullName", hoTen);
                     RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
                     rd.forward(request, response);
                }

            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG DangKyServlet doPost: " + e.getMessage());
                e.printStackTrace();
                request.setAttribute("error", "Đã xảy ra lỗi không mong muốn trong quá trình đăng ký.");
                request.setAttribute("username", tenDangNhap);
                request.setAttribute("email", email);
                request.setAttribute("fullName", hoTen);
                RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
                rd.forward(request, response);
            }
        }
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
