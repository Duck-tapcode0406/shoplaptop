/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/JSP_Servlet/Servlet.java to edit this template
 */
package controller.KhachHang;

import database.NhaXuatBanDAO;
import database.SanPhamDAO;
import database.TacGiaDAO;
import database.TheLoaiDAO;
import jakarta.servlet.RequestDispatcher;
import java.io.IOException;
import java.io.PrintWriter;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.util.ArrayList;
import model.NhaXuatBan;
import model.SanPham;
import model.TacGia;
import model.TheLoai;

/**
 *
 * @author Acer
 */
@WebServlet(name = "DanhSachSanPhamServlet", urlPatterns = {"/danh-sach-san-pham"})
public class DanhSachSanPhamServlet extends HttpServlet {

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
            out.println("<title>Servlet DanhSachSanPhamServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet DanhSachSanPhamServlet at " + request.getContextPath() + "</h1>");
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
        
        // 1. Lấy các tham số lọc từ URL
        String categoryId = request.getParameter("category");
        String authorId = request.getParameter("author");
        String publisherId = request.getParameter("publisher");
        String sortOrder = request.getParameter("sort"); // (price-asc, price-desc, newest)
        
        SanPhamDAO sanPhamDAO = new SanPhamDAO();
        
        // 2. Lấy danh sách sản phẩm đã lọc
        ArrayList<SanPham> listSanPham = sanPhamDAO.selectAllWithFilter(categoryId, authorId, publisherId, sortOrder);
        
        // 3. Lấy dữ liệu cho các bộ lọc (Sidebar)
        TheLoaiDAO theLoaiDAO = new TheLoaiDAO();
        ArrayList<TheLoai> listTheLoai = theLoaiDAO.selectAll();
        
        TacGiaDAO tacGiaDAO = new TacGiaDAO();
        ArrayList<TacGia> listTacGia = tacGiaDAO.selectAll();
        
        NhaXuatBanDAO nxbDAO = new NhaXuatBanDAO();
        ArrayList<NhaXuatBan> listNXB = nxbDAO.selectAll();
        
        // 4. Gửi tất cả dữ liệu sang JSP
        request.setAttribute("listSanPham", listSanPham);
        request.setAttribute("listTheLoai", listTheLoai);
        request.setAttribute("listTacGia", listTacGia);
        request.setAttribute("listNXB", listNXB);
        
        // 5. Gửi lại các tham số lọc để giữ trạng thái trên JSP
        request.setAttribute("selectedCategory", categoryId);
        request.setAttribute("selectedAuthor", authorId);
        request.setAttribute("selectedPublisher", publisherId);
        request.setAttribute("selectedSort", sortOrder);
        
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/khachhang/sanpham/product-list.jsp");
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
