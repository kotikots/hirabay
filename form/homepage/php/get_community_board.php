<?php
  $conn = new mysqli("localhost", "root", "", "hirabay_db");

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT id, description, image, created_at FROM community_board ORDER BY created_at DESC";
  $result = $conn->query($sql);

  $posts = [];

  while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
  }

  header('Content-Type: application/json');
  echo json_encode($posts);
?>
