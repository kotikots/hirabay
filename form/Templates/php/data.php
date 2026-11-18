<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    while ($row = mysqli_fetch_assoc($sql)) {
        if ($row['id'] == $_SESSION['id']) continue;

        $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = {$row['id']}
                OR outgoing_msg_id = {$row['id']}) AND (outgoing_msg_id = {$outgoing_id}
                OR incoming_msg_id = {$outgoing_id}) ORDER BY msg_id DESC LIMIT 1";

        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);

        if (mysqli_num_rows($query2) > 0 && $row2) {
            $result = $row2["msg"];
            $you = ($outgoing_id == $row2['outgoing_msg_id']) ? "You: " : "";
        } else {
            $result = "No message available";
            $you = "";
        }

        $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;
        $offline = ($row['status'] == "Offline now") ? "offline" : "";

        $output .= '<a href="#" class="user-link" data-id="' . $row['id'] . '" data-name="' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '">
                    <div class="content">
                        <img src="pics/hirabayLogo.png" alt="">
                        <div class="details">
                            <span>' . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . '</span>
                            <p>'. $you . $msg .'</p>
                        </div>
                    </div>
                    <div class="status-dot ' . $offline . '"><i class="fas fa-circle"></i></div>
                    </a>';
    }
?>