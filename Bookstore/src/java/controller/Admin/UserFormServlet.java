package controller.Admin;

import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.KhachHang;
import util.MaGeneratorUtil;
import util.PasswordUtil;

import java.io.IOException;
import java.sql.Date;
import java.text.SimpleDateFormat;

@WebServlet(name = "UserFormServlet", urlPatterns = {"/admin/users/new", "/admin/users/edit"})
public class UserFormServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String id = request.getParameter("id");
        KhachHang user = null;

        if (id != null && !id.isEmpty()) {
            // Edit mode
            KhachHangDAO khachHangDAO = new KhachHangDAO();
            user = khachHangDAO.selectById(id);
            request.setAttribute("isEdit", true);
        } else {
            // New mode
            request.setAttribute("isEdit", false);
        }

        request.setAttribute("user", user);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/user-form.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String maKhachHang = request.getParameter("maKhachHang");
        String tenDangNhap = request.getParameter("tenDangNhap");
        String matKhau = request.getParameter("matKhau");
        String hoVaTen = request.getParameter("hoVaTen");
        String email = request.getParameter("email");
        String soDienThoai = request.getParameter("soDienThoai");
        String gioiTinh = request.getParameter("gioiTinh");
        String diaChi = request.getParameter("diaChi");
        String ngaySinhStr = request.getParameter("ngaySinh");
        String statusStr = request.getParameter("status");
        String roleStr = request.getParameter("role");

        boolean isEdit = (maKhachHang != null && !maKhachHang.isEmpty());

        KhachHangDAO khachHangDAO = new KhachHangDAO();

        // Validation
        if (tenDangNhap == null || tenDangNhap.trim().isEmpty()) {
            request.setAttribute("error", "Tên đăng nhập không được để trống.");
            doGet(request, response);
            return;
        }

        if (!isEdit && (matKhau == null || matKhau.trim().length() < 6)) {
            request.setAttribute("error", "Mật khẩu phải có ít nhất 6 ký tự.");
            doGet(request, response);
            return;
        }

        if (hoVaTen == null || hoVaTen.trim().isEmpty()) {
            request.setAttribute("error", "Họ và tên không được để trống.");
            doGet(request, response);
            return;
        }

        if (email == null || email.trim().isEmpty()) {
            request.setAttribute("error", "Email không được để trống.");
            doGet(request, response);
            return;
        }

        // Check username exists (only for new user)
        if (!isEdit && khachHangDAO.checkUsernameExists(tenDangNhap)) {
            request.setAttribute("error", "Tên đăng nhập đã tồn tại.");
            doGet(request, response);
            return;
        }

        // Check email exists (only for new user)
        if (!isEdit && khachHangDAO.checkEmailExists(email)) {
            request.setAttribute("error", "Email đã tồn tại.");
            doGet(request, response);
            return;
        }

        KhachHang user;
        if (isEdit) {
            // Edit mode - load existing user
            user = khachHangDAO.selectById(maKhachHang);
            if (user == null) {
                request.setAttribute("error", "Không tìm thấy người dùng.");
                doGet(request, response);
                return;
            }
        } else {
            // New mode - create new user
            user = new KhachHang();
            user.setMaKhachHang(MaGeneratorUtil.generateUUID());
            user.setTenDangNhap(tenDangNhap);
            // Không hash mật khẩu ở đây vì KhachHangDAO.insert() sẽ tự động hash
            user.setMatKhau(matKhau); // Truyền mật khẩu gốc, DAO sẽ hash
        }

        // Update fields
        user.setHoVaTen(hoVaTen);
        user.setEmail(email);
        user.setSoDienThoai(soDienThoai != null ? soDienThoai : "");
        user.setGioiTinh(gioiTinh != null ? gioiTinh : "");
        user.setDiaChi(diaChi != null ? diaChi : "");
        
        if (ngaySinhStr != null && !ngaySinhStr.isEmpty()) {
            try {
                user.setNgaySinh(Date.valueOf(ngaySinhStr));
            } catch (Exception e) {
                // Invalid date format, ignore
            }
        }
        
        if (statusStr != null) {
            user.setStatus(Integer.parseInt(statusStr));
        } else {
            user.setStatus(1); // Default active
        }

        // Cho phép thiết lập role (0 = User, 1 = Admin)
        if (roleStr != null && !roleStr.isEmpty()) {
            user.setRole(Integer.parseInt(roleStr));
        } else {
            // Mặc định là user thường, nhưng nếu đang edit và user đã là admin thì giữ nguyên
            if (isEdit) {
                KhachHang existingUser = khachHangDAO.selectById(maKhachHang);
                if (existingUser != null) {
                    user.setRole(existingUser.getRole()); // Giữ nguyên role hiện tại
                } else {
                    user.setRole(0); // Default user
                }
            } else {
                user.setRole(0); // Default user for new accounts
            }
        }
        
        user.setDangKyNhanBangTin(false);

        int result;
        if (isEdit) {
            result = khachHangDAO.update(user);
            if (result > 0) {
                request.getSession().setAttribute("successMessage", "Cập nhật người dùng thành công.");
            } else {
                request.getSession().setAttribute("errorMessage", "Không thể cập nhật người dùng.");
            }
        } else {
            result = khachHangDAO.insert(user);
            if (result > 0) {
                request.getSession().setAttribute("successMessage", "Thêm người dùng thành công.");
            } else {
                request.getSession().setAttribute("errorMessage", "Không thể thêm người dùng.");
            }
        }

        response.sendRedirect(request.getContextPath() + "/admin/users");
    }
}


