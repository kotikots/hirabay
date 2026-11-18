<?php
session_start();
$user_id = $_SESSION['id'] ?? null;
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed"]);
    exit;
}

$content = trim($_POST['content'] ?? '');
$imageName = "";

// Handle image upload if provided
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $uploadDir = __DIR__ . "/../uploads-comu/";

    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Clean filename: remove spaces and unsafe characters
    $originalName = str_replace(" ", "_", basename($_FILES['image']['name']));
    $sanitizedFilename = preg_replace("/[^A-Za-z0-9_.-]/", "_", $originalName);
    $imageName = time() . "_" . $sanitizedFilename;
    $targetPath = $uploadDir . $imageName;

    $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($_FILES['image']['type'], $validTypes)) {
        echo json_encode(["status" => "error", "message" => "Invalid image format."]);
        exit;
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
        exit;
    }
}

// Insert post into database
if (!empty($content) || $imageName !== "") {
    $stmt = $conn->prepare("INSERT INTO community_board (user_id, description, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $content, $imageName);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Empty post"]);
}
?>
