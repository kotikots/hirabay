<?php 
    session_start();
    $conn = new mysqli('localhost', 'root', '', 'hirabay_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST['regSubmit'])) {
        $lastName = $_POST['lastName'];
        $firstName = $_POST['firstName'];
        $studNum = $_POST['studNum'];
        $email = $_POST['email'];
        $contactNum = $_POST['contactNum'];
        $password = $_POST['password'];

        $checkQuery = "SELECT * FROM users WHERE email='$email' OR studNum='$studNum'";
        $result = $conn->query($checkQuery);

        if ($result->num_rows > 0) {
            echo "<script>alert('Email or Student Number already exists!'); window.location.href='register.php';</script>";
        } else {
            $insertQuery = "INSERT INTO users (lastName, firstName, studNum, email, contactNum, password, status)
                            VALUES ('$lastName','$firstName','$studNum','$email','$contactNum','$password', 'Active now')";

            if ($conn->query($insertQuery) === TRUE) {
                header("location: login.php");
                exit();
            } else {
                echo "Error: " . $conn->error;
            }
        }
    }

    if (isset($_POST['loginSubmit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['id'] = $row['id'];

            $userId = $row['id'];
            $conn->query("UPDATE users SET status = 'Active now' WHERE id = $userId");

            header("Location: homepage\mainpage.php");
            exit();
        } else {
            echo "<script>alert('Email or Password is Incorrect!'); window.location.href='login.php';</script>";
        }
    }
?>
