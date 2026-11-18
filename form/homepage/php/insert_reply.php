<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Connection failed"]);
  exit;
}

$postId = $_POST['post_id'];
$reply = $_POST['reply_content'];
$username = "You"; // Replace with session username if available

$stmt = $conn->prepare("INSERT INTO replies (post_id, username, reply_content) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $postId, $username, $reply);
$stmt->execute();

echo json_encode(["status" => "success"]);
?>
