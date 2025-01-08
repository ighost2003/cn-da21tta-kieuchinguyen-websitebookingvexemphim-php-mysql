<?php
include_once("db/connect.php");
session_start();

// Xử lý AJAX yêu cầu thành phố
if (isset($_POST['action']) && $_POST['action'] === 'get_theaters') {
    $city = mysqli_real_escape_string($mysqli, $_POST['city']);
    $query = "SELECT theaters_id, theaters_name FROM theaters WHERE theaters_city = '$city'";
    $result = mysqli_query($mysqli, $query);
    $options = '<option value="">-- Select Theater --</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        $options .= "<option value='" . $row['theaters_id'] . "'>" . $row['theaters_name'] . "</option>";
    }
    echo $options;
    exit;
}

// Xử lý AJAX yêu cầu thông tin rạp
if (isset($_POST['action']) && $_POST['action'] === 'get_theater_info') {
    $theaterId = mysqli_real_escape_string($mysqli, $_POST['theater_id']);
    $query = "SELECT * FROM theaters WHERE theaters_id = '$theaterId'";
    $result = mysqli_query($mysqli, $query);
    $data = mysqli_fetch_assoc($result);
    echo json_encode($data);
    exit;
}

// Xử lý AJAX yêu cầu thông tin phòng
if (isset($_POST['action']) && $_POST['action'] === 'get_rooms') {
    $theaterId = mysqli_real_escape_string($mysqli, $_POST['theater_id']);
    $query = "SELECT * FROM room WHERE room_theater = '$theaterId'";
    $result = mysqli_query($mysqli, $query);
    $rooms = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rooms[] = $row;
    }
    echo json_encode($rooms);
    exit;
}

// Xử lý AJAX yêu cầu thông tin suất chiếu cho tất cả các phòng
if (isset($_POST['action']) && $_POST['action'] === 'get_showings_for_rooms') {
    $roomIds = $_POST['room_ids']; // Expecting a comma-separated list of room IDs
    $query = "SELECT showings_id, showings_name_movie, showings_room, showings_time FROM showings WHERE showings_room IN ($roomIds)";
    $result = mysqli_query($mysqli, $query);
    $showings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $showings[] = $row;
    }
    echo json_encode($showings);
    exit;
}

// Xử lý AJAX yêu cầu thông tin phim
if (isset($_POST['action']) && $_POST['action'] === 'get_movies') {
    $movieIds = $_POST['movie_ids']; // Expecting a comma-separated list of movie IDs
    $query = "SELECT movie_id, movie_name, movie_directors, movie_cast, movie_cate, movie_date, movie_time, movie_language, movie_rate, movie_img, movie_decription, movie_trailer, id_theaters FROM movie WHERE movie_id IN ($movieIds)";
    $result = mysqli_query($mysqli, $query);
    $movies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $movies[] = $row;
    }
    echo json_encode($movies);
    exit;
}
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

        /* General Reset */
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
}

h1, h2 {
    text-align: center;
    color: #444;
}

/* Dropdown Styling */
label {
    font-size: 1.2em;
    margin-right: 10px;
}

select {
    font-size: 1em;
    padding: 10px;
    margin: 10px 0;
    border: 2px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s;
}

select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Theater Information Styling */
#theater-info, #room-info, #showings-info {
    background: #fff;
    margin: 20px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 800px;
}

p {
    font-size: 1em;
    line-height: 1.5;
}

/* Button Styling */
button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 1em;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

button:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

/* List Styling */
ul {
    list-style-type: none;
    padding: 0;
}

li {
    background: #f1f1f1;
    margin: 10px 0;
    padding: 10px;
    border-radius: 5px;
    transition: transform 0.2s;
}

li:hover {
    transform: scale(1.02);
    background-color: #e7e7e7;
}

/* Image Styling */
img {
    max-width: 100%;
    border-radius: 8px;
    transition: transform 0.3s;
}

img:hover {
    transform: scale(1.1);
}

/* Trailer Link */
a {
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s;
}

a:hover {
    color: #0056b3;
}

/* Container Styling */
.container {
    margin: 0 auto;
    padding: 20px;
    max-width: 1200px;
    text-align: center;
}

/* Highlight Theater Details */
#details {
    font-weight: bold;
    color: #555;
    margin-top: 15px;
}

/* Smooth Transitions */
* {
    transition: all 0.3s ease;
}

    </style>
</head>
<body>
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
    <h1>Chọn Rạp</h1>

    <!-- Dropdown to select city -->
    <label for="city">Tỉnh thành:</label>
    <select id="city" name="city">
        <option value="">-- Select City --</option>
        <?php
        $query = "SELECT DISTINCT theaters_city FROM theaters";
        $result = mysqli_query($mysqli, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<option value='" . $row['theaters_city'] . "'>" . $row['theaters_city'] . "</option>";
        }
        ?>
    </select>

    <!-- Dropdown to select theater name -->
    <label for="theater">Danh sách Rạp:</label>
    <select id="theater" name="theater">
        <option value="">-- Select Theater --</option>
    </select>

    <!-- Display theater information -->
    <div id="theater-info">
        <h2>Thông tin rạp</h2>
        <table style="border-collapse: collapse; width: 100%;">
            <tr>
                <th style="border: 1px solid #ccc; padding: 8px;">Tên Rạp</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Địa Chỉ</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Thành Phố</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Email</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Điện Thoại</th>
            </tr>
            <tr>
                <td id="theater-name" style="border: 1px solid #ccc; padding: 8px;"></td>
                <td id="theater-address" style="border: 1px solid #ccc; padding: 8px;"></td>
                <td id="theater-city" style="border: 1px solid #ccc; padding: 8px;"></td>
                <td id="theater-email" style="border: 1px solid #ccc; padding: 8px;"></td>
                <td id="theater-phone" style="border: 1px solid #ccc; padding: 8px;"></td>
            </tr>
        </table>
        <!-- <p id="id">Theater ID: </p> -->
    </div>

    <!-- Display room information -->

    <!-- <div id="room-info">
        <h2>Thông tin các phòng</h2>
        <ul id="room-list"></ul>
    </div> -->

    <!-- Display showings information -->
    <div id="showings-info">
        <h2>Thông tin chiếu phim</h2>
        <ul id="showings-list"></ul>
    </div>

    <script>
        $(document).ready(function () {
            // Populate theater dropdown based on city selection
            $('#city').change(function () {
                let city = $(this).val();
                if (city !== "") {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: {action: 'get_theaters', city: city},
                        success: function (response) {
                            $('#theater').html(response);
                        }
                    });
                } else {
                    $('#theater').html('<option value="">-- Select Theater --</option>');
                }
            });

            // Display theater details based on theater selection
            $('#theater').change(function () {
                let theaterId = $(this).val();
                if (theaterId !== "") {
                    $.ajax({
                        url: '',
                        type: 'POST',
                        data: {action: 'get_theater_info', theater_id: theaterId},
                        success: function (response) {
                            let data = JSON.parse(response);
                            $('#theater-name').text(data.theaters_name);
                            $('#theater-address').text(data.theaters_address);
                            $('#theater-city').text(data.theaters_city);
                            $('#theater-email').text(data.theaters_mail);
                            $('#theater-phone').text(data.theaters_phone);
                            $('#id').text(`Theater ID: ${data.theaters_id}`);

                            // Fetch room information
                            $.ajax({
                                url: '',
                                type: 'POST',
                                data: {action: 'get_rooms', theater_id: theaterId},
                                success: function (roomResponse) {
                                    let rooms = JSON.parse(roomResponse);
                                    $('#room-list').empty(); // Clear previous room list
                                    let roomIds = []; // Array to hold room IDs
                                    let movieIds = new Set(); // Set to hold unique movie IDs
                                    rooms.forEach(function(room) {
                                        $('#room-list').append(`<li>Room ID: ${room.room_id}, Room Name: ${room.room_name}</li>`);
                                        roomIds.push(room.room_id); // Collect room IDs
                                    });

                                    // Fetch showings for all rooms
                                    if (roomIds.length > 0) {
                                        $.ajax({
                                            url: '',
                                            type: 'POST',
                                            data: {action: 'get_showings_for_rooms', room_ids: roomIds.join(',')},
                                            success: function (showingsResponse) {
                                                let showings = JSON.parse(showingsResponse);
                                                $('#showings-list').empty(); // Clear previous showings list
                                                showings.forEach(function(showing) {
                                                    $('#showings-list').append(`<li style="display: none;">Showing ID: ${showing.showings_id}, Movie: ${showing.showings_name_movie}, Room: ${showing.showings_room}, Time: ${showing.showings_time}</li>`);
                                                    movieIds.add(showing.showings_name_movie); // Collect unique movie IDs
                                                });

                                                // Fetch movie details for all unique movie IDs
                                                if (movieIds.size > 0) {
                                                    $.ajax({
                                                        url: '',
                                                        type: 'POST',
                                                        data: {action: 'get_movies', movie_ids: Array.from(movieIds).join(',')},
                                                        success: function (moviesResponse) {
                                                            let movies = JSON.parse(moviesResponse);
                                                            movies.forEach(function(movie) {
                                                                $('#showings-list').append(`<div>
                                                                    <h3>${movie.movie_name}</h3>
                                                                    <a href="index.php?controller=phim&id=${movie.movie_id}">
                                                                        <img src="${movie.movie_img}" alt="${movie.movie_name}" style="width:100px;height:auto;">
                                                                    </a>
                                                                    <p>Directors: ${movie.movie_directors}</p>
                                                                    <p>Cast: ${movie.movie_cast}</p>
                                                                    <p>Category: ${movie.movie_cate}</p>
                                                                    <p>Release Date: ${movie.movie_date}</p>
                                                                    <p>Duration: ${movie.movie_time} minutes</p>
                                                                    <p>Language: ${movie.movie_language}</p>
                                                                    <p>Rating: ${movie.movie_rate}</p>
                                                                    <p>Description: ${movie.movie_decription}</p>
                                                                    <p>Trailer: <a href="${movie.movie_trailer}">Watch here</a></p>
                                                                    <button onclick="window.location.href='index.php?controller=phim&id=${movie.movie_id}'">Đặt Vé</button>
                                                                </div>`);
                                                            });
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });
                } else {
                    $('#theater-name').text('Name will be displayed here');
                    $('#theater-address').text('Address will be displayed here');
                    $('#theater-city').text('City will be displayed here');
                    $('#theater-email').text('Email will be displayed here');
                    $('#theater-phone').text('Phone will be displayed here');
                    $('#id').text('Theater ID: ');
                    $('#room-list').empty(); // Clear room list if no theater is selected
                    $('#showings-list').empty(); // Clear showings list if no theater is selected
                }
            });
        });
    </script>

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
</body>
</html>
