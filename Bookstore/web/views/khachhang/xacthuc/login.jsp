<%-- Trang đăng nhập chung cho Admin và Khách hàng --%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - BookStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Hiệu ứng nền động */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
            z-index: 0;
        }

        @keyframes moveBackground {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            animation: slideUp 0.6s ease;
            position: relative;
            z-index: 1;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 45px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 15s linear infinite;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg);
            }
            100% {
                transform: translate(30px, 30px) rotate(360deg);
            }
        }

        .login-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            font-weight: 700;
        }

        .login-header p {
            opacity: 0.95;
            font-size: 1rem;
            position: relative;
            z-index: 1;
        }

        .login-header .icon {
            font-size: 3rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            display: block;
        }

        .login-body {
            padding: 40px 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
            z-index: 2;
        }

        .input-icon input {
            width: 100%;
            padding: 14px 15px 14px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input-icon input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #c33;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .remember-me label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            color: #555;
        }

        .form-options a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .form-options a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .auth-switch-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95rem;
            color: #666;
        }

        .auth-switch-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-switch-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a:hover {
            color: #764ba2;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                border-radius: 15px;
            }

            .login-header {
                padding: 35px 25px;
            }

            .login-header h1 {
                font-size: 1.8rem;
            }

            .login-body {
                padding: 30px 25px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fa-solid fa-book icon"></i>
            <h1>BookStore</h1>
            <p>Đăng nhập vào hệ thống</p>
        </div>
        <div class="login-body">
            <%-- Hiển thị thông báo thành công từ session --%>
            <c:if test="${not empty sessionScope.successMessage}">
                <div class="alert-success">
                    <i class="fa-solid fa-check-circle"></i> ${sessionScope.successMessage}
                </div>
                <c:remove var="successMessage" scope="session"/>
            </c:if>

            <%-- Hiển thị lỗi đăng nhập --%>
            <c:if test="${not empty requestScope.error}">
                <div class="error-message">
                    <i class="fa-solid fa-circle-exclamation"></i> ${requestScope.error}
                </div>
            </c:if>

            <%-- Form đăng nhập chung --%>
            <form method="POST" action="${baseURL}/dang-nhap">
                <div class="form-group">
                    <label for="username">Tên đăng nhập *</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="username" name="username" 
                               value="<c:out value="${requestScope.username}"/>" 
                               required autofocus autocomplete="username"
                               placeholder="Nhập tên đăng nhập">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" 
                               required autocomplete="current-password"
                               placeholder="Nhập mật khẩu">
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="${baseURL}/quen-mat-khau">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập
                </button>
            </form>

            <div class="auth-switch-link">
                Chưa có tài khoản? <a href="${baseURL}/dang-ky">Đăng ký ngay</a>
            </div>

            <div class="back-link">
                <a href="${baseURL}/trang-chu">
                    <i class="fa-solid fa-arrow-left"></i> Quay về trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
