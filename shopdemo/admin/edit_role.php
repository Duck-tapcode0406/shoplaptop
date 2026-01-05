<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Kiểm tra quyền admin
$sql_check_admin = "SELECT r.name FROM role r 
                    JOIN user_role ur ON ur.role_id = r.id 
                    WHERE ur.user_id = '$user_id' AND r.name = 'admin'";
$result_check_admin = $conn->query($sql_check_admin);

if ($result_check_admin->num_rows == 0) {
    echo "Bạn không có quyền thực hiện hành động này.";
    exit();
}

// Nhận dữ liệu từ form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id_to_update = $_POST['user_id'];
    $role_id = $_POST['role_id'];

    // Kiểm tra xem người dùng đã có quyền trong bảng user_role chưa
    $sql_check_user_role = "SELECT * FROM user_role WHERE user_id = '$user_id_to_update'";
    $result_check_user_role = $conn->query($sql_check_user_role);

    if ($result_check_user_role->num_rows > 0) {
        // Nếu đã có quyền, thì chỉ cần cập nhật quyền mới
        $sql_update_role = "UPDATE user_role SET role_id = '$role_id' WHERE user_id = '$user_id_to_update'";
    } else {
        // Nếu chưa có quyền, thêm quyền mới
        $sql_update_role = "INSERT INTO user_role (user_id, role_id) VALUES ('$user_id_to_update', '$role_id')";
    }

    if ($conn->query($sql_update_role) === TRUE) {
        header("Location: http://localhost/shop/admin/role.php");
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

$conn->close();
?>
