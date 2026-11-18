<?php
session_start();
$user_id = $_SESSION['id'] ?? null;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $college = $_POST['college'];
    $category = $_POST['category'];
    $contact = $_POST['contact'];
    $saleBorrow = $_POST['saleBorrow'];
    $meetup = $_POST['meetup'];
    $description = $_POST['description'];
    $condition = $_POST['condition'];
    $price = floatval($_POST['price']);

    $imageName = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = dirname(__DIR__) . "/uploads/";
 
       // Create uploads directory if it doesn't exist
       if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
 
       // Clean and create safe filename
       $originalName = basename($_FILES['image']['name']);
       $sanitizedFilename = preg_replace("/[^A-Za-z0-9_.-]/", "_", $originalName);
       $imageName = time() . "_" . $sanitizedFilename;
 
       $targetPath = $uploadDir . $imageName;
 
       // Check for valid image MIME type (optional, but safer)
       $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
       if (!in_array($_FILES['image']['type'], $validTypes)) {
           echo json_encode(["status" => "error", "message" => "Invalid image format."]);
           exit;
       }
 
       // Move uploaded file
       if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
         error_log("Failed to move file to: " . $targetPath);
         echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
         exit;
     }
     
   }

    $stmt = $conn->prepare("INSERT INTO items (user_id, title, college, category, contact, saleBorrow, meetup, description, conditionItem, price, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssds", 
        $user_id, $title, $college, $category, $contact, $saleBorrow,
        $meetup, $description, $condition, $price, $imageName
    );

    if ($stmt->execute()) {
        header("Location: ../my_listings.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
