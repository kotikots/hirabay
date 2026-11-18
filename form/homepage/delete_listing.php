<?php
if (isset($_GET['id'])) {
    $conn = new mysqli("localhost", "root", "", "hirabay_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: my_listings.php");
        exit();
    } else {
        echo "Error deleting item: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    echo "No ID provided.";
}
?>