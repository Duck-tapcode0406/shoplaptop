<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form và thực hiện xử lý bảo mật
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Mã hóa mật khẩu
    $familyname = $conn->real_escape_string($_POST['familyname']);
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);

    // Kiểm tra xem tên đăng nhập có tồn tại không
    $sql_check = "SELECT * FROM user WHERE username='$username'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
    } else {
        // Thêm người dùng vào bảng user
        $sql = "INSERT INTO user (username, password, familyname, firstname, phone, email) 
                VALUES ('$username', '$password', '$familyname', '$firstname', '$phone', '$email')";

        if ($conn->query($sql) === TRUE) {
            // Lấy id của người dùng vừa thêm
            $user_id = $conn->insert_id;

            // Gán role với role_id = 2 cho người dùng vào bảng user_role
            $role_id = 2;
            $sql_role = "INSERT INTO user_role (role_id, user_id) VALUES ('$role_id', '$user_id')";

            if ($conn->query($sql_role) === TRUE) {
                echo "Đăng ký thành công! Bạn có thể <a href='login.php'>đăng nhập</a> ngay bây giờ.";
            } else {
                echo "Lỗi khi gán quyền: " . $conn->error;
            }
        } else {
            echo "Lỗi: " . $conn->error;
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="css/register.css"> <!-- Kết nối với file CSS -->
</head>
<body>
    <div class="register-container">
        <h2>Đăng ký tài khoản</h2>
        <form method="POST">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" required><br>

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" required><br>

            <label for="familyname">Họ:</label>
            <input type="text" name="familyname" required><br>

            <label for="firstname">Tên:</label>
            <input type="text" name="firstname" required><br>

            <label for="phone">Số điện thoại:</label>
            <input type="text" name="phone" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" required><br>

            <button type="submit">Đăng ký</button>
        </form>

        <button onclick="window.location.href='login.php';">Trở về</button>
    </div>
</body>
</html>
