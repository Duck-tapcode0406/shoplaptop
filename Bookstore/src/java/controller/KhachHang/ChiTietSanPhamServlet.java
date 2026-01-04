/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.DanhGiaDAO;
import database.DanhGiaLikeDAO;
import database.SanPhamDAO;
import database.TraLoiDanhGiaDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.util.ArrayList;
import model.DanhGia;
import model.KhachHang;
import model.SanPham;

/**
 *
 * @author Acer
 */
@WebServlet(name = "ChiTietSanPhamServlet", urlPatterns = {"/chi-tiet-san-pham"})
public class ChiTietSanPhamServlet extends HttpServlet {

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
            out.println("<title>Servlet ChiTietSanPhamServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet ChiTietSanPhamServlet at " + request.getContextPath() + "</h1>");
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

        // --- 1. Lấy ID sản phẩm từ URL ---
        String productId = request.getParameter("id");
        String error = null;
        SanPham sanPham = null; // Đổi tên biến chính thành sanPham
        ArrayList<DanhGia> listDanhGia = new ArrayList<>();
        ArrayList<SanPham> listSanPhamLienQuan = new ArrayList<>();
        boolean daMuaHang = false; // Biến kiểm tra đã mua hàng chưa

        // --- 2. Kiểm tra ID hợp lệ ---
        if (productId == null || productId.trim().isEmpty()) {
            error = "Mã sản phẩm không hợp lệ.";
        } else {
            try {
                // --- 3. Khởi tạo DAO ---
                SanPhamDAO sanPhamDAO = new SanPhamDAO();
                DanhGiaDAO danhGiaDAO = new DanhGiaDAO(); // Cần có DAO này
                // <<< BẠN SẼ CẦN DAO NÀY: DonHangDAO donHangDAO = new DonHangDAO();

                // --- 4. Lấy thông tin sản phẩm chính ---
                SanPham spTemp = new SanPham();
                spTemp.setMaSanPham(productId);
                sanPham = sanPhamDAO.selectById(spTemp); // Gọi DAO lấy sản phẩm

                // Nếu tìm thấy sản phẩm
                if (sanPham != null) {
                    // --- 5. Lấy danh sách đánh giá ---
                    listDanhGia = danhGiaDAO.selectAllByProductId(productId);
                    
                    // Lấy currentUser trước để dùng ở nhiều nơi
                    HttpSession session = request.getSession(false);
                    KhachHang currentUser = session != null ? (KhachHang) session.getAttribute("user") : null;
                    
                    // --- 5.1. Load replies và like/dislike counts cho mỗi đánh giá ---
                    // Bọc trong try-catch riêng để không làm crash nếu bảng chưa tồn tại
                    try {
                        TraLoiDanhGiaDAO traLoiDAO = new TraLoiDanhGiaDAO();
                        DanhGiaLikeDAO likeDAO = new DanhGiaLikeDAO();
                        
                        for (DanhGia dg : listDanhGia) {
                            try {
                                // Load replies
                                java.util.ArrayList<model.TraLoiDanhGia> replies = traLoiDAO.selectAllByReviewId(dg.getMaDanhGia());
                                request.setAttribute("replies_" + dg.getMaDanhGia(), replies);
                            } catch (Exception e) {
                                // Nếu bảng chưa tồn tại, set empty list
                                System.err.println("Lỗi khi load replies cho đánh giá " + dg.getMaDanhGia() + ": " + e.getMessage());
                                request.setAttribute("replies_" + dg.getMaDanhGia(), new java.util.ArrayList<model.TraLoiDanhGia>());
                            }
                            
                            try {
                                // Load like/dislike counts
                                int likeCount = likeDAO.countLikes(dg.getMaDanhGia());
                                int dislikeCount = likeDAO.countDislikes(dg.getMaDanhGia());
                                request.setAttribute("likeCount_" + dg.getMaDanhGia(), likeCount);
                                request.setAttribute("dislikeCount_" + dg.getMaDanhGia(), dislikeCount);
                                
                                // Check if current user has liked/disliked
                                if (currentUser != null) {
                                    boolean hasLiked = likeDAO.hasLiked(dg.getMaDanhGia(), currentUser.getMaKhachHang());
                                    boolean hasDisliked = likeDAO.hasDisliked(dg.getMaDanhGia(), currentUser.getMaKhachHang());
                                    request.setAttribute("hasLiked_" + dg.getMaDanhGia(), hasLiked);
                                    request.setAttribute("hasDisliked_" + dg.getMaDanhGia(), hasDisliked);
                                } else {
                                    // Nếu chưa đăng nhập, set false
                                    request.setAttribute("hasLiked_" + dg.getMaDanhGia(), false);
                                    request.setAttribute("hasDisliked_" + dg.getMaDanhGia(), false);
                                }
                            } catch (Exception e) {
                                // Nếu bảng chưa tồn tại, set default values
                                System.err.println("Lỗi khi load likes/dislikes cho đánh giá " + dg.getMaDanhGia() + ": " + e.getMessage());
                                request.setAttribute("likeCount_" + dg.getMaDanhGia(), 0);
                                request.setAttribute("dislikeCount_" + dg.getMaDanhGia(), 0);
                                request.setAttribute("hasLiked_" + dg.getMaDanhGia(), false);
                                request.setAttribute("hasDisliked_" + dg.getMaDanhGia(), false);
                            }
                        }
                    } catch (Exception e) {
                        // Nếu có lỗi khi khởi tạo DAO, chỉ log và tiếp tục
                        System.err.println("Lỗi khi khởi tạo TraLoiDanhGiaDAO hoặc DanhGiaLikeDAO: " + e.getMessage());
                        e.printStackTrace();
                        // Set default values cho tất cả đánh giá
                        for (DanhGia dg : listDanhGia) {
                            request.setAttribute("replies_" + dg.getMaDanhGia(), new java.util.ArrayList<model.TraLoiDanhGia>());
                            request.setAttribute("likeCount_" + dg.getMaDanhGia(), 0);
                            request.setAttribute("dislikeCount_" + dg.getMaDanhGia(), 0);
                            request.setAttribute("hasLiked_" + dg.getMaDanhGia(), false);
                            request.setAttribute("hasDisliked_" + dg.getMaDanhGia(), false);
                        }
                    }

                    // --- 6. Lấy danh sách sản phẩm liên quan ---
                    String categoryId = (sanPham.getTheLoai() != null) ? sanPham.getTheLoai().getMaTheLoai() : null;
                    if (categoryId != null) {
                        listSanPhamLienQuan = sanPhamDAO.getRelatedProducts(categoryId, productId, 4); // Lấy 4 SP
                    }

                    // --- 7. Kiểm tra xem người dùng đã mua sản phẩm này chưa ---
                    // Sử dụng lại session đã lấy ở trên
                    if (currentUser != null) {
                        try {
                            // Kiểm tra thực tế từ database
                            database.DonHangDAO donHangDAO = new database.DonHangDAO();
                            daMuaHang = donHangDAO.checkIfCustomerBoughtProduct(currentUser.getMaKhachHang(), productId);
                        } catch (Exception e) {
                            System.err.println("Lỗi khi kiểm tra đã mua hàng: " + e.getMessage());
                            daMuaHang = false; // Set mặc định là false nếu có lỗi
                        }
                    }

                } else {
                    error = "Không tìm thấy sản phẩm với mã " + productId + ".";
                }

            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG ChiTietSanPhamServlet: " + e.getMessage());
                e.printStackTrace();
                error = "Đã xảy ra lỗi khi tải dữ liệu sản phẩm.";
            }
        }

        // --- 8. Kiểm tra nếu là AJAX request chỉ lấy đánh giá ---
        String ajax = request.getParameter("ajax");
        if ("reviews".equals(ajax) && sanPham != null) {
            // Chỉ trả về phần đánh giá cho AJAX (bao gồm cả thống kê)
            request.setAttribute("listDanhGia", listDanhGia);
            request.setAttribute("sanPham", sanPham);
            // Forward đến JSP nhưng chỉ render phần reviews
            String url = "/views/khachhang/sanpham/product-detail.jsp";
            RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
            rd.forward(request, response);
            return;
        }
        
        // --- 9. Đặt thuộc tính vào request ---
        if (error != null) {
            request.setAttribute("errorMessage", error);
        }
        // **QUAN TRỌNG**: Đặt tên attribute khớp với ${...} trong JSP
        request.setAttribute("sanPham", sanPham); // Đổi tên attribute thành "sanPham"
        request.setAttribute("listDanhGia", listDanhGia);
        request.setAttribute("listSanPhamLienQuan", listSanPhamLienQuan);
        request.setAttribute("daMuaHang", daMuaHang);

        // --- 10. Forward đến JSP ---
        String url = "/views/khachhang/sanpham/product-detail.jsp";
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
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
        processRequest(request, response);
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
