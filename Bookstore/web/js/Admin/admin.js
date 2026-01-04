/* WebContent/js/admin/admin.js */

// Chờ DOM tải xong
document.addEventListener('DOMContentLoaded', function() {

    /**
     * Chức năng 1: Sidebar Toggle (Nếu có)
     * Giả sử có nút #sidebarToggle và sidebar #adminSidebar
     */
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const adminWrapper = document.getElementById('adminWrapper'); // Vùng chứa chính

    if (sidebarToggle && adminSidebar && adminWrapper) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            adminSidebar.classList.toggle('collapsed');
            adminWrapper.classList.toggle('sidebar-collapsed');
            // Lưu trạng thái vào localStorage (tùy chọn)
            localStorage.setItem('sidebarCollapsed', adminSidebar.classList.contains('collapsed'));
        });

        // Khôi phục trạng thái sidebar khi tải trang (tùy chọn)
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            adminSidebar.classList.add('collapsed');
            adminWrapper.classList.add('sidebar-collapsed');
        }
    }

    /**
     * Chức năng 1.5: User Dropdown Menu Toggle
     * Xử lý click để mở/đóng dropdown menu hồ sơ (hỗ trợ cả hover và click)
     */
    const userBtn = document.querySelector('.user-btn');
    const userMenu = document.querySelector('.user-menu');
    const userDropdown = document.querySelector('.user-dropdown');

    if (userBtn && userMenu && userDropdown) {
        // Toggle dropdown khi click vào button
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Ngăn event bubble
            userMenu.classList.toggle('active');
        });

        // Đóng dropdown khi click ra ngoài (chỉ khi dùng click, không ảnh hưởng hover)
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target) && userMenu.classList.contains('active')) {
                userMenu.classList.remove('active');
            }
        });

        // Giữ dropdown mở khi hover vào dropdown
        userDropdown.addEventListener('mouseenter', function() {
            userMenu.classList.add('active');
        });

        // Đóng khi rời khỏi menu (nếu đang dùng click)
        userMenu.addEventListener('mouseleave', function() {
            // Chỉ đóng nếu đang dùng click (có class active từ click)
            // Hover sẽ tự động đóng khi rời chuột
            if (!userMenu.matches(':hover')) {
                // Để CSS hover tự xử lý
            }
        });
    }

    /**
     * Chức năng 2: Xác nhận trước khi Xóa
     * Áp dụng cho các nút/link xóa có class="delete-confirm"
     * và data-item-name="tên mục cần xóa"
     * Yêu cầu: Nút xóa phải nằm trong một <form> hoặc là link GET thực hiện xóa.
     */
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const itemName = this.getAttribute('data-item-name') || 'mục này';
            const message = `Bạn có chắc chắn muốn xóa ${itemName} không? Hành động này không thể hoàn tác.`;

            if (!confirm(message)) {
                event.preventDefault(); // Ngăn chặn hành động mặc định (submit form hoặc chuyển link)
            }
        });
    });

    /**
     * Chức năng 3: Xem trước ảnh khi Upload
     * Áp dụng cho input type="file" có id="imageUpload"
     * và thẻ img có id="imagePreview" để hiển thị ảnh.
     */
    const imageUpload = document.getElementById('imageUpload'); // Input chọn file ảnh
    const imagePreview = document.getElementById('imagePreview'); // Thẻ <img> để xem trước
    const defaultImageSrc = imagePreview ? imagePreview.src : ''; // Lưu ảnh mặc định/hiện tại

    if (imageUpload && imagePreview) {
        imageUpload.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    imagePreview.src = e.target.result; // Hiển thị ảnh mới chọn
                }
                reader.readAsDataURL(file); // Đọc file thành data URL
            } else {
                // Nếu không chọn file hoặc file không phải ảnh, hiển thị lại ảnh cũ/mặc định
                imagePreview.src = defaultImageSrc;
                // Có thể thêm thông báo lỗi
                // alert('Vui lòng chọn một file ảnh hợp lệ.');
                this.value = ''; // Reset input file
            }
        });
    }

    /**
     * Chức năng 4: Khởi tạo Date Picker (Ví dụ dùng thư viện Flatpickr)
     * Yêu cầu:
     * 1. Nhúng thư viện Flatpickr CSS/JS vào trang admin (header/footer).
     * 2. Thêm class="datepicker" cho các input ngày tháng.
     * Link: https://flatpickr.js.org/
     */
    // if (typeof flatpickr !== 'undefined') { // Kiểm tra thư viện tồn tại
    //     flatpickr(".datepicker", {
    //         dateFormat: "d/m/Y", // Định dạng ngày Việt Nam
    //         allowInput: true     // Cho phép nhập tay
    //         // Thêm các tùy chọn khác nếu cần (ví dụ: chọn giờ)
    //         // enableTime: true,
    //         // time_24hr: true,
    //         // defaultDate: "today"
    //     });
    //     flatpickr(".datetimepicker", { // Input cho cả ngày và giờ
    //        enableTime: true,
    //        dateFormat: "d/m/Y H:i",
    //        time_24hr: true,
    //        allowInput: true
    //    });
    // } else {
    //    console.warn('Thư viện Flatpickr chưa được nhúng.');
    // }

    /**
     * Chức năng 5: Khởi tạo Rich Text Editor (Ví dụ dùng TinyMCE)
     * Yêu cầu:
     * 1. Nhúng thư viện TinyMCE JS vào trang admin (header/footer).
     * 2. Thêm class="richtext-editor" cho các textarea cần soạn thảo nâng cao.
     * Link: https://www.tiny.cloud/docs/tinymce/latest/jquery-cloud-deployment/
     */
    // if (typeof tinymce !== 'undefined') { // Kiểm tra thư viện tồn tại
    //     tinymce.init({
    //         selector: 'textarea.richtext-editor',
    //         plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    //         toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    //         height: 300,
    //         // Thêm cấu hình upload ảnh nếu cần
    //         // images_upload_url: 'URL_XU_LY_UPLOAD_ANH_CUA_BAN',
    //         // automatic_uploads: true,
    //         // file_picker_types: 'image',
    //         /* file_picker_callback: function (cb, value, meta) { ... } */
    //     });
    // } else {
    //    console.warn('Thư viện TinyMCE chưa được nhúng.');
    // }


     /**
     * Chức năng 6: Khởi tạo Biểu đồ Thống kê (Ví dụ dùng Chart.js)
     * Yêu cầu:
     * 1. Nhúng thư viện Chart.js vào trang admin.
     * 2. Chuẩn bị dữ liệu (labels, data) từ Servlet/JSP.
     * 3. Có thẻ <canvas id="myChartId"></canvas> trong HTML.
     * Link: https://www.chartjs.org/docs/latest/getting-started/
     */
    // const ctxRevenue = document.getElementById('revenueChart'); // Ví dụ canvas cho doanh thu
    // if (ctxRevenue && typeof Chart !== 'undefined') {
    //     // Lấy dữ liệu từ JSP (ví dụ qua data-* attributes hoặc biến JS)
    //     const revenueLabels = JSON.parse(ctxRevenue.getAttribute('data-labels') || '[]');
    //     const revenueData = JSON.parse(ctxRevenue.getAttribute('data-values') || '[]');

    //     new Chart(ctxRevenue, {
    //         type: 'line', // hoặc 'bar'
    //         data: {
    //             labels: revenueLabels, // ['Tháng 1', 'Tháng 2', ...]
    //             datasets: [{
    //                 label: 'Doanh thu (VNĐ)',
    //                 data: revenueData,    // [1200000, 1900000, ...]
    //                 borderColor: 'rgb(75, 192, 192)',
    //                 tension: 0.1
    //             }]
    //         },
    //         options: {
    //             scales: {
    //                 y: { beginAtZero: true }
    //             },
    //             responsive: true,
    //             maintainAspectRatio: false
    //         }
    //     });
    // }
    // // Tương tự cho các biểu đồ khác (đơn hàng, sách bán chạy...)


    /**
     * Chức năng 7: Client-side Validation cơ bản cho Form (Ví dụ form sách)
     * Áp dụng cho form có id="bookForm"
     */
    const bookForm = document.getElementById('bookForm');
    if (bookForm) {
        bookForm.addEventListener('submit', function(event) {
            let isValid = true;
            const errorMessages = [];

            // Xóa lỗi cũ
            bookForm.querySelectorAll('.form-error').forEach(el => el.remove());
            bookForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Kiểm tra Tên sách
            const titleInput = bookForm.querySelector('#bookTitle'); // Giả sử id là bookTitle
            if (titleInput && titleInput.value.trim() === '') {
                isValid = false;
                errorMessages.push('Tên sách không được để trống.');
                showValidationError(titleInput, 'Tên sách không được để trống.');
            }

            // Kiểm tra Giá bán (là số và > 0)
            const priceInput = bookForm.querySelector('#bookPrice'); // Giả sử id là bookPrice
            if (priceInput) {
                const priceValue = parseFloat(priceInput.value);
                if (isNaN(priceValue) || priceValue <= 0) {
                     isValid = false;
                     errorMessages.push('Giá bán phải là một số lớn hơn 0.');
                     showValidationError(priceInput, 'Giá bán không hợp lệ.');
                }
            }

            // Kiểm tra Số lượng (là số nguyên >= 0)
            const quantityInput = bookForm.querySelector('#bookQuantity'); // Giả sử id là bookQuantity
             if (quantityInput) {
                const quantityValue = parseInt(quantityInput.value, 10);
                 if (isNaN(quantityValue) || quantityValue < 0 || quantityInput.value.includes('.')) {
                     isValid = false;
                     errorMessages.push('Số lượng phải là một số nguyên không âm.');
                     showValidationError(quantityInput, 'Số lượng không hợp lệ.');
                 }
            }

            // Thêm các kiểm tra khác (Tác giả, Thể loại bắt buộc chọn, Năm XB hợp lệ...)


            // Ngăn submit nếu không hợp lệ
            if (!isValid) {
                event.preventDefault();
                // Có thể hiển thị một thông báo lỗi tổng hợp ở đầu form
                 alert('Vui lòng kiểm tra lại các trường được đánh dấu lỗi:\n- ' + errorMessages.join('\n- '));
            }
        });
    }

    // Hàm hiển thị lỗi validation dưới input
    function showValidationError(inputElement, message) {
        inputElement.classList.add('is-invalid'); // Thêm class để CSS highlight (ví dụ: viền đỏ)
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error'; // Class để style (ví dụ: chữ đỏ, nhỏ)
        errorDiv.textContent = message;
        // Chèn thông báo lỗi ngay sau input hoặc sau thẻ cha của nó
        if (inputElement.parentNode) {
             inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
        }
         // Xóa lỗi khi người dùng sửa input
         inputElement.addEventListener('input', function() {
              inputElement.classList.remove('is-invalid');
              const nextError = inputElement.nextElementSibling;
              if (nextError && nextError.classList.contains('form-error')) {
                  nextError.remove();
              }
         }, { once: true }); // Chỉ chạy 1 lần
    }


}); // --- KẾT THÚC DOMContentLoaded ---