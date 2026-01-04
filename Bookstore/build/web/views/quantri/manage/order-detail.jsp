<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/fmt" prefix="fmt" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="donHang" value="${requestScope.donHang}" />
<c:set var="chiTiet" value="${requestScope.chiTiet}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
</head>
<body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fa-solid fa-file-invoice"></i> Chi tiết đơn hàng #${donHang.maDonHang}</h2>
                <a href="${baseURL}/admin/orders" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session" />
            </c:if>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h3 style="margin-bottom: 1rem;">Thông tin khách hàng</h3>
                    <p><strong>Họ tên:</strong> ${donHang.khachHang.hoVaTen}</p>
                    <p><strong>Email:</strong> ${donHang.khachHang.email}</p>
                    <p><strong>Số điện thoại:</strong> ${donHang.khachHang.soDienThoai}</p>
                    <p><strong>Địa chỉ nhận hàng:</strong> ${donHang.diaChiNhanHang}</p>
                </div>
                <div>
                    <h3 style="margin-bottom: 1rem;">Thông tin đơn hàng</h3>
                    <p><strong>Ngày đặt:</strong> <fmt:formatDate value="${donHang.ngayDatHang}" pattern="dd/MM/yyyy HH:mm"/></p>
                    <p><strong>Trạng thái:</strong> 
                        <span class="badge 
                            ${donHang.trangThai == 'Hoàn tất' ? 'badge-success' : ''}
                            ${donHang.trangThai == 'Chờ duyệt' ? 'badge-warning' : ''}
                            ${donHang.trangThai == 'Đang giao' ? 'badge-info' : ''}
                            ${donHang.trangThai == 'Hủy' ? 'badge-danger' : ''}
                        ">${donHang.trangThai}</span>
                    </p>
                    <p><strong>Hình thức thanh toán:</strong> ${donHang.hinhThucThanhToan}</p>
                    <p><strong>Trạng thái thanh toán:</strong> 
                        <span class="badge ${donHang.trangThaiThanhToan == 'Đã thanh toán' ? 'badge-success' : 'badge-warning'}">
                            ${donHang.trangThaiThanhToan}
                        </span>
                    </p>
                    <p><strong>Tổng tiền:</strong> 
                        <strong style="color: #dc3545; font-size: 1.1em;">
                            <fmt:formatNumber value="${donHang.soTienDaThanhToan}" type="currency" currencySymbol="₫"/>
                        </strong>
                    </p>
                </div>
            </div>

            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Danh sách sản phẩm</h3>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng đặt</th>
                                <th>Tồn kho</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <c:forEach var="ct" items="${chiTiet}">
                                <c:set var="stockQty" value="${stockInfo[ct.sanPham.maSanPham]}" />
                                <tr style="${stockQty != null && stockQty < ct.soLuong ? 'background-color: #f8d7da;' : ''}">
                                    <td>${ct.sanPham.tenSanPham}</td>
                                    <td>${ct.soLuong}</td>
                                    <td>
                                        <c:choose>
                                            <c:when test="${stockQty == null}">
                                                <span style="color: #999;">N/A</span>
                                            </c:when>
                                            <c:when test="${stockQty < ct.soLuong}">
                                                <span style="color: #dc3545; font-weight: bold;">
                                                    <i class="fa-solid fa-exclamation-triangle"></i> ${stockQty} (Thiếu)
                                                </span>
                                            </c:when>
                                            <c:otherwise>
                                                <span style="color: #28a745;">${stockQty}</span>
                                            </c:otherwise>
                                        </c:choose>
                                    </td>
                                    <td><fmt:formatNumber value="${ct.giaBan}" type="currency" currencySymbol="₫"/></td>
                                    <td><fmt:formatNumber value="${ct.giaBan * ct.soLuong}" type="currency" currencySymbol="₫"/></td>
                                </tr>
                            </c:forEach>
                            <tr style="font-weight: bold; border-top: 2px solid #dee2e6;">
                                <td colspan="4" class="text-right">Tổng cộng:</td>
                                <td><fmt:formatNumber value="${donHang.soTienDaThanhToan}" type="currency" currencySymbol="₫"/></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <c:if test="${donHang.trangThai == 'Chờ duyệt'}">
                    <div class="card" style="background: #fff3cd; border: 2px solid #ffc107;">
                        <h3 style="margin-bottom: 1rem; color: #856404;">
                            <i class="fa-solid fa-clock"></i> Đơn hàng chờ duyệt
                        </h3>
                        <p style="margin-bottom: 1rem;">Đơn hàng này đang chờ được duyệt. Bạn có thể:</p>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <form method="POST" action="${baseURL}/admin/orders/approve" style="display: inline;">
                                <input type="hidden" name="maDonHang" value="${donHang.maDonHang}">
                                <button type="submit" class="btn btn-success" 
                                        onclick="return confirm('Bạn có chắc muốn duyệt đơn hàng này? Hệ thống sẽ kiểm tra tồn kho và cập nhật số lượng.');">
                                    <i class="fa-solid fa-check-circle"></i> Duyệt đơn hàng
                                </button>
                            </form>
                            <form method="POST" action="${baseURL}/admin/orders/cancel" style="display: inline;">
                                <input type="hidden" name="maDonHang" value="${donHang.maDonHang}">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                                    <i class="fa-solid fa-times-circle"></i> Hủy
                                </button>
                            </form>
                        </div>
                    </div>
                </c:if>
            </div>

            <form method="POST" action="${baseURL}/admin/orders/detail">
                <input type="hidden" name="maDonHang" value="${donHang.maDonHang}">
                
                <div class="form-group">
                    <label for="trangThai">Cập nhật trạng thái *</label>
                    <select id="trangThai" name="trangThai" class="form-control" required>
                        <option value="Chờ duyệt" ${donHang.trangThai == 'Chờ duyệt' ? 'selected' : ''}>Chờ duyệt</option>
                        <option value="Đang giao" ${donHang.trangThai == 'Đang giao' ? 'selected' : ''}>Đang giao</option>
                        <option value="Hoàn tất" ${donHang.trangThai == 'Hoàn tất' ? 'selected' : ''}>Hoàn tất</option>
                        <option value="Hủy" ${donHang.trangThai == 'Hủy' ? 'selected' : ''}>Hủy</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ghiChu">Ghi chú</label>
                    <textarea id="ghiChu" name="ghiChu" class="form-control" rows="3" placeholder="Ghi chú về đơn hàng (lý do duyệt/từ chối)..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Cập nhật đơn hàng
                    </button>
                    <a href="${baseURL}/admin/orders" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
</body>
</html>
