<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$conn = new mysqli('localhost', 'root', '', 'hirabay_db');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'polclarence20@gmail.com';
                $mail->Password   = 'hggo dddq fqdw woak';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    ]
                ];

                $reset_link = "http://localhost/hirabay/form/recovery2.php";
                $mail->setFrom('polclarence20@gmail.com', 'HiraBay');
                $mail->addAddress($email);
                $mail->Subject = 'Password Reset Instructions';
                $mail->Body    = "Instructions for resetting your password will be sent to you shortly.\n$reset_link";

                $mail->send();
                echo "<script>alert('A message has been sent to your email.'); window.location.href='recovery.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent.'); window.location.href='recovery.php';</script>";
            }
        } else {
            echo "<script>alert('No user found with that email.'); window.location.href='recovery.php';</script>";
        }
        $stmt->close();
    } else {
        echo "Database error: " . htmlspecialchars($conn->error);
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
