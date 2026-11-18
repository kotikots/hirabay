<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if (!isset($_POST['borrow_id']) || !isset($_SESSION['id'])) {
  http_response_code(400);
  echo "Missing data.";
  exit;
}

$borrow_id = intval($_POST['borrow_id']);
$seller_id = $_SESSION['id'];

// Get full borrow request info
$sql = "SELECT * FROM borrow_requests WHERE id = $borrow_id AND seller_id = $seller_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  http_response_code(403);
  echo "Borrow request not found or not authorized.";
  exit;
}

$borrow = $result->fetch_assoc();

// Insert into transaction history
$stmt = $conn->prepare("INSERT INTO transaction_history (item_id, borrower_id, seller_id, returned_date) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iii", $borrow['item_id'], $borrow['borrower_id'], $borrow['seller_id']);
$stmt->execute();

// Delete original borrow request
$conn->query("DELETE FROM borrow_requests WHERE id = $borrow_id");

// Remove related notification
$conn->query("DELETE FROM notifications WHERE related_borrow_id = $borrow_id AND action_type = 'return_request'");

echo "success";
?>
