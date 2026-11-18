<?php
    session_start();
    $conn = new mysqli('localhost', 'root', '', 'hirabay_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");

        if (mysqli_num_rows($sql) === 1) {
            $user = mysqli_fetch_assoc($sql);

            if ($password === $user['password']) {
                $_SESSION['id'] = $user['id'];

                // ✅ Update status
                $updateStatus = "UPDATE users SET status = 'Active now' WHERE id = {$user['id']}";
                if (!mysqli_query($conn, $updateStatus)) {
                    die("Status update failed: " . mysqli_error($conn));
                }

                header("Location: Templates/template.php");
                exit;
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
    } else {
        echo "Invalid request.";
    }
?>
