package controller.KhachHang;

import database.DonHangDAO;
import database.SanPhamDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import model.KhachHang;
import model.SanPham;

@WebServlet(name = "DocSachServlet", urlPatterns = {"/doc-sach"})
public class DocSachServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        HttpSession session = request.getSession(false);
        
        // Kiểm tra đăng nhập
        if (session == null || session.getAttribute("user") == null) {
            session = request.getSession();
            session.setAttribute("redirectAfterLogin", request.getRequestURI() + "?" + request.getQueryString());
            response.sendRedirect(request.getContextPath() + "/dang-nhap");
            return;
        }

        KhachHang user = (KhachHang) session.getAttribute("user");
        String productId = request.getParameter("id");

        if (productId == null || productId.isEmpty()) {
            session.setAttribute("errorMessage", "Mã sách không hợp lệ.");
            response.sendRedirect(request.getContextPath() + "/trang-chu");
            return;
        }

        // Kiểm tra xem user có gói cước còn hiệu lực không
        if (!user.isGoiCuocConHan()) {
            session.setAttribute("errorMessage", "Bạn cần đăng ký gói cước để đọc sách. Vui lòng đăng ký gói cước trước.");
            response.sendRedirect(request.getContextPath() + "/goi-cuoc");
            return;
        }

        // Lấy thông tin sách
        SanPhamDAO sanPhamDAO = new SanPhamDAO();
        SanPham sach = sanPhamDAO.selectById(productId);

        if (sach == null) {
            session.setAttribute("errorMessage", "Không tìm thấy sách.");
            response.sendRedirect(request.getContextPath() + "/trang-chu");
            return;
        }

        // Lấy nội dung sách (giả sử có trường noiDungSach trong database)
        // Nếu chưa có, có thể lấy từ file hoặc database
        String noiDungSach = sach.getMoTa(); // Tạm thời dùng mô tả, sau sẽ thêm trường riêng

        request.setAttribute("sach", sach);
        request.setAttribute("noiDungSach", noiDungSach);

        String url = "/views/khachhang/doc-sach.jsp";
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
        rd.forward(request, response);
    }
}





