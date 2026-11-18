<?php
    session_start();

    $conn = new mysqli('localhost', 'root', '', 'hirabay_db');
    $outgoing_id = $_SESSION['id'];
    $searchTerm = isset($_POST['searchTerm']) ? mysqli_real_escape_string($conn, $_POST['searchTerm']) : '';

    $output = "";
    
    $sql = mysqli_query($conn, "SELECT * FROM users WHERE firstName LIKE '%{$searchTerm}%' OR lastName LIKE '%{$searchTerm}%'");

    if (mysqli_num_rows($sql) > 0) {
        include "data.php";
    } else {
        $output .= "No user found related to your search term";
    }

    echo $output;
?>