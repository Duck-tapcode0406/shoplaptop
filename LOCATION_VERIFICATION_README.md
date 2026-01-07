<<<<<<< Current (Your changes)
=======
# Hướng Dẫn Sử Dụng Location Verification

## Tổng Quan

Hệ thống xác nhận vị trí cho phép khách hàng tự động xác nhận vị trí hiện tại và cập nhật thông tin địa chỉ giao hàng dựa trên dữ liệu từ SerpAPI Google Maps.

## Tính Năng

- ✅ Xác nhận vị trí hiện tại của khách hàng bằng browser geolocation API
- ✅ Tìm địa điểm gần nhất sử dụng SerpAPI Google Maps
- ✅ Tự động điền thông tin địa chỉ giao hàng
- ✅ Cập nhật địa chỉ vào database
- ✅ Hiển thị dialog xác nhận với thông tin chi tiết

## Cài Đặt

### 1. Cấu Hình SerpAPI

1. Đăng ký tài khoản tại [SerpAPI](https://serpapi.com/)
2. Lấy API key từ dashboard
3. Tạo file `api/config.php` từ `api/config.example.php`:
   ```bash
   cp api/config.example.php api/config.php
   ```
4. Điền API key vào file `api/config.php`:
   ```php
   define('SERPAPI_KEY', 'your_actual_api_key_here');
   ```

### 2. Kiểm Tra Database

Đảm bảo bảng `shipping_addresses` đã được tạo bằng cách chạy file:
```sql
database_wishlist_addresses.sql
```

### 3. Cấu Trúc File

```
shop/
├── api/
│   ├── update_location.php      # API endpoint xử lý location
│   ├── config.php                # File cấu hình (tạo từ config.example.php)
│   └── config.example.php        # File cấu hình mẫu
├── js/
│   └── location-verification.js  # JavaScript xử lý location verification
├── checkout.php                  # Đã tích hợp location verification
└── addresses.php                 # Đã tích hợp location verification
```

## Cách Sử Dụng

### Trong Trang Checkout

1. Khách hàng vào trang thanh toán (Step 2: Vận Chuyển)
2. Click nút **"Xác Nhận Vị Trí Hiện Tại"**
3. Cho phép trình duyệt truy cập vị trí
4. Hệ thống sẽ:
   - Lấy tọa độ GPS hiện tại
   - Tìm địa điểm gần nhất
   - Hiển thị dialog xác nhận
   - Tự động điền form địa chỉ
   - Cập nhật vào database

### Trong Trang Quản Lý Địa Chỉ

1. Khách hàng vào trang `addresses.php`
2. Click nút **"Xác Nhận Vị Trí Hiện Tại"** trong form thêm địa chỉ
3. Quy trình tương tự như checkout

## Cấu Trúc Dữ Liệu SerpAPI

Hệ thống sử dụng cấu trúc dữ liệu từ SerpAPI Google Maps:

```json
{
  "local_results": [
    {
      "title": "Tên địa điểm",
      "address": "Địa chỉ",
      "phone": "Số điện thoại",
      "rating": 4.5,
      "gps_coordinates": {
        "latitude": 40.7477172,
        "longitude": -73.98653019999999
      }
    }
  ]
}
```

Hệ thống hỗ trợ cả định dạng tiếng Việt và tiếng Anh từ SerpAPI.

## API Endpoints

### POST `/api/update_location.php`

#### Action: `find_nearby`
Tìm địa điểm gần nhất dựa trên tọa độ GPS.

**Request:**
```json
{
  "action": "find_nearby",
  "latitude": 40.7455096,
  "longitude": -74.0083012,
  "query": "Coffee",
  "ll": "@40.7455096,-74.0083012,14z"
}
```

**Response:**
```json
{
  "success": true,
  "places": [
    {
      "title": "Tên địa điểm",
      "address": "Địa chỉ",
      "phone": "Số điện thoại",
      "rating": 4.5,
      "gps_coordinates": {
        "latitude": 40.7477172,
        "longitude": -73.98653019999999
      }
    }
  ]
}
```

#### Action: `update_address`
Cập nhật địa chỉ giao hàng dựa trên vị trí.

**Request:**
```json
{
  "action": "update_address",
  "location": {
    "latitude": 40.7455096,
    "longitude": -74.0083012,
    "address": "17 W 32nd St, New York, NY 10001",
    "title": "Quán cà phê",
    "phone": "(917) 540-2776",
    "city": "New York",
    "district": "Manhattan",
    "gps_coordinates": {
      "latitude": 40.7477172,
      "longitude": -73.98653019999999
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đã cập nhật địa chỉ giao hàng thành công",
  "address_id": 123
}
```

## JavaScript API

### Khởi Tạo

```javascript
const locationVerification = new LocationVerification({
    apiEndpoint: 'api/update_location.php',
    serpApiKey: 'your_api_key', // Optional, nếu không dùng config.php
    onSuccess: function(data) {
        console.log('Thành công:', data);
    },
    onError: function(message) {
        console.error('Lỗi:', message);
    },
    onLocationFound: function(place, location) {
        console.log('Địa điểm:', place);
        console.log('Vị trí:', location);
    }
});
```

### Methods

#### `getCurrentLocation()`
Lấy vị trí hiện tại của người dùng.

```javascript
locationVerification.getCurrentLocation()
    .then(location => {
        console.log('Vị trí:', location);
    })
    .catch(error => {
        console.error('Lỗi:', error);
    });
```

#### `findNearbyPlaces(latitude, longitude, query)`
Tìm địa điểm gần nhất.

```javascript
locationVerification.findNearbyPlaces(40.7455096, -74.0083012, 'Coffee')
    .then(places => {
        console.log('Địa điểm:', places);
    })
    .catch(error => {
        console.error('Lỗi:', error);
    });
```

#### `verifyAndUpdate()`
Xác nhận và cập nhật vị trí tự động (recommended).

```javascript
locationVerification.verifyAndUpdate();
```

## Xử Lý Lỗi

Hệ thống sẽ hiển thị thông báo lỗi trong các trường hợp:

- Trình duyệt không hỗ trợ geolocation
- Người dùng từ chối quyền truy cập vị trí
- Không tìm thấy địa điểm gần đây
- Lỗi kết nối API
- Lỗi lưu database

## Bảo Mật

- ✅ Kiểm tra đăng nhập trước khi sử dụng
- ✅ Validate và sanitize dữ liệu đầu vào
- ✅ Sử dụng prepared statements cho database
- ✅ API key được lưu trong config file (không commit vào git)

## Lưu Ý

1. **Quyền truy cập vị trí**: Trình duyệt sẽ yêu cầu quyền truy cập vị trí. Người dùng cần cho phép.

2. **HTTPS**: Geolocation API chỉ hoạt động trên HTTPS hoặc localhost.

3. **SerpAPI Limits**: Kiểm tra giới hạn API calls của SerpAPI plan của bạn.

4. **Fallback**: Nếu không có SerpAPI key, hệ thống sẽ sử dụng dữ liệu mẫu.

## Troubleshooting

### Lỗi "Trình duyệt không hỗ trợ định vị"
- Đảm bảo đang sử dụng trình duyệt hiện đại (Chrome, Firefox, Safari, Edge)
- Kiểm tra kết nối HTTPS

### Lỗi "Không tìm thấy địa điểm"
- Kiểm tra SerpAPI key có hợp lệ không
- Kiểm tra kết nối internet
- Thử với query khác

### Địa chỉ không được cập nhật
- Kiểm tra bảng `shipping_addresses` đã được tạo chưa
- Kiểm tra log lỗi PHP
- Kiểm tra quyền database user

## Hỗ Trợ

Nếu gặp vấn đề, vui lòng kiểm tra:
1. Console browser (F12) để xem lỗi JavaScript
2. Network tab để xem request/response API
3. PHP error log
4. Database connection









>>>>>>> Incoming (Background Agent changes)



