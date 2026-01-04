package controller.KhachHang;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;

@WebServlet(name = "GeminiChatServlet", urlPatterns = {"/api/gemini-chat"})
public class GeminiChatServlet extends HttpServlet {

    private static final String GEMINI_API_KEY = "AIzaSyCU6ZdNzyMFh50L8Sg2PrWIdTiJhoOuNvI";
    // Thử các model khác nhau, ưu tiên gemini-2.5-flash (model mới nhất)
    private static final String[] GEMINI_MODELS = {
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent",
        "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent",
        "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-latest:generateContent",
        "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro-latest:generateContent",
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent",
        "https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent"
    };

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("application/json; charset=UTF-8");
        
        // Đọc message từ request body (JSON)
        StringBuilder requestBody = new StringBuilder();
        try (BufferedReader reader = request.getReader()) {
            String line;
            while ((line = reader.readLine()) != null) {
                requestBody.append(line);
            }
        }
        
        String bodyStr = requestBody.toString().trim();
        String message = "";
        
        // Parse JSON đơn giản để lấy message
        try {
            // Tìm trường "message" trong JSON
            int messageStart = bodyStr.indexOf("\"message\"");
            if (messageStart != -1) {
                int colonIndex = bodyStr.indexOf(":", messageStart);
                int valueStart = bodyStr.indexOf("\"", colonIndex) + 1;
                int valueEnd = bodyStr.indexOf("\"", valueStart);
                if (valueEnd == -1) {
                    // Có thể là string dài, tìm đến dấu ngoặc kép cuối cùng
                    valueEnd = bodyStr.lastIndexOf("\"");
                }
                if (valueStart > 0 && valueEnd > valueStart) {
                    message = bodyStr.substring(valueStart, valueEnd);
                    // Unescape JSON
                    message = message.replace("\\\"", "\"").replace("\\n", "\n").replace("\\r", "\r").replace("\\t", "\t").replace("\\\\", "\\");
                }
            } else {
                // Nếu không có trường message, thử parse như string đơn giản
                if (bodyStr.startsWith("\"") && bodyStr.endsWith("\"")) {
                    message = bodyStr.substring(1, bodyStr.length() - 1);
                    message = message.replace("\\\"", "\"").replace("\\n", "\n").replace("\\r", "\r").replace("\\t", "\t").replace("\\\\", "\\");
                } else {
                    message = bodyStr;
                }
            }
        } catch (Exception e) {
            // Nếu parse lỗi, dùng toàn bộ body làm message
            message = bodyStr;
        }
        
        if (message == null || message.trim().isEmpty()) {
            response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            response.getWriter().write("{\"error\": \"Message không được để trống\"}");
            return;
        }
        
        // Thử các model khác nhau cho đến khi thành công
        Exception lastException = null;
        String lastError = null;
        String lastModel = null;
        
        for (String modelUrl : GEMINI_MODELS) {
            lastModel = modelUrl;
            try {
                // Gọi API Gemini
                URL url = new URL(modelUrl + "?key=" + GEMINI_API_KEY);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("POST");
                conn.setRequestProperty("Content-Type", "application/json");
                conn.setDoOutput(true);
                conn.setConnectTimeout(10000); // 10 seconds
                conn.setReadTimeout(30000); // 30 seconds
                
                // Tạo request body cho Gemini với system instruction để trả lời dễ thương, ngắn gọn
                String systemInstruction = "Bạn là trợ lý AI dễ thương và thân thiện của một thư viện sách trực tuyến. " +
                    "Hãy trả lời ngắn gọn (tối đa 2-3 câu), dễ thương, thân thiện và có thể dùng emoji phù hợp. " +
                    "Khi người dùng muốn mua sách, hãy trả lời ngắn gọn và thân thiện. " +
                    "Nếu người dùng đề cập tên sách cụ thể, hãy nhắc lại tên sách đó trong câu trả lời để hệ thống có thể tự động tìm kiếm. " +
                    "Tập trung vào việc giúp người dùng tìm sách, đọc sách và trả lời câu hỏi về sách.";
                String geminiRequestBody = "{\"systemInstruction\":{\"parts\":[{\"text\":\"" + 
                    escapeJson(systemInstruction) + "\"}]},\"contents\":[{\"parts\":[{\"text\":\"" + 
                    escapeJson(message) + "\"}]}]}";
                
                // Gửi request
                try (OutputStreamWriter writer = new OutputStreamWriter(conn.getOutputStream(), StandardCharsets.UTF_8)) {
                    writer.write(geminiRequestBody);
                    writer.flush();
                }
                
                // Đọc response
                int responseCode = conn.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    try (BufferedReader reader = new BufferedReader(
                            new InputStreamReader(conn.getInputStream(), StandardCharsets.UTF_8))) {
                        StringBuilder responseBody = new StringBuilder();
                        String line;
                        while ((line = reader.readLine()) != null) {
                            responseBody.append(line);
                        }
                        response.getWriter().write(responseBody.toString());
                        return; // Thành công, thoát khỏi method
                    }
                } else {
                    // Đọc error response để log
                    try (BufferedReader reader = new BufferedReader(
                            new InputStreamReader(conn.getErrorStream(), StandardCharsets.UTF_8))) {
                        StringBuilder errorBody = new StringBuilder();
                        String line;
                        while ((line = reader.readLine()) != null) {
                            errorBody.append(line);
                        }
                        lastError = errorBody.toString();
                    }
                    // Tiếp tục thử model tiếp theo
                    continue;
                }
            } catch (Exception e) {
                lastException = e;
                // Tiếp tục thử model tiếp theo
                continue;
            }
        }
        
        // Nếu tất cả các model đều thất bại, thử list models để debug
        try {
            // Thử gọi API list models để xem model nào có sẵn
            URL listUrl = new URL("https://generativelanguage.googleapis.com/v1/models?key=" + GEMINI_API_KEY);
            HttpURLConnection listConn = (HttpURLConnection) listUrl.openConnection();
            listConn.setRequestMethod("GET");
            listConn.setConnectTimeout(5000);
            listConn.setReadTimeout(10000);
            
            int listCode = listConn.getResponseCode();
            if (listCode == HttpURLConnection.HTTP_OK) {
                try (BufferedReader reader = new BufferedReader(
                        new InputStreamReader(listConn.getInputStream(), StandardCharsets.UTF_8))) {
                    StringBuilder listResponse = new StringBuilder();
                    String line;
                    while ((line = reader.readLine()) != null) {
                        listResponse.append(line);
                    }
                    // Log available models (có thể dùng để debug)
                    System.out.println("Available models: " + listResponse.toString());
                }
            }
        } catch (Exception e) {
            // Ignore list models error
        }
        
        // Trả về lỗi chi tiết
        response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        String errorMsg = "Không thể kết nối với bất kỳ model Gemini nào. ";
        if (lastModel != null) {
            errorMsg += "Model cuối thử: " + lastModel + ". ";
        }
        if (lastError != null && !lastError.isEmpty()) {
            errorMsg += "Lỗi cuối: " + lastError;
        } else if (lastException != null) {
            errorMsg += "Exception: " + lastException.getMessage();
        }
        response.getWriter().write("{\"error\": \"" + escapeJson(errorMsg) + "\"}");
    }
    
    private String escapeJson(String str) {
        if (str == null) return "";
        return str.replace("\\", "\\\\")
                  .replace("\"", "\\\"")
                  .replace("\n", "\\n")
                  .replace("\r", "\\r")
                  .replace("\t", "\\t");
    }
}

