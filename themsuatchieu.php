<?php
include_once("db/connect.php"); // Đảm bảo kết nối cơ sở dữ liệu
echo '<a href="admin.php"><img src="./img/cgvlogo.png" alt="" style="position: absolute; top: 10px; left: 10px; width: 180px; transition: transform 0.3s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"></a>';
echo "<style>
        h3 {
            font-size: 36px; /* Tăng kích cỡ chữ */
            text-align: center; /* Căn giữa chữ */
            transition: transform 0.3s ease-in-out, color 0.3s ease; /* Hiệu ứng động */
        }
        h3:hover {
            transform: scale(1.1); /* Tăng kích thước khi hover */
            color: #ff6347; /* Thay đổi màu chữ khi hover */
        }
      </style>";
echo "<h3>THÊM SUẤT CHIẾU</h3>";

// Lấy danh sách phim
$query_movies = "SELECT movie_id, movie_name FROM movie WHERE 1";
$result_movies = $mysqli->query($query_movies);

// Lấy danh sách phòng theo rạp
$query_theaters = "SELECT theaters_id, theaters_name, theaters_city FROM theaters WHERE 1";
$result_theaters = $mysqli->query($query_theaters);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy giá trị từ form
    $movie_id = $_POST['showings_name_movie'];
    $room_id = $_POST['showings_room'];
    $showings_time = $_POST['showings_time'];

    // Chèn dữ liệu vào bảng showings
    $insert_query = "INSERT INTO showings (showings_name_movie, showings_room, showings_time) 
                     VALUES (?, ?, ?)";
    
    if ($stmt = $mysqli->prepare($insert_query)) {
        $stmt->bind_param("iis", $movie_id, $room_id, $showings_time);
        if ($stmt->execute()) {
            echo '<p>Website status is good or Movie showing added successfully</p>';
        } else {
            echo "<p>Lỗi khi chèn dữ liệu.</p>";
        }
        $stmt->close();
    }
}
?>

<form method="POST" action="">
    <label for="showings_name_movie">Chọn phim:</label>
    <select name="showings_name_movie" id="showings_name_movie" required>
        <option value="">Chọn phim</option>
        <?php
        while ($movie = $result_movies->fetch_assoc()) {
            echo "<option value='" . $movie['movie_id'] . "'>" . $movie['movie_name'] . "</option>";
        }
        ?>
    </select>
    <br><br>

    <label for="showings_room">Chọn phòng:</label>
    <select name="showings_room" id="showings_room" required>
        <option value="">Chọn phòng</option>
        <?php
        // Lặp qua các rạp và phòng của từng rạp
        while ($theater = $result_theaters->fetch_assoc()) {
            // Lấy các phòng từ từng rạp
            $theater_id = $theater['theaters_id'];
            $query_rooms = "SELECT room_id, room_name FROM room WHERE room_theater = ?";
            if ($room_stmt = $mysqli->prepare($query_rooms)) {
                $room_stmt->bind_param("i", $theater_id);
                $room_stmt->execute();
                $room_result = $room_stmt->get_result();
                echo "<optgroup label='" . $theater['theaters_name'] . " - " . $theater['theaters_city'] . "'>";
                while ($room = $room_result->fetch_assoc()) {
                    echo "<option value='" . $room['room_id'] . "'>" . $room['room_name'] . "</option>";
                }
                echo "</optgroup>";
                $room_stmt->close();
            }
        }
        ?>
    </select>
    <br><br>

    <label for="showings_time">Chọn thời gian:</label>
    <input type="datetime-local" name="showings_time" id="showings_time" required>
    <br><br>

    <input type="submit" value="Thêm suất chiếu">
</form>
<style>
    /* Thiết lập nền và căn chỉnh form */
form {
    width: 60%;
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Căn chỉnh nhãn và input trong form */
form label {
    display: block;
    margin-bottom: 8px;
    font-size: 16px;
    color: #333;
    font-weight: bold;
}

/* Style cho select box */
form select,
form input[type="datetime-local"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

/* Thêm hiệu ứng focus cho select và input */
form select:focus,
form input[type="datetime-local"]:focus {
    border-color: #3498db;
    outline: none;
}

/* Style cho nút submit */
/* Căn giữa nút submit */
form input[type="submit"] {
    background-color: #3498db;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: block;
    margin: 0 auto; /* Căn giữa */
}

/* Hover effect cho nút submit */
form input[type="submit"]:hover {
    background-color: #2980b9;
}


/* Căn chỉnh logo trên thanh menu */
a img {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 180px;
    transition: transform 0.3s;
}

a img:hover {
    transform: scale(1.1);
}

</style>