<?php
include_once("db/connect.php");
session_start();

echo '<a href="index.php"><img src="./img/cgvlogo.png" alt="" style="position: absolute; top: 10px; left: 10px; width: 180px; transition: transform 0.3s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"></a>';

$email = isset($_GET['email']) ? $_GET['email'] : '';

if (!empty($email)) {
    echo "<h2 style='text-align: right;'>Xin chào, " . htmlspecialchars($email) . "!</h2>";

    $mysqli = new mysqli("localhost", "root", "", "cgvdb");

    if ($mysqli->connect_error) {
        die("Kết nối thất bại: " . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("SELECT user_id, use_code FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $use_code);
    $stmt->fetch();
    $stmt->close();

    if (isset($user_id)) {
        $booking_stmt = $mysqli->prepare("SELECT b.booking_id, b.booking_movie, m.movie_name, b.booking_ticket, b.trangthai_tt, b.temp_money 
                                           FROM booking b 
                                           JOIN movie m ON m.movie_id = b.booking_movie 
                                           WHERE b.booking_user = ?");
        $booking_stmt->bind_param("i", $user_id);
        $booking_stmt->execute();
        $result = $booking_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<br>";
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
            margin: 20px 0;
            border-collapse: collapse;
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
        .apply-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .apply-btn:hover {
            background-color: #45a049;
        }
    </style>";

echo "<h3>Thông tin đặt chỗ:</h3>";

echo "<table>
        <tr>
            <th>Booking ID</th>
            <th>Movie</th>
            <th>Ticket</th>
            <th>Price</th>
            <th>Action</th>
        </tr>";

            while ($row = $result->fetch_assoc()) {
                $status = ($row['trangthai_tt'] == 0) ? 'Chưa thanh toán' : 'Đã thanh toán';
                $price = $row['temp_money'];

                // Check if temp_money is NULL, and if so, update it with the base price
                if (is_null($price)) {
                    $price_stmt = $mysqli->prepare("SELECT c.price FROM category_ticket c WHERE c.name = ?");
                    $price_stmt->bind_param("s", $row['booking_ticket']);
                    $price_stmt->execute();
                    $price_stmt->bind_result($base_price);
                    $price_stmt->fetch();
                    $price_stmt->close();

                    if ($base_price) {
                        // Update temp_money with the base price
                        $update_price_stmt = $mysqli->prepare("UPDATE booking SET temp_money = ? WHERE booking_id = ?");
                        $update_price_stmt->bind_param("di", $base_price, $row['booking_id']);
                        $update_price_stmt->execute();
                        $update_price_stmt->close();

                        $price = $base_price;  // Use base price for display
                    }
                }

                echo "<style>
                table {
                    width: 100%;
                    margin: 20px 0;
                    border-collapse: collapse;
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
                input[type='text'] {
                    padding: 8px;
                    width: 200px;
                    margin: 5px 0;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                }
                input[type='submit'] {
                    background-color: #4CAF50;
                    color: white;
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
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
                    <td>" . htmlspecialchars($row['booking_ticket']) . "</td>
                    <td>" . number_format($price) . " VNĐ</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['booking_id']) . "' />
                            <input type='text' name='promo_code' placeholder='Nhập mã khuyến mãi' required />
                            <input type='submit' name='apply_promo' value='Áp dụng' />
                        </form>
                    </td>
                </tr>";
            }

            echo "</table>";
        } else {
            echo "<p>Không có thông tin đặt chỗ.</p>";
        }

        $booking_stmt->close();
    } else {
        echo "<p>User ID không tìm thấy.</p>";
    }

    if (isset($_POST['apply_promo'])) {
        $booking_id = $_POST['booking_id'];
        $promo_code = trim($_POST['promo_code']);

        // Kiểm tra mã khuyến mãi đã được sử dụng chưa
        if (strpos($use_code, $promo_code) !== false) {
            echo "<script>alert('Mã khuyến mãi đã được sử dụng trước đó.');</script>";
        } else {
            // Lấy thông tin khuyến mãi
            $promo_stmt = $mysqli->prepare("SELECT gia_tri_khuyen_mai FROM code_khuyen_mai WHERE ma_so_khuyen_mai = ? AND trang_thai = 1 AND ngay_bat_dau <= NOW() AND ngay_ket_thuc >= NOW()");
            $promo_stmt->bind_param("s", $promo_code);
            $promo_stmt->execute();
            $promo_stmt->bind_result($discount_value);
            $promo_stmt->fetch();
            $promo_stmt->close();

            if ($discount_value) {
                // Lấy giá tiền gốc
                $price_stmt = $mysqli->prepare("SELECT c.price FROM category_ticket c JOIN booking b ON b.booking_ticket = c.name WHERE b.booking_id = ?");
                $price_stmt->bind_param("i", $booking_id);
                $price_stmt->execute();
                $price_stmt->bind_result($base_price);
                $price_stmt->fetch();
                $price_stmt->close();

                if ($base_price) {
                    $new_price = max(0, $base_price - $discount_value);

                    // Cập nhật giá tiền tạm
                    $update_price_stmt = $mysqli->prepare("UPDATE booking SET temp_money = ? WHERE booking_id = ?");
                    $update_price_stmt->bind_param("di", $new_price, $booking_id);
                    $update_price_stmt->execute();
                    $update_price_stmt->close();

                    // Cập nhật use_code để lưu mã khuyến mãi đã sử dụng
                    $update_code_stmt = $mysqli->prepare("UPDATE users SET use_code = CONCAT(IFNULL(use_code, ''), ?, ',') WHERE user_id = ?");
                    $update_code_stmt->bind_param("si", $promo_code, $user_id);
                    $update_code_stmt->execute();
                    $update_code_stmt->close();

                    echo "<script>alert('Mã khuyến mãi áp dụng thành công!'); window.location.href = 'nhapmakm.php?email=' + encodeURIComponent('" . $_SESSION['user'] . "');</script>";
                } else {
                    echo "<script>alert('Giá tiền gốc không tìm thấy.');</script>";
                }
            } else {
                echo "<script>alert('Mã khuyến mãi không hợp lệ hoặc đã hết hạn.');</script>";
            }
        }
    }

    $mysqli->close();
}
?>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }
    
    .logo {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 180px;
        transition: transform 0.3s;
    }

    .logo:hover {
        transform: scale(1.1);
    }

    h2.greeting {
        text-align: right;
        margin: 20px;
        font-size: 24px;
        color: #333;
    }

    h3.booking-title {
        font-size: 28px;
        color: #333;
        margin: 20px;
    }

    .booking-table {
        width: 100%;
        margin: 20px;
        border-collapse: collapse;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .booking-table th, .booking-table td {
        padding: 12px;
        text-align: center;
        border: 1px solid #ddd;
    }

    .booking-table th {
        background-color: #4CAF50;
        color: white;
    }

    .booking-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .booking-table tr:hover {
        background-color: #ddd;
    }

    .apply-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 8px 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .apply-btn:hover {
        background-color: #45a049;
    }

    .no-booking, .error {
        text-align: center;
        font-size: 18px;
        color: #d9534f;
        margin-top: 20px;
    }

    .no-booking {
        color: #5bc0de;
    }

    .error {
        color: #d9534f;
    }
</style>