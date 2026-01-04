/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.SanPhamDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import model.Cart;
import model.CartItem;
import model.SanPham;

/**
 *
 * @author Acer
 */
@WebServlet(name = "GioHangServlet", urlPatterns = {"/gio-hang"})
public class GioHangServlet extends HttpServlet {

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
            out.println("<title>Servlet GioHangServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet GioHangServlet at " + request.getContextPath() + "</h1>");
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
        
        // Lấy giỏ hàng từ session để hiển thị
        HttpSession session = request.getSession();
        Cart cart = (Cart) session.getAttribute("cart");
        if (cart == null) {
            cart = new Cart();
            session.setAttribute("cart", cart);
        }
        
        // Gửi thông báo (nếu có)
        String successMsg = (String) session.getAttribute("successMessage");
        if (successMsg != null) {
            request.setAttribute("successMessage", successMsg);
            session.removeAttribute("successMessage");
        }
        
        String errorMsg = (String) session.getAttribute("errorMessage");
        if (errorMsg != null) {
            request.setAttribute("errorMessage", errorMsg);
            session.removeAttribute("errorMessage");
        }
        
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/giohang/cart.jsp");
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
        
        HttpSession session = request.getSession();
        String action = request.getParameter("action");
        String productId = request.getParameter("productId");
        
        // Kiểm tra nếu là admin thì không cho phép thao tác với giỏ hàng
        model.KhachHang user = (model.KhachHang) session.getAttribute("user");
        if (user != null && user.getRole() == 1) {
            session.setAttribute("errorMessage", "Quản trị viên không thể mua hàng. Vui lòng sử dụng tài khoản khách hàng để mua hàng.");
            response.sendRedirect(request.getContextPath() + "/trang-chu");
            return;
        }
        
        // Lấy giỏ hàng, nếu chưa có thì tạo mới
        Cart cart = (Cart) session.getAttribute("cart");
        if (cart == null) {
            cart = new Cart();
        }

        if (action != null && productId != null) {
            switch (action) {
                case "add":
                    // Lấy số lượng từ form
                    int soLuong = 1;
                    try {
                        soLuong = Integer.parseInt(request.getParameter("quantity"));
                    } catch (NumberFormatException e) {
                        soLuong = 1; // Mặc định là 1 nếu có lỗi
                    }
                    
                    // Lấy sản phẩm từ CSDL
                    SanPhamDAO spDAO = new SanPhamDAO();
                    SanPham sp = new SanPham();
                    sp.setMaSanPham(productId);
                    SanPham sanPham = spDAO.selectById(sp); // Lấy đầy đủ thông tin
                    
                    if (sanPham != null && sanPham.getSoLuong() > 0) { // Kiểm tra tồn kho
                        CartItem item = new CartItem(sanPham, soLuong);
                        cart.add(item);
                        
                        // Cập nhật session
                        session.setAttribute("cart", cart);
                        session.setAttribute("cartCount", cart.getSoLuongTong());
                        
                        // Thêm thông báo thành công
                        String redirectUrl = request.getContextPath() + "/gio-hang" +
                                            "?notification=success&title=" + 
                                            java.net.URLEncoder.encode("Đã thêm vào giỏ hàng", "UTF-8") +
                                            "&message=" + 
                                            java.net.URLEncoder.encode(sanPham.getTenSanPham() + " đã được thêm vào giỏ hàng!", "UTF-8");
                        response.sendRedirect(redirectUrl);
                        return;
                    } else {
                        session.setAttribute("errorMessage", "Sản phẩm không còn hàng hoặc không tồn tại.");
                    }
                    break;
                    
                case "update":
                    int newQuantity = 1;
                    try {
                        newQuantity = Integer.parseInt(request.getParameter("quantity"));
                    } catch (NumberFormatException e) {
                        newQuantity = 1;
                    }
                    cart.update(productId, newQuantity);
                    break;
                    
                case "remove":
                    cart.remove(productId);
                    break;
            }
        }

        // Lưu giỏ hàng trở lại session
        session.setAttribute("cart", cart);
        // Cập nhật số lượng cho badge
        session.setAttribute("cartCount", cart.getSoLuongTong());
        
        // Quay lại trang giỏ hàng
        response.sendRedirect(request.getContextPath() + "/gio-hang");
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
