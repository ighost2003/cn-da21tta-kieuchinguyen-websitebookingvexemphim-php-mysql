<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cgvdb"; // Thay bằng tên cơ sở dữ liệu của bạn

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
session_start();
// ... existing code ...
echo '<a href="index.php"><img src="./img/cgvlogo.png" alt="" style="position: absolute; top: 10px; left: 10px; width: 180px; transition: transform 0.3s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"></a>';
// ... existing code ...

// Check if promotion code is provided
if (!isset($_GET['ma_km'])) {
    echo "<script>alert('Không tìm thấy mã khuyến mãi');window.location.href='codekm.php';</script>";
    exit();
}

$ma_km = $_GET['ma_km'];

// Get promotion details
$sql = "SELECT * FROM code_khuyen_mai WHERE ma_so_khuyen_mai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ma_km);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Không tìm thấy mã khuyến mãi');window.location.href='codekm.php';</script>";
    exit();
}

$promotion = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_khuyen_mai = $_POST['ten_khuyen_mai'];
    $gia_tri_khuyen_mai = $_POST['gia_tri_khuyen_mai'];
    $ngay_ket_thuc = $_POST['ngay_ket_thuc'];
    $mo_ta = $_POST['mo_ta'];

    $update_sql = "UPDATE code_khuyen_mai SET 
                   ten_khuyen_mai = ?,
                   gia_tri_khuyen_mai = ?,
                   ngay_ket_thuc = ?,
                   mo_ta = ?
                   WHERE ma_so_khuyen_mai = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sdsss", $ten_khuyen_mai, $gia_tri_khuyen_mai, $ngay_ket_thuc, $mo_ta, $ma_km);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!');window.location.href='codekm.php';</script>";
    } else {
        $error_message = "Lỗi khi cập nhật: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Mã Khuyến Mãi</title>
    <!-- Use the same CSS as codekm.php -->
    <style>
        /* Copy the CSS from codekm.php */
    </style>
</head>
<body>
    <h2>Sửa Mã Khuyến Mãi</h2>
    <form method="post">
        <label for="ten_khuyen_mai">Tên Khuyến Mãi</label>
        <input type="text" id="ten_khuyen_mai" name="ten_khuyen_mai" 
               value="<?php echo htmlspecialchars($promotion['ten_khuyen_mai']); ?>" required>

        <label for="gia_tri_khuyen_mai">Giá Trị Khuyến Mãi</label>
        <input type="number" id="gia_tri_khuyen_mai" name="gia_tri_khuyen_mai" 
               value="<?php echo htmlspecialchars($promotion['gia_tri_khuyen_mai']); ?>" step="0.01" required>

        <label for="ngay_ket_thuc">Ngày Kết Thúc</label>
        <input type="date" id="ngay_ket_thuc" name="ngay_ket_thuc" 
               value="<?php echo htmlspecialchars($promotion['ngay_ket_thuc']); ?>" required>

        <label for="mo_ta">Mô Tả</label>
        <textarea id="mo_ta" name="mo_ta" rows="4"><?php echo htmlspecialchars($promotion['mo_ta']); ?></textarea>

        <input type="submit" value="Cập Nhật">
    </form>

    <?php if (isset($error_message)): ?>
        <p class="message error"><?php echo $error_message; ?></p>
    <?php endif; ?>
</body>
</html> 