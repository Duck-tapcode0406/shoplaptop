# Kiểm Tra và Sửa Lỗi Google Maps API Key

## Lỗi hiện tại:
- Map hiển thị nhưng có popup lỗi "Trang này không thể tải Google Maps đúng cách"
- Có dòng chữ "For development purposes only" trên map

## Nguyên nhân có thể:
1. **API Key Restrictions không đúng** (thường gặp nhất)
2. **Thiếu Billing Account** (cần kích hoạt billing để dùng production)
3. **API chưa được bật đầy đủ**

## Cách sửa:

### Bước 1: Kiểm tra API Key Restrictions

1. Vào Google Cloud Console: https://console.cloud.google.com/
2. Chọn project của bạn
3. Vào **"APIs & Services"** → **"Credentials"**
4. Click vào API Key của bạn

#### A. Application restrictions (Hạn chế ứng dụng):
- Chọn **"HTTP referrers (web sites)"**
- **QUAN TRỌNG**: Đảm bảo format đúng với `/*` ở cuối:
  ```
  http://localhost/*
  http://localhost/shop/*
  ```
- **KHÔNG** dùng:
  ```
  http://localhost/
  http://localhost/shop/
  ```
- Click **"Lưu"** (Save)

#### B. API restrictions (Hạn chế API):
- Chọn **"Restrict key"** (Hạn chế khóa)
- Đảm bảo đã chọn:
  - ✅ **Maps JavaScript API**
  - ✅ **Places API**
  - ✅ **Geocoding API** (nếu đang dùng geocoding)

### Bước 2: Bật Billing Account (Nếu cần)

**Lưu ý**: Google Maps yêu cầu billing account để dùng production, nhưng có $200 credit miễn phí mỗi tháng.

1. Vào **"Billing"** (Thanh toán) trong Google Cloud Console
2. Nếu chưa có billing account, click **"Link a billing account"**
3. Thêm thẻ tín dụng (sẽ không bị charge nếu dùng trong giới hạn free tier)
4. Quay lại project và đảm bảo billing đã được link

### Bước 3: Kiểm tra các API đã bật

1. Vào **"APIs & Services"** → **"Library"**
2. Tìm và bật các API sau:
   - ✅ **Maps JavaScript API** - Đã bật
   - ✅ **Places API** - Đã bật
   - ✅ **Geocoding API** - Cần bật thêm

### Bước 4: Kiểm tra lại

1. Refresh trang `addresses.php` (Ctrl+F5)
2. Mở Console (F12) và xem có lỗi gì không
3. Click "Mở Google Maps" lại
4. Nếu vẫn lỗi, xem Console để biết lỗi cụ thể

## Lỗi thường gặp trong Console:

- **"RefererNotAllowedMapError"**: API Key restrictions không đúng
  - Sửa: Kiểm tra lại HTTP referrers có `/*` ở cuối
  
- **"ApiNotActivatedMapError"**: API chưa được bật
  - Sửa: Bật Maps JavaScript API và Places API
  
- **"BillingNotEnabledMapError"**: Chưa có billing account
  - Sửa: Link billing account (có $200 free credit)

## Test nhanh:

1. Tạm thời **bỏ restrictions** để test:
   - Application restrictions: Chọn **"None"**
   - API restrictions: Chọn **"Don't restrict key"**
   - Lưu và test lại
   
2. Nếu map hoạt động = vấn đề là ở restrictions
3. Nếu vẫn lỗi = vấn đề là ở billing hoặc API chưa bật

**Lưu ý**: Sau khi test xong, nhớ **bật lại restrictions** để bảo mật!

