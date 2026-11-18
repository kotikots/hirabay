<?php
    session_start();

    if (isset($_SESSION['id'])) {
        $conn = new mysqli('localhost', 'root', '', 'hirabay_db');

        if ($conn->connect_error) {
            die("DB connection failed: " . $conn->connect_error);
        }

        $outgoing_id = mysqli_real_escape_string($conn, $_POST['outgoing_id']);
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        if (empty($outgoing_id) || empty($incoming_id) || empty($message)) {
            echo "Missing required fields.";
            exit;
        }

        $sql = "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg)
                VALUES ('$incoming_id', '$outgoing_id', '$message')";
        
        if ($conn->query($sql)) {
            echo "Message inserted successfully.";
        } else {
            echo "Query Error: " . $conn->error;
        }
    } else {
        echo "Not logged in.";
    }
?>