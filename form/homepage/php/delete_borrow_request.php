<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
    die("Connection failed");
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Optional: Make sure the logged-in user owns this request
    $sellerId = $_SESSION['id'];
    $conn->query("DELETE FROM borrow_requests WHERE id = $id AND seller_id = $sellerId");

    echo "deleted";
} else {
    echo "invalid";
}
?>
