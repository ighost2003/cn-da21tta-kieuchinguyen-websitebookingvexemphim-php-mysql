<?php
    include_once("db/connect.php");
    session_start();

    // Function to fetch bookings
    // Function to fetch bookings
function fetchBookings($bookingId = null) {
    global $mysqli;
    $query = "SELECT `booking_id`, `booking_user`, `booking_movie`, `booking_theater`, `booking_seat`, `booking_ticket`, `booking_time`, `trangthai_tt`, `payment_method` FROM `booking`";
    if ($bookingId) {
        $query .= " WHERE `booking_id` = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $bookingId);
    } else {
        $stmt = $mysqli->prepare($query);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to update a booking
function updateBooking($bookingId, $user, $movie, $theater, $seat, $ticket, $time, $status, $paymentMethod) {
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE `booking` SET `booking_user` = ?, `booking_movie` = ?, `booking_theater` = ?, `booking_seat` = ?, `booking_ticket` = ?, `booking_time` = ?, `trangthai_tt` = ?, `payment_method` = ? WHERE `booking_id` = ?");
    $stmt->bind_param("ssssssssi", $user, $movie, $theater, $seat, $ticket, $time, $status, $paymentMethod, $bookingId);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to delete a booking
function deleteBooking($bookingId) {
    global $mysqli;
    $stmt = $mysqli->prepare("DELETE FROM `booking` WHERE `booking_id` = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Function to update payment status
function updatePaymentStatus($bookingId) {
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE `booking` SET `trangthai_tt` = 1 WHERE `booking_id` = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'])) {
        updateBooking($_POST['booking_id'], $_POST['booking_user'], $_POST['booking_movie'], $_POST['booking_theater'], $_POST['booking_seat'], $_POST['booking_ticket'], $_POST['booking_time'], $_POST['trangthai_tt'], $_POST['payment_method']);
    } elseif (isset($_POST['delete'])) {
        deleteBooking($_POST['booking_id']);
    } elseif (isset($_POST['confirm_payment'])) {
        updatePaymentStatus($_POST['booking_id']);
    } elseif (isset($_POST['search'])) {
        $bookings = fetchBookings($_POST['booking_id']);
    }
} else {
    $bookings = fetchBookings(); // Lấy tất cả đặt chỗ nếu không có tìm kiếm
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thanh toán</title>
    <style>
        /* styles.css */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            margin-bottom: 20px;
            text-align: center;
        }

        input[type="text"] {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 8px 12px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4cae4c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #5bc0de;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<a href="admin.php" style="position: absolute; top: 20px; left: 20px;">
    <!-- {{ edit_1 }} -->
    <i class="fas fa-home" style="font-size: 30px;"></i>
    <!-- {{ edit_1 }} -->
</a>

    <h1>Quản lý thanh toán</h1>

    <!-- Form tìm kiếm -->
    <form method="POST">
        <label for="booking_id">Tìm kiếm Booking ID:</label>
        <input type="text" name="booking_id" id="booking_id" required>
        <button type="submit" name="search">Tìm</button>
    </form>

    <table border="1">
        <tr>
            <th>Booking ID</th>
            <th>User</th>
            <th>Movie</th>
            <th>Theater</th>
            <th>Seat</th>
            <th>Ticket</th>
            <th>Time</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($bookings as $booking): ?>
        <tr>
            <form method="POST">
                <td><?php echo $booking['booking_id']; ?></td>
                <td><input type="text" name="booking_user" value="<?php echo $booking['booking_user']; ?>"></td>
                <td><input type="text" name="booking_movie" value="<?php echo $booking['booking_movie']; ?>"></td>
                <td><input type="text" name="booking_theater" value="<?php echo $booking['booking_theater']; ?>"></td>
                <td><input type="text" name="booking_seat" value="<?php echo $booking['booking_seat']; ?>"></td>
                <td><input type="text" name="booking_ticket" value="<?php echo $booking['booking_ticket']; ?>"></td>
                <td><input type="text" name="booking_time" value="<?php echo $booking['booking_time']; ?>"></td>
                <td><input type="text" name="trangthai_tt" value="<?php echo $booking['trangthai_tt']; ?>"></td>
                <td><input type="text" name="payment_method" value="<?php echo $booking['payment_method']; ?>"></td>
                <td>
                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                    <button type="submit" name="update">Cập nhật</button>
                    <button type="submit" name="delete">Xóa</button>
                    <?php if ($booking['trangthai_tt'] == 0): ?>
                        <button type="submit" name="confirm_payment">Xác nhận thanh toán</button>
                    <?php endif; ?>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>