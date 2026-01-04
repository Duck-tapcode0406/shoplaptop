package util;

import database.KhachHangDAO;
import model.KhachHang;


public class UpdatePasswordUtil {

    public static boolean updatePasswordByUsername(String username, String plainPassword) {
        try {
            KhachHangDAO khachHangDAO = new KhachHangDAO();
            KhachHang user = khachHangDAO.selectByUsername(username);
            
            if (user == null) {
                System.err.println("❌ Không tìm thấy tài khoản: " + username);
                return false;
            }
            
            // Hash mật khẩu và cập nhật
            int result = khachHangDAO.updatePassword(user.getMaKhachHang(), plainPassword);
            
            if (result > 0) {
                System.out.println("✅ Đã cập nhật mật khẩu cho tài khoản: " + username + " (Mã: " + user.getMaKhachHang() + ")");
                return true;
            } else {
                System.err.println("❌ Không thể cập nhật mật khẩu cho: " + username);
                return false;
            }
        } catch (Exception e) {
            System.err.println("❌ Lỗi khi cập nhật mật khẩu cho " + username + ": " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }
    
    /**
     * Hash lại mật khẩu cho nhiều tài khoản
     */
    public static void main(String[] args) {
        System.out.println("===========================================");
        System.out.println("  CẬP NHẬT MẬT KHẨU - BOOKSTORE");
        System.out.println("===========================================\n");
        
        // Danh sách tài khoản cần cập nhật (username, password)
        String[][] accounts = {
            {"admin", "pass123"},
            {"nguyenvana", "pass123"},
            {"tranvanbinh", "pass123"},
            {"lethic", "pass123"},
            {"phamvand", "pass123"}
        };
        
        int successCount = 0;
        int failCount = 0;
        
        for (String[] account : accounts) {
            String username = account[0];
            String password = account[1];
            
            System.out.println("Đang cập nhật mật khẩu cho: " + username);
            boolean success = updatePasswordByUsername(username, password);
            
            if (success) {
                successCount++;
            } else {
                failCount++;
            }
            System.out.println();
        }
        
        System.out.println("===========================================");
        System.out.println("  KẾT QUẢ");
        System.out.println("===========================================");
        System.out.println("✅ Thành công: " + successCount);
        System.out.println("❌ Thất bại: " + failCount);
        System.out.println("\n===========================================");
        System.out.println("  HOÀN TẤT");
        System.out.println("===========================================");
        System.out.println("\nBây giờ bạn có thể đăng nhập với:");
        System.out.println("- Tên đăng nhập: admin, nguyenvana, tranvanbinh, lethic, phamvand");
        System.out.println("- Mật khẩu: pass123");
    }
}

