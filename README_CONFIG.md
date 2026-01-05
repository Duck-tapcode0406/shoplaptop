# Config & Deploy notes

1. Đặt biến môi trường (ví dụ in Linux):
   export SHOP_DB_HOST=localhost
   export SHOP_DB_USER=root
   export SHOP_DB_PASS=''
   export SHOP_DB_NAME=shop
   export SHOP_BASE_URL=/shop
   export SHOP_DEBUG=0

2. Chạy SQL để tạo bảng reset:
   mysql -u root -p shop < sql/create_password_resets.sql

3. Tạo file includes/config.php được sinh tự động từ env. Đảm bảo .env (local) hoặc biến môi trường được thiết lập.

4. Các file mới:
   - reset_request.php
   - reset_password.php
   - snippets/stock_lock.php
   - scripts/escape_messages.php

5. Kiểm tra includes/session.php đã bật cookie_secure khi dùng HTTPS.

