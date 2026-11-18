<?php
session_start();

if (isset($_SESSION['id'])) {
    $conn = new mysqli('localhost', 'root', '', 'hirabay_db');
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $logout_id = $_SESSION['id'];
    $status = "Offline now";

    $sql = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE id = {$logout_id}");
    if ($sql) {
        session_unset();
        session_destroy();

        header("Location: /hirabay/FirstPage/firstPage.html");
        exit;
    } else {
        die("Failed to update status.");
    }
} else {
    header("Location: /hirabay/FirstPage/firstPage.html");
    exit;
}
