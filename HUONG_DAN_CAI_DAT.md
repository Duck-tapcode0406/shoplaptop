<<<<<<< Current (Your changes)
=======
# Hướng Dẫn Cài Đặt và Sử Dụng DuckShop

## 📋 Các Tính Năng Mới Đã Hoàn Thiện

### 1. **Danh Sách Yêu Thích (Wishlist)**
- ✅ Thêm/xóa sản phẩm vào danh sách yêu thích
- ✅ Trang quản lý wishlist (`wishlist.php`)
- ✅ Tích hợp vào trang chủ và trang chi tiết sản phẩm
- ✅ API endpoints: `add_wishlist.php`, `remove_wishlist.php`

### 2. **Quản Lý Địa Chỉ Giao Hàng**
- ✅ Thêm, sửa, xóa địa chỉ giao hàng
- ✅ Đặt địa chỉ mặc định
- ✅ Trang quản lý địa chỉ (`addresses.php`)
- ✅ Tích hợp vào quy trình checkout

### 3. **Cải Thiện Checkout**
- ✅ Chọn địa chỉ có sẵn hoặc nhập địa chỉ mới
- ✅ Hiển thị địa chỉ đã chọn trong bước thanh toán
- ✅ Validation và xử lý địa chỉ giao hàng

## 🗄️ Cài Đặt Database

### Bước 1: Tạo bảng Reviews (nếu chưa có)
Chạy file `database_reviews.sql` trong phpMyAdmin để tạo bảng đánh giá sản phẩm.

### Bước 2: Tạo bảng Wishlist và Addresses
Chạy file `database_wishlist_addresses.sql` trong phpMyAdmin để tạo:
- Bảng `wishlist` - Lưu danh sách yêu thích
- Bảng `shipping_addresses` - Lưu địa chỉ giao hàng

**Lưu ý:** Đảm bảo các bảng `user` và `product` đã tồn tại và có kiểu dữ liệu `BIGINT(20)` cho cột `id`.

## 📁 Các File Mới Đã Tạo

1. **Database:**
   - `database_wishlist_addresses.sql` - Schema cho wishlist và addresses

2. **Wishlist:**
   - `wishlist.php` - Trang hiển thị danh sách yêu thích
   - `add_wishlist.php` - API thêm sản phẩm vào wishlist
   - `remove_wishlist.php` - API xóa sản phẩm khỏi wishlist

3. **Addresses:**
   - `addresses.php` - Trang quản lý địa chỉ giao hàng

## 🔗 Các Link Đã Cập Nhật

- `user.php` - Link đến wishlist và addresses
- `includes/header.php` - Link trong dropdown menu
- `index.php` - Tích hợp wishlist button
- `product_detail.php` - Tích hợp wishlist button
- `checkout.php` - Tích hợp chọn địa chỉ giao hàng

## 🎯 Cách Sử Dụng

### Sử dụng Wishlist:
1. Trên trang sản phẩm, click vào icon ❤️ để thêm vào wishlist
2. Xem danh sách yêu thích tại: Menu người dùng → "Danh Sách Yêu Thích"
3. Xóa sản phẩm khỏi wishlist bằng nút X trên card sản phẩm

### Sử dụng Địa Chỉ Giao Hàng:
1. Vào Menu người dùng → "Địa Chỉ Giao Hàng"
2. Thêm địa chỉ mới hoặc chỉnh sửa địa chỉ có sẵn
3. Đặt địa chỉ mặc định để sử dụng nhanh khi checkout
4. Trong quy trình checkout, chọn địa chỉ có sẵn hoặc nhập địa chỉ mới

## ⚠️ Lưu Ý

1. **Database:** Đảm bảo chạy các file SQL theo thứ tự:
   - `database_reviews.sql` (nếu chưa có)
   - `database_wishlist_addresses.sql`

2. **Permissions:** Đảm bảo các file PHP có quyền đọc/ghi phù hợp

3. **Session:** Các tính năng yêu cầu người dùng đã đăng nhập

4. **Foreign Keys:** Các bảng mới sử dụng foreign keys, đảm bảo:
   - Bảng `user` và `product` tồn tại
   - Kiểu dữ liệu `id` là `BIGINT(20)`
   - Charset là `utf8` và collation là `utf8_general_ci`

## 🐛 Xử Lý Lỗi

### Lỗi Foreign Key Constraint:
- Kiểm tra kiểu dữ liệu của `id` trong bảng `user` và `product`
- Đảm bảo charset và collation khớp nhau

### Wishlist không hoạt động:
- Kiểm tra bảng `wishlist` đã được tạo chưa
- Kiểm tra user đã đăng nhập chưa
- Kiểm tra console browser để xem lỗi JavaScript

### Địa chỉ không lưu được:
- Kiểm tra bảng `shipping_addresses` đã được tạo chưa
- Kiểm tra các trường bắt buộc đã điền đầy đủ chưa

## 📝 Các Cải Tiến Khác

- ✅ Đổi tên từ ModernShop sang DuckShop
- ✅ Cải thiện UI/UX cho search bar
- ✅ Căn chỉnh navbar và hero section
- ✅ Tích hợp đầy đủ các link navigation
- ✅ Responsive design cho tất cả các trang mới

---

**Ngày cập nhật:** 2024  
**Phiên bản:** 2.0










>>>>>>> Incoming (Background Agent changes)

