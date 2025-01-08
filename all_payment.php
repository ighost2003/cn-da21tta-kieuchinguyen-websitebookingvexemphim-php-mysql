<?php
include_once("db/connect.php");
session_start();


?>

<!DOCTYPE html>
<html>
<head>
    <title>Site</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- -------------------------------- -->
    <link rel="icon" href="./img/icon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick-theme.min.css'>
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
    <style>
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
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.qr-image {
    position: relative;
    display: inline-block;
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
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
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

    </style>

</head>
<body class="body-effect">
<?php
// Kiểm tra URL hiện tại và chuyển hướng nếu cần
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


// Display user information if logged in
//--
if (isset($_SESSION['user'])) {
    //echo "<p>Welcome, " . htmlspecialchars($_SESSION['user']) . "!</p>";

    // Retrieve user ID from the database
    $username = htmlspecialchars($_SESSION['user']);
    $result = mysqli_query($mysqli, "SELECT `user_id` FROM `users` WHERE `user_email` = '$username'");
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $id_user = $row['user_id'];
        //echo "<p>Your User ID: " . htmlspecialchars($id_user) . "</p>";
    } else {
        echo "<p>Error retrieving user ID.</p>";
    }
} else {
    echo "<p>Please log in to access your account.</p>";
}

// Khởi tạo tổng tiền
$all_money = 0;

// Tổng 1: Cộng dồn temp_money nếu khác null hoặc khác 0
$total_temp_money = 0;
$stmt_temp_money = $mysqli->prepare("
    SELECT `temp_money`
    FROM `booking`
    WHERE `booking_user` = ?
");
$stmt_temp_money->bind_param("i", $id_user); // Changed $user_id to $id_user
$stmt_temp_money->execute();
$stmt_temp_money->bind_result($temp_money);

// Duyệt qua các bản ghi và cộng dồn temp_money
while ($stmt_temp_money->fetch()) {
    if (!empty($temp_money) && $temp_money != 0) {
        $total_temp_money += $temp_money; // Cộng dồn temp_money
    }
}
$stmt_temp_money->close();

// Tổng 2: Cộng tiền vé nếu temp_money bằng 0 hoặc rỗng
$total_ticket_price = 0;
$stmt_ticket_price = $mysqli->prepare("
    SELECT `booking_ticket`, `temp_money`
    FROM `booking`
    WHERE `booking_user` = ?
");
$stmt_ticket_price->bind_param("i", $id_user); // Changed $user_id to $id_user
$stmt_ticket_price->execute();
$stmt_ticket_price->bind_result($booking_ticket, $temp_money);

while ($stmt_ticket_price->fetch()) {
    // Chỉ tính tiền vé nếu temp_money bằng 0 hoặc rỗng
    if (empty($temp_money) || $temp_money == 0) {
        if (strpos($booking_ticket, '2') !== false) {
            $total_ticket_price += 45000; // Vé 2D
        }
        if (strpos($booking_ticket, '3') !== false) {
            $total_ticket_price += 75000; // Vé 3D
        }
    }
}
$stmt_ticket_price->close();

// Tổng tất cả: Tổng 1 + Tổng 2
$all_money = $total_temp_money + $total_ticket_price;

// Hiển thị tổng tiền
//--
echo "<p>Tổng tiền: " . htmlspecialchars($all_money) . " VNĐ</p>";

// Gán $amount bằng $all_money
$amount = $all_money;

// Tạo URL cho QR code VietQR
$bank_id = "mbbank";
$account_no = "1234567886699";
$template = "compact2";
$account_name = "KIEU CHI NGUYEN";
$add_info = "All-ticket".$id_user;  // Sử dụng mã code vừa tạo

// Tạo URL cho QR
$quicklink_url = "https://img.vietqr.io/image/$bank_id-$account_no-$template.png?amount=$amount&addInfo=$add_info&accountName=$account_name";

// Cập nhật phương thức thanh toán khi tải trang
$stmt_update_payment = $mysqli->prepare("UPDATE `booking` SET `payment_method`='online' WHERE `booking_user` = ?");
$stmt_update_payment->bind_param("i", $id_user);
$stmt_update_payment->execute();
$stmt_update_payment->close();

?>
<script async src="https://dochat.vn/code.js?id=9241223012513217920"></script>
<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<script>
    window.fbAsyncInit = function() {
        FB.init({
            xfbml            : true,
            version          : 'v7.0'
        });
    };

    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

<!-- Your Chat Plugin code -->
<div class="fb-customerchat"
     attribution=setup_tool
     page_id="111493383903916"
     theme_color="#fa3c4c"
     logged_in_greeting="Xin chào, tôi có thể giúp gì cho bạn"
     logged_out_greeting="Xin chào, tôi có thể giúp gì cho bạn">
</div>
    <?php
     include("include/header.php");
          
    ?>

<div class="qr-container">
    <h1>Quét mã QR để thanh toán</h1>
    <div class="qr-image">
        <img src="<?php echo htmlspecialchars($quicklink_url); ?>" alt="QR Code">
    </div>
</div>

   

   
    <!-- ----------------------------------------------- -->

    <?php
       

        include("include/footer.php");
    ?>
    <!-- partial -->
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.5.9/slick.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
    <script src="./js/index.js"></script>
    <script src="./js/listTheater.js"></script>
    <script src="./js/movieD.js"></script>
    <script src="./js/userInfo.js"></script>
    <!-- Hiển thị hình ảnh QR code -->
    
</body>
</html>
