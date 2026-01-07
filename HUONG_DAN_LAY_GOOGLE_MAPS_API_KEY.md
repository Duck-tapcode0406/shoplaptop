# Hướng Dẫn Lấy Google Maps API Key

## Bước 1: Truy cập Google Cloud Console

1. Truy cập: https://console.cloud.google.com/
2. Đăng nhập bằng tài khoản Google của bạn

## Bước 2: Tạo Project Mới (hoặc chọn project hiện có)

1. Click vào dropdown project ở thanh trên cùng (bên cạnh logo Google Cloud)
2. Click **"NEW PROJECT"** (Dự án mới)
3. Đặt tên project (ví dụ: "Shop Maps")
4. Click **"CREATE"** (Tạo)
5. Chờ vài giây để project được tạo, sau đó chọn project vừa tạo

## Bước 3: Bật Google Maps JavaScript API

1. Vào **"APIs & Services"** > **"Library"** (Thư viện)
2. Tìm kiếm: **"Maps JavaScript API"**
3. Click vào **"Maps JavaScript API"**
4. Click nút **"ENABLE"** (Bật)

## Bước 4: Bật Places API (cho tính năng tìm kiếm địa chỉ)

1. Vẫn trong **"APIs & Services"** > **"Library"**
2. Tìm kiếm: **"Places API"**
3. Click vào **"Places API"**
4. Click nút **"ENABLE"** (Bật)

## Bước 5: Tạo API Key

1. Vào **"APIs & Services"** > **"Credentials"** (Thông tin xác thực)
2. Click **"+ CREATE CREDENTIALS"** (Tạo thông tin xác thực)
3. Chọn **"API key"**
4. API Key sẽ được tạo và hiển thị trong popup
5. **SAO CHÉP API KEY** ngay lập tức (bạn sẽ cần nó)

## Bước 6: Giới hạn API Key (Quan trọng - Bảo mật)

1. Click vào API Key vừa tạo để chỉnh sửa
2. Trong phần **"API restrictions"**:
   - Chọn **"Restrict key"**
   - Chọn các API sau:
     - ✅ Maps JavaScript API
     - ✅ Places API
     - ✅ Geocoding API (nếu cần)
3. Trong phần **"Application restrictions"**:
   - Chọn **"HTTP referrers (web sites)"**
   - Thêm các referrer sau:
     ```
     http://localhost/*
     http://localhost/shop/*
     https://yourdomain.com/*
     https://yourdomain.com/shop/*
     ```
   - (Thay `yourdomain.com` bằng domain thực tế của bạn)
4. Click **"SAVE"** (Lưu)

## Bước 7: Cập nhật API Key vào code

1. Mở file `includes/config.php`
2. Tìm dòng:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'cdabba37434cb5ff0d99dc4ea1addfcf137eef3fcbb6ee1c0e705f7ba2dd3ab6');
   ```
3. Thay thế bằng API Key vừa lấy:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');
   ```

## Bước 8: Kiểm tra

1. Refresh trang `addresses.php`
2. Click nút **"Mở Google Maps"**
3. Nếu map hiển thị bình thường = thành công! ✅

## Lưu ý Quan Trọng

### ⚠️ Bảo mật API Key:
- **KHÔNG** commit API Key vào Git
- **KHÔNG** chia sẻ API Key công khai
- Luôn giới hạn API Key theo domain/IP
- Giới hạn API Key chỉ cho các API cần thiết

### 💰 Chi phí:
- Google Maps có **$200 credit miễn phí mỗi tháng**
- Với lượng sử dụng vừa phải, thường không mất phí
- Xem chi tiết: https://mapsplatform.google.com/pricing/

### 🔧 Nếu gặp lỗi:
- **"InvalidKey"**: Kiểm tra lại API Key đã đúng chưa
- **"RefererNotAllowed"**: Thêm domain vào Application restrictions
- **"ApiNotActivated"**: Bật Maps JavaScript API và Places API

## Cấu trúc API Key

Google Maps API Key thường có dạng:
- Độ dài: ~39 ký tự
- Ví dụ: `AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

**KHÁC** với SerpAPI key (64+ ký tự)



