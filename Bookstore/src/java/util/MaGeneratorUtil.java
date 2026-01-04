package util;

import java.util.UUID;
import java.util.concurrent.ThreadLocalRandom;

public class MaGeneratorUtil {

    /**
     * Tạo một ID duy nhất dựa trên UUID (Universally Unique Identifier).
     * Rất dài và đảm bảo không bao giờ trùng lặp.
     * Ví dụ: "a1b2c3d4-e5f6-7890-g1h2-i3j4k5l6m7n8"
     * * @return Chuỗi ID 36 ký tự
     */
    public static String generateUUID() {
        return UUID.randomUUID().toString();
    }

    /**
     * Tạo một mã ngẫu nhiên có độ dài xác định (chỉ chứa số).
     * Dùng cho mã xác thực "Quên mật khẩu"
     * * @param length Độ dài của mã (ví dụ: 6)
     * @return Chuỗi số ngẫu nhiên (ví dụ: "123456")
     */
    public static String generateRandomCode(int length) {
        if (length <= 0) {
            throw new IllegalArgumentException("Độ dài phải lớn hơn 0");
        }
        
        long min = (long) Math.pow(10, length - 1);
        long max = (long) Math.pow(10, length) - 1;
        
        long randomNum = ThreadLocalRandom.current().nextLong(min, max + 1);
        return String.valueOf(randomNum);
    }

    /**
     * Tạo một mã đơn hàng (ví dụ: "HD-12345678")
     * @return Chuỗi mã đơn hàng
     */
    public static String generateOrderCode() {
        // Lấy 8 số cuối của timestamp (cho nhanh và gần như duy nhất)
        String timestampPart = String.valueOf(System.currentTimeMillis() % 100000000);
        return "HD-" + timestampPart;
    }
    
    /**
     * Tạo mã khách hàng (ví dụ: "KH-123456")
     * @return Chuỗi mã khách hàng
     */
    public static String generateCustomerCode() {
        String timestampPart = String.valueOf(System.currentTimeMillis() % 1000000);
        return "KH-" + timestampPart;
    }
    
    /*
     * Dùng để test
     */
    public static void main(String[] args) {
        System.out.println("UUID: " + generateUUID());
        System.out.println("Mã xác thực (6 số): " + generateRandomCode(6));
        System.out.println("Mã Đơn hàng: " + generateOrderCode());
        System.out.println("Mã Khách hàng: " + generateCustomerCode());
    }
}