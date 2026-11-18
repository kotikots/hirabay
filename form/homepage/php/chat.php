<?php
    session_start();

    $conn = new mysqli('localhost', 'root', '', 'hirabay_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $outgoing_id = $_SESSION['id'];

    $sql = mysqli_query($conn, "SELECT * FROM users");

    $output = "";

    if (mysqli_num_rows($sql) <= 1) {
        $output .= "No users are available to chat";
    } else {
        include "data.php"; // this fills $output
    }

    echo $output;
?>
