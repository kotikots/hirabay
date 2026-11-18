<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['id'] ?? null;
if (!$userId) {
    die("Unauthorized access.");
}

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$college = trim($_POST['college'] ?? '');
$bio = trim($_POST['bio'] ?? '');

if (strlen($bio) > 50) {
    die("Bio must be 50 characters or less.");
}

// Handle profile picture upload
$profilePicName = null;
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === 0) {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename($_FILES['profilePic']['name']);
    $safeName = time() . '_' . preg_replace("/[^A-Za-z0-9_.-]/", "_", $originalName);
    $targetPath = $uploadDir . $safeName;

    $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($_FILES['profilePic']['type'], $validTypes)) {
        die("Invalid image format.");
    }

    if (!move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetPath)) {
        die("Failed to upload image.");
    }

    $profilePicName = $safeName;
}

// Build the SQL update query
$sql = "UPDATE users SET firstName=?, lastName=?, college=?, bio=?";
$params = [$firstName, $lastName, $college, $bio];
$types = "ssss";

if ($profilePicName) {
    $sql .= ", profilePic=?";
    $params[] = $profilePicName;
    $types .= "s";
}

$sql .= " WHERE id=?";
$params[] = $userId;
$types .= "i";

// Prepare and execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    header("Location: ../index.php?updated=1");
    exit;
} else {
    echo "Update failed: " . $stmt->error;
}
?>
