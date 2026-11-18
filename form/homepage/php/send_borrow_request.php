<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) die("Connection failed");

$item_id = intval($_POST['item_id']);
$borrower_id = intval($_POST['borrower_id']);
$seller_id = intval($_POST['seller_id']);

// Prevent duplicate requests
$check = $conn->query("SELECT * FROM borrow_requests WHERE item_id=$item_id AND borrower_id=$borrower_id AND seller_id=$seller_id AND status='pending'");
if ($check->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO borrow_requests (item_id, borrower_id, seller_id, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iii", $item_id, $borrower_id, $seller_id);
    $stmt->execute();
    echo "Request sent";
} else {
    echo "Already requested";
}
?>