<?php
// getUserId.php

if (isset($_POST['userGmail'])) {
    $email = $_POST['userGmail'];
    include_once("db/connect.php"); // Assuming your database connection is in db_connection.php

    // Prepare the SQL statement
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $email); // Bind the email parameter

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo $row['user_id']; // Return the user_id as a response
    } else {
        echo "Email not found"; // Return error message if email doesn't exist
    }

    // Close the statement
    $stmt->close();
}
?>
