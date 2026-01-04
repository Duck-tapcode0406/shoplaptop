<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>

<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <%-- Font chữ & Icon --%>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <%-- CSS chính --%>
    <link rel="stylesheet" href="${baseURL}/css/khachhang/main.css">

    ${pageStyles}
    <title>BookStore - Đọc Sách Trực Tuyến</title>
    <style>
        /* Badge "Đăng ký gói cước" với hiệu ứng ánh sao */
        .subscribe-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.55rem;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 50%, #ff6b35 100%);
            background-size: 200% 100%;
            color: white;
            font-weight: 700;
            font-size: 0.75rem;
            border-radius: 18px;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(255, 107, 53, 0.4);
            animation: shimmer 2s infinite, pulse 2s infinite;
            overflow: hidden;
            white-space: nowrap;
            z-index: 1;
            pointer-events: auto;
        }
        
        .subscribe-badge::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.3) 50%,
                transparent 70%
            );
            animation: shine 3s infinite;
        }
        
        .subscribe-text {
            position: relative;
            z-index: 1;
            white-space: nowrap;
            font-size: 0.85rem;
            line-height: 1.2;
        }
        
        .sparkle {
            position: relative;
            z-index: 1;
            font-size: 0.75rem;
            animation: sparkle 1.5s infinite;
            display: inline-block;
            line-height: 1;
        }
        
        @keyframes shimmer {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 3px 10px rgba(255, 107, 53, 0.4);
            }
            50% {
                transform: scale(1.03);
                box-shadow: 0 4px 12px rgba(255, 107, 53, 0.6);
            }
        }
        
        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }
        
        @keyframes sparkle {
            0%, 100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
            25% {
                opacity: 0.7;
                transform: scale(1.2) rotate(90deg);
            }
            50% {
                opacity: 1;
                transform: scale(1) rotate(180deg);
            }
            75% {
                opacity: 0.7;
                transform: scale(1.2) rotate(270deg);
            }
        }
        
        .subscribe-badge:hover {
            animation: shimmer 1s infinite, pulse 1s infinite;
            transform: translateY(-1px) scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.6);
            text-decoration: none;
            color: white !important;
        }
        
        /* Đảm bảo badge không bị ảnh hưởng bởi nav-icons a hover */
        .nav-icons .subscribe-badge,
        .nav-icons .pro-badge {
            pointer-events: auto;
        }
        
        /* Badge "PRO" khi đã đăng ký */
        .pro-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.7rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 900;
            font-size: 0.75rem;
            border-radius: 15px;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            overflow: hidden;
            z-index: 1;
            pointer-events: auto;
        }
        
        .pro-badge::before {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            background: linear-gradient(45deg, #ffd700, #ffed4e, #ffd700);
            border-radius: 15px;
            z-index: -1;
            animation: borderGlow 2s linear infinite;
        }
        
        .pro-text {
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }
        
        @keyframes borderGlow {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 1;
            }
        }
        
        .pro-badge:hover {
            transform: translateY(-1px) scale(1.05);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.7);
            text-decoration: none;
            color: white !important;
        }
        
        /* Sửa lỗi hover và layout cho nav-icons */
        .nav-icons-list {
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            flex-shrink: 0;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .nav-icons {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        /* Đảm bảo container header không bị lệch */
        .main-header .container {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 1rem;
            flex-wrap: nowrap;
            width: 100%;
            box-sizing: border-box;
        }
        
        /* Đảm bảo badge không bị ảnh hưởng bởi nav-icons a hover */
        .nav-icons a.subscribe-badge,
        .nav-icons a.pro-badge {
            pointer-events: auto !important;
            display: inline-flex !important;
            margin: 0;
        }
        
        /* Hover chỉ áp dụng cho icon thông thường, không phải badge */
        .nav-icons > a:not(.subscribe-badge):not(.pro-badge) {
            width: auto;
            height: auto;
        }
        
        .nav-icons > a:not(.subscribe-badge):not(.pro-badge):hover {
            color: var(--primary-color) !important;
            transform: scale(1.1) !important;
        }
        
        @media (max-width: 768px) {
            .nav-icons-list {
                gap: 0.5rem !important;
            }
            .subscribe-badge {
                font-size: 0.7rem;
                padding: 0.2rem 0.5rem;
            }
            .subscribe-text {
                font-size: 0.75rem;
            }
            .pro-badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.6rem;
            }
        }
    </style>
</head>
<body>
    <%-- HEADER --%>
    <header class="main-header">
        <div class="container">
            <a href="${baseURL}/trang-chu" class="logo">BookStore</a>

            <nav class="main-nav">
                <ul>
                    <li><a href="${baseURL}/trang-chu">Trang Chủ</a></li>
                    <li><a href="${baseURL}/danh-sach-san-pham">Sản Phẩm</a></li>
                    <li><a href="${baseURL}/khuyen-mai">Khuyến Mãi</a></li>
                    <li><a href="${baseURL}/tin-tuc">Tin Tức</a></li>
                </ul>
            </nav>

            <%-- Thanh tìm kiếm --%>
            <form action="${baseURL}/tim-kiem" method="GET" class="search-bar" role="search">
                <input type="text" name="query" placeholder="Tìm kiếm sách..." aria-label="Tìm kiếm sách">
                <button type="submit" aria-label="Tìm kiếm"><i class="fa-solid fa-search"></i></button>
            </form>

            <%-- Biểu tượng tài khoản và giỏ hàng --%>
            <ul class="nav-icons-list">
                <%-- Icon Gemini AI ở góc phải --%>
                <li class="nav-icons">
                    <a href="javascript:void(0)" id="geminiChatIcon" aria-label="Đại Đức AI" style="position: relative; cursor: pointer;">
                        <i class="fa-solid fa-robot" style="font-size: 1.2rem; color: #00467f;"></i>
                    </a>
                </li>
                <%-- Hiển thị gói cước nếu không phải admin --%>
                <%-- TẠM THỜI TẮT ĐỂ TRÁNH VÒNG LẶP --%>
                <%--
                <c:if test="${empty sessionScope.user or sessionScope.user.role != 1}">
                    <li class="nav-icons">
                        <c:choose>
                            <c:when test="${not empty sessionScope.user}">
                                <c:set var="userObj" value="${sessionScope.user}" />
                                <c:if test="${not empty userObj}">
                                    <c:set var="hasValidPackage" value="false" />
                                    <c:catch var="packageError">
                                        <c:set var="hasValidPackage" value="${userObj.goiCuocConHan}" />
                                    </c:catch>
                                    <c:if test="${hasValidPackage == true}">
                                        <a href="${baseURL}/thong-tin-goi-cuoc" class="pro-badge" aria-label="Gói cước PRO" title="Xem thông tin gói cước của bạn">
                                            <span class="pro-text">PRO</span>
                                        </a>
                                    </c:if>
                                    <c:if test="${hasValidPackage != true}">
                                        <a href="${baseURL}/goi-cuoc" class="subscribe-badge" aria-label="Đăng ký gói cước">
                                            <span class="subscribe-text">Đăng ký gói cước</span>
                                            <span class="sparkle">✨</span>
                                        </a>
                                    </c:if>
                                </c:if>
                            </c:when>
                            <c:otherwise>
                                <a href="${baseURL}/goi-cuoc" class="subscribe-badge" aria-label="Đăng ký gói cước">
                                    <span class="subscribe-text">Đăng ký gói cước</span>
                                    <span class="sparkle">✨</span>
                                </a>
                            </c:otherwise>
                        </c:choose>
                    </li>
                </c:if>
                --%>
                <li class="nav-icons">
                    <c:choose>
                        <c:when test="${not empty sessionScope.user}">
                            <c:set var="currentUser" value="${sessionScope.user}" />
                            <c:choose>
                                <c:when test="${currentUser.role == 1}">
                                    <%-- Admin: Link đến admin dashboard --%>
                                    <a href="${baseURL}/admin/dashboard" aria-label="Admin Panel">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </a>
                                </c:when>
                                <c:otherwise>
                                    <%-- Khách hàng: Link đến hồ sơ --%>
                                    <a href="${baseURL}/tai-khoan/ho-so" aria-label="Hồ sơ cá nhân">
                                        <i class="fa-solid fa-user-circle"></i>
                                    </a>
                                </c:otherwise>
                            </c:choose>
                        </c:when>
                        <c:otherwise>
                            <a href="${baseURL}/dang-nhap" aria-label="Đăng nhập">
                                <i class="fa-solid fa-user"></i>
                            </a>
                        </c:otherwise>
                    </c:choose>
                </li>
            </ul>
        </div>
    </header>

    <%-- THÔNG BÁO --%>
    <c:if test="${not empty sessionScope.successMessage}">
        <div class="alert alert-success"><c:out value="${sessionScope.successMessage}" /></div>
        <c:remove var="successMessage" scope="session" />
    </c:if>
    <c:if test="${not empty sessionScope.errorMessage}">
        <div class="alert alert-danger"><c:out value="${sessionScope.errorMessage}" /></div>
        <c:remove var="errorMessage" scope="session" />
    </c:if>
    
    <%-- Gemini AI Chat Widget --%>
    <style>
        #geminiChatWidget {
            display: none !important;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 420px;
            max-width: 90vw;
            height: 600px;
            max-height: 80vh;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            z-index: 99999 !important;
            flex-direction: column;
            font-family: 'Roboto', sans-serif;
            overflow: hidden;
        }
        #geminiChatWidget.show {
            display: flex !important;
            animation: slideUp 0.3s ease-out;
        }
        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .chat-header {
            background: linear-gradient(135deg, #00467f 0%, #0066cc 100%);
            color: white;
            padding: 1.2rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chat-header h3 {
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .chat-header button {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        .chat-header button:hover {
            background: rgba(255,255,255,0.3);
        }
        .chat-messages {
            flex: 1;
            padding: 1.2rem;
            overflow-y: auto;
            background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
            scroll-behavior: smooth;
        }
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .chat-messages::-webkit-scrollbar-thumb {
            background: #00467f;
            border-radius: 10px;
        }
        .message {
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message-user {
            display: flex;
            justify-content: flex-end;
            margin-left: 20%;
        }
        .message-user .message-content {
            background: linear-gradient(135deg, #00467f 0%, #0066cc 100%);
            color: white;
            padding: 0.9rem 1.2rem;
            border-radius: 18px 18px 4px 18px;
            box-shadow: 0 2px 8px rgba(0,70,127,0.2);
            max-width: 100%;
            word-wrap: break-word;
        }
        .message-ai {
            display: flex;
            justify-content: flex-start;
            margin-right: 20%;
        }
        .message-ai .message-content {
            background: white;
            color: #333;
            padding: 0.9rem 1.2rem;
            border-radius: 18px 18px 18px 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #00467f;
            max-width: 100%;
            word-wrap: break-word;
        }
        .message-ai .message-header {
            color: #00467f;
            font-weight: 600;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .chat-input-container {
            padding: 1rem;
            border-top: 1px solid #e0e0e0;
            background: white;
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        .chat-input {
            flex: 1;
            padding: 0.85rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.3s;
        }
        .chat-input:focus {
            border-color: #00467f;
        }
        .chat-send-btn {
            padding: 0.85rem 1.5rem;
            background: linear-gradient(135deg, #00467f 0%, #0066cc 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(0,70,127,0.3);
        }
        .chat-send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,70,127,0.4);
        }
        .chat-send-btn:active {
            transform: translateY(0);
        }
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 0.5rem 0;
        }
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00467f;
            animation: typing 1.4s infinite;
        }
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.7;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
    </style>
    <div id="geminiChatWidget">
        <div class="chat-header">
            <h3><i class="fa-solid fa-robot"></i> Đại Đức AI</h3>
            <button onclick="closeGeminiChat()" aria-label="Đóng">&times;</button>
        </div>
        <div id="geminiChatMessages" class="chat-messages">
            <div class="message message-ai">
                <div class="message-content">
                    <div class="message-header">
                        <i class="fa-solid fa-robot"></i> Đại Đức AI
                    </div>
                    <span>Xin chào! 👋 Tôi là Đại Đức AI dễ thương của bạn! Tôi có thể giúp bạn tìm sách, trả lời câu hỏi về sách, hoặc hỗ trợ bạn đọc sách. Bạn cần giúp gì nhỉ? 😊</span>
                </div>
            </div>
        </div>
        <div class="chat-input-container">
            <input type="text" id="geminiChatInput" class="chat-input" placeholder="Nhập câu hỏi..." onkeypress="if(event.key === 'Enter') sendGeminiMessage()">
            <button onclick="sendGeminiMessage()" class="chat-send-btn" aria-label="Gửi">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>
    
    <script>
    // Đảm bảo hàm được định nghĩa TRƯỚC khi được gọi
    (function() {
        'use strict';
        
        // Định nghĩa hàm ngay lập tức
        window.openGeminiChat = function() {
            try {
                const widget = document.getElementById('geminiChatWidget');
                if (widget) {
                    widget.classList.add('show');
                    widget.style.display = 'flex';
                    widget.style.zIndex = '99999';
                    // Khôi phục chat history khi mở
                    if (typeof loadChatHistory === 'function') {
                        loadChatHistory();
                    }
                    console.log('Chat widget đã mở thành công');
                } else {
                    console.error('Không tìm thấy geminiChatWidget element');
                    alert('Không thể mở chat. Vui lòng tải lại trang.');
                }
            } catch (e) {
                console.error('Lỗi khi mở chat:', e);
                alert('Lỗi khi mở chat: ' + e.message);
            }
            return false;
        };
        
        window.closeGeminiChat = function() {
            try {
                const widget = document.getElementById('geminiChatWidget');
                if (widget) {
                    widget.classList.remove('show');
                    widget.style.display = 'none';
                }
            } catch (e) {
                console.error('Lỗi khi đóng chat:', e);
            }
            return false;
        };
        
        console.log('Hàm openGeminiChat và closeGeminiChat đã được định nghĩa');
    })();
    
    const GEMINI_API_URL = '${baseURL}/api/gemini-chat';
    
    function sendGeminiMessage() {
        const input = document.getElementById('geminiChatInput');
        const message = input.value.trim();
        if (!message) return;
        
        const messagesDiv = document.getElementById('geminiChatMessages');
        
        // Hiển thị tin nhắn người dùng với style đẹp
        const userMsg = document.createElement('div');
        userMsg.className = 'message message-user';
        userMsg.innerHTML = '<div class="message-content"><strong>Bạn:</strong> ' + escapeHtml(message) + '</div>';
        messagesDiv.appendChild(userMsg);
        
        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        // Lưu tin nhắn người dùng vào history
        saveChatHistory();
        
        // Hiển thị typing indicator
        const thinkingMsg = document.createElement('div');
        thinkingMsg.id = 'thinkingMsg';
        thinkingMsg.className = 'message message-ai';
        thinkingMsg.innerHTML = '<div class="message-content">' +
            '<div class="message-header"><i class="fa-solid fa-robot"></i> Đại Đức AI</div>' +
            '<div class="typing-indicator">' +
            '<div class="typing-dot"></div>' +
            '<div class="typing-dot"></div>' +
            '<div class="typing-dot"></div>' +
            '</div></div>';
        messagesDiv.appendChild(thinkingMsg);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        // Gọi API Gemini qua backend servlet (tránh CORS)
        fetch(GEMINI_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({message: message})
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error || `HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('thinkingMsg').remove();
            
            const aiMsg = document.createElement('div');
            aiMsg.className = 'message message-ai';
            
            let aiResponse = 'Xin lỗi, tôi không thể trả lời câu hỏi này. 😅';
            if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
                aiResponse = data.candidates[0].content.parts[0].text;
            } else if (data.error) {
                aiResponse = 'Lỗi: ' + data.error;
            }
            
            // Format lại text với line breaks và giữ emoji
            aiResponse = escapeHtml(aiResponse).replace(/\n/g, '<br>');
            
            // Tạo message element
            aiMsg.innerHTML = '<div class="message-content">' +
                '<div class="message-header"><i class="fa-solid fa-robot"></i> Đại Đức AI</div>' +
                '<span>' + aiResponse + '</span></div>';
            messagesDiv.appendChild(aiMsg);
            
            // Kiểm tra xem có muốn mua/tìm/xem sách không và tự động tìm kiếm
            const userMessageLower = message.toLowerCase();
            const searchKeywords = [
                'tìm', 'tìm kiếm', 'tìm sách', 'tìm cuốn', 'tìm quyển',
                'muốn tìm', 'cần tìm', 'hãy tìm', 'tìm cho tôi', 'tìm giúp',
                'mua', 'muốn mua', 'cần mua', 'hãy mua',
                'xem', 'muốn xem', 'cần xem', 'xem những', 'xem các', 'hãy xem',
                'có không', 'có sách', 'có cuốn', 'có quyển',
                'đọc sách', 'muốn đọc', 'cần đọc', 'hãy đọc',
                'tìm tác giả', 'sách của tác giả', 'tác giả'
            ];
            const wantsToSearch = searchKeywords.some(keyword => userMessageLower.includes(keyword));
            
            if (wantsToSearch) {
                // Trích xuất tên sách/tác giả - loại bỏ tất cả các từ thừa
                let searchQuery = extractSearchQuery(message);
                
                // Nếu tìm được query và đủ dài, tự động tìm kiếm
                if (searchQuery && searchQuery.length > 2) {
                    // Thêm thông báo vào AI response
                    aiResponse += '<br><br><div style="margin-top: 0.75rem; padding: 0.75rem; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #00467f;"><i class="fa-solid fa-search"></i> <strong>Đang tìm kiếm:</strong> "' + escapeHtml(searchQuery) + '"...</div>';
                    aiMsg.innerHTML = '<div class="message-content">' +
                        '<div class="message-header"><i class="fa-solid fa-robot"></i> Đại Đức AI</div>' +
                        '<span>' + aiResponse + '</span></div>';
                    
                    // Lưu chat history trước khi redirect
                    saveChatHistory();
                    
                    // Tự động redirect ngay (không delay) để tìm kiếm nhanh
                    setTimeout(() => {
                        searchBook(searchQuery);
                    }, 500);
                }
            }
            
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            
            // Lưu chat history
            saveChatHistory();
        })
        .catch(error => {
            document.getElementById('thinkingMsg').remove();
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'message message-ai';
            errorMsg.innerHTML = '<div class="message-content" style="background: #ffebee; border-left-color: #c62828;">' +
                '<div class="message-header" style="color: #c62828;"><i class="fa-solid fa-exclamation-triangle"></i> Lỗi</div>' +
                '<span style="color: #c62828;">😔 Xin lỗi, tôi không thể kết nối được. ' + 
                (error.message ? escapeHtml(error.message) : 'Vui lòng thử lại sau nhé!') + '</span></div>';
            messagesDiv.appendChild(errorMsg);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            console.error('Gemini API Error:', error);
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Hàm xác định loại tìm kiếm và trích xuất query
    function extractSearchTypeAndQuery(message) {
        if (!message || message.trim() === '') return { type: 'book', query: '' };
        
        let text = message.trim().toLowerCase();
        
        // Kiểm tra tìm kiếm theo thể loại
        const categoryPatterns = [
            /(?:tìm|tìm kiếm|tìm sách|sách|sách của)\s+(?:thể loại|thể loại là|thể loại sách|loại sách)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /(?:thể loại|thể loại là|thể loại sách|loại sách)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i
        ];
        for (let pattern of categoryPatterns) {
            const match = message.match(pattern);
            if (match && match[1]) {
                let categoryName = match[1].trim();
                categoryName = categoryName.replace(/\s+(nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi)$/i, '').trim();
                if (categoryName.length > 1) {
                    return { type: 'category', query: categoryName };
                }
            }
        }
        
        // Kiểm tra tìm kiếm theo nhà xuất bản
        const publisherPatterns = [
            /(?:tìm|tìm kiếm|tìm sách|sách|sách của)\s+(?:nhà xuất bản|nhà xuất bản là|nxb|nhà xuất bản sách)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /(?:nhà xuất bản|nhà xuất bản là|nxb|nhà xuất bản sách)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i
        ];
        for (let pattern of publisherPatterns) {
            const match = message.match(pattern);
            if (match && match[1]) {
                let publisherName = match[1].trim();
                publisherName = publisherName.replace(/\s+(nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi)$/i, '').trim();
                if (publisherName.length > 1) {
                    return { type: 'publisher', query: publisherName };
                }
            }
        }
        
        // Mặc định là tìm kiếm sách/tác giả
        return { type: 'book', query: extractSearchQuery(message) };
    }
    
    // Hàm trích xuất query tìm kiếm (tên sách hoặc tác giả) từ câu nói của người dùng
    function extractSearchQuery(message) {
        if (!message || message.trim() === '') return '';
        
        let text = message.trim();
        const originalText = text;
        
        // Xử lý đặc biệt cho "sách của tác giả X" hoặc "tác giả X" - giữ lại TOÀN BỘ tên tác giả
        const authorPatterns = [
            /(?:sách\s+)?(?:của\s+)?(?:tác giả|tác|nhà văn|tác giả là)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /(?:tìm|tìm kiếm|tìm sách|sách của)\s+(?:tác giả|tác)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /(?:tác giả|tác)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i
        ];
        for (let pattern of authorPatterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                let authorName = match[1].trim();
                // Loại bỏ các từ thừa ở cuối
                authorName = authorName.replace(/\s+(nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi)$/i, '').trim();
                if (authorName.length > 1) {
                    return authorName;
                }
            }
        }
        
        // Xử lý đặc biệt cho "sách X có không" hoặc "có sách X không" - giữ lại TOÀN BỘ tên sách
        const bookPatterns = [
            /sách\s+(.+?)\s+(?:có|có không|có không\?)/i,
            /(?:có|có sách|có cuốn|có quyển)\s+(.+?)(?:\s+(?:không|có không)|$|,|\.|!|\?)/i
        ];
        for (let pattern of bookPatterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                let bookName = match[1].trim();
                bookName = bookName.replace(/\s+(không|có không|nhé|nè|nhỉ|ok|okay|ạ|nhá)$/i, '').trim();
                if (bookName.length > 1) {
                    return bookName;
                }
            }
        }
        
        // Xử lý đặc biệt cho "tìm kiếm cho tôi sách X" - ưu tiên pattern này TRƯỚC TẤT CẢ
        // Tìm vị trí của "sách" và lấy tất cả từ sau đó
        const sachIndex = text.toLowerCase().indexOf('sách');
        if (sachIndex !== -1 && text.toLowerCase().includes('tìm') && text.toLowerCase().includes('kiếm')) {
            // Lấy phần sau "sách"
            let afterSach = text.substring(sachIndex + 4).trim(); // +4 để bỏ qua "sách"
            // Loại bỏ các từ thừa ở cuối
            afterSach = afterSach.replace(/\s+(nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi|có không|không)$/i, '').trim();
            if (afterSach.length > 1) {
                return afterSach;
            }
        }
        
        // Pattern regex backup cho "tìm kiếm cho tôi sách X"
        const findBookWithSearchPatterns = [
            /tìm\s+kiếm\s+(?:cho tôi|giúp tôi|cho|giúp)\s+sách\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /tìm\s+kiếm\s+(?:cho tôi|giúp tôi|cho|giúp)\s+(?:sách|cuốn|quyển)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /tìm\s+kiếm\s+sách\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i
        ];
        for (let pattern of findBookWithSearchPatterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                let bookName = match[1].trim();
                // Loại bỏ các từ thừa ở cuối
                bookName = bookName.replace(/\s+(sách|cuốn|quyển|nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi)$/i, '').trim();
                if (bookName.length > 1) {
                    return bookName;
                }
            }
        }
        
        // Xử lý đặc biệt cho "tìm cho tôi sách X" hoặc "tìm sách X" - giữ lại TOÀN BỘ tên sách
        const findBookPatterns = [
            /tìm\s+(?:cho tôi|giúp tôi|cho|giúp)\s+sách\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /tìm\s+(?:cho tôi|giúp tôi|cho|giúp)?\s*(?:sách|cuốn|quyển)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i,
            /tìm\s+(?:sách|cuốn|quyển)\s+(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i
        ];
        for (let pattern of findBookPatterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                let bookName = match[1].trim();
                // Loại bỏ các từ thừa ở cuối, nhưng giữ lại toàn bộ tên sách
                bookName = bookName.replace(/\s+(sách|cuốn|quyển|nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi)$/i, '').trim();
                if (bookName.length > 1) {
                    return bookName;
                }
            }
        }
        
        // Xử lý đặc biệt cho "xem những sách X" hoặc "xem các sách X" - giữ lại TOÀN BỘ tên
        const viewPatterns = [
            /(?:xem|xem những|xem các)\s+(?:sách|cuốn|quyển)?\s*(.+?)(?:\s*$|,|\.|!|\?|nhé|nè|nhỉ|ok|okay|ạ|nhá)/i
        ];
        for (let pattern of viewPatterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                let query = match[1].trim();
                query = query.replace(/\s+(sách|cuốn|quyển|nhé|nè|nhỉ|ok|okay|ạ|nhá)$/i, '').trim();
                if (query.length > 1) {
                    return query;
                }
            }
        }
        
        // Danh sách các từ/cụm từ cần loại bỏ (không phân biệt hoa thường)
        const removePatterns = [
            // Các từ về hành động mua/tìm/xem/đọc
            /^(tôi|mình|em|anh|chị|bạn)\s+(muốn|cần|đang|sẽ|hãy)\s+(mua|tìm|tìm kiếm|tìm sách|mua sách|tìm cuốn|mua cuốn|tìm quyển|mua quyển|xem|xem những|xem các|đọc|đọc sách|đọc online)/gi,
            /^(muốn|cần|đang|sẽ|hãy)\s+(mua|tìm|tìm kiếm|tìm sách|mua sách|tìm cuốn|mua cuốn|xem|xem những|xem các|đọc|đọc sách)/gi,
            /(tôi|mình|em|anh|chị|bạn)\s+(muốn|cần|đang|sẽ|hãy)\s+(mua|tìm|xem|đọc)/gi,
            /(muốn|cần|đang|sẽ|hãy)\s+(mua|tìm|xem|đọc)/gi,
            /^(xem|xem những|xem các|tìm|tìm sách|tìm cuốn|tìm quyển|hãy tìm|hãy tìm cho|tìm cho tôi|tìm giúp)\s+/gi,
            // Loại bỏ "tìm kiếm" nhưng chỉ khi không có "sách" sau đó
            /^tìm\s+kiếm\s+(?!.*sách)(.+?)$/gi,
            
            // Các từ về sách/cuốn ở đầu
            /^(sách|cuốn|quyển|tên|tên sách|tên cuốn|cuốn sách|quyển sách|sách tên)\s+/gi,
            // Các từ về sách/cuốn ở cuối (nhưng không phải là tên sách)
            /\s+(sách|cuốn|quyển)(?:\s+(?:có|có không|nào|gì))?$/gi,
            
            // Các từ về tác giả - chỉ loại bỏ phần "của tác giả" nhưng giữ lại tên tác giả
            /^(sách\s+)?(của|bởi|từ)\s+(tác giả|tác|nhà văn|tác giả là)\s+/gi,
            /(sách\s+)?(của|bởi|từ)\s+(tác giả|tác|nhà văn)\s+/gi,
            
            // Các từ thừa khác
            /^(cho|với|về|liên quan đến|ở đây|này|nè|nhé|nhỉ)\s+/gi,
            /\s+(cho|với|về|liên quan đến|ở đây|này|nè|nhé|nhỉ|ok|okay)$/gi,
            
            // Loại bỏ dấu ngoặc kép
            /^["']|["']$/g,
            
            // Loại bỏ các từ đơn lẻ không cần thiết
            /^(là|đó|này|ấy|đây)\s+/gi,
            /\s+(là|đó|này|ấy|đây)$/gi,
            
            // Loại bỏ "những", "các" ở đầu
            /^(những|các)\s+/gi,
            
            // Loại bỏ "có không", "có", "nào" ở cuối
            /\s+(có không|có|nào|gì)(\s|$)/gi
        ];
        
        // Áp dụng tất cả các pattern loại bỏ
        removePatterns.forEach(pattern => {
            text = text.replace(pattern, '').trim();
        });
        
        // Loại bỏ các từ khóa mua/tìm/xem/đọc còn sót lại
        // Lưu ý: KHÔNG loại "tìm kiếm" ở đây vì đã xử lý ở pattern đặc biệt
        const searchWords = [
            'mua', 'tìm', 'muốn mua', 'cần mua', 'muốn tìm', 'cần tìm', 
            'mua sách', 'tìm sách', 'tìm cuốn', 'tìm quyển',
            'xem', 'xem những', 'xem các', 'muốn xem', 'cần xem',
            'đọc', 'đọc sách', 'muốn đọc', 'cần đọc', 'đọc online',
            'những', 'các', 'hãy', 'hãy tìm', 'tìm cho tôi', 'tìm giúp', 'cho tôi', 'giúp tôi',
            'có không', 'có', 'nào', 'gì', 'ok', 'okay'
        ];
        searchWords.forEach(word => {
            // Escape các ký tự đặc biệt trong regex, tránh JSP parser nhầm với EL expression
            const escapedWord = word.replace(/[.*+?^$()|[\]\\]/g, '\\$&').replace(/\{/g, '\\{').replace(/\}/g, '\\}');
            const regex = new RegExp('\\b' + escapedWord + '\\b', 'gi');
            text = text.replace(regex, '').trim();
        });
        
        // Loại bỏ khoảng trắng thừa
        text = text.replace(/\s+/g, ' ').trim();
        
        // Nếu text còn lại quá ngắn hoặc chỉ là các từ thừa, thử cách khác
        if (text.length < 2) {
            // Thử tìm text trong dấu ngoặc kép
            const quotedMatch = originalText.match(/["']([^"']+)["']/);
            if (quotedMatch && quotedMatch[1]) {
                text = quotedMatch[1].trim();
            } else {
                // Thử lấy từ cuối cùng (có thể là tên sách/tác giả)
                // Nhưng ưu tiên lấy nhiều từ hơn nếu có từ khóa tìm kiếm
                const words = originalText.split(/\s+/);
                if (words.length > 0) {
                    // Tìm vị trí của từ khóa tìm kiếm
                    const searchKeywords = ['tìm', 'tìm kiếm', 'tìm sách', 'tác giả', 'sách', 'của'];
                    let keywordIndex = -1;
                    for (let i = 0; i < words.length; i++) {
                        if (searchKeywords.some(kw => words[i].toLowerCase().includes(kw.toLowerCase()))) {
                            keywordIndex = i;
                            break;
                        }
                    }
                    
                    if (keywordIndex >= 0 && keywordIndex < words.length - 1) {
                        // Lấy tất cả từ sau từ khóa
                        const afterKeyword = words.slice(keywordIndex + 1).join(' ');
                        // Loại bỏ các từ thừa ở cuối
                        const cleaned = afterKeyword.replace(/\s+(nhé|nè|nhỉ|ok|okay|ạ|nhá|cho tôi|giúp tôi|có không|không)$/i, '').trim();
                        if (cleaned.length > 2) {
                            text = cleaned;
                        } else {
                            // Nếu quá ngắn, lấy 3-5 từ cuối
                            const lastWords = words.slice(-5).join(' ');
                            if (lastWords.length > 2) {
                                text = lastWords;
                            }
                        }
                    } else {
                        // Không tìm thấy từ khóa, lấy 3-5 từ cuối
                        const lastWords = words.slice(-5).join(' ');
                        if (lastWords.length > 2) {
                            text = lastWords;
                        }
                    }
                }
            }
        }
        
        // Loại bỏ các từ đơn lẻ không có nghĩa nếu text quá dài
        if (text.split(' ').length > 10) {
            // Có thể là câu dài, thử lấy phần cuối (thường là tên sách/tác giả)
            const words = text.split(' ');
            if (words.length > 5) {
                text = words.slice(-5).join(' '); // Lấy 5 từ cuối
            }
        }
        
        return text;
    }
    
    // Hàm trích xuất tên sách từ câu nói của người dùng (giữ lại để tương thích)
    function extractBookName(message) {
        return extractSearchQuery(message);
    }
    
    // Hàm tìm kiếm sách (redirect trong cùng tab, giữ chat mở)
    function searchBook(bookName) {
        if (!bookName || bookName.trim() === '') return;
        
        // Xác định loại tìm kiếm và trích xuất query
        const searchInfo = extractSearchTypeAndQuery(bookName);
        const searchType = searchInfo.type;
        const finalQuery = searchInfo.query;
        
        if (finalQuery.length < 2) {
            // Hiển thị thông báo trong chat thay vì alert
            const messagesDiv = document.getElementById('geminiChatMessages');
            if (messagesDiv) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'message message-ai';
                errorMsg.innerHTML = '<div class="message-content" style="background: #fff3cd; border-left-color: #ffc107;">' +
                    '<div class="message-header" style="color: #856404;"><i class="fa-solid fa-exclamation-triangle"></i> Lưu ý</div>' +
                    '<span style="color: #856404;">😅 Từ khóa tìm kiếm quá ngắn. Vui lòng nhập tên sách, tác giả, thể loại hoặc nhà xuất bản cụ thể hơn nhé!</span></div>';
                messagesDiv.appendChild(errorMsg);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
                saveChatHistory();
            }
            return;
        }
        
        // Encode query để dùng trong URL
        const encodedQuery = encodeURIComponent(finalQuery);
        let searchUrl = '';
        
        // Xây dựng URL tùy theo loại tìm kiếm
        if (searchType === 'category') {
            searchUrl = '${baseURL}/tim-kiem?category=' + encodedQuery;
        } else if (searchType === 'publisher') {
            searchUrl = '${baseURL}/tim-kiem?publisher=' + encodedQuery;
        } else {
            // Mặc định là tìm kiếm sách/tác giả
            searchUrl = '${baseURL}/tim-kiem?query=' + encodedQuery;
        }
        
        // Lưu chat history và trạng thái chat (đang mở) trước khi redirect
        saveChatHistory();
        sessionStorage.setItem('geminiChatShouldOpen', 'true');
        
        // Redirect trong cùng tab, chat sẽ được khôi phục khi trang load
        window.location.href = searchUrl;
    }
    
    // Lưu chat history vào sessionStorage
    function saveChatHistory() {
        const messagesDiv = document.getElementById('geminiChatMessages');
        if (messagesDiv) {
            const messages = [];
            const messageElements = messagesDiv.querySelectorAll('.message');
            messageElements.forEach(msg => {
                messages.push({
                    className: msg.className,
                    innerHTML: msg.innerHTML
                });
            });
            try {
                sessionStorage.setItem('geminiChatHistory', JSON.stringify(messages));
            } catch (e) {
                console.error('Lỗi khi lưu chat history:', e);
            }
        }
    }
    
    // Khôi phục chat history từ sessionStorage
    function loadChatHistory() {
        const messagesDiv = document.getElementById('geminiChatMessages');
        if (!messagesDiv) return;
        
        try {
            const savedHistory = sessionStorage.getItem('geminiChatHistory');
            if (savedHistory) {
                const messages = JSON.parse(savedHistory);
                // Xóa message mặc định nếu có
                const defaultMsg = messagesDiv.querySelector('.message');
                if (defaultMsg && messages.length > 1) {
                    defaultMsg.remove();
                }
                // Khôi phục các message đã lưu
                messages.forEach(msgData => {
                    const msg = document.createElement('div');
                    msg.className = msgData.className;
                    msg.innerHTML = msgData.innerHTML;
                    messagesDiv.appendChild(msg);
                });
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        } catch (e) {
            console.error('Lỗi khi khôi phục chat history:', e);
        }
    }
    
    // Xóa chat history khi đăng xuất (có thể gọi từ logout page)
    function clearChatHistory() {
        try {
            sessionStorage.removeItem('geminiChatHistory');
            const messagesDiv = document.getElementById('geminiChatMessages');
            if (messagesDiv) {
                messagesDiv.innerHTML = '<div class="message message-ai">' +
                    '<div class="message-content">' +
                    '<div class="message-header"><i class="fa-solid fa-robot"></i> Đại Đức AI</div>' +
                    '<span>Xin chào! 👋 Tôi là Đại Đức AI dễ thương của bạn! Tôi có thể giúp bạn tìm sách, trả lời câu hỏi về sách, hoặc hỗ trợ bạn đọc sách. Bạn cần giúp gì nhỉ? 😊</span></div></div>';
            }
        } catch (e) {
            console.error('Lỗi khi xóa chat history:', e);
        }
    }
    
        // Khôi phục chat history khi trang load (nếu chat đang mở)
        document.addEventListener('DOMContentLoaded', function() {
            // Gán event listener cho icon robot
            const geminiIcon = document.getElementById('geminiChatIcon');
            if (geminiIcon) {
                geminiIcon.addEventListener('click', function(e) {
                    e.preventDefault();
                    openGeminiChat();
                });
            }
            
            // Kiểm tra xem có cần mở chat tự động không (sau khi tìm kiếm)
            const shouldOpenChat = sessionStorage.getItem('geminiChatShouldOpen');
            if (shouldOpenChat === 'true') {
                sessionStorage.removeItem('geminiChatShouldOpen');
                // Mở chat và khôi phục history
                openGeminiChat();
            } else {
                // Khôi phục chat history nếu widget đang mở
                const chatWidget = document.getElementById('geminiChatWidget');
                if (chatWidget && chatWidget.classList.contains('show')) {
                    loadChatHistory();
                }
            }
            
            // Tạo hiệu ứng ánh sao động cho badge "Đăng ký gói cước"
            const subscribeBadge = document.querySelector('.subscribe-badge');
            if (subscribeBadge) {
                createSparkleEffect(subscribeBadge);
            }
        });
        
        // Hàm tạo hiệu ứng ánh sao động
        function createSparkleEffect(container) {
            const sparkles = ['✨', '⭐', '💫', '🌟'];
            const colors = ['#ffd700', '#ffed4e', '#fff', '#ff6b35'];
            
            setInterval(() => {
                const sparkle = document.createElement('span');
                sparkle.textContent = sparkles[Math.floor(Math.random() * sparkles.length)];
                sparkle.style.position = 'absolute';
                sparkle.style.fontSize = '0.8rem';
                sparkle.style.color = colors[Math.floor(Math.random() * colors.length)];
                sparkle.style.pointerEvents = 'none';
                sparkle.style.zIndex = '10';
                
                // Vị trí ngẫu nhiên trong container
                const rect = container.getBoundingClientRect();
                const x = Math.random() * rect.width;
                const y = Math.random() * rect.height;
                
                sparkle.style.left = x + 'px';
                sparkle.style.top = y + 'px';
                
                container.appendChild(sparkle);
                
                // Animation
                sparkle.style.animation = 'sparkleFloat 1.2s ease-out forwards';
                
                // Xóa sau khi animation kết thúc
                setTimeout(() => {
                    if (sparkle.parentNode) {
                        sparkle.parentNode.removeChild(sparkle);
                    }
                }, 1200);
            }, 1000);
        }
        
        // Thêm keyframe animation cho sparkle
        const style = document.createElement('style');
        style.textContent = `
            @keyframes sparkleFloat {
                0% {
                    opacity: 1;
                    transform: translateY(0) scale(0.4) rotate(0deg);
                }
                100% {
                    opacity: 0;
                    transform: translateY(-20px) scale(1.2) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
