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
echo '<a href="admin.php"><img src="./img/cgvlogo.png" alt="" style="position: absolute; top: 10px; left: 10px; width: 180px; transition: transform 0.3s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"></a>';
// ... existing code ...



// Sử dụng các lớp PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Đường dẫn tới thư viện PHPMailer
require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';


// Hàm tạo mã khuyến mãi random
function generateRandomCode($length = 6) {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $characters = $letters . $numbers;
    $randomCode = $letters[rand(0, strlen($letters) - 1)] . $numbers[rand(0, strlen($numbers) - 1)];
    for ($i = 2; $i < $length; $i++) {
        $randomCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return str_shuffle($randomCode);
}

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_khuyen_mai = $_POST['ten_khuyen_mai'];
    $gia_tri_khuyen_mai = $_POST['gia_tri_khuyen_mai'];
    $ngay_bat_dau = date('Y-m-d');
    $ngay_ket_thuc = isset($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : 'Không xác định';
    $mo_ta = $_POST['mo_ta'];
    $ma_so_khuyen_mai = generateRandomCode();

    // Chèn mã khuyến mãi vào cơ sở dữ liệu
    $sql = "INSERT INTO code_khuyen_mai (ma_so_khuyen_mai, ten_khuyen_mai, gia_tri_khuyen_mai, ngay_bat_dau, ngay_ket_thuc, mo_ta) 
            VALUES ('$ma_so_khuyen_mai', '$ten_khuyen_mai', '$gia_tri_khuyen_mai', '$ngay_bat_dau', '$ngay_ket_thuc', '$mo_ta')";
    if ($conn->query($sql) === TRUE) {
        $message = "Thêm mã khuyến mãi thành công!";

        // Lấy danh sách email từ bảng users
        $email_query = "SELECT user_email FROM users WHERE 1";
        $email_result = $conn->query($email_query);

        if ($email_result->num_rows > 0) {
            while ($row = $email_result->fetch_assoc()) {
                $email = $row['user_email'];

                // Gửi mã khuyến mãi qua email
                $mail = new PHPMailer(true);
                try {
                    // Cấu hình SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'chinguyen29825@gmail.com'; // Email của bạn
                    $mail->Password = 'iqbm acqv btzn jusk'; // Mật khẩu ứng dụng của bạn
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Cài đặt người gửi và người nhận
                    $mail->setFrom('chinguyen29825@gmail.com', '<110121275@st.tvu.edu.vn>');
                    $mail->addAddress($email);

                    // Thêm charset UTF-8
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';

                    // Nội dung email
                    $mail->isHTML(true);
                    $mail->Subject = "Mã Khuyến Mãi Từ 
110121275@st.tvu.edu.vn";
                    $mail->Body = "Chào bạn!<br><br>
                                   Mã khuyến mãi của bạn là: <b>$ma_so_khuyen_mai</b><br>
                                   Tên khuyến mãi: $ten_khuyen_mai<br>
                                   Giá trị: $gia_tri_khuyen_mai<br><br>
                                   Áp dụng từ ngày: $ngay_bat_dau đến ngày: " . ($ngay_ket_thuc ?? 'Không xác định') . ".<br>
                                   Chi tiết: $mo_ta";

                    $mail->send();
                } catch (Exception $e) {
                    $message = "Không thể gửi mã khuyến mãi. Lỗi: {$mail->ErrorInfo}";
                }
            }
        } else {
            $message = "Không có email nào trong danh sách người dùng.";
        }

    } else {
        $message = "Lỗi: " . $sql . "<br>" . $conn->error;
    }
}


// Handle delete action
if (isset($_GET['delete'])) {
    $ma_km = $_GET['delete'];
    $delete_sql = "DELETE FROM code_khuyen_mai WHERE ma_so_khuyen_mai = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $ma_km);
    if ($stmt->execute()) {
        echo "<script>alert('Xóa mã khuyến mãi thành công!'); window.location.href='codekm.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Mã Khuyến Mãi</title>
    <style>
        :root {
            --primary-color: #2196F3;
            --hover-color: #1976D2;
            --error-color: #f44336;
            --success-color: #4CAF50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
            color: #333;
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.2em;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        form:hover {
            transform: translateY(-5px);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 0.95em;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }

        input[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        .message {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-radius: 6px;
            font-weight: 500;
            animation: slideIn 0.5s ease;
        }

        .message.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .message.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 1.8em;
            }

            input, textarea {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <h2>Thêm Mã Khuyến Mãi</h2>
    <form method="post">
        <label for="ten_khuyen_mai">Tên Khuyến Mãi</label>
        <input type="text" id="ten_khuyen_mai" name="ten_khuyen_mai" required>

        <label for="gia_tri_khuyen_mai">Giá Trị Khuyến Mãi</label>
        <input type="number" id="gia_tri_khuyen_mai" name="gia_tri_khuyen_mai" step="0.01" required>

        <label for="ngay_ket_thuc">Ngày Kết Thúc</label>
        <input type="date" id="ngay_ket_thuc" name="ngay_ket_thuc" required>

        <label for="mo_ta">Mô Tả</label>
        <textarea id="mo_ta" name="mo_ta" rows="4"></textarea>

        <input type="submit" value="Thêm Mã Khuyến Mãi">
    </form>

    <?php if (!empty($message)): ?>
        <p class="message <?php echo strpos($message, 'thành công') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <div class="container">
        <h2>Danh Sách Mã Khuyến Mãi</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã Khuyến Mãi</th>
                        <th>Tên Khuyến Mãi</th>
                        <th>Giá Trị</th>
                        <th>Ngày Bắt Đầu</th>
                        <th>Ngày Kết Thúc</th>
                        <th>Mô Tả</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM code_khuyen_mai ORDER BY ngay_bat_dau DESC";
                    $result = $conn->query($sql);
                    $stt = 1;
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>".$stt++."</td>";
                        echo "<td>".$row['ma_so_khuyen_mai']."</td>";
                        echo "<td>".$row['ten_khuyen_mai']."</td>";
                        echo "<td>".number_format($row['gia_tri_khuyen_mai'])." VNĐ</td>";
                        echo "<td>".date('d/m/Y', strtotime($row['ngay_bat_dau']))."</td>";
                        echo "<td>".date('d/m/Y', strtotime($row['ngay_ket_thuc']))."</td>";
                        echo "<td>".$row['mo_ta']."</td>";
                        echo "<td>
                                <button onclick='deletePromotion(\"".$row['ma_so_khuyen_mai']."\")' class='btn-delete'>Xóa</button>
                                <button onclick='editPromotion(\"".$row['ma_so_khuyen_mai']."\")' class='btn-edit'>Sửa</button>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .container {
            margin-top: 30px;
            padding: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #2196F3;
            color: white;
            font-weight: 600;
        }

        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tr:hover {
            background-color: #f5f5f5;
        }

        .btn-delete, .btn-edit {
            padding: 6px 12px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-edit {
            background-color: #ffc107;
            color: black;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        @media screen and (max-width: 768px) {
            .table {
                font-size: 14px;
            }
            
            .table th, .table td {
                padding: 8px 10px;
            }
        }
    </style>

    <script>
        function deletePromotion(maKM) {
            if(confirm('Bạn có chắc chắn muốn xóa mã khuyến mãi này?')) {
                window.location.href = '?delete=' + maKM;
            }
        }

        function editPromotion(maKM) {
            window.location.href = 'edit_khuyenmai.php?ma_km=' + maKM;
        }
    </script>
</body>
</html>
