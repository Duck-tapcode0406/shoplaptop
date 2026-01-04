package controller.KhachHang;

import database.GoiCuocDAO;
import database.KhachHangDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.GoiCuoc;
import model.KhachHang;
import util.VNPayUtil;
import java.io.IOException;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.Calendar;

/**
 * Servlet xử lý đăng ký gói cước và thanh toán
 */
@WebServlet(name = "SubscriptionServlet", urlPatterns = {"/dang-ky-goi-cuoc", "/goi-cuoc"})
public class SubscriptionServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String path = request.getServletPath();
        
        if ("/goi-cuoc".equals(path)) {
            // Luôn hiển thị trang đăng ký gói cước (chỉ khi chưa có hoặc muốn gia hạn)
            // Nếu user đã có gói cước và muốn xem thông tin, sẽ vào /thong-tin-goi-cuoc
            GoiCuocDAO goiCuocDAO = new GoiCuocDAO();
            ArrayList<GoiCuoc> listGoi = goiCuocDAO.getAllActivePackages();
            request.setAttribute("listGoi", listGoi);
            
            RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/subscription-packages.jsp");
            rd.forward(request, response);
        } else {
            // Redirect về trang gói cước
            response.sendRedirect(request.getContextPath() + "/goi-cuoc");
        }
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        
        HttpSession session = request.getSession();
        String action = request.getParameter("action");
        
        if ("subscribe".equals(action)) {
            // Xử lý đăng ký gói cước
            KhachHang user = (KhachHang) session.getAttribute("user");
            
            if (user == null) {
                session.setAttribute("errorMessage", "Vui lòng đăng nhập để đăng ký gói cước.");
                response.sendRedirect(request.getContextPath() + "/dang-nhap");
                return;
            }
            
            String maGoi = request.getParameter("maGoi");
            if (maGoi == null || maGoi.isEmpty()) {
                session.setAttribute("errorMessage", "Vui lòng chọn gói cước.");
                response.sendRedirect(request.getContextPath() + "/goi-cuoc");
                return;
            }
            
            GoiCuocDAO goiCuocDAO = new GoiCuocDAO();
            GoiCuoc goi = goiCuocDAO.getByMaGoi(maGoi);
            
            if (goi == null) {
                session.setAttribute("errorMessage", "Gói cước không tồn tại.");
                response.sendRedirect(request.getContextPath() + "/goi-cuoc");
                return;
            }
            
            // Lưu thông tin gói cước vào session để thanh toán
            session.setAttribute("pendingSubscription", goi);
            
            // Tạo URL thanh toán VNPay
            String orderId = "GOI_" + System.currentTimeMillis();
            long amount = goi.getGiaTien();
            String orderInfo = "Thanh toan goi cuoc: " + goi.getTenGoi();
            String returnUrl = request.getRequestURL().toString().replace(request.getServletPath(), "") 
                    + "/vnpay-callback";
            String ipAddr = request.getRemoteAddr();
            
            String paymentUrl = VNPayUtil.createPaymentUrl(orderId, amount, orderInfo, returnUrl, ipAddr);
            
            // Lưu orderId vào session
            session.setAttribute("pendingSubscriptionOrderId", orderId);
            
            // Redirect đến VNPay
            response.sendRedirect(paymentUrl);
        } else {
            response.sendRedirect(request.getContextPath() + "/goi-cuoc");
        }
    }
}


