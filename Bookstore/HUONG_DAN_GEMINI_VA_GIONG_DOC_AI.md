# Hướng Dẫn Chi Tiết: Gemini AI Chat và Giọng Đọc AI

## 📋 Mục Lục
1. [Tổng Quan](#tổng-quan)
2. [Luồng Chạy Hệ Thống](#luồng-chạy-hệ-thống)
3. [Cấu Hình Gemini API](#cấu-hình-gemini-api)
4. [Cấu Hình Giọng Đọc AI](#cấu-hình-giọng-đọc-ai)
5. [Cách Sử Dụng](#cách-sử-dụng)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Tổng Quan

Dự án Bookstore sử dụng:
- **Google Gemini AI** để tạo chatbot trợ lý AI giúp người dùng tìm sách
- **Text-to-Speech (TTS) API** để đọc sách bằng giọng nói AI

---

## 🔄 Luồng Chạy Hệ Thống

### 1. Luồng Gemini AI Chat

```
Người dùng click icon robot
    ↓
Mở widget chat (geminiChatWidget)
    ↓
Người dùng nhập câu hỏi
    ↓
JavaScript gửi POST request đến /api/gemini-chat
    ↓
GeminiChatServlet xử lý:
    - Lấy API key từ config
    - Gọi Gemini API
    - Trả về response
    ↓
JavaScript hiển thị response trong chat
    ↓
Nếu người dùng muốn tìm sách:
    - AI tự động trích xuất tên sách/tác giả
    - Redirect đến trang tìm kiếm
```

### 2. Luồng Đọc Sách với Giọng Đọc AI

```
Người dùng click "Đọc sách"
    ↓
Kiểm tra gói cước còn hiệu lực
    ↓
DocSachServlet load nội dung sách
    ↓
Trang doc-sach.jsp hiển thị:
    - Nội dung sách
    - Button "Đọc bằng giọng AI"
    ↓
JavaScript gọi TTS API
    ↓
Phát audio đọc sách
```

---

## 🔑 Cấu Hình Gemini API

### Bước 1: Lấy API Key từ Google AI Studio

1. Truy cập: https://makersuite.google.com/app/apikey
2. Đăng nhập bằng tài khoản Google
3. Click "Create API Key"
4. Copy API key (dạng: `AIzaSy...`)

### Bước 2: Lưu API Key vào Database

Có 2 cách:

#### Cách 1: Qua Admin Panel (Khuyến nghị)

1. Đăng nhập admin: `/admin/login`
2. Vào **Cấu hình** → **Settings**
3. Tìm key `GEMINI_API_KEY` hoặc tạo mới
4. Nhập API key vào value
5. Lưu

#### Cách 2: Qua SQL

```sql
INSERT INTO `config` (`key`, `value`, `moTa`) 
VALUES ('GEMINI_API_KEY', 'AIzaSy...', 'API Key cho Gemini AI');

-- Hoặc cập nhật nếu đã có
UPDATE `config` 
SET `value` = 'AIzaSy...' 
WHERE `key` = 'GEMINI_API_KEY';
```

### Bước 3: Kiểm Tra Cấu Hình

File: `src/java/controller/KhachHang/GeminiChatServlet.java`

```java
// Servlet tự động lấy API key từ database
ConfigDAO configDAO = new ConfigDAO();
Config apiKeyConfig = configDAO.getByKey("GEMINI_API_KEY");
String apiKey = apiKeyConfig != null ? apiKeyConfig.getValue() : null;
```

**Lưu ý:** Nếu không có API key, chat sẽ hiển thị lỗi.

---

## 🎤 Cấu Hình Giọng Đọc AI

### Option 1: Google Text-to-Speech API (Khuyến nghị)

#### Bước 1: Bật Google Cloud TTS API

1. Truy cập: https://console.cloud.google.com/
2. Tạo project mới hoặc chọn project
3. Vào **APIs & Services** → **Library**
4. Tìm "Cloud Text-to-Speech API"
5. Click **Enable**

#### Bước 2: Tạo Service Account

1. Vào **IAM & Admin** → **Service Accounts**
2. Click **Create Service Account**
3. Đặt tên: `tts-service`
4. Chọn role: **Cloud Text-to-Speech API User**
5. Tạo và download JSON key file

#### Bước 3: Lưu Credentials

**Cách 1: Environment Variable (Khuyến nghị)**

```bash
# Windows (PowerShell)
$env:GOOGLE_APPLICATION_CREDENTIALS="D:\path\to\service-account-key.json"

# Linux/Mac
export GOOGLE_APPLICATION_CREDENTIALS="/path/to/service-account-key.json"
```

**Cách 2: Trong Code**

File: `src/java/controller/KhachHang/TextToSpeechServlet.java` (cần tạo)

```java
System.setProperty("GOOGLE_APPLICATION_CREDENTIALS", 
    "D:\\path\\to\\service-account-key.json");
```

#### Bước 4: Cấu Hình trong Database

```sql
INSERT INTO `config` (`key`, `value`, `moTa`) 
VALUES 
('TTS_PROVIDER', 'google', 'Nhà cung cấp TTS: google, azure, amazon'),
('TTS_LANGUAGE', 'vi-VN', 'Ngôn ngữ giọng đọc'),
('TTS_VOICE', 'vi-VN-Standard-A', 'Tên giọng đọc');
```

### Option 2: Azure Cognitive Services (Thay thế)

1. Tạo Azure account: https://azure.microsoft.com/
2. Tạo Speech resource
3. Lấy API key và region
4. Lưu vào database:

```sql
INSERT INTO `config` (`key`, `value`, `moTa`) 
VALUES 
('TTS_PROVIDER', 'azure', 'Nhà cung cấp TTS'),
('AZURE_SPEECH_KEY', 'your-key-here', 'Azure Speech API Key'),
('AZURE_SPEECH_REGION', 'southeastasia', 'Azure Region');
```

### Option 3: Amazon Polly (Thay thế)

1. Tạo AWS account
2. Bật Amazon Polly
3. Tạo IAM user với quyền Polly
4. Lưu credentials:

```sql
INSERT INTO `config` (`key`, `value`, `moTa`) 
VALUES 
('TTS_PROVIDER', 'amazon', 'Nhà cung cấp TTS'),
('AWS_ACCESS_KEY', 'your-key', 'AWS Access Key'),
('AWS_SECRET_KEY', 'your-secret', 'AWS Secret Key'),
('AWS_REGION', 'ap-southeast-1', 'AWS Region');
```

---

## 💻 Cách Sử Dụng

### 1. Sử Dụng Gemini Chat

1. **Mở chat:**
   - Click icon robot ở header (góc phải)
   - Hoặc gọi hàm: `openGeminiChat()`

2. **Chat với AI:**
   ```
   Người dùng: "Tìm sách về lập trình"
   AI: "Tôi sẽ tìm sách về lập trình cho bạn..."
   → Tự động redirect đến trang tìm kiếm
   ```

3. **Các câu lệnh hỗ trợ:**
   - "Tìm sách [tên sách]"
   - "Tìm sách của tác giả [tên tác giả]"
   - "Sách [tên sách] có không?"
   - "Tìm kiếm sách [từ khóa]"

### 2. Sử Dụng Giọng Đọc AI

1. **Đăng ký gói cước:**
   - Vào `/goi-cuoc`
   - Chọn gói (1 tháng, 6 tháng, 1 năm)
   - Thanh toán qua VNPay

2. **Đọc sách:**
   - Vào trang chi tiết sách
   - Click "Đọc sách"
   - Trong trang đọc, click "Đọc bằng giọng AI"
   - Audio sẽ tự động phát

3. **Điều khiển:**
   - Play/Pause
   - Tốc độ đọc (0.5x - 2x)
   - Chọn giọng (nếu có nhiều giọng)

---

## 🔧 Troubleshooting

### Lỗi Gemini API

**Lỗi:** "API key không hợp lệ"
- **Nguyên nhân:** API key sai hoặc chưa được lưu
- **Giải pháp:**
  1. Kiểm tra trong database: `SELECT * FROM config WHERE key = 'GEMINI_API_KEY'`
  2. Kiểm tra trong GeminiChatServlet có lấy đúng key không
  3. Thử API key mới từ Google AI Studio

**Lỗi:** "Quota exceeded"
- **Nguyên nhân:** Vượt quá giới hạn free tier
- **Giải pháp:**
  1. Đợi reset quota (hàng tháng)
  2. Nâng cấp lên paid plan

**Lỗi:** "CORS error"
- **Nguyên nhân:** Gọi API từ client
- **Giải pháp:** Đã xử lý qua backend servlet, không cần lo

### Lỗi Giọng Đọc AI

**Lỗi:** "Không thể tạo audio"
- **Nguyên nhân:** 
  - Chưa cấu hình credentials
  - API key sai
  - Không có internet
- **Giải pháp:**
  1. Kiểm tra `GOOGLE_APPLICATION_CREDENTIALS`
  2. Kiểm tra API key trong database
  3. Kiểm tra kết nối internet

**Lỗi:** "Giọng đọc không tự nhiên"
- **Giải pháp:**
  1. Thử giọng khác (Standard-B, Standard-C)
  2. Điều chỉnh tốc độ
  3. Sử dụng Neural voices (premium)

---

## 📁 Vị Trí Các File Quan Trọng

### Gemini AI
- **Frontend:** `web/views/khachhang/layout/header.jsp` (dòng 103-916)
- **Backend:** `src/java/controller/KhachHang/GeminiChatServlet.java`
- **API Endpoint:** `/api/gemini-chat`

### Giọng Đọc AI
- **Frontend:** `web/views/khachhang/doc-sach.jsp`
- **Backend:** `src/java/controller/KhachHang/TextToSpeechServlet.java` (cần tạo)
- **API Endpoint:** `/api/text-to-speech`

### Cấu Hình
- **Database:** Bảng `config`
- **Admin Panel:** `/admin/config/settings`

---

## 🎯 Best Practices

1. **Bảo mật API Key:**
   - Không commit API key vào Git
   - Sử dụng environment variables
   - Rotate key định kỳ

2. **Tối ưu Performance:**
   - Cache responses khi có thể
   - Giới hạn số request/người dùng
   - Sử dụng CDN cho audio files

3. **User Experience:**
   - Hiển thị loading indicator
   - Xử lý lỗi gracefully
   - Cung cấp fallback options

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề, kiểm tra:
1. Logs trong console (F12)
2. Server logs
3. Database config table
4. API quota/limits

---

**Cập nhật lần cuối:** 2024








