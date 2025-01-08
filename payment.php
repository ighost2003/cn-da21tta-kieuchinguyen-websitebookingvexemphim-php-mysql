<?php
session_start();

// Kiểm tra và xóa bộ đệm đầu ra nếu có dữ liệu
if (ob_get_length()) ob_end_clean();

include_once("db/connect.php");

// Lấy URL hiện tại
$current_url = $_SERVER['REQUEST_URI'];

// Loại bỏ thư mục đầu tiên
$trimmed_url = preg_replace('/^\/[^\/]+\//', '/', $current_url);

// Kiểm tra nếu $trimmed_url là "/payment.php?controller=listmovies"
if ($trimmed_url === "/payment.php?controller=listmovies") {
    // Chuyển hướng đến index.php
    header("Location: index.php?controller=listmovies");
    exit;
}
if ($trimmed_url === "/payment.php?controller=listTheater") {
    // Chuyển hướng đến index.php
    header("Location: index.php?controller=listTheater");
    exit;
}
if ($trimmed_url === "/payment.php?controller=listnews") {
    // Chuyển hướng đến index.php
    header("Location: index.php?controller=listnews");
    exit;
}


// Không include header.php trước khi kiểm tra chuyển hướng
include("include/header.php");

// Lấy email từ session
$email = $_SESSION['user'] ?? '';

// Hiển thị URL đã được xử lý
//--
// echo "<div style='margin: 20px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;'>
//         <p style='margin: 0; color: #333;'>URL đã xử lý: <strong>" . htmlspecialchars($trimmed_url) . "</strong></p>
//       </div>";


      
// Đảm bảo không có đầu ra trước khi chuyển hướng
if (isset($_GET['controller']) && $_GET['controller'] === 'listmovies') {
    header("Location: index.php?controller=listmovies");
    exit;
}
if (isset($_GET['controller']) && $_GET['controller'] === 'listTheater') {
    header("Location: index.php?controller=listTheater");
    exit;
}
if (isset($_GET['controller']) && $_GET['controller'] === 'listnews') {
    header("Location: index.php?controller=listnews");
    exit;
}



if (!empty($email)) {
    //echo "<h2 style='text-align: right;'>Xin chào, " . htmlspecialchars($email) . "!</h2>";

    // Kết nối đến cơ sở dữ liệu
    //$mysqli = new mysqli("localhost", "root", "", "cgvdb");

    // Kiểm tra kết nối
    //if ($mysqli->connect_error) {
        //die("Kết nối thất bại: " . $mysqli->connect_error);
   // }



    // Truy vấn để lấy user_id
    $stmt = $mysqli->prepare("SELECT `user_id` FROM `users` WHERE `user_email` = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();
    echo "<br>";

    // Hiển thị user_id
    if (isset($user_id)) {
        //echo "<p>User ID: " . htmlspecialchars($user_id) . "</p>";

        // Truy vấn để lấy thông tin đặt chỗ
        $booking_stmt = $mysqli->prepare("SELECT b.`booking_id`, b.`booking_user`, b.`booking_movie`, m.`movie_name`, b.`booking_theater`, t.`theaters_address`, b.`booking_seat`, b.`booking_ticket`, b.`booking_time`, b.`trangthai_tt` 
                                           FROM `booking` b 
                                           JOIN `movie` m ON m.`movie_id` = b.`booking_movie` 
                                           JOIN `theaters` t ON t.`theaters_id` = b.`booking_theater` 
                                           WHERE b.`booking_user` = ?");
        $booking_stmt->bind_param("i", $user_id);
        $booking_stmt->execute();
        $result = $booking_stmt->get_result();

        if ($result->num_rows > 0) {  // Check if there are results
            // Hiển thị bảng đặt chỗ
            echo "<style>
            h3 {
                text-align: center;
                font-size: 24px;
                color: #333;
                font-weight: bold;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            th, td {
                padding: 12px;
                text-align: center;
                border: 1px solid #ddd;
            }
            th {
                background-color: #4CAF50;
                color: white;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #ddd;
            }
            input[type='text'], input[type='submit'] {
                padding: 8px;
                border-radius: 4px;
                margin: 5px 0;
                font-size: 14px;
            }
            input[type='text'] {
                width: 200px;
                border: 1px solid #ddd;
            }
            input[type='submit'] {
                background-color: #4CAF50;
                color: white;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            input[type='submit']:hover {
                background-color: #45a049;
            }
        </style>";
    
    echo "<h3>Thông tin đặt chỗ:</h3>";
    echo '<a href="all_payment.php" style="display: inline-block; padding: 10px 20px; background-color: red; color: white; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 0 auto; display: block;">THANH TOÁN TẤT CẢ VÉ</a>';

    echo "<table>
            <tr>
                <th>Booking ID</th>
                <th>Movie</th>
                <th>Theater</th>
                <th>Address</th>
                <th>Seat</th>
                <th>Ticket</th>
                <th>Time</th>
                <th>Trạng thái</th>
                <th>Action</th>
            </tr>";

            while ($row = $result->fetch_assoc()) {
                $status = ($row['trangthai_tt'] == 0) ? 'Chưa thanh toán' : 'Đã thanh toán';
                $amount = ($row['booking_ticket'] == "Vé phim 2D") ? 45000 : (($row['booking_ticket'] == "Vé phim 3D") ? 75000 : 0);
                $quicklink_url = "#qr";

                echo "<style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        input[type='submit'] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        input[type='submit']:hover {
            background-color: #45a049;
        }
    </style>";
    echo "<tr>
    <td>" . htmlspecialchars($row['booking_id']) . "</td>
    <td>" . htmlspecialchars($row['movie_name']) . "</td>
    <td>" . htmlspecialchars($row['booking_theater']) . "</td>
    <td>" . htmlspecialchars($row['theaters_address']) . "</td>
    <td>" . htmlspecialchars($row['booking_seat']) . "</td>
    <td>" . htmlspecialchars($row['booking_ticket']) . "</td>
    <td>" . htmlspecialchars($row['booking_time']) . "</td>
    <td>" . htmlspecialchars($status) . "</td>
    <td>
        <form method='POST' action=''>
            <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['booking_id']) . "' />
            <input type='hidden' name='movie_name' value='" . htmlspecialchars($row['movie_name']) . "' />
            <input type='hidden' name='ticket_type' value='" . htmlspecialchars($row['booking_ticket']) . "' />
            <input type='submit' name='create_qr' value='Tạo QR' />
            <input type='submit' name='delete_booking' value='Xóa' 
                   onclick=\"if(confirm('Bạn có chắc chắn muốn xóa đặt chỗ này?')) { 
                                setTimeout(() => window.location.reload(), 500); 
                                return true; 
                            } else { 
                                return false; 
                            }\" />
        </form>
    </td>
  </tr>";



                $product_name = htmlspecialchars($row['booking_ticket']);  // Assign booking_ticket to product_name
                $product_code = htmlspecialchars($row['booking_id']);  // Mã sản phẩm
            }

            echo "</table>";
        } else {
            echo "<p>No bookings found for this user.</p>";  // Handle case with no results
        }

        // Đóng truy vấn đặt chỗ
        $booking_stmt->close();
    } else {
        echo "<p>User ID không tìm thấy.</p>";
    }

    // Lấy thông tin thanh toán
    $amount = 0;  // Khởi tạo số tiền

    // Lấy loại vé từ cơ sở dữ liệu
    $loaive_stmt = $mysqli->prepare("SELECT `booking_ticket` FROM `booking` WHERE `booking_user` = ?");
    $loaive_stmt->bind_param("i", $user_id);
    $loaive_stmt->execute();
    $loaive_stmt->bind_result($loaive);
    $loaive_stmt->fetch();
    $loaive_stmt->close();

    // Kiểm tra giá trị của $user_id và $loaive
    //echo "User ID: " . htmlspecialchars($user_id) . "<br>";
    //echo "Loại vé: " . htmlspecialchars($loaive) . "<br>";

    // Loại bỏ khoảng trắng và kiểm tra lại
    $loaive = trim($loaive);

    // Xác định số tiền dựa trên loại vé

    //---------------------------------------------------?

    if (strpos($loaive, '2') !== false) {
        $amount = 45000;
    } elseif (strpos($loaive, '3') !== false) {
        $amount = 75000;
    }

    // Kiểm tra giá trị của $amount
    //echo "Số tiền: " . $amount . "<br>";

    $product_code = "";  // Initialize the variable
    // Lấy mã sản phẩm mới nhất từ bảng `port_duphong`

    $latest_product_code_stmt = $mysqli->prepare("SELECT `code` FROM `port_duphong` WHERE `id_user` = ? ORDER BY `ngay_tao` DESC LIMIT 1");
    $latest_product_code_stmt->bind_param("i", $user_id);
    $latest_product_code_stmt->execute();
    $latest_product_code_stmt->bind_result($product_code);
    $latest_product_code_stmt->fetch();
    $latest_product_code_stmt->close();

    // Nếu người dùng nhấn "Tạo QR", thực hiện INSERT vào bảng `port_duphong`
    if (isset($_POST['create_qr'])) {
        $booking_id = $_POST['booking_id'];
        $movie_name = $_POST['movie_name'];
        $ticket_type = $_POST['ticket_type']; // Loại vé từ người dùng gửi
        $current_time = date("Y-m-d H:i:s");

        // Xác định số tiền dựa trên temp_money hoặc loại vé
        $loaive_stmt = $mysqli->prepare("SELECT `temp_money`, `booking_ticket` FROM `booking` WHERE `booking_id` = ?");
        $loaive_stmt->bind_param("i", $booking_id);
        $loaive_stmt->execute();
        $loaive_stmt->bind_result($temp_money, $booking_ticket);
        $loaive_stmt->fetch();
        $loaive_stmt->close();

        if(!empty($temp_money)){
    
        $amount = !empty($temp_money) ? $temp_money : (($booking_ticket == "Vé phim 2D") ? 45000 : (($booking_ticket == "Vé phim 3D") ? 75000 : 0));
        $formatted_time = date("YmdHis");
        $code = $user_id . $booking_id . "-" . $formatted_time;


    // Đảm bảo luôn sử dụng $booking_ticket cho info_ticket
    $info_ticket = $booking_ticket;

    // Kiểm tra giá trị của $info_ticket
    if (empty($info_ticket)) {
        echo "Error: 'info_ticket' is empty.";
        exit;
    }

    // Đảm bảo info_ticket không vượt quá độ dài 255 ký tự
    if (strlen($info_ticket) > 255) {
        $info_ticket = substr($info_ticket, 0, 255); // Cắt ngắn nếu quá 255 ký tự
    }

    // Chuyển kiểu dữ liệu nếu cần
    $user_id = (int)$user_id;
    $booking_id = (int)$booking_id;
    $amount = (float)$amount;

    // Thực hiện câu lệnh INSERT trực tiếp vào bảng `port_duphong`
    $sql = "INSERT INTO `port_duphong` (`id_user`, `id_bk`, `ten`, `code`, `ngay_tao`, `sotien`, `info_ticket`) 
            VALUES ('$user_id', '$booking_id', '$movie_name', '$code', '$current_time', '$amount', '$info_ticket')";

        //__________________________________________________________
        // Cập nhật phương thức thanh toán
        $update_stmt = $mysqli->prepare("UPDATE `booking` SET `payment_method` = 'online' WHERE `booking_id` = ?");
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Tạo mã QR sau khi INSERT thành công
         if ($mysqli->query($sql)) {
        echo "Mã QR đã được tạo. Loại vé: " . htmlspecialchars($info_ticket, ENT_QUOTES, 'UTF-8') . ".";
    } else {
        echo "Error in INSERT: " . $mysqli->error;
    }

    //_______________________________________---------------------________
    // Chuẩn bị câu lệnh truy vấn
    $kqtvv = ""; // Khởi tạo biến kết quả

    // Câu lệnh SELECT để lấy thông tin vé
    $vexemphim = "SELECT `info_ticket` FROM `port_duphong` WHERE `id_user` = ? ORDER BY `ngay_tao` DESC LIMIT 1";
    
    // Chuẩn bị câu lệnh SQL
    $stmt = $mysqli->prepare($vexemphim);
    
    // Liên kết tham số (ở đây là id_user)
    $stmt->bind_param("i", $user_id);
    
    // Thực thi câu lệnh
    $stmt->execute();
    
    // Liên kết kết quả trả về vào biến
    $stmt->bind_result($kqtvv);
    
    // Lấy kết quả
    if ($stmt->fetch()) {
       // echo "Info Ticket: " . htmlspecialchars($kqtvv, ENT_QUOTES, 'UTF-8'); // Hiển thị thông tin vé
    } else {
        echo "Không tìm thấy thông tin vé."; // Nếu không có kết quả
    }
    
    // Đóng câu lệnh chuẩn bị
    $stmt->close();
    
    // Gán giá trị cho $product_name
    $product_name = $kqtvv;
    
//######################################

        
        // Tạo URL cho QR code VietQR
        $bank_id = "mbbank";
        $account_no = "1234567886699";
        $template = "compact2";
        $account_name = "KIEU CHI NGUYEN";
        $add_info = $code;  // Sử dụng mã code vừa tạo

        // Tạo URL cho QR
        $quicklink_url = "https://img.vietqr.io/image/$bank_id-$account_no-$template.png?amount=$amount&addInfo=$add_info&accountName=$account_name";


}

else{

// Xác định số tiền dựa trên loại vé
if (strpos($ticket_type, '2') !== false) {
    $amount = 45000;
} elseif (strpos($ticket_type, '3') !== false) {
    $amount = 75000;
} else {
    $amount = 0; // Default value if ticket type is not recognized
}

$formatted_time = date("YmdHis");  // Format the current time as YYYYMMDDHHMMSS
$code = "defaut hay bam tao ma QR de thanh toan ve";  // Giá trị mặc định
if (isset($_POST['create_qr'])) {
    $code = $user_id . $booking_id . "-" . $formatted_time;  // Mã code sẽ là sự kết hợp của user_id và booking_id
}

// Thực hiện câu lệnh INSERT vào bảng `port_duphong`
$insert_stmt = $mysqli->prepare("INSERT INTO `port_duphong` (`id_user`, `id_bk`, `ten`, `code`, `ngay_tao`, `sotien`) 
                                VALUES (?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param("iisssd", $user_id, $booking_id, $movie_name, $code, $current_time, $amount);
$insert_stmt->execute();

// Cập nhật phương thức thanh toán
$update_stmt = $mysqli->prepare("UPDATE `booking` SET `payment_method` = 'online' WHERE `booking_id` = ?");
$update_stmt->bind_param("i", $booking_id);
$update_stmt->execute();
$update_stmt->close();

// Tạo mã QR sau khi INSERT thành công
echo "<p>Mã QR đã được tạo và thông tin đã được lưu vào bảng `port_duphong`.</p>";

// Tạo URL cho QR code VietQR
$bank_id = "mbbank";
$account_no = "1234567886699";
$template = "compact2";
$account_name = "KIEU CHI NGUYEN";
$add_info = $code;  // Sử dụng mã code vừa tạo

// Tạo URL cho QR
$quicklink_url = "https://img.vietqr.io/image/$bank_id-$account_no-$template.png?amount=$amount&addInfo=$add_info&accountName=$account_name";


}
    }

        // Handle deletion of booking
        if (isset($_POST['delete_booking'])) {
            $booking_id = $_POST['booking_id']; // Get the booking ID from the form
            $delete_stmt = $mysqli->prepare("DELETE FROM `booking` WHERE `booking_id` = ?");
            $delete_stmt->bind_param("i", $booking_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            echo "<p>Đặt chỗ đã được xóa thành công!Booking id:" . htmlspecialchars($booking_id) . "</p>"; // Confirmation message
        }

    echo '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="styles.css">';
    // echo '<link rel="stylesheet" href="css/footer.css">';
    echo '<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-minimal/minimal.css" rel="stylesheet">';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';


    echo '
    <link rel="icon" href="./img/icon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick-theme.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="./css/movieD.css">
    <link rel="stylesheet" href="./css/listEvent.css">
    <link rel="stylesheet" href="./css/listMovies.css">
    <link rel="stylesheet" href="./css/newsD.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/listTheater.css">
    <link rel="stylesheet" href="./css/user.css">
    <link rel="stylesheet" href="css/footer.css"> <!-- Đường dẫn tới footer.css -->
';
echo '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
    <script src="./js/index.js"></script>
    <script src="./js/listTheater.js"></script>
    <script src="./js/movieD.js"></script>
    <script src="./js/userInfo.js"></script>
';
    //---
    // Thêm CSS tùy chỉnh cho dialog
    echo '<link rel="stylesheet" href="style.css">';

    echo "<h1>Mã QR thanh toán</h1>"; 
    echo '<div class="qr-container" id="qr">';
    echo '<div class="product-info">';
    echo "<p><strong>Số tiền:</strong> " . number_format($amount) . " VNĐ</p>";
    echo "<p><strong>Thông tin:</strong> $product_name</p>";
       // Kiểm tra nếu $code rỗng
       if (empty($code)) {
        echo "<p>Vui lòng bấm vào tạo mã QR để thanh toán.</p>";
    } else {
        echo "<p><strong>Mã Thanh Toán:</strong> $code</p>";
    }
    echo "</div>";
    echo '<div class="qr-image">';
    echo "<img src='$quicklink_url' alt='VietQR' />";
    echo "</div>";
    echo "</div>";

    // Lấy thông tin sản phẩm mới nhất từ bảng `port_duphong`
    $delete_stmt = $mysqli->prepare("DELETE FROM `port_duphong` WHERE `id_user` = ? AND `code` NOT IN (SELECT `code` FROM (SELECT `code` FROM `port_duphong` WHERE `id_user` = ? ORDER BY `ngay_tao` DESC LIMIT 1) AS temp)");
    $delete_stmt->bind_param("ii", $user_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Đóng kết nối
    $mysqli->close();
}

echo'<br>';
echo'<br>';
echo'<br>';
echo'<br>';
       

include("include/footer.php");

?>
