<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if (!isset($_POST['borrow_id']) || !isset($_SESSION['id'])) {
  http_response_code(400);
  echo "Missing data.";
  exit;
}

$borrow_id = intval($_POST['borrow_id']);
$borrower_id = $_SESSION['id'];

// Get seller_id
$getSeller = $conn->query("SELECT seller_id FROM borrow_requests WHERE id = $borrow_id");
if ($getSeller->num_rows == 0) {
  http_response_code(404);
  echo "Request not found.";
  exit;
}
$seller_id = $getSeller->fetch_assoc()['seller_id'];

// Avoid duplicate notifications
$conn->query("DELETE FROM notifications WHERE action_type = 'return_request' AND related_borrow_id = $borrow_id");

$stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, action_type, related_borrow_id) VALUES (?, ?, 0, 'return_request', ?)");
$msg = "The borrower has clicked 'Returned'. Please confirm if the item was returned.";
$stmt->bind_param("isi", $seller_id, $msg, $borrow_id);
$stmt->execute();

echo "success";
?>
