<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT community_board.*, 
               CONCAT(users.firstName, ' ', users.lastName) AS author, 
               users.id AS author_id
        FROM community_board 
        JOIN users ON community_board.user_id = users.id 
        ORDER BY created_at ASC";

$result = $conn->query($sql);

$posts = [];
while ($row = $result->fetch_assoc()) {
    $imageUrl = $row['image'] ? 'uploads-comu/' . $row['image'] : null;

    $posts[] = [
        'id' => $row['id'],
        'content' => $row['description'],
        'image_url' => $imageUrl,
        'created_at' => $row['created_at'],
        'author' => $row['author'],
        'author_id' => $row['author_id']
    ];
}

header('Content-Type: application/json');
echo json_encode($posts);
?>
