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
import java.util.Comparator;
import model.SanPham;
import model.TheLoai;
import model.NhaXuatBan;

/**
 *
 * @author Acer
 */
@WebServlet(name = "TimKiemServlet", urlPatterns = {"/tim-kiem"})
public class TimKiemServlet extends HttpServlet {

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
            out.println("<title>Servlet TimKiemServlet</title>");
            out.println("</head>");
            out.println("<body>");
            out.println("<h1>Servlet TimKiemServlet at " + request.getContextPath() + "</h1>");
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

        String query = request.getParameter("query");
        String categoryName = request.getParameter("category");
        String publisherName = request.getParameter("publisher");
        String sortOrder = request.getParameter("sort");

        SanPhamDAO sanPhamDAO = new SanPhamDAO();
        TheLoaiDAO theLoaiDAO = new TheLoaiDAO();
        NhaXuatBanDAO nhaXuatBanDAO = new NhaXuatBanDAO();
        ArrayList<SanPham> searchResults = new ArrayList<>();
        String searchMessage = null;
        String pageTitle = "Danh Mục Sách";
        String categoryId = null;
        String publisherId = null;

        // Xử lý tìm kiếm theo thể loại
        if (categoryName != null && !categoryName.trim().isEmpty()) {
            try {
                ArrayList<TheLoai> matchedCategories = theLoaiDAO.searchByName(categoryName.trim());
                if (!matchedCategories.isEmpty()) {
                    // Lấy thể loại đầu tiên khớp
                    categoryId = matchedCategories.get(0).getMaTheLoai();
                    pageTitle = "Sách thể loại: " + matchedCategories.get(0).getTenTheLoai();
                } else {
                    searchMessage = "Không tìm thấy thể loại \"" + categoryName + "\".";
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG TimKiemServlet - Lỗi khi tìm thể loại: " + e.getMessage());
                e.printStackTrace();
            }
        }
        
        // Xử lý tìm kiếm theo nhà xuất bản
        if (publisherName != null && !publisherName.trim().isEmpty()) {
            try {
                ArrayList<NhaXuatBan> matchedPublishers = nhaXuatBanDAO.searchByName(publisherName.trim());
                if (!matchedPublishers.isEmpty()) {
                    // Lấy nhà xuất bản đầu tiên khớp
                    publisherId = matchedPublishers.get(0).getMaNhaXuatBan();
                    if (pageTitle.equals("Danh Mục Sách")) {
                        pageTitle = "Sách nhà xuất bản: " + matchedPublishers.get(0).getTenNhaXuatBan();
                    }
                } else {
                    if (searchMessage == null) {
                        searchMessage = "Không tìm thấy nhà xuất bản \"" + publisherName + "\".";
                    } else {
                        searchMessage += " Không tìm thấy nhà xuất bản \"" + publisherName + "\".";
                    }
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG TimKiemServlet - Lỗi khi tìm nhà xuất bản: " + e.getMessage());
                e.printStackTrace();
            }
        }

        // Xử lý tìm kiếm theo từ khóa (tên sách, tác giả)
        if (query != null && !query.trim().isEmpty()) {
            query = query.trim();
            if (pageTitle.equals("Danh Mục Sách")) {
                pageTitle = "Kết quả tìm kiếm cho \"" + query + "\"";
            }
            try {
                // Tìm kiếm theo từ khóa
                searchResults = sanPhamDAO.searchByKeywordCaseInsensitive(query);
                
                // Nếu có filter theo category hoặc publisher, lọc thêm kết quả
                if (categoryId != null || publisherId != null) {
                    ArrayList<SanPham> filteredResults = new ArrayList<>();
                    for (SanPham sp : searchResults) {
                        boolean matchCategory = categoryId == null || sp.getTheLoai().getMaTheLoai().equals(categoryId);
                        boolean matchPublisher = publisherId == null || sp.getNhaXuatBan().getMaNhaXuatBan().equals(publisherId);
                        if (matchCategory && matchPublisher) {
                            filteredResults.add(sp);
                        }
                    }
                    searchResults = filteredResults;
                }

                // --- Sắp xếp kết quả (giữ nguyên) ---
                if (sortOrder != null && !sortOrder.isEmpty() && !sortOrder.equals("default")) {
                    Comparator<SanPham> comparator = null;
                    switch (sortOrder) {
                        case "price-asc":
                            comparator = Comparator.comparingDouble(SanPham::getGiaBan);
                            break;
                        case "price-desc":
                            comparator = Comparator.comparingDouble(SanPham::getGiaBan).reversed();
                            break;
                        case "newest":
                            comparator = Comparator.comparingInt(SanPham::getNamXuatBan).reversed();
                            break;
                    }
                    if (comparator != null) {
                        searchResults.sort(comparator);
                    }
                }
                // --- Kết thúc sắp xếp ---

                 if (searchResults.isEmpty()) {
                     if (searchMessage == null) {
                         searchMessage = "Không tìm thấy sản phẩm nào khớp với từ khóa \"" + query + "\".";
                     }
                 }

            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG TimKiemServlet - Lỗi DAO khi gọi searchByKeywordCaseInsensitive: " + e.getMessage());
                e.printStackTrace();
                if (searchMessage == null) {
                    searchMessage = "Đã xảy ra lỗi trong quá trình tìm kiếm. Vui lòng thử lại.";
                }
            }
        } else if (categoryId != null || publisherId != null) {
            // Nếu chỉ có filter category/publisher mà không có query, lấy tất cả sách theo filter
            try {
                searchResults = sanPhamDAO.selectAllWithFilter(categoryId, null, publisherId, sortOrder);
                if (searchResults.isEmpty()) {
                    if (searchMessage == null) {
                        searchMessage = "Không tìm thấy sản phẩm nào.";
                    }
                }
            } catch (Exception e) {
                System.err.println("!!! LỖI TRONG TimKiemServlet - Lỗi khi lọc sản phẩm: " + e.getMessage());
                e.printStackTrace();
                if (searchMessage == null) {
                    searchMessage = "Đã xảy ra lỗi trong quá trình tìm kiếm. Vui lòng thử lại.";
                }
            }
        } else {
            if (searchMessage == null) {
                searchMessage = "Vui lòng nhập từ khóa tìm kiếm.";
            }
        }

        // --- Chuẩn bị Dữ liệu cho JSP (giữ nguyên) ---
        request.setAttribute("listSanPham", searchResults);
        request.setAttribute("searchQuery", query);
        request.setAttribute("searchMessage", searchMessage);
        request.setAttribute("pageTitle", pageTitle);
        request.setAttribute("selectedSort", sortOrder);

        // --- Lấy dữ liệu cho Bộ lọc (Sidebar) (giữ nguyên) ---
        try {
            TacGiaDAO tacGiaDAO = new TacGiaDAO();
            request.setAttribute("listTheLoai", theLoaiDAO.selectAll());
            request.setAttribute("listTacGia", tacGiaDAO.selectAll());
            request.setAttribute("listNXB", nhaXuatBanDAO.selectAll());
            // Gửi lại các filter đã chọn để highlight trong sidebar
            request.setAttribute("selectedCategory", categoryId);
            request.setAttribute("selectedPublisher", publisherId);
        } catch (Exception e) {
             System.err.println("!!! LỖI TRONG TimKiemServlet - Lỗi DAO khi lấy dữ liệu bộ lọc: " + e.getMessage());
             e.printStackTrace();
             request.setAttribute("filterError", "Không thể tải dữ liệu bộ lọc.");
        }

        // --- Forward đến trang JSP (giữ nguyên) ---
        String url = "/views/khachhang/sanpham/product-list.jsp";
        RequestDispatcher rd = getServletContext().getRequestDispatcher(url);
        rd.forward(request, response);
    }

    // doPost giữ nguyên, chỉ gọi doGet
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        doGet(request, response);
    }

    @Override
    public String getServletInfo() {
        return "Servlet xử lý tìm kiếm sản phẩm theo từ khóa (không phân biệt hoa thường)";
    }
}