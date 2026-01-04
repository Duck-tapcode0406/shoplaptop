package controller.Admin;

import database.*;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.*;

import java.io.IOException;

@WebServlet(name = "ProductFormServlet", urlPatterns = {"/admin/products/edit", "/admin/products/new"})
public class ProductFormServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String id = request.getParameter("id");
        SanPham product = null;

        if (id != null && !id.isEmpty()) {
            // Edit mode
            SanPhamDAO sanPhamDAO = new SanPhamDAO();
            SanPham sp = new SanPham();
            sp.setMaSanPham(id);
            product = sanPhamDAO.selectById(sp);
            request.setAttribute("isEdit", true);
        } else {
            // New mode
            request.setAttribute("isEdit", false);
        }

        // Load dropdowns data
        TheLoaiDAO theLoaiDAO = new TheLoaiDAO();
        TacGiaDAO tacGiaDAO = new TacGiaDAO();
        NhaXuatBanDAO nhaXuatBanDAO = new NhaXuatBanDAO();

        request.setAttribute("product", product);
        request.setAttribute("categories", theLoaiDAO.selectAll());
        request.setAttribute("authors", tacGiaDAO.selectAll());
        request.setAttribute("publishers", nhaXuatBanDAO.selectAll());

        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/manage/product-form.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        String id = request.getParameter("maSanPham");
        boolean isEdit = id != null && !id.isEmpty();

        try {
            SanPham product = new SanPham();
            
            if (isEdit) {
                product.setMaSanPham(id);
            } else {
                // Generate new ID if needed
                if (id == null || id.isEmpty()) {
                    id = "SP" + System.currentTimeMillis();
                }
                product.setMaSanPham(id);
            }

            product.setTenSanPham(request.getParameter("tenSanPham"));
            
            // Set TacGia
            String maTacGia = request.getParameter("maTacGia");
            if (maTacGia != null && !maTacGia.isEmpty()) {
                TacGia tacGia = new TacGia();
                tacGia.setMaTacGia(maTacGia);
                product.setTacGia(tacGia);
            }

            // Set TheLoai
            String maTheLoai = request.getParameter("maTheLoai");
            if (maTheLoai != null && !maTheLoai.isEmpty()) {
                TheLoai theLoai = new TheLoai();
                theLoai.setMaTheLoai(maTheLoai);
                product.setTheLoai(theLoai);
            }

            // Set NhaXuatBan
            String maNhaXuatBan = request.getParameter("maNhaXuatBan");
            if (maNhaXuatBan != null && !maNhaXuatBan.isEmpty()) {
                NhaXuatBan nxb = new NhaXuatBan();
                nxb.setMaNhaXuatBan(maNhaXuatBan);
                product.setNhaXuatBan(nxb);
            }

            // Năm xuất bản
            String namXuatBanStr = request.getParameter("namXuatBan");
            if (namXuatBanStr != null && !namXuatBanStr.trim().isEmpty()) {
                product.setNamXuatBan(Integer.parseInt(namXuatBanStr));
            } else {
                product.setNamXuatBan(2024); // Giá trị mặc định
            }
            
            // Giá nhập và số lượng không còn cần thiết, đặt giá trị mặc định
            product.setGiaNhap(0);
            product.setSoLuong(0);
            
            // Giá bán
            String giaBanStr = request.getParameter("giaBan");
            if (giaBanStr != null && !giaBanStr.trim().isEmpty()) {
                double giaBan = Double.parseDouble(giaBanStr);
                product.setGiaBan(giaBan);
                // Giá gốc = giá bán
                product.setGiaGoc(giaBan);
            } else {
                product.setGiaBan(0);
                product.setGiaGoc(0);
            }
            
            // Ngôn ngữ - xử lý null
            String ngonNgu = request.getParameter("ngonNgu");
            product.setNgonNgu(ngonNgu != null ? ngonNgu : "");
            
            // Mô tả - xử lý null
            String moTa = request.getParameter("moTa");
            product.setMoTa(moTa != null ? moTa : "");
            
            // Hình ảnh - xử lý null
            String hinhAnh = request.getParameter("hinhAnh");
            product.setHinhAnh(hinhAnh != null ? hinhAnh : "");
            
            // Trạng thái
            String trangThaiStr = request.getParameter("trangThai");
            if (trangThaiStr != null && !trangThaiStr.trim().isEmpty()) {
                product.setTrangThai(Integer.parseInt(trangThaiStr));
            } else {
                product.setTrangThai(1); // Mặc định là hiển thị
            }
            
            // File EPUB - xử lý null và empty string, loại bỏ text "Đang tải lên..."
            String fileEpub = request.getParameter("fileEpub");
            SanPhamDAO sanPhamDAO = new SanPhamDAO();
            
            // Log để debug
            System.out.println("DEBUG - fileEpub parameter: " + fileEpub);
            System.out.println("DEBUG - isEdit: " + isEdit);
            System.out.println("DEBUG - productId: " + id);
            
            if (isEdit) {
                // Khi cập nhật: nếu có file mới thì dùng file mới, nếu không thì giữ nguyên file cũ
                if (fileEpub != null && !fileEpub.trim().isEmpty() && !fileEpub.equals("Đang tải lên...") && !fileEpub.equals("null")) {
                    // Có file mới được upload
                    System.out.println("DEBUG - Sử dụng file mới: " + fileEpub);
                    product.setFileEpub(fileEpub);
                } else {
                    // Không có file mới, giữ nguyên file cũ từ database
                    SanPham existingProduct = new SanPham();
                    existingProduct.setMaSanPham(id);
                    SanPham productFromDB = sanPhamDAO.selectById(existingProduct);
                    if (productFromDB != null && productFromDB.getFileEpub() != null && !productFromDB.getFileEpub().trim().isEmpty()) {
                        System.out.println("DEBUG - Giữ nguyên file cũ: " + productFromDB.getFileEpub());
                        product.setFileEpub(productFromDB.getFileEpub());
                    } else {
                        System.out.println("DEBUG - Không có file cũ, set null");
                        product.setFileEpub(null);
                    }
                }
            } else {
                // Khi thêm mới: chỉ set file nếu có
                if (fileEpub != null && !fileEpub.trim().isEmpty() && !fileEpub.equals("Đang tải lên...") && !fileEpub.equals("null")) {
                    System.out.println("DEBUG - Thêm mới với file: " + fileEpub);
                    product.setFileEpub(fileEpub);
                } else {
                    System.out.println("DEBUG - Thêm mới không có file");
                    product.setFileEpub(null);
                }
            }
            
            System.out.println("DEBUG - Final fileEpub value: " + product.getFileEpub());
            
            int result;
            
            if (isEdit) {
                result = sanPhamDAO.update(product);
                if (result > 0) {
                    request.getSession().setAttribute("successMessage", "Cập nhật sản phẩm thành công.");
                    response.sendRedirect(request.getContextPath() + "/admin/products");
                    return;
                } else {
                    request.setAttribute("error", "Có lỗi xảy ra khi cập nhật sản phẩm. Vui lòng kiểm tra lại thông tin hoặc xem log server để biết chi tiết.");
                }
            } else {
                result = sanPhamDAO.insert(product);
                if (result > 0) {
                    request.getSession().setAttribute("successMessage", "Thêm sản phẩm thành công.");
                    response.sendRedirect(request.getContextPath() + "/admin/products");
                    return;
                } else {
                    request.setAttribute("error", "Có lỗi xảy ra khi thêm sản phẩm. Vui lòng kiểm tra lại thông tin hoặc xem log server để biết chi tiết. " +
                        "Lưu ý: Nếu lỗi liên quan đến cột 'fileEpub', vui lòng chạy script SQL để thêm cột này vào database.");
                }
            }
            
            // Nếu đến đây nghĩa là có lỗi
            doGet(request, response);

        } catch (NumberFormatException e) {
            e.printStackTrace();
            request.setAttribute("error", "Lỗi: Giá trị số không hợp lệ. Vui lòng kiểm tra lại các trường số (năm xuất bản, giá bán, trạng thái).");
            doGet(request, response);
        } catch (Exception e) {
            e.printStackTrace();
            String errorMsg = "Lỗi: " + e.getMessage();
            // Kiểm tra nếu lỗi do thiếu thông tin bắt buộc
            if (e.getMessage() != null && (e.getMessage().contains("null") || e.getMessage().contains("required"))) {
                errorMsg = "Lỗi: Vui lòng điền đầy đủ thông tin bắt buộc (tên sản phẩm, tác giả, thể loại, nhà xuất bản, giá bán).";
            }
            request.setAttribute("error", errorMsg);
            doGet(request, response);
        }
    }
}




