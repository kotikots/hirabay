<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sellerId = $_SESSION['id'];

    // Update the borrow request status to approved
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved' WHERE id = ? AND seller_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $id, $sellerId);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Get borrower_id to send them a notification
            $borrowerResult = $conn->query("SELECT borrower_id FROM borrow_requests WHERE id = $id");
            $borrowerRow = $borrowerResult->fetch_assoc();
            $borrowerId = $borrowerRow['borrower_id'];

            // Insert notification to notify borrower
            $message = "Your borrow request was approved by the seller.";
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifStmt->bind_param("is", $borrowerId, $message);
            $notifStmt->execute();
            $notifStmt->close();

            echo "confirmed";
        } else {
            echo "no rows updated — check ID or seller_id";
        }
    } else {
        echo "query error: " . $stmt->error;
    }

    $stmt->close(); // Make sure you close only after execute
} else {
    echo "invalid request — no ID";
}
?>
