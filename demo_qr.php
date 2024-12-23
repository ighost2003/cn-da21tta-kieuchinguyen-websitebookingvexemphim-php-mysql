<?php
include("include/header.php");
// Kết nối cơ sở dữ liệu
include "include/connection.php";

// Kiểm tra xem id_user có tồn tại trong session không
if (!empty($_SESSION['id_user'])) {
    // Hiển thị id_user
    echo "ID User: " . $_SESSION['id_user'];
} else {
    echo "ID User không tồn tại.";
}
//-----------
// Lấy id_user và id_product từ URL
$id_user = $_SESSION['id_user'];

// Truy vấn để lấy id_product từ bảng `port_duphong`
$query = "SELECT id_product FROM port_duphong WHERE id_user = '$id_user'";
$result = mysqli_query($conn, $query);

// Kiểm tra nếu truy vấn thành công
if ($result) {
    $productIds = []; // Mảng chứa các id_product
    
    // Lặp qua kết quả truy vấn và thêm từng id_product vào mảng
    while ($row = mysqli_fetch_assoc($result)) {
        $productIds[] = $row['id_product']; // Thêm id_product vào mảng
    }
    
    // Kiểm tra nếu mảng có giá trị
    if (!empty($productIds)) {
        // Mảng $productIds chứa các id_product
        // In ra mảng để kiểm tra
        echo "Danh sách id_product: ";
        print_r($productIds);
    } else {
        echo "Không có sản phẩm nào cho người dùng này.";
    }
} else {
    // Xử lý lỗi nếu truy vấn thất bại
    echo "Lỗi khi truy vấn dữ liệu: " . mysqli_error($conn);
}

// Lấy thông tin số lượng
$query2 = "SELECT so_luong, reload FROM port_duphong WHERE id_user = '$id_user'";
$result2 = mysqli_query($conn, $query2);

// Kiểm tra nếu truy vấn thành công
if ($result2) {
    $row2 = mysqli_fetch_assoc($result2);
    
    if ($row2) {
        $so_luong = $row2['so_luong']; // Lưu giá trị so_luong vào biến
        $reload = $row2['reload']; // Lưu giá trị reload vào biến
        echo "Số lượng: " . $so_luong;
        echo " Trạng thái reload: " . ($reload == 1 ? "Đã tải lại" : "Chưa tải lại");
    } else {
        echo "Không tìm thấy dữ liệu cho người dùng này.";
    }
} else {
    echo "Lỗi khi truy vấn dữ liệu: " . mysqli_error($conn);
}

// Kiểm tra nếu thiếu tham số cần thiết
if ($id_user == 0 || empty($productIds) || $so_luong == 0) {
    echo "Thiếu tham số id_user, id_product hoặc số lượng!";
    exit;
}

// Truy vấn thông tin sản phẩm
$query_info = "SELECT san_pham.ma_san_pham, san_pham.ten_san_pham 
              FROM ct_hoa_don AS cthd 
              JOIN hoa_don ON cthd.id_hoa_don = hoa_don.id_hoa_don 
              JOIN san_pham ON cthd.id_san_pham = san_pham.id_san_pham
              WHERE hoa_don.id_user = ? AND cthd.id_san_pham = ?";
$stmt = $conn->prepare($query_info);
$stmt->bind_param("ii", $id_user, $productIds[0]);
$stmt->execute();
$product_info = $stmt->get_result()->fetch_assoc();

if (!$product_info) {
    echo "Không tìm thấy sản phẩm cho đơn hàng này!";
    exit;
}

$query_amount = "SELECT SUM(tong_tien) as tong_tien
                FROM ct_hoa_don AS cthd 
                JOIN hoa_don ON cthd.id_hoa_don = hoa_don.id_hoa_don 
                WHERE hoa_don.id_user = ? ORDER BY hoa_don.ngay_tao DESC 
                LIMIT 2";
$stmt = $conn->prepare($query_amount);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$amount_result = $stmt->get_result()->fetch_assoc();

// Kiểm tra và lấy số tiền
if ($amount_result) {
    $amount = $amount_result['tong_tien'];
} else {
    echo "Không tìm thấy dữ liệu về số tiền!";
    exit;
}
//--------------------------

// Thêm dữ liệu vào bảng lich_su_don_hang
$insert_query = "INSERT INTO lich_su_don_hang (id_user, money, ngay_tao) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ids", $id_user, $amount, $created_at);

$created_at = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại ở định dạng phù hợp
if ($stmt->execute()) {
    // echo "Đã thêm dữ liệu vào lich_su_don_hang thành công";
} else {
    echo "Lỗi khi thêm dữ liệu: " . $stmt->error;
}

// Lấy giá trị money_temp mới nhất
$money_temp_query = "SELECT money FROM lich_su_don_hang WHERE id_user = $id_user ORDER BY ngay_tao DESC LIMIT 1 OFFSET 1";
$money_temp_result = mysqli_query($conn, $money_temp_query);
if ($money_temp_result) {
    $money_temp_row = mysqli_fetch_assoc($money_temp_result);
    $money_temp = $money_temp_row['money'];
} else {
    echo "Lỗi khi lấy money_temp: " . mysqli_error($conn);
}
//--------------------------
$amount = $amount - $money_temp;

$product_code = $product_info['ma_san_pham'];
$product_name = $product_info['ten_san_pham'];

// Tạo URL cho QR code VietQR
$bank_id = "mbbank";
$account_no = "012332101111";
$template = "compact2";
$account_name = "HTM+Team";
$add_info = $product_name;

// Tạo URL cho QR
$quicklink_url = "https://img.vietqr.io/image/$bank_id-$account_no-$template.png?amount=$amount&addInfo=$add_info&accountName=$account_name";

// Kiểm tra hình thức thanh toán trước
$check_payment_query = "SELECT hinh_thuc_thanh_toan 
                       FROM hoa_don 
                       WHERE id_user = ? 
                       ORDER BY ngay_tao DESC 
                       LIMIT 1";
$stmt = $conn->prepare($check_payment_query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$payment_result = $stmt->get_result()->fetch_assoc();

if ($payment_result && $payment_result['hinh_thuc_thanh_toan'] == 'tienmat') {
    header("Location: ../index.php");
    exit;
}

// Nếu reload = 0, tiến hành cập nhật giá trị reload thành 1 trong cơ sở dữ liệu
if ($reload == 0) {
    $update_reload_query = "UPDATE port_duphong SET reload = 1 WHERE id_user = '$id_user'";
    if (mysqli_query($conn, $update_reload_query)) {
        echo "Cập nhật trạng thái reload thành công!";
    } else {
        echo "Lỗi khi cập nhật trạng thái reload: " . mysqli_error($conn);
    }
}

echo '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">';
echo '<link rel="stylesheet" href="styles.css">';
echo '<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-minimal/minimal.css" rel="stylesheet">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
//---

// Thêm biến PHP vào JavaScript và include file script
echo '<script>const userId = ' . $id_user . ';</script>';
echo '<script src="payment_status.js"></script>';

// Thêm CSS tùy chỉnh cho dialog
echo '<style>
.my-custom-popup-class {
    font-family: "Roboto", sans-serif;
    border-radius: 15px;
}
.my-custom-button-class {
    background-color: #4CAF50 !important;
    border-radius: 5px;
    padding: 10px 24px;
}

body {
    font-family: "Roboto", sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 20px;
}

h1 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}

.qr-container {
    width: 1000px;
    height: 500px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.product-info {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.product-info p {
    margin: 10px 0;
    color: #444;
    font-size: 20px;
}

.product-info strong {
    color: #333;
    font-weight: 500;
}

.qr-image {
    text-align: center;
    position: relative;
    overflow: hidden;
}

.qr-image::after {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        to bottom,
        transparent 46%,
        rgba(255, 255, 255, 0.4) 48%,
        rgba(255, 255, 255, 0.4) 52%,
        transparent 54%
    );
    animation: scan 2s linear infinite;
    transform: rotate(45deg);
}

@keyframes scan {
    0% {
        transform: translate(-50%, -50%) rotate(45deg);
    }
    100% {
        transform: translate(50%, 50%) rotate(45deg);
    }
}

.qr-image img {
    max-width: 300px;
    height: auto;
    border-radius: 5px;
    padding: 15px;
    background: white;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    position: relative;
    border: 2px solid transparent;
    animation: borderAnimation 4s linear infinite;
}

@keyframes borderAnimation {
    0% { border-color: #4CAF50; }
    25% { border-color: #2196F3; }
    50% { border-color: #9C27B0; }
    75% { border-color: #F44336; }
    100% { border-color: #4CAF50; }
}

@media (max-width: 600px) {
    .qr-container {
        padding: 15px;
        margin: 0 10px;
    }
    .qr-image img {
        max-width: 100%;
    }
    h1 {
        font-size: 24px;
    }
}
</style>';

echo "<h1>Mã QR thanh toán</h1>"; 
echo '<div class="qr-container">';
echo '<div class="product-info">';
echo "<p><strong>Số tiền:</strong> " . number_format($amount) . " VNĐ</p>";
echo "<p><strong>Thông tin sản phẩm:</strong> $product_name</p>";
echo "<p><strong>Mã sản phẩm:</strong> $product_code</p>";
echo "</div>";
echo '<div class="qr-image">';
echo "<img src='$quicklink_url' alt='VietQR' />";
echo "</div>";
echo "</div>";

// Thực hiện xóa dữ liệu từ bảng port_duphong
$delete_query = "DELETE FROM port_duphong";
if (mysqli_query($conn, $delete_query)) {
    // echo "Đã xóa dữ liệu thành công từ bảng port_duphong";
} else {
    echo "Lỗi khi xóa dữ liệu: " . mysqli_error($conn);
}
?>
