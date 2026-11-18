<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set("display_errors", 1);

$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit;
}

$userType = $conn->real_escape_string($data['userType']);
$userId = $conn->real_escape_string($data['userId']);
$userName = $conn->real_escape_string($data['userName']);
$issue = $conn->real_escape_string($data['issue']);

$sql = "INSERT INTO reports (user_type, user_id, user_name, issue) VALUES ('$userType', '$userId', '$userName', '$issue')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$conn->close();
?>
