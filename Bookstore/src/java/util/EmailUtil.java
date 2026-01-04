package util;

import jakarta.mail.Authenticator;
import jakarta.mail.Message;
import jakarta.mail.MessagingException;
import jakarta.mail.PasswordAuthentication;
import jakarta.mail.Session;
import jakarta.mail.Transport;
import jakarta.mail.internet.InternetAddress;
import jakarta.mail.internet.MimeMessage;
import java.util.Properties;

public class EmailUtil {

    // --- THÔNG TIN CẤU HÌNH EMAIL ---
    // ⚠️ THAY THẾ BẰNG EMAIL VÀ "Mật khẩu ứng dụng" CỦA BẠN
    private static final String FROM_EMAIL = "daiducka123@gmail.com"; 
    private static final String FROM_PASSWORD = "pzjmoglukxrljil"; 

    /**
     * Gửi một email từ địa chỉ email đã cấu hình
     *
     * @param toEmail   Email của người nhận
     * @param subject   Tiêu đề email
     * @param htmlContent Nội dung email (hỗ trợ HTML)
     * @return true nếu gửi thành công, false nếu thất bại
     */
    public static boolean sendEmail(String toEmail, String subject, String htmlContent) {
        
        // 1. Cấu hình thuộc tính cho Session
        Properties props = new Properties();
        
        // Cấu hình SMTP cơ bản
        props.put("mail.smtp.host", "smtp.gmail.com"); // SMTP Host
        props.put("mail.smtp.port", "587"); // TLS Port (hoặc 465 cho SSL)
        props.put("mail.smtp.auth", "true"); // Bật xác thực
        
        // Cấu hình TLS (Port 587)
        props.put("mail.smtp.starttls.enable", "true"); // Bật STARTTLS
        props.put("mail.smtp.starttls.required", "true"); // Bắt buộc STARTTLS
        
        // Cấu hình timeout
        props.put("mail.smtp.connectiontimeout", "5000"); // 5 giây
        props.put("mail.smtp.timeout", "5000"); // 5 giây
        
        // Debug mode (tùy chọn - bật để xem log chi tiết)
        props.put("mail.debug", "false"); // Đặt true để xem log chi tiết
        
        // Cấu hình bổ sung
        props.put("mail.smtp.ssl.trust", "smtp.gmail.com"); // Trust Gmail server

        // 2. Tạo Authenticator
        Authenticator auth = new Authenticator() {
            @Override
            protected PasswordAuthentication getPasswordAuthentication() {
                System.out.println("EmailUtil: Đang xác thực với email: " + FROM_EMAIL);
                return new PasswordAuthentication(FROM_EMAIL, FROM_PASSWORD);
            }
        };

        // 3. Tạo Session
        Session session = Session.getInstance(props, auth);

        try {
            System.out.println("EmailUtil: Bắt đầu gửi email đến " + toEmail);
            System.out.println("EmailUtil: Từ " + FROM_EMAIL);
            
            // 4. Tạo MimeMessage
            MimeMessage msg = new MimeMessage(session);
            
            // 5. Đặt thông tin (From, To, Subject, Content)
            msg.setFrom(new InternetAddress(FROM_EMAIL));
            msg.setRecipients(Message.RecipientType.TO, InternetAddress.parse(toEmail, false));
            
            msg.setSubject(subject, "UTF-8");
            
            // Nội dung (quan trọng: set UTF-8)
            msg.setContent(htmlContent, "text/html; charset=UTF-8");

            // 6. Gửi Email
            System.out.println("EmailUtil: Đang kết nối và gửi email...");
            Transport.send(msg);
            
            System.out.println("EmailUtil: ✅ Đã gửi email thành công đến " + toEmail);
            return true;

        } catch (MessagingException e) {
            System.err.println("!!! EmailUtil LỖI: Không thể gửi email đến " + toEmail);
            System.err.println("!!! Loại lỗi: " + e.getClass().getName());
            System.err.println("!!! Thông báo lỗi: " + e.getMessage());
            
            // In chi tiết lỗi
            if (e.getCause() != null) {
                System.err.println("!!! Nguyên nhân: " + e.getCause().getMessage());
            }
            
            e.printStackTrace();
            return false;
        } catch (Exception e) {
            System.err.println("!!! EmailUtil LỖI KHÔNG XÁC ĐỊNH: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }

    /**
     * [MỚI] Tạo nội dung email chào mừng
     * (Dùng cho DangKyServlet)
     */
    public static String createWelcomeEmailContent(String tenNguoiNhan) {
         return "<!DOCTYPE html>"
             + "<html>"
             + "<head><meta charset='UTF-8'></head>"
             + "<body style='font-family: Arial, sans-serif; line-height: 1.6;'>"
             + "<h2>Chào mừng bạn đến với BookStore, " + tenNguoiNhan + "!</h2>"
             + "<p>Tài khoản của bạn đã được tạo thành công.</p>"
             + "<p>Hãy bắt đầu khám phá hàng ngàn đầu sách hấp dẫn ngay hôm nay.</p>"
             + "<a href='http://localhost:8080/TenProjectCuaBan/dang-nhap' " // Nhớ thay đổi URL
             + "   style='background-color: #00466a; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>"
             + "Đăng nhập ngay</a>"
             + "<p>Trân trọng,<br>Đội ngũ BookStore</p>"
             + "</body></html>";
    }

    /**
     * Tạo nội dung email xác thực
     * (Dùng cho QuenMatKhauServlet)
     * @param tenNguoiNhan Tên của người nhận
     * @param maXacThuc Mã xác thực
     * @return Chuỗi HTML nội dung email
     */
    public static String createVerificationEmailContent(String tenNguoiNhan, String maXacThuc) {
        return "<!DOCTYPE html>"
             + "<html>"
             + "<head><meta charset='UTF-8'></head>"
             + "<body style='font-family: Arial, sans-serif; line-height: 1.6;'>"
             + "<h2>Xin chào " + tenNguoiNhan + ",</h2>"
             + "<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại BookStore.</p>"
             + "<p>Vui lòng sử dụng mã xác thực dưới đây để hoàn tất quá trình. Mã này có hiệu lực trong 10 phút.</p>"
             + "<h3 style='color: #00466a; background: #f0f0f0; padding: 10px 15px; display: inline-block; border-radius: 4px;'>"
             + maXacThuc
             + "</h3>"
             + "<p>Nếu bạn không yêu cầu thao tác này, vui lòng bỏ qua email.</p>"
             + "<p>Trân trọng,<br>Đội ngũ BookStore</p>"
             + "</body>"
             + "</html>";
    }

    /**
     * Tạo nội dung email gửi mật khẩu mới
     * (Dùng cho QuenMatKhauGuiEmailServlet - gửi mật khẩu trực tiếp)
     * @param tenNguoiNhan Tên của người nhận
     * @param matKhauMoi Mật khẩu mới
     * @return Chuỗi HTML nội dung email
     */
    public static String createNewPasswordEmailContent(String tenNguoiNhan, String matKhauMoi) {
        return "<!DOCTYPE html>"
             + "<html>"
             + "<head><meta charset='UTF-8'></head>"
             + "<body style='font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4; padding: 20px;'>"
             + "<div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>"
             + "<h2 style='color: #00466a;'>Xin chào " + tenNguoiNhan + ",</h2>"
             + "<p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại BookStore.</p>"
             + "<p>Mật khẩu mới của bạn là:</p>"
             + "<div style='background-color: #f0f0f0; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;'>"
             + "<h3 style='color: #00466a; margin: 0; font-size: 24px; letter-spacing: 2px; font-family: monospace;'>"
             + matKhauMoi
             + "</h3>"
             + "</div>"
             + "<p style='color: #ff0000; font-weight: bold;'>⚠️ Vui lòng đăng nhập và đổi mật khẩu ngay sau khi nhận được email này để đảm bảo an toàn.</p>"
             + "<p>Nếu bạn không yêu cầu thao tác này, vui lòng liên hệ với chúng tôi ngay lập tức.</p>"
             + "<p style='margin-top: 30px;'>Trân trọng,<br><strong>Đội ngũ BookStore</strong></p>"
             + "</div>"
             + "</body>"
             + "</html>";
    }

    /*
     * Dùng để test
     */
    public static void main(String[] args) {
        System.out.println("=== TEST EMAIL UTIL ===");
        System.out.println("FROM_EMAIL: " + FROM_EMAIL);
        System.out.println("FROM_PASSWORD: " + (FROM_PASSWORD != null ? "***" : "NULL"));
        System.out.println("========================\n");
        
        String testEmail = "daiducka123@gmail.com"; // Email để test
        String noiDung = createNewPasswordEmailContent("Test User", "Test123!");
        
        System.out.println("Đang gửi email test đến: " + testEmail);
        boolean success = sendEmail(testEmail, "Test Email - BookStore", noiDung);
        System.out.println("\n=== KẾT QUẢ ===");
        System.out.println("Gửi email: " + (success ? "✅ THÀNH CÔNG" : "❌ THẤT BẠI"));
    }
}