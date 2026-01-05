<?php
session_start();

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Lấy thông tin người dùng hiện tại
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, familyname, firstname, email, phone, password FROM user WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $familyname = $_POST['familyname'];
    $firstname = $_POST['firstname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $old_password = $_POST['old_password'];

    // Nếu người dùng muốn thay đổi mật khẩu
    if (!empty($password)) {
        // Kiểm tra mật khẩu cũ
        if (empty($old_password)) {
            $message = "Bạn phải nhập mật khẩu cũ để thay đổi mật khẩu mới!";
        } else {
            if (!password_verify($old_password, $user['password'])) {
                $message = "Mật khẩu cũ không chính xác!";
            } else {
                // Mật khẩu cũ đúng, cập nhật mật khẩu mới
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE user SET familyname = '$familyname', firstname = '$firstname', email = '$email', phone = '$phone', password = '$hashed_password' WHERE id = $user_id";
                if ($conn->query($update_sql)) {
                    $message = "Thông tin đã được cập nhật thành công!";
                } else {
                    $message = "Lỗi khi cập nhật thông tin!";
                }
            }
        }
    } else {
        // Nếu không thay đổi mật khẩu, chỉ cập nhật các thông tin khác
        $update_sql = "UPDATE user SET familyname = '$familyname', firstname = '$firstname', email = '$email', phone = '$phone' WHERE id = $user_id";
        if ($conn->query($update_sql)) {
            $message = "Thông tin đã được cập nhật thành công!";
        } else {
            $message = "Lỗi khi cập nhật thông tin!";
        }
    }
}
?>
<!-- Bao gồm header từ thư mục includes -->
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Cá Nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Chỉnh sửa thông tin cá nhân</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Form chỉnh sửa thông tin cá nhân -->
        <form method="POST" action="user.php">
            <div class="mb-3">
                <label for="familyname" class="form-label">Họ</label>
                <input type="text" class="form-control" id="familyname" name="familyname" value="<?php echo htmlspecialchars($user['familyname']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="firstname" class="form-label">Tên</label>
                <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <!-- Mật khẩu cũ -->
            <div class="mb-3">
                <label for="old_password" class="form-label">Mật khẩu cũ</label>
                <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Nhập mật khẩu cũ để thay đổi mật khẩu mới">
            </div>

            <!-- Mật khẩu mới -->
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu mới (nếu muốn thay đổi)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </form>

        <br>
        <a href="index.php" class="btn btn-secondary">Trở lại trang chủ</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <!-- Include Footer -->
    <?php include('footer.php'); ?>
</body>
</html>

<?php
$conn->close();
?>
