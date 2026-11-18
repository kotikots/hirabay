<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$postId = $_GET['post_id'];
$sql = "SELECT username, reply_content, created_at FROM replies WHERE post_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();

$replies = [];

while ($row = $result->fetch_assoc()) {
  $replies[] = $row;
}

header('Content-Type: application/json');
echo json_encode($replies);
?>
