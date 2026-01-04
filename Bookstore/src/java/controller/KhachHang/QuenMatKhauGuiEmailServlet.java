package controller.KhachHang;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import model.KhachHang;
import util.EmailUtil;
import util.PasswordGeneratorUtil;

/**
 * Servlet xử lý chức năng quên mật khẩu - gửi mật khẩu mới trực tiếp qua email
 * 
 * @author BookStore Team
 */
@WebServlet(name = "QuenMatKhauGuiEmailServlet", urlPatterns = {"/quen-mat-khau-gui-email"})
public class QuenMatKhauGuiEmailServlet extends HttpServlet {

    /**
     * Handles the HTTP <code>GET</code> method.
     * Hiển thị form nhập email
     */
    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/xacthuc/forgot-password-send-email.jsp");
        rd.forward(request, response);
    }

    /**
     * Handles the HTTP <code>POST</code> method.
     * Xử lý yêu cầu quên mật khẩu: tạo mật khẩu mới và gửi qua email
     */
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String email = request.getParameter("email");
        String url = "";

        if (email == null || email.trim().isEmpty()) {
            request.setAttribute("error", "Vui lòng nhập địa chỉ email.");
            url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
        } else {
            try {
                KhachHangDAO khachHangDAO = new KhachHangDAO();
                KhachHang user = khachHangDAO.selectByEmail(email.trim());

                if (user == null) {
                    request.setAttribute("error", "Email không tồn tại trong hệ thống!");
                    request.setAttribute("email", email);
                    url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                } else {
                    // Tạo mật khẩu mới ngẫu nhiên (12 ký tự)
                    String matKhauMoi = PasswordGeneratorUtil.generateRandomPassword(12);

                    // Cập nhật mật khẩu mới vào database (tự động hash)
                    int updateResult = khachHangDAO.updatePasswordByEmail(email.trim(), matKhauMoi);

                    if (updateResult > 0) {
                        // Gửi email chứa mật khẩu mới
                        String noiDungEmail = EmailUtil.createNewPasswordEmailContent(user.getHoVaTen(), matKhauMoi);
                        boolean emailSent = EmailUtil.sendEmail(email.trim(), "Mật khẩu mới - BookStore", noiDungEmail);

                        if (emailSent) {
                            request.setAttribute("success", "Mật khẩu mới đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.");
                            request.setAttribute("email", email);
                            url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                        } else {
                            request.setAttribute("error", "Lỗi khi gửi email. Vui lòng kiểm tra cấu hình email hoặc thử lại sau.");
                            request.setAttribute("email", email);
                            url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                        }
                    } else {
                        request.setAttribute("error", "Lỗi hệ thống khi cập nhật mật khẩu. Vui lòng thử lại sau.");
                        request.setAttribute("email", email);
                        url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
                    }
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG QuenMatKhauGuiEmailServlet: " + e.getMessage());
                e.printStackTrace();
                request.setAttribute("error", "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại sau.");
                request.setAttribute("email", email);
                url = "/views/khachhang/xacthuc/forgot-password-send-email.jsp";
            }
        }

        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
        rd.forward(request, response);
    }
}





