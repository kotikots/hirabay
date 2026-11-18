<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$imageName = "";

// ✅ Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $uploadDir = __DIR__ . "/uploads/";


    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageName = basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
}

// ✅ Insert to database
$stmt = $conn->prepare("INSERT INTO community_board (title, description, image) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $title, $description, $imageName);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
