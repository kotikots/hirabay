<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['id'])) {
    $userId = intval($_SESSION['id']);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
    echo "success";
} else {
    echo "not_logged_in";
}
?>
