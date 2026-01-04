package util;

import java.sql.Date;
import java.sql.Timestamp; // Import thêm Timestamp

public class DateUtil {

    /**
     * Chuyển đổi từ java.util.Date (thường dùng trong logic) 
     * sang java.sql.Date (dùng để lưu vào CSDL - chỉ lưu NGÀY).
     * * @param utilDate Đối tượng java.util.Date
     * @return Đối tượng java.sql.Date
     */
    public static java.sql.Date toSqlDate(java.util.Date utilDate) {
        if (utilDate == null) {
            return null;
        }
        return new java.sql.Date(utilDate.getTime());
    }

    /**
     * Chuyển đổi từ java.sql.Date (lấy từ CSDL) 
     * sang java.util.Date (dùng cho logic hoặc hiển thị).
     * * @param sqlDate Đối tượng java.sql.Date
     * @return Đối tượng java.util.Date
     */
    public static java.util.Date toUtilDate(java.sql.Date sqlDate) {
        if (sqlDate == null) {
            return null;
        }
        return new java.util.Date(sqlDate.getTime());
    }
    
    /**
     * Lấy ngày hiện tại dưới dạng java.sql.Date (sẵn sàng để lưu vào CSDL)
     * * @return java.sql.Date của ngày hôm nay
     */
    public static java.sql.Date getCurrentSqlDate() {
        return new java.sql.Date(System.currentTimeMillis());
    }
    
    // ---------------------------------------------------------------- //
    // --- CÁC HÀM BỔ SUNG ĐỂ SỬA LỖI "QUÊN MẬT KHẨU" ---
    // ---------------------------------------------------------------- //

    /**
     * [MỚI] Lấy thời điểm hiện tại (Ngày + Giờ + Phút + Giây)
     * Dùng để lưu thời gian tạo mã, thời gian đăng bài...
     * @return java.sql.Timestamp
     */
    public static java.sql.Timestamp getCurrentSqlTimestamp() {
        return new java.sql.Timestamp(System.currentTimeMillis());
    }

    /**
     * [MỚI] Lấy thời điểm trong tương lai (sau N phút)
     * Dùng để lưu thời gian hết hạn của mã xác thực
     * @param minutes Số phút
     * @return java.sql.Timestamp
     */
    public static java.sql.Timestamp getSqlTimestampAfterMinutes(int minutes) {
        long millisInFuture = System.currentTimeMillis() + (minutes * 60L * 1000L);
        return new java.sql.Timestamp(millisInFuture);
    }
    
    /*
     * Dùng để test
     */
    public static void main(String[] args) {
        java.util.Date utilNow = new java.util.Date();
        System.out.println("Util Date hiện tại: " + utilNow);
        
        java.sql.Date sqlNow = getCurrentSqlDate();
        System.out.println("SQL Date hiện tại:  " + sqlNow);
        
        System.out.println("---------------------------------");
        
        java.sql.Timestamp tsNow = getCurrentSqlTimestamp();
        System.out.println("SQL Timestamp hiện tại: " + tsNow);
        
        java.sql.Timestamp tsExpiry = getSqlTimestampAfterMinutes(10);
        System.out.println("SQL Timestamp 10p sau:  " + tsExpiry);
    }
}