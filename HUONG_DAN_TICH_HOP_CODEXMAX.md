# Hướng Dẫn Tích Hợp Code từ CodexMax

## 🎯 Tổng Quan
Hướng dẫn ngắn gọn cách lấy code từ CodexMax (hoặc nguồn code mẫu) và tích hợp vào file chính của dự án.

---

## 📋 Các Bước Tích Hợp

### 1. **Lấy Code từ CodexMax**

**Cách 1: Copy trực tiếp**
- Copy toàn bộ code snippet từ CodexMax
- Paste vào file tạm (ví dụ: `temp_code.php` hoặc `codexmax_snippet.php`)

**Cách 2: Tải file**
- Nếu CodexMax cung cấp file `.php`, tải về thư mục `snippets/` hoặc `includes/`

---

### 2. **Kiểm Tra Dependencies**

Trước khi tích hợp, kiểm tra:

```php
// Code từ CodexMax có require/include các file nào không?
require_once 'config.php';
require_once 'db.php';
// ... các file khác
```

**→ Đảm bảo:**
- Tất cả file được require đã tồn tại trong dự án
- Đường dẫn đúng (relative path hoặc sử dụng `__DIR__`)

---

### 3. **Tích Hợp vào File Chính**

#### **Option A: Include/Require (Khuyến nghị cho code dài)**

**Bước 1:** Đặt file codexmax vào thư mục `snippets/` hoặc `includes/`
```
snippets/codexmax_feature.php
```

**Bước 2:** Trong file chính (ví dụ `index.php`), thêm:
```php
<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
// ... các require hiện có

// Tích hợp CodexMax
require_once 'snippets/codexmax_feature.php';  // ← Thêm dòng này
?>
```

#### **Option B: Copy trực tiếp (Cho code ngắn)**

**Bước 1:** Mở file chính cần tích hợp (ví dụ: `index.php`, `checkout.php`)

**Bước 2:** Tìm vị trí phù hợp (thường là sau các require/include)

**Bước 3:** Paste code từ CodexMax vào

**Bước 4:** Kiểm tra:
- ✅ Không có lỗi cú pháp PHP
- ✅ Tên hàm/biến không trùng với code hiện có
- ✅ Đường dẫn file, database connection đúng

---

### 4. **Điều Chỉnh Code để Hoạt Động**

#### **4.1. Sửa Database Connection**

Code từ CodexMax có thể dùng:
```php
$conn = new mysqli('localhost', 'user', 'pass', 'db');
```

**→ Thay bằng:**
```php
require_once 'includes/db.php';
$conn = getDB();  // Dùng connection từ dự án
```

#### **4.2. Sửa Đường Dẫn**

Code từ CodexMax có thể dùng:
```php
include 'config.php';
```

**→ Thay bằng:**
```php
require_once __DIR__ . '/includes/config.php';
// hoặc
require_once 'includes/config.php';  // Nếu đã có trong include_path
```

#### **4.3. Sửa URL/Base Path**

Code từ CodexMax có thể dùng:
```php
$base_url = '/';
$image_path = '/images/logo.png';
```

**→ Thay bằng:**
```php
$base_url = defined('BASE_URL') ? BASE_URL : '/shop';
$image_path = $base_url . '/images/logo.png';
```

#### **4.4. Sửa Session Handling**

Code từ CodexMax có thể dùng:
```php
session_start();
$_SESSION['key'] = 'value';
```

**→ Đảm bảo đã có:**
```php
require_once 'includes/session.php';  // Đã có session security config
// Sau đó dùng $_SESSION như bình thường
```

---

### 5. **Test & Kiểm Tra**

#### **Checklist:**
- [ ] Code không báo lỗi cú pháp (Syntax Error)
- [ ] Trang load được (không 500 Error)
- [ ] Chức năng hoạt động đúng
- [ ] Không conflict với code hiện có
- [ ] CSS/JS (nếu có) load đúng

#### **Debug nếu có lỗi:**

**Lỗi "Call to undefined function":**
```php
// Kiểm tra hàm có tồn tại chưa
if (!function_exists('ten_ham')) {
    // Include file chứa hàm đó
    require_once 'path/to/file.php';
}
```

**Lỗi "Undefined variable":**
```php
// Đảm bảo biến đã được khởi tạo
$variable = isset($variable) ? $variable : 'default_value';
```

**Lỗi Database:**
```php
// Kiểm tra connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
```

---

## 📝 Ví Dụ Cụ Thể

### Ví Dụ 1: Tích hợp hàm utility

**File CodexMax:** `snippets/codexmax_utils.php`
```php
<?php
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' đ';
}
?>
```

**Tích hợp vào `index.php`:**
```php
<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'snippets/codexmax_utils.php';  // ← Thêm dòng này

// Sử dụng
echo formatPrice(1000000);  // Output: 1.000.000 đ
?>
```

### Ví Dụ 2: Tích hợp form component

**Code từ CodexMax:**
```php
<form method="POST">
    <input type="text" name="email">
    <button type="submit">Submit</button>
</form>
```

**Tích hợp vào `register.php`:**
```php
<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';  // Cho CSRF protection

// Thêm CSRF token vào form
echo '<form method="POST">';
echo getCSRFTokenField();  // ← Thêm CSRF token
echo '<input type="text" name="email">';
echo '<button type="submit">Submit</button>';
echo '</form>';

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRFPost();  // ← Validate CSRF
    // ... xử lý form
}
?>
```

---

## ⚠️ Lưu Ý Quan Trọng

1. **Backup trước khi sửa:**
   ```bash
   cp index.php index.php.backup
   ```

2. **Namespace/Prefix để tránh conflict:**
   ```php
   // Thay vì
   function calculate() { }
   
   // Dùng
   function codexmax_calculate() { }
   // hoặc
   class CodexMaxCalculator { }
   ```

3. **Kiểm tra Security:**
   - ✅ Escape output: `htmlspecialchars()`
   - ✅ Prepared statements cho SQL
   - ✅ CSRF protection cho forms
   - ✅ Validate input

4. **Tối ưu Performance:**
   - Chỉ include khi cần
   - Tránh include nhiều lần (dùng `require_once`)

---

## 🔧 Troubleshooting

**Lỗi: "Cannot redeclare function"**
- → Kiểm tra function đã tồn tại chưa: `if (!function_exists('ten_ham'))`

**Lỗi: "Class already exists"**
- → Kiểm tra class: `if (!class_exists('TenClass'))`

**Code chạy nhưng không hiển thị:**
- → Kiểm tra có `echo` hoặc `return` không
- → Kiểm tra có đặt trong `<body>` (cho HTML) không

**Database error:**
- → Kiểm tra `includes/db.php` đã được require chưa
- → Kiểm tra database credentials trong `includes/config.php`

---

## 📚 Tài Liệu Tham Khảo

- File config: `includes/config.php`
- Database: `includes/db.php`
- Session: `includes/session.php`
- CSRF: `includes/csrf.php`

---

**💡 Tip:** Nếu code từ CodexMax quá phức tạp, tách thành các file nhỏ trong `snippets/` và include từng phần khi cần.

