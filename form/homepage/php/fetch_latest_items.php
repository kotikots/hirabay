<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get 3 most recent listings, with seller info
$sql = "SELECT items.*, 
               CONCAT(users.firstName, ' ', users.lastName) AS seller 
        FROM items 
        JOIN users ON items.user_id = users.id 
        ORDER BY items.id DESC 
        LIMIT 3";

$result = $conn->query($sql);
$items = [];

while ($row = $result->fetch_assoc()) {
    $imageUrl = $row['image'] ? '../uploads/' . $row['image'] : null;

    $items[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'category' => $row['category'],
        'price' => $row['price'],
        'image' => $imageUrl,
        'seller' => $row['seller']
    ];
}

header('Content-Type: application/json');
echo json_encode($items);
?>
