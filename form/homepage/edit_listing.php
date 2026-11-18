<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        $imageName = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        $stmt = $conn->prepare("UPDATE items SET title=?, college=?, category=?, contact=?, saleBorrow=?, meetup=?, description=?, conditionItem=?, price=?, image=? WHERE id=?");
        $stmt->bind_param("ssssssssdis", $title, $college, $category, $contact, $saleBorrow, $meetup, $description, $condition, $price, $imageName, $id);
    } else {
        $stmt = $conn->prepare("UPDATE items SET title=?, college=?, category=?, contact=?, saleBorrow=?, meetup=?, description=?, conditionItem=?, price=? WHERE id=?");
        $stmt->bind_param("ssssssssdi", $title, $college, $category, $contact, $saleBorrow, $meetup, $description, $condition, $price, $id);
    }

    if ($stmt->execute()) {
        header("Location: my_listings.php");
        exit;
    } else {
        echo "Update failed: " . $stmt->error;
    }
}

$item = null;
if ($id) {
    $result = $conn->query("SELECT * FROM items WHERE id = $id");
    $item = $result->fetch_assoc();
    if (!$item) {
        echo "Listing not found.";
        exit;
    }
} else {
    echo "Invalid ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <style>
    * { box-sizing: border-box; font-family: Arial, sans-serif; }
    body { margin: 0; padding: 10px; background: #d8e8f9; }
    .edit-wrapper { max-width: 1000px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; display: flex; flex-wrap: wrap; gap: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .form-left, .form-right { flex: 1; min-width: 300px; }
    .form-left img { width: 100%; max-height: 240px; object-fit: cover; border-radius: 8px; margin: 20px 0; }
    .form-group { margin-bottom: 15px; }
    label { font-weight: bold; display: block; margin-bottom: 5px; }
    input, select, textarea { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
    .form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
    .form-actions button {
      padding: 10px 20px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .form-actions button:hover { background-color: #0056b3; }
    .form-actions .cancel-btn {
      background-color: #6c757d;
    }
    .form-actions .cancel-btn:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>
<h2 style="text-align:center; margin-bottom:20px;">Edit Product</h2>
<form method="POST" enctype="multipart/form-data">
  <div class="edit-wrapper">
    <div class="form-left">
      <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Current Image">
      <label>Change Image</label>
      <input type="file" name="image" accept="image/*">
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
      </div>
      <div class="form-group">
        <label>Preferred Place to Meet</label>
        <input type="text" name="meetup" value="<?php echo htmlspecialchars($item['meetup']); ?>" required>
      </div>
    </div>

    <div class="form-right">
      <div class="form-group">
        <label>Category</label>
        <select name="category" required>
          <option value="">-- Select Category --</option>
          <?php
          $categories = ["Books", "Uniform", "Electronics", "Food & Drinks", "School Supplies", "Flashcards/Reviewers", "Beauty Products", "Sports"];
          foreach ($categories as $cat) {
              $selected = ($item['category'] === $cat) ? 'selected' : '';
              echo "<option value='$cat' $selected>$cat</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Sale or Borrow</label>
        <select name="saleBorrow" required>
          <option value="">-- Select --</option>
          <option value="Sale" <?php if($item['saleBorrow'] === 'Sale') echo 'selected'; ?>>Sale</option>
          <option value="Borrow" <?php if($item['saleBorrow'] === 'Borrow') echo 'selected'; ?>>Borrow</option>
        </select>
      </div>

      <div class="form-group">
        <label>College Department</label>
        <select name="college" required>
          <option value="">-- Select College --</option>
          <?php
          $colleges = [
            "College of Engineering",
            "College of Communication Information Technology",
            "College of Management And Business Technology",
            "College of Education",
            "College of Criminology",
            "College of Public Administration And Disaster Management",
            "College of Arts And Science",
            "College of Nursing",
            "College of Architecture"
          ];
          foreach ($colleges as $col) {
              $selected = ($item['college'] === $col) ? 'selected' : '';
              echo "<option value='$col' $selected>$col</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Contact No.</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($item['contact']); ?>" required pattern="\d{11}" maxlength="11" title="Enter exactly 11 digits">
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3" required><?php echo htmlspecialchars($item['description']); ?></textarea>
      </div>

      <div class="form-group">
        <label>Condition</label>
        <select name="condition" required>
          <option value="">-- Select Condition --</option>
          <?php
          $conditions = ["New", "Used - Like New", "Used - Good", "Used - Fair"];
          foreach ($conditions as $cond) {
              $selected = ($item['conditionItem'] === $cond) ? 'selected' : '';
              echo "<option value='$cond' $selected>$cond</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Price</label>
        <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($item['price']); ?>" required>
      </div>

      <div class="form-actions">
        <button type="submit">Edit</button>
        <button type="button" class="cancel-btn" onclick="window.location.href='my_listings.php'">Cancel</button>
      </div>
    </div>
  </div>
</form>
</body>
</html>
