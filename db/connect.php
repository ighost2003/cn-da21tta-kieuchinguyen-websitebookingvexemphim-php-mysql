
<?php
$servername = "localhost";
$username = "root"; // Thường là "root" trong XAMPP
$password = ""; // Nếu bạn không đặt mật khẩu cho root, để trống
$dbname = "CGVDB";

// Tạo kết nối
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($mysqli->connect_error) {
    die("Kết nối thất bại: " . $mysqli->connect_error);
}
?>