<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            // Kiểm tra quyền admin từ bảng user_role
            $user_id = $row['id'];
            $sql_role = "SELECT r.id AS role_id, r.name AS role_name FROM role r 
                         JOIN user_role ur ON ur.role_id = r.id
                         WHERE ur.user_id = '$user_id' AND r.name = 'admin'";
            $role_result = $conn->query($sql_role);

            if ($role_result->num_rows > 0) {
                // Lưu role_id vào session
                $role = $role_result->fetch_assoc();
                $_SESSION['role_id'] = $role['role_id'];
                $_SESSION['role_name'] = $role['role_name'];
                header("Location: http://localhost/shop/admin/index.php");
            } else {
                header("Location: http://localhost/shop/index.php");
            }
        } else {
            echo "Mật khẩu sai.";
        }
    } else {
        echo "Không tìm thấy tài khoản.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <form method="POST" id="loginForm">
            <h2>Đăng nhập</h2>
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" id="username" required><br>

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" id="password" required><br>

            <button type="submit">Đăng nhập</button>
        </form>

        <button type="button" class="register-btn" onclick="window.location.href='register.php';">Đăng ký</button>
    </div>
</body>
</html>
