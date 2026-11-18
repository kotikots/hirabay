<?php
    session_start();

    if (isset($_SESSION['id'])) {
        $conn = new mysqli('localhost', 'root', '', 'hirabay_db');

        if ($conn->connect_error) {
            die("DB connection failed: " . $conn->connect_error);
        }

        $outgoing_id = mysqli_real_escape_string($conn, $_POST['outgoing_id'] ?? '');
        $incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id'] ?? '');
        $output = "";

        // ✅ Validate before using in SQL
        if (empty($outgoing_id) || empty($incoming_id)) {
            echo "Missing sender or receiver ID.";
            exit;
        }

        $sql = "SELECT * FROM messages WHERE 
                (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id}) OR 
                (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id})
                ORDER BY msg_id ASC";

        $query = mysqli_query($conn, $sql);

        if ($query && mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                if ($row['outgoing_msg_id'] == $outgoing_id) {
                    $output .= '<div class="outgoing">
                                    <div class="details">
                                        <p>' . $row['msg'] . '</p>
                                    </div>
                                </div>';
                } else {
                    $output .= '<div class="incoming">
                                    <img src="pics/hirabayLogo.png" alt="">
                                    <div class="details">
                                        <p>' . $row['msg'] . '</p>
                                    </div>
                                </div>';
                }
            }
        }

        echo $output;
    } else {
        echo "Not logged in.";
    }
?>
