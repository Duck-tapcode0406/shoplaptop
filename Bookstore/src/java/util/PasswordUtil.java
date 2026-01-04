package util;

import org.mindrot.jbcrypt.BCrypt;

public class PasswordUtil {

    /**
     * Băm mật khẩu sử dụng BCrypt
     * @param plainPassword Mật khẩu gốc (dạng rõ)
     * @return Mật khẩu đã được băm
     */
    public static String hash(String plainPassword) {
        // Tham số "workload" (độ phức tạp), 12 là giá trị khuyến nghị
        return BCrypt.hashpw(plainPassword, BCrypt.gensalt(12));
    }

    /**
     * Kiểm tra mật khẩu gốc có khớp với mật khẩu đã băm hay không
     * @param plainPassword Mật khẩu gốc (do người dùng nhập)
     * @param hashedPassword Mật khẩu đã băm (lấy từ CSDL)
     * @return true nếu khớp, false nếu không khớp
     */
    public static boolean check(String plainPassword, String hashedPassword) {
        // (Xử lý lỗi nếu hashedPassword bị null hoặc không phải là chuỗi bcrypt)
        if (hashedPassword == null || !hashedPassword.startsWith("$2a$")) {
            System.err.println("!!! LỖI PasswordUtil: Mật khẩu trong CSDL không hợp lệ.");
            return false;
        }
        
        try {
            return BCrypt.checkpw(plainPassword, hashedPassword);
        } catch (Exception e) {
            System.err.println("!!! LỖI PasswordUtil: Lỗi khi kiểm tra checkpw: " + e.getMessage());
            return false;
        }
    }
    
    /*
     * Dùng để test
     */
    public static void main(String[] args) {
        String passGoc = "123456";
        String passBam = hash(passGoc);
        
        System.out.println("Mật khẩu gốc: " + passGoc);
        System.out.println("Mật khẩu băm:  " + passBam);
        System.out.println("Độ dài băm:  " + passBam.length());
        
        System.out.println("Kiểm tra (đúng):  " + check("123456", passBam));
        System.out.println("Kiểm tra (sai):   " + check("abcdef", passBam));
    }
}