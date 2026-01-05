# Cấu Hình SerpAPI Google Maps

## Tổng Quan

SerpAPI Google Maps API cho phép tìm kiếm địa điểm trên Google Maps thông qua API endpoint.

## Endpoint

```
https://serpapi.com/search.json?engine=google_maps
```

## Tham Số API Quan Trọng

### Tham số bắt buộc:
- `engine`: `"google_maps"` (bắt buộc)
- `api_key`: SerpAPI key của bạn (bắt buộc)

### Tham số tìm kiếm:
- `q`: Truy vấn tìm kiếm (ví dụ: "Coffee", "Restaurant", "near me")
- `ll`: Tọa độ GPS format `@latitude,longitude,zoom` (ví dụ: `@40.7455096,-74.0083012,14z`)
  - `zoom`: 3-30 (mặc định 14)
  - Có thể dùng `m` (mét) thay vì `z`: `@40.7455096,-74.0083012,10410m`
- `type`: `"search"` (mặc định) hoặc `"place"`
- `nearby`: `true` (khuyến nghị khi query có "near me")

### Tham số vị trí (thay thế cho `ll`):
- `location`: Tên địa điểm (ví dụ: "New York")
- `lat`: Vĩ độ (phải dùng cùng `lon`)
- `lon`: Kinh độ (phải dùng cùng `lat`)
- `z`: Mức zoom (3-30) hoặc `m`: độ cao tính bằng mét

### Tham số ngôn ngữ:
- `hl`: Ngôn ngữ (ví dụ: `vi`, `en`)
- `gl`: Quốc gia (ví dụ: `vn`, `us`)
- `google_domain`: Tên miền Google (mặc định: `google.com`)

### Tham số phân trang:
- `start`: Offset (0, 20, 40, ...) - tối đa khuyến nghị 100

## Cấu Trúc Response

### Response chính:
```json
{
  "search_metadata": {
    "id": "...",
    "status": "Success",
    "json_endpoint": "...",
    "created_at": "...",
    "processed_at": "...",
    "google_maps_url": "..."
  },
  "search_parameters": {
    "engine": "google_maps",
    "q": "...",
    "ll": "..."
  },
  "local_results": [
    {
      "position": 1,
      "title": "Tên địa điểm",
      "place_id": "ChIJ...",
      "data_id": "0x...",
      "data_cid": "...",
      "gps_coordinates": {
        "latitude": 40.7455096,
        "longitude": -74.0083012
      },
      "address": "Địa chỉ đầy đủ",
      "phone": "Số điện thoại",
      "rating": 4.5,
      "reviews": 100,
      "type": "Coffee shop",
      "price": "$1–10",
      "open_state": "Open · Closes 5 PM",
      "hours": "Open · Closes 5 PM",
      "website": "https://...",
      "thumbnail": "https://...",
      "service_options": {
        "dine_in": true,
        "takeout": true,
        "delivery": false
      }
    }
  ],
  "serpapi_pagination": {
    "next": "URL trang tiếp theo"
  }
}
```

### Các trường quan trọng trong `local_results`:
- `title`: Tên địa điểm
- `address`: Địa chỉ đầy đủ
- `gps_coordinates`: {latitude, longitude}
- `place_id`: Google Place ID (duy nhất)
- `data_id`: Data ID của Google
- `data_cid`: Customer ID của Google
- `phone`: Số điện thoại
- `rating`: Điểm đánh giá (0-5)
- `reviews`: Số lượng đánh giá
- `type`: Loại địa điểm
- `price`: Mức giá (nếu có)
- `open_state`: Trạng thái mở/đóng
- `hours`: Giờ mở cửa
- `website`: Website (nếu có)

## Ví Dụ Sử Dụng

### 1. Tìm kiếm cơ bản:
```
GET https://serpapi.com/search.json?engine=google_maps&q=Coffee&ll=@40.7455096,-74.0083012,14z&api_key=YOUR_KEY
```

### 2. Tìm kiếm với "near me":
```
GET https://serpapi.com/search.json?engine=google_maps&q=Coffee+near+me&ll=@40.7455096,-74.0083012,14z&nearby=true&api_key=YOUR_KEY
```

### 3. Tìm kiếm với ngôn ngữ Việt:
```
GET https://serpapi.com/search.json?engine=google_maps&q=Cà+phê&ll=@10.762622,106.660172,14z&hl=vi&gl=vn&api_key=YOUR_KEY
```

### 4. Phân trang:
```
GET https://serpapi.com/search.json?engine=google_maps&q=Coffee&ll=@40.7455096,-74.0083012,14z&start=20&api_key=YOUR_KEY
```

## Lưu Ý Quan Trọng

1. **Kết quả không đảm bảo trong phạm vi `ll`**: Kết quả có thể nằm ngoài vùng được chỉ định
2. **Sử dụng `nearby=true`**: Khuyến nghị khi query có "near me"
3. **Không dùng `nearby` với location trong query**: Nếu query đã có thành phố/địa điểm, không nên dùng `nearby`
4. **Giới hạn phân trang**: Khuyến nghị tối đa `start=100` (trang 6)
5. **Cache**: Kết quả được cache 1 giờ, các request giống nhau sẽ không tính vào quota

## Gói Miễn Phí

- 250 lượt tìm kiếm/tháng
- Các request cache không tính vào quota

## Cách Lấy API Key

1. Đăng ký tại: https://serpapi.com/
2. Vào Dashboard
3. Copy API key
4. Thêm vào `api/config.php`:
   ```php
   define('SERPAPI_KEY', 'your_actual_api_key_here');
   ```

## Tích Hợp Vào Code

File `api/update_location.php` đã được cập nhật để sử dụng SerpAPI với các tham số đúng. Chỉ cần:
1. Thêm `SERPAPI_KEY` vào `api/config.php`
2. API sẽ tự động sử dụng SerpAPI để tìm địa điểm

