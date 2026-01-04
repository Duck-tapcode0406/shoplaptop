package controller.Admin;

import database.TinTucDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.TinTuc;

import java.io.IOException;
import java.sql.Timestamp;

@WebServlet(name = "NewsFormServlet", urlPatterns = {"/admin/news/edit", "/admin/news/new"})
public class NewsFormServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String id = request.getParameter("id");
        TinTuc news = null;

        if (id != null && !id.isEmpty()) {
            TinTucDAO dao = new TinTucDAO();
            TinTuc t = new TinTuc();
            t.setMaTinTuc(id);
            news = dao.selectById(t);
            request.setAttribute("isEdit", true);
        } else {
            request.setAttribute("isEdit", false);
        }

        request.setAttribute("news", news);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/news-form.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String id = request.getParameter("maTinTuc");
        boolean isEdit = id != null && !id.isEmpty();

        try {
            // Validation các trường bắt buộc
            String tieuDe = request.getParameter("tieuDe");
            String noiDung = request.getParameter("noiDung");
            String hinhAnh = request.getParameter("hinhAnh");
            
            if (tieuDe == null || tieuDe.trim().isEmpty()) {
                request.setAttribute("error", "Vui lòng nhập tiêu đề tin tức.");
                doGet(request, response);
                return;
            }
            
            if (noiDung == null || noiDung.trim().isEmpty()) {
                request.setAttribute("error", "Vui lòng nhập nội dung tin tức.");
                doGet(request, response);
                return;
            }
            
            if (hinhAnh == null || hinhAnh.trim().isEmpty()) {
                request.setAttribute("error", "Vui lòng chọn ảnh cho tin tức.");
                doGet(request, response);
                return;
            }
            
            TinTuc news = new TinTuc();
            
            if (isEdit) {
                news.setMaTinTuc(id);
            } else {
                if (id == null || id.isEmpty()) {
                    id = "TT" + System.currentTimeMillis();
                }
                news.setMaTinTuc(id);
            }

            news.setTieuDe(tieuDe.trim());
            news.setNoiDung(noiDung.trim());
            // Không cần trường tác giả nữa, để rỗng thay vì null để tránh lỗi database
            news.setTacGia("");
            news.setHinhAnh(hinhAnh.trim());
            
            // Nếu là thêm mới, set ngày đăng là hiện tại. Nếu là sửa, giữ nguyên ngày đăng cũ
            if (!isEdit) {
                news.setNgayDang(new Timestamp(System.currentTimeMillis()));
            } else {
                // Lấy ngày đăng cũ từ database
                TinTucDAO tempDao = new TinTucDAO();
                TinTuc oldNews = tempDao.selectById(news);
                if (oldNews != null && oldNews.getNgayDang() != null) {
                    news.setNgayDang(oldNews.getNgayDang());
                } else {
                    news.setNgayDang(new Timestamp(System.currentTimeMillis()));
                }
            }
            
            // Set người đăng là admin hiện tại
            jakarta.servlet.http.HttpSession session = request.getSession(false);
            if (session != null) {
                model.KhachHang admin = (model.KhachHang) session.getAttribute("user");
                if (admin != null) {
                    news.setNguoiDang(admin);
                }
            }

            TinTucDAO dao = new TinTucDAO();
            int result;
            
            if (isEdit) {
                result = dao.update(news);
                if (result > 0) {
                    request.getSession().setAttribute("successMessage", "Cập nhật tin tức thành công.");
                    response.sendRedirect(request.getContextPath() + "/admin/news");
                    return;
                } else {
                    request.setAttribute("error", "Không thể cập nhật tin tức. Vui lòng kiểm tra lại thông tin.");
                }
            } else {
                result = dao.insert(news);
                if (result > 0) {
                    request.getSession().setAttribute("successMessage", "Thêm tin tức thành công.");
                    response.sendRedirect(request.getContextPath() + "/admin/news");
                    return;
                } else {
                    request.setAttribute("error", "Không thể thêm tin tức. Vui lòng kiểm tra lại thông tin.");
                }
            }
            
            // Nếu đến đây nghĩa là có lỗi
            // Giữ lại dữ liệu đã nhập để người dùng không phải nhập lại
            request.setAttribute("news", news);
            request.setAttribute("isEdit", isEdit);
            doGet(request, response);

        } catch (Exception e) {
            System.err.println("Lỗi khi lưu tin tức: " + e.getMessage());
            e.printStackTrace();
            
            String errorMsg;
            // Kiểm tra nếu là SQLException
            if (e instanceof java.sql.SQLException || (e.getCause() != null && e.getCause() instanceof java.sql.SQLException)) {
                java.sql.SQLException sqlEx = (e instanceof java.sql.SQLException) ? 
                    (java.sql.SQLException) e : (java.sql.SQLException) e.getCause();
                errorMsg = "Lỗi cơ sở dữ liệu: ";
                String msg = sqlEx.getMessage();
                if (msg != null) {
                    if (msg.contains("NULL")) {
                        errorMsg += "Có trường bắt buộc chưa được điền.";
                    } else if (msg.contains("Duplicate") || msg.contains("duplicate")) {
                        errorMsg += "Mã tin tức đã tồn tại.";
                    } else {
                        errorMsg += msg;
                    }
                } else {
                    errorMsg += "Không thể kết nối hoặc thực thi lệnh SQL.";
                }
            } else {
                errorMsg = "Lỗi: " + e.getMessage();
            }
            
            request.setAttribute("error", errorMsg);
            // Giữ lại dữ liệu đã nhập nếu có
            try {
                TinTuc news = new TinTuc();
                news.setTieuDe(request.getParameter("tieuDe"));
                news.setNoiDung(request.getParameter("noiDung"));
                news.setHinhAnh(request.getParameter("hinhAnh"));
                if (isEdit) {
                    news.setMaTinTuc(id);
                }
                request.setAttribute("news", news);
                request.setAttribute("isEdit", isEdit);
            } catch (Exception ex) {
                // Bỏ qua nếu không thể lấy dữ liệu
            }
            doGet(request, response);
        }
    }
}




