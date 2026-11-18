<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'hirabay_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords must match'); window.location.href='recovery2.php';</script>";
        exit;
    }

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        echo "<script>alert('Email not found'); window.location.href='recovery2.php';</script>";
        $checkStmt->close();
        exit;
    }

    $checkStmt->close();


    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $password, $email);

    if ($stmt->execute()) {
        echo "<script>alert('Password successfully updated'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error updating password'); window.location.href='recovery2.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
