<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<%@ taglib uri="http://java.sun.com/jsp/jstl/functions" prefix="fn" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="product" value="${requestScope.product}" />
<c:set var="isEdit" value="${requestScope.isEdit}" />
<c:set var="categories" value="${requestScope.categories}" />
<c:set var="authors" value="${requestScope.authors}" />
<c:set var="publishers" value="${requestScope.publishers}" />

<!DOCTYPE html>
<html lang="vi">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${isEdit ? 'Sửa' : 'Thêm'} sản phẩm - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="${baseURL}/css/quantri/style.css">
    <link rel="stylesheet" href="${baseURL}/css/upload-styles.css">
    </head>
    <body id="adminWrapper">
    <jsp:include page="../layout/header.jsp" />
    <jsp:include page="../layout/sidebar.jsp" />
    
    <div class="admin-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-${isEdit ? 'edit' : 'plus'}"></i> 
                    ${isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới'}
                </h2>
                <a href="${baseURL}/admin/products" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <c:if test="${not empty requestScope.error}">
                <div class="alert alert-danger">
                    <i class="fa-solid fa-exclamation-circle"></i> ${requestScope.error}
                </div>
            </c:if>

            <form method="POST" action="${baseURL}/admin/products/${isEdit ? 'edit' : 'new'}" style="max-width: 800px;">
                <input type="hidden" name="maSanPham" value="${product != null ? product.maSanPham : ''}">

                <div class="form-group">
                    <label for="tenSanPham">Tên sản phẩm *</label>
                    <input type="text" id="tenSanPham" name="tenSanPham" class="form-control" 
                           value="${product != null ? product.tenSanPham : ''}" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="maTacGia">Tác giả *</label>
                        <select id="maTacGia" name="maTacGia" class="form-control" required>
                            <option value="">-- Chọn tác giả --</option>
                            <c:forEach var="author" items="${authors}">
                                <option value="${author.maTacGia}" 
                                        ${product != null && product.tacGia != null && product.tacGia.maTacGia == author.maTacGia ? 'selected' : ''}>
                                    ${author.hoVaTen}
                                </option>
                            </c:forEach>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="maTheLoai">Thể loại *</label>
                        <select id="maTheLoai" name="maTheLoai" class="form-control" required>
                            <option value="">-- Chọn thể loại --</option>
                            <c:forEach var="category" items="${categories}">
                                <option value="${category.maTheLoai}" 
                                        ${product != null && product.theLoai != null && product.theLoai.maTheLoai == category.maTheLoai ? 'selected' : ''}>
                                    ${category.tenTheLoai}
                                </option>
                            </c:forEach>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="maNhaXuatBan">Nhà xuất bản *</label>
                        <select id="maNhaXuatBan" name="maNhaXuatBan" class="form-control" required>
                            <option value="">-- Chọn NXB --</option>
                            <c:forEach var="publisher" items="${publishers}">
                                <option value="${publisher.maNhaXuatBan}" 
                                        ${product != null && product.nhaXuatBan != null && product.nhaXuatBan.maNhaXuatBan == publisher.maNhaXuatBan ? 'selected' : ''}>
                                    ${publisher.tenNhaXuatBan}
                                </option>
                            </c:forEach>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="giaBan">Giá bán *</label>
                    <input type="number" id="giaBan" name="giaBan" class="form-control" 
                           value="${product != null ? product.giaBan : ''}" step="1000" min="0" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="namXuatBan">Năm xuất bản *</label>
                        <input type="number" id="namXuatBan" name="namXuatBan" class="form-control" 
                               value="${product != null ? product.namXuatBan : ''}" min="1900" max="2100" required>
                    </div>

                    <div class="form-group">
                        <label for="ngonNgu">Ngôn ngữ *</label>
                        <input type="text" id="ngonNgu" name="ngonNgu" class="form-control" 
                               value="${product != null ? product.ngonNgu : ''}" required>
                    </div>

                    <div class="form-group">
                        <label for="trangThai">Trạng thái *</label>
                        <select id="trangThai" name="trangThai" class="form-control" required>
                            <option value="1" ${product != null && product.trangThai == 1 ? 'selected' : ''}>Hiển thị</option>
                            <option value="0" ${product != null && product.trangThai == 0 ? 'selected' : ''}>Ẩn</option>
                        </select>
                    </div>
                </div>

                <div class="form-group upload-container">
                    <label for="hinhAnh" class="upload-label">Ảnh sản phẩm *</label>
                    <div class="upload-wrapper">
                        <div style="flex: 1;">
                            <input type="text" id="hinhAnh" name="hinhAnh" class="form-control" 
                                   value="${product != null ? product.hinhAnh : ''}" 
                                   placeholder="Tên file ảnh sẽ tự động điền sau khi chọn" 
                                   readonly required>
                        </div>
                        <div>
                            <div class="upload-input-wrapper">
                                <label for="productImageInput" class="upload-input-label">
                                    <i class="fa-solid fa-image"></i> Chọn ảnh
                                </label>
                                <input type="file" id="productImageInput" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="upload-preview" id="productImagePreview">
                        <c:if test="${product != null && not empty product.hinhAnh}">
                            <c:choose>
                                <c:when test="${fn:startsWith(product.hinhAnh, '/') or fn:startsWith(product.hinhAnh, 'assets/')}">
                                    <img src="${pageContext.request.contextPath}${fn:startsWith(product.hinhAnh, '/') ? '' : '/'}${product.hinhAnh}" 
                                         alt="Product preview"
                                         onerror="this.style.display='none';">
                                </c:when>
                                <c:otherwise>
                                    <img src="${pageContext.request.contextPath}/assets/images/products/${product.hinhAnh}" 
                                         alt="Product preview"
                                         onerror="this.style.display='none';">
                                </c:otherwise>
                            </c:choose>
                        </c:if>
                    </div>
                    <div class="upload-help">
                        <i class="fa-solid fa-info-circle"></i>
                        <span>JPG, PNG, tối đa 10MB. Ảnh sẽ tự động tải lên khi bạn chọn.</span>
                    </div>
                    <div class="upload-error" id="productImageError"></div>
                    <div class="upload-success" id="productImageSuccess"></div>
                </div>

                <div class="form-group">
                    <label for="moTa">Mô tả</label>
                    <textarea id="moTa" name="moTa" class="form-control" rows="5">${product != null ? product.moTa : ''}</textarea>
                </div>

                <div class="form-group upload-container">
                    <label for="fileEpub" class="upload-label">File nội dung sách (EPUB/TXT) *</label>
                    <div class="upload-wrapper">
                        <div style="flex: 1;">
                            <input type="text" id="fileEpub" name="fileEpub" class="form-control" 
                                   value="${product != null ? product.fileEpub : ''}" 
                                   placeholder="Tên file sẽ tự động điền sau khi chọn" 
                                   ${product != null && not empty product.fileEpub ? '' : 'readonly'}>
                        </div>
                        <div>
                            <div class="upload-input-wrapper">
                                <label for="epubFileInput" class="upload-input-label">
                                    <i class="fa-solid fa-file-upload"></i> Chọn file
                                </label>
                                <input type="file" id="epubFileInput" accept=".epub,.txt,application/epub+zip,text/plain">
                            </div>
                        </div>
                    </div>
                    <div class="upload-help">
                        <i class="fa-solid fa-info-circle"></i>
                        <span>File EPUB hoặc TXT, tối đa 50MB. File sẽ tự động tải lên khi bạn chọn. 
                        <c:if test="${isEdit && product != null && not empty product.fileEpub}">
                            <br><strong>Lưu ý:</strong> Nếu không chọn file mới, file hiện tại (${product.fileEpub}) sẽ được giữ nguyên.
                        </c:if>
                        </span>
                    </div>
                    <div class="upload-error" id="epubFileError"></div>
                    <div class="upload-success" id="epubFileSuccess"></div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" onclick="return validateForm()">
                        <i class="fa-solid fa-save"></i> ${isEdit ? 'Cập nhật' : 'Thêm mới'}
                    </button>
                    <a href="${baseURL}/admin/products" class="btn btn-secondary">
                        <i class="fa-solid fa-times"></i> Hủy
                    </a>
                </div>
            </form>
            
<script>
// Validate form trước khi submit
function validateForm() {
    const fileEpubInput = document.getElementById('fileEpub');
    if (fileEpubInput) {
        const fileEpubValue = fileEpubInput.value;
        console.log('Form submit - fileEpub value: ' + fileEpubValue);
        
        // Nếu đang ở chế độ edit và không có file mới, vẫn cho phép submit (sẽ giữ file cũ)
        // Nếu đang thêm mới và không có file, có thể cảnh báo (tùy yêu cầu)
        return true;
    }
    return true;
}
</script>
        </div>
    </div>

    <jsp:include page="../layout/footer.jsp" />
    
<script>
// Xử lý upload ảnh sản phẩm - tự động upload khi chọn ảnh
document.addEventListener('DOMContentLoaded', function() {
    const productImageInput = document.getElementById('productImageInput');
    const hinhAnhInput = document.getElementById('hinhAnh');
    const productImagePreview = document.getElementById('productImagePreview');
    const errorMsg = document.getElementById('productImageError');
    const successMsg = document.getElementById('productImageSuccess');
    
    // Tự động upload khi chọn ảnh
    if (productImageInput) {
        productImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (!file) {
                return;
            }
            
            // Kiểm tra loại file
            if (!file.type.startsWith('image/')) {
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> File phải là ảnh.';
                    errorMsg.classList.add('active');
                } else {
                    alert('File phải là ảnh.');
                }
                productImageInput.value = '';
                return;
            }
            
            // Kiểm tra kích thước
            if (file.size > 10 * 1024 * 1024) {
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Kích thước ảnh không được vượt quá 10MB.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Kích thước ảnh không được vượt quá 10MB.');
                }
                productImageInput.value = '';
                return;
            }
            
            // Xem trước ảnh ngay lập tức
            const reader = new FileReader();
            reader.onload = function(e) {
                if (productImagePreview) {
                    productImagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Product preview">';
                    productImagePreview.classList.add('has-image');
                }
            };
            reader.readAsDataURL(file);
            
            // Ẩn thông báo lỗi cũ
            if (errorMsg) {
                errorMsg.classList.remove('active');
            }
            if (successMsg) {
                successMsg.classList.remove('active');
            }
            
            // Tự động upload
            const formData = new FormData();
            formData.append('productImage', file);
            
            // Hiển thị trạng thái đang tải
            if (hinhAnhInput) {
                hinhAnhInput.value = 'Đang tải lên...';
            }
            
            fetch('${baseURL}/admin/upload-product-image', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    if (successMsg) {
                        successMsg.innerHTML = '<i class="fa-solid fa-check-circle"></i> Tải ảnh thành công!';
                        successMsg.classList.add('active');
                        setTimeout(() => successMsg.classList.remove('active'), 3000);
                    }
                    
                    // Cập nhật input với tên file
                    if (hinhAnhInput && data.imagePath) {
                        hinhAnhInput.value = data.imagePath;
                    }
                    
                    // Cập nhật preview với URL đầy đủ
                    if (productImagePreview && data.imageUrl) {
                        productImagePreview.innerHTML = '<img src="' + data.imageUrl + '" alt="Product preview">';
                        productImagePreview.classList.add('has-image');
                    }
                } else {
                    // Hiển thị lỗi
                    if (errorMsg) {
                        errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> ' + (data.message || 'Không thể tải ảnh lên.');
                        errorMsg.classList.add('active');
                    } else {
                        alert(data.message || 'Không thể tải ảnh lên.');
                    }
                    
                    // Reset input
                    if (hinhAnhInput) {
                        hinhAnhInput.value = '';
                    }
                    if (productImagePreview) {
                        productImagePreview.innerHTML = '';
                        productImagePreview.classList.remove('has-image');
                    }
                    productImageInput.value = '';
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                
                if (errorMsg) {
                    errorMsg.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Đã xảy ra lỗi khi tải ảnh lên.';
                    errorMsg.classList.add('active');
                } else {
                    alert('Đã xảy ra lỗi khi tải ảnh lên.');
                }
                
                // Reset input
                if (hinhAnhInput) {
                    hinhAnhInput.value = '';
                }
                if (productImagePreview) {
                    productImagePreview.innerHTML = '';
                    productImagePreview.classList.remove('has-image');
                }
                productImageInput.value = '';
            });
        });
    }
    
    // Xử lý upload file EPUB - tự động upload khi chọn file
    const epubFileInput = document.getElementById('epubFileInput');
    const fileEpubInput = document.getElementById('fileEpub');
    const epubFileError = document.getElementById('epubFileError');
    const epubFileSuccess = document.getElementById('epubFileSuccess');
    
    if (epubFileInput) {
        epubFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (!file) {
                return;
            }
            
            // Kiểm tra loại file (chấp nhận EPUB và TXT)
            const lowerFileName = file.name.toLowerCase();
            if (!lowerFileName.endsWith('.epub') && !lowerFileName.endsWith('.txt') && 
                file.type !== 'application/epub+zip' && file.type !== 'text/plain') {
                if (epubFileError) {
                    epubFileError.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> File phải là định dạng EPUB hoặc TXT.';
                    epubFileError.classList.add('active');
                } else {
                    alert('File phải là định dạng EPUB hoặc TXT.');
                }
                epubFileInput.value = '';
                return;
            }
            
            // Kiểm tra kích thước
            if (file.size > 50 * 1024 * 1024) {
                if (epubFileError) {
                    epubFileError.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Kích thước file không được vượt quá 50MB.';
                    epubFileError.classList.add('active');
                } else {
                    alert('Kích thước file không được vượt quá 50MB.');
                }
                epubFileInput.value = '';
                return;
            }
            
            // Ẩn thông báo lỗi cũ
            if (epubFileError) {
                epubFileError.classList.remove('active');
            }
            if (epubFileSuccess) {
                epubFileSuccess.classList.remove('active');
            }
            
            // Tự động upload
            const formData = new FormData();
            formData.append('epubFile', file);
            
            // Hiển thị trạng thái đang tải
            if (fileEpubInput) {
                fileEpubInput.value = 'Đang tải lên...';
            }
            
            fetch('${baseURL}/admin/upload-epub', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    if (epubFileSuccess) {
                        epubFileSuccess.innerHTML = '<i class="fa-solid fa-check-circle"></i> Tải file EPUB thành công!';
                        epubFileSuccess.classList.add('active');
                        setTimeout(() => epubFileSuccess.classList.remove('active'), 3000);
                    }
                    
                    // Cập nhật input với tên file
                    if (fileEpubInput && data.filePath) {
                        fileEpubInput.value = data.filePath;
                        console.log('File EPUB đã được upload: ' + data.filePath);
                        // Đảm bảo input không bị readonly để có thể submit
                        fileEpubInput.removeAttribute('readonly');
                        // Đảm bảo input có name attribute để được submit
                        if (!fileEpubInput.hasAttribute('name')) {
                            fileEpubInput.setAttribute('name', 'fileEpub');
                        }
                    }
                } else {
                    // Hiển thị lỗi
                    if (epubFileError) {
                        epubFileError.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> ' + (data.message || 'Không thể tải file EPUB lên.');
                        epubFileError.classList.add('active');
                    } else {
                        alert(data.message || 'Không thể tải file EPUB lên.');
                    }
                    
                    // Reset input
                    if (fileEpubInput) {
                        fileEpubInput.value = '';
                    }
                    epubFileInput.value = '';
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                
                if (epubFileError) {
                    epubFileError.innerHTML = '<i class="fa-solid fa-exclamation-circle"></i> Đã xảy ra lỗi khi tải file EPUB lên.';
                    epubFileError.classList.add('active');
                } else {
                    alert('Đã xảy ra lỗi khi tải file EPUB lên.');
                }
                
                // Reset input
                if (fileEpubInput) {
                    fileEpubInput.value = '';
                }
                epubFileInput.value = '';
            });
        });
    }
});
</script>
    </body>
</html>
