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

/**
 * Phiên bản EmailUtil sử dụng SSL (Port 465) thay vì TLS (Port 587)
 * Dùng khi TLS không hoạt động
 */
public class EmailUtilSSL {

    private static final String FROM_EMAIL = "daiducka123@gmail.com"; 
    private static final String FROM_PASSWORD = "pzjmoglukxrljil"; 

    /**
     * Gửi email sử dụng SSL (Port 465)
     * Dùng khi TLS không hoạt động
     */
    public static boolean sendEmail(String toEmail, String subject, String htmlContent) {
        
        // 1. Cấu hình thuộc tính cho Session - DÙNG SSL
        Properties props = new Properties();
        
        // Cấu hình SMTP với SSL
        props.put("mail.smtp.host", "smtp.gmail.com");
        props.put("mail.smtp.port", "465"); // SSL Port
        props.put("mail.smtp.auth", "true");
        
        // Cấu hình SSL
        props.put("mail.smtp.socketFactory.port", "465");
        props.put("mail.smtp.socketFactory.class", "javax.net.ssl.SSLSocketFactory");
        props.put("mail.smtp.socketFactory.fallback", "false");
        
        // Timeout
        props.put("mail.smtp.connectiontimeout", "5000");
        props.put("mail.smtp.timeout", "5000");
        
        // Debug
        props.put("mail.debug", "false");

        // 2. Tạo Authenticator
        Authenticator auth = new Authenticator() {
            @Override
            protected PasswordAuthentication getPasswordAuthentication() {
                return new PasswordAuthentication(FROM_EMAIL, FROM_PASSWORD);
            }
        };

        // 3. Tạo Session
        Session session = Session.getInstance(props, auth);

        try {
            MimeMessage msg = new MimeMessage(session);
            msg.setFrom(new InternetAddress(FROM_EMAIL));
            msg.setRecipients(Message.RecipientType.TO, InternetAddress.parse(toEmail, false));
            msg.setSubject(subject, "UTF-8");
            msg.setContent(htmlContent, "text/html; charset=UTF-8");

            Transport.send(msg);
            System.out.println("EmailUtilSSL: ✅ Đã gửi email thành công đến " + toEmail);
            return true;

        } catch (MessagingException e) {
            System.err.println("!!! EmailUtilSSL LỖI: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }
}




