package util;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.TimeZone;
import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

/**
 * VNPay Payment Gateway Utility
 * Dựa trên code mẫu vnpay_jsp
 * Tích hợp với VNPay sandbox environment
 */
public class VNPayUtil {
    
    // VNPay Sandbox Configuration
    public static final String VNPAY_URL = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    public static final String VNPAY_TMN_CODE = "WPQLJKWB"; // Cần cấu hình từ VNPay sandbox
    public static final String VNPAY_HASH_SECRET = "U2SCH4G883S3UUDTUB626CU5EARUTLOL"; // Cần cấu hình từ VNPay sandbox
    public static final String VNPAY_RETURN_URL = "http://localhost:8081/Bookstore/vnpay-callback";
    
    /**
     * Tạo URL thanh toán VNPay
     * Dựa trên ajaxServlet.java từ vnpay_jsp
     * 
     * @param orderId Mã đơn hàng
     * @param amount Số tiền (VND) - sẽ được nhân 100
     * @param orderInfo Thông tin đơn hàng
     * @param returnUrl URL callback sau khi thanh toán
     * @param ipAddr Địa chỉ IP của khách hàng
     * @return URL thanh toán VNPay
     */
    public static String createPaymentUrl(String orderId, long amount, String orderInfo, String returnUrl, String ipAddr) {
        String vnp_Version = "2.1.0";
        String vnp_Command = "pay";
        String orderType = "other";
        long vnp_Amount = amount * 100; // VNPay yêu cầu số tiền nhân 100
        
        String vnp_TxnRef = orderId;
        String vnp_IpAddr = (ipAddr != null && !ipAddr.isEmpty()) ? ipAddr : "127.0.0.1";
        String vnp_TmnCode = VNPAY_TMN_CODE;
        
        Map<String, String> vnp_Params = new HashMap<>();
        vnp_Params.put("vnp_Version", vnp_Version);
        vnp_Params.put("vnp_Command", vnp_Command);
        vnp_Params.put("vnp_TmnCode", vnp_TmnCode);
        vnp_Params.put("vnp_Amount", String.valueOf(vnp_Amount));
        vnp_Params.put("vnp_CurrCode", "VND");
        vnp_Params.put("vnp_TxnRef", vnp_TxnRef);
        vnp_Params.put("vnp_OrderInfo", orderInfo);
        vnp_Params.put("vnp_OrderType", orderType);
        vnp_Params.put("vnp_Locale", "vn");
        vnp_Params.put("vnp_ReturnUrl", returnUrl != null ? returnUrl : VNPAY_RETURN_URL);
        vnp_Params.put("vnp_IpAddr", vnp_IpAddr);
        
        Calendar cld = Calendar.getInstance(TimeZone.getTimeZone("Etc/GMT+7"));
        SimpleDateFormat formatter = new SimpleDateFormat("yyyyMMddHHmmss");
        String vnp_CreateDate = formatter.format(cld.getTime());
        vnp_Params.put("vnp_CreateDate", vnp_CreateDate);
        
        cld.add(Calendar.MINUTE, 15);
        String vnp_ExpireDate = formatter.format(cld.getTime());
        vnp_Params.put("vnp_ExpireDate", vnp_ExpireDate);
        
        // Sắp xếp params theo key
        List<String> fieldNames = new ArrayList<>(vnp_Params.keySet());
        Collections.sort(fieldNames);
        
        StringBuilder hashData = new StringBuilder();
        StringBuilder query = new StringBuilder();
        Iterator<String> itr = fieldNames.iterator();
        
        while (itr.hasNext()) {
            String fieldName = itr.next();
            String fieldValue = vnp_Params.get(fieldName);
            
            if ((fieldValue != null) && (fieldValue.length() > 0)) {
                // Build hash data
                hashData.append(fieldName);
                hashData.append('=');
                hashData.append(URLEncoder.encode(fieldValue, StandardCharsets.US_ASCII));
                
                // Build query
                query.append(URLEncoder.encode(fieldName, StandardCharsets.US_ASCII));
                query.append('=');
                query.append(URLEncoder.encode(fieldValue, StandardCharsets.US_ASCII));
                
                if (itr.hasNext()) {
                    query.append('&');
                    hashData.append('&');
                }
            }
        }
        
        String queryUrl = query.toString();
        String vnp_SecureHash = hmacSHA512(VNPAY_HASH_SECRET, hashData.toString());
        queryUrl += "&vnp_SecureHash=" + vnp_SecureHash;
        String paymentUrl = VNPAY_URL + "?" + queryUrl;
        
        return paymentUrl;
    }
    
    /**
     * Xác thực chữ ký từ VNPay callback
     * Dựa trên createPaymentUrl - phải encode fieldValue như khi tạo hash ban đầu
     */
    public static boolean verifySignature(Map<String, String> params, String vnp_SecureHash) {
        // Loại bỏ vnp_SecureHash và vnp_SecureHashType
        Map<String, String> fields = new HashMap<>(params);
        if (fields.containsKey("vnp_SecureHashType")) {
            fields.remove("vnp_SecureHashType");
        }
        if (fields.containsKey("vnp_SecureHash")) {
            fields.remove("vnp_SecureHash");
        }
        
        // Sắp xếp params
        List<String> fieldNames = new ArrayList<>(fields.keySet());
        Collections.sort(fieldNames);
        
        StringBuilder hashData = new StringBuilder();
        Iterator<String> itr = fieldNames.iterator();
        
        while (itr.hasNext()) {
            String fieldName = itr.next();
            String fieldValue = fields.get(fieldName);
            
            if ((fieldValue != null) && (fieldValue.length() > 0)) {
                // Encode fieldValue như trong createPaymentUrl để khớp với hash ban đầu
                hashData.append(fieldName);
                hashData.append('=');
                hashData.append(URLEncoder.encode(fieldValue, StandardCharsets.US_ASCII));
                if (itr.hasNext()) {
                    hashData.append('&');
                }
            }
        }
        
        String calculatedHash = hmacSHA512(VNPAY_HASH_SECRET, hashData.toString());
        
        // Debug logging (có thể xóa sau khi test xong)
        if (!calculatedHash.equals(vnp_SecureHash)) {
            System.out.println("=== VNPay Signature Verification Failed ===");
            System.out.println("Hash Data: " + hashData.toString());
            System.out.println("Calculated Hash: " + calculatedHash);
            System.out.println("Received Hash: " + vnp_SecureHash);
            System.out.println("Hash Secret configured: " + (VNPAY_HASH_SECRET != null && !VNPAY_HASH_SECRET.isEmpty()));
        }
        
        return calculatedHash.equals(vnp_SecureHash);
    }
    
    /**
     * HMAC SHA512 hash
     * Dựa trên Config.hmacSHA512() từ vnpay_jsp
     */
    public static String hmacSHA512(String key, String data) {
        try {
            if (key == null || data == null) {
                throw new NullPointerException();
            }
            final Mac hmac512 = Mac.getInstance("HmacSHA512");
            byte[] hmacKeyBytes = key.getBytes();
            final SecretKeySpec secretKey = new SecretKeySpec(hmacKeyBytes, "HmacSHA512");
            hmac512.init(secretKey);
            byte[] dataBytes = data.getBytes(StandardCharsets.UTF_8);
            byte[] result = hmac512.doFinal(dataBytes);
            StringBuilder sb = new StringBuilder(2 * result.length);
            for (byte b : result) {
                sb.append(String.format("%02x", b & 0xff));
            }
            return sb.toString();
        } catch (Exception ex) {
            ex.printStackTrace();
            return "";
        }
    }
    
    /**
     * Lấy IP address từ request
     * Dựa trên Config.getIpAddress() từ vnpay_jsp
     */
    public static String getIpAddress(jakarta.servlet.http.HttpServletRequest request) {
        String ipAddress;
        try {
            ipAddress = request.getHeader("X-FORWARDED-FOR");
            if (ipAddress == null) {
                ipAddress = request.getRemoteAddr();
            }
        } catch (Exception e) {
            ipAddress = "127.0.0.1";
        }
        return ipAddress;
    }
}

