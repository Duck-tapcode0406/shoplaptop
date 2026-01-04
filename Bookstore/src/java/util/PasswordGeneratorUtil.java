package util;

import java.security.SecureRandom;

/**
 * Utility class để tạo mật khẩu ngẫu nhiên an toàn
 * Sử dụng SecureRandom để đảm bảo tính ngẫu nhiên
 */
public class PasswordGeneratorUtil {
    
    private static final String UPPERCASE = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private static final String LOWERCASE = "abcdefghijklmnopqrstuvwxyz";
    private static final String DIGITS = "0123456789";
    private static final String SPECIAL = "!@#$%^&*";
    private static final String ALL_CHARS = UPPERCASE + LOWERCASE + DIGITS + SPECIAL;
    
    private static final SecureRandom random = new SecureRandom();
    
    /**
     * Tạo mật khẩu ngẫu nhiên an toàn
     * @param length Độ dài mật khẩu (tối thiểu 8, mặc định 12 nếu < 8)
     * @return Mật khẩu ngẫu nhiên
     */
    public static String generateRandomPassword(int length) {
        if (length < 8) {
            length = 12; // Mặc định 12 ký tự
        }
        
        StringBuilder password = new StringBuilder(length);
        
        // Đảm bảo có ít nhất 1 ký tự từ mỗi loại
        password.append(UPPERCASE.charAt(random.nextInt(UPPERCASE.length())));
        password.append(LOWERCASE.charAt(random.nextInt(LOWERCASE.length())));
        password.append(DIGITS.charAt(random.nextInt(DIGITS.length())));
        password.append(SPECIAL.charAt(random.nextInt(SPECIAL.length())));
        
        // Điền các ký tự còn lại
        for (int i = password.length(); i < length; i++) {
            password.append(ALL_CHARS.charAt(random.nextInt(ALL_CHARS.length())));
        }
        
        // Trộn ngẫu nhiên các ký tự để đảm bảo không có pattern
        char[] passwordArray = password.toString().toCharArray();
        for (int i = passwordArray.length - 1; i > 0; i--) {
            int j = random.nextInt(i + 1);
            char temp = passwordArray[i];
            passwordArray[i] = passwordArray[j];
            passwordArray[j] = temp;
        }
        
        return new String(passwordArray);
    }
    
    /**
     * Tạo mật khẩu ngẫu nhiên với độ dài mặc định (12 ký tự)
     * @return Mật khẩu ngẫu nhiên 12 ký tự
     */
    public static String generateRandomPassword() {
        return generateRandomPassword(12);
    }
}





