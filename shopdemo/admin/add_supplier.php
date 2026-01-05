<?php
session_start();
include '../includes/db.php';  // Kết nối với cơ sở dữ liệu

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kiểm tra quyền admin
$user_id = $_SESSION['user_id'];
$sql_check_admin = "SELECT r.name FROM role r 
                    JOIN user_role ur ON ur.role_id = r.id 
                    WHERE ur.user_id = '$user_id' AND r.name = 'admin'";
$result_check_admin = $conn->query($sql_check_admin);

if ($result_check_admin->num_rows == 0) {
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

// Xử lý việc thêm nhà cung cấp
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $insertQuery = "INSERT INTO supplier (name, description) VALUES ('$name', '$description')";
        if ($conn->query($insertQuery)) {
            header('Location: supplier.php');  // Chuyển hướng về trang danh sách nhà cung cấp
            exit;
        } else {
            echo "Có lỗi khi thêm nhà cung cấp.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Nhà Cung Cấp</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Bao gồm CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }

        .content {
            margin: 30px auto;
            padding: 20px;
            max-width: 600px;
            background-color: #181818;
            color: #e0e0e0;
        }

        h2, h3 {
            color: #f1f1f1;
        }

        label {
            color: #e0e0e0;
        }

        input[type="text"], textarea {
            background-color: #333;
            color: #e0e0e0;
            border: 1px solid #444;
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            color: #4CAF50;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="content">
        <h2>Thêm Nhà Cung Cấp</h2>

        <!-- Form Thêm Nhà Cung Cấp -->
        <form method="POST" action="add_supplier.php">
            <input type="hidden" name="action" value="add">
            <label for="name">Tên nhà cung cấp:</label>
            <input type="text" id="name" name="name" required>
            <label for="description">Mô tả:</label>
            <textarea id="description" name="description" required></textarea>
            <button type="submit">Thêm</button>
        </form>

        <a href="supplier.php" class="back-link">Quay lại danh sách nhà cung cấp</a>
    </div>

</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>
