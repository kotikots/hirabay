<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit;
}

$loggedInUserId = $_SESSION['id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch borrow requests (excluding profilePic now)
$sql2 = "SELECT br.*, u.firstName, u.lastName, i.title
        FROM borrow_requests br
        JOIN users u ON br.borrower_id = u.id
        JOIN items i ON br.item_id = i.id
        WHERE br.seller_id = $loggedInUserId AND br.status = 'pending'
        ORDER BY br.request_date DESC";
$result2 = $conn->query($sql2);
// Fetch unread notifications
$countNotif = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id = $loggedInUserId AND is_read = 0")->fetch_assoc();
$notifCount = $countNotif['unread'];

$notifSQL = "SELECT * FROM notifications WHERE user_id = $loggedInUserId AND is_read = 0 ORDER BY created_at DESC";
$notifResult = $conn->query($notifSQL);

// Add pending borrow requests to the total count
$notifCount += $result2->num_rows;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create New Listing</title>
  <link rel="stylesheet" href="listing2.css" />
  <link rel="stylesheet" href="notification.css" />
</head>
<body>
  <div class="navbar">
    <div class="navbar-left">
      <img src="pics/logo.png" alt="HiraBay Logo" class="logo-img">
      <span class="brand"><span class="orange">Hira</span><span class="blue">Bay</span></span>
    </div>

    <div class="navbar-center">
    </div>

    <div class="navbar-right">
      <div class="icon notification-icon" onclick="toggleNotifications()">🔔
        <?php if ($notifCount > 0): ?>
          <span class="notification-count"><?php echo $notifCount; ?></span>
        <?php endif; ?>
      </div>

      <div id="notificationDropdown" class="notification-dropdown hidden">
        <div class="dropdown-header">Notifications</div>

        <!-- Seller-related borrow requests -->
        <?php while ($row = $result2->fetch_assoc()): ?>
          <div class="notif-item">
            <img src="pics/hirabayLogo.png" class="notif-pic">
            <div class="notif-content">
              <strong><?php echo $row['firstName'] . ' ' . $row['lastName']; ?></strong>
              <p>wants to borrow <em><?php echo $row['title']; ?></em></p>
              <div class="notif-actions">
                <button class="confirm-btn" data-id="<?php echo $row['id']; ?>">Confirm</button>
                <button class="delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>
              </div>
            </div>
          </div>
        <?php endwhile; ?>

        <!-- Return request confirmations -->
        <?php
          $notifReturnQuery = "SELECT * FROM notifications WHERE user_id = $loggedInUserId AND action_type = 'return_request' ORDER BY created_at DESC";
          $returnNotifResult = $conn->query($notifReturnQuery);
          while ($row = $returnNotifResult->fetch_assoc()):
        ?>
          <div class="notif-item">
            <img src="pics/hirabayLogo.png" class="notif-pic">
            <div class="notif-content">
              <p><?php echo $row['message']; ?></p>
              <div class="notif-actions">
                <button class="confirm-return-btn" data-id="<?php echo $row['related_borrow_id']; ?>">Confirm Returned</button>
              </div>
              <small><?php echo date("M d, Y H:i", strtotime($row['created_at'])); ?></small>
            </div>
          </div>
        <?php endwhile; ?>

        <!-- Borrower-side notifications -->
        <?php while ($notif = $notifResult->fetch_assoc()): ?>
          <div class="notif-item">
            <img src="pics/hirabayLogo.png" class="notif-pic">
            <div class="notif-content">
              <p><?php echo $notif['message']; ?></p>
              <small><?php echo date("M d, Y H:i", strtotime($notif['created_at'])); ?></small>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <div class="icon settings-wrapper" onclick="toggleSettingsMenu()">
        ⚙️
        <div class="settings-menu" id="settingsMenu">
          <a href="support.php">❓ Help & Support</a>
          <a href="php/logout.php"><span>↩️</span> Log Out</a>
        </div>
      </div>
    </div>
  </div>

  <div class="sidebar open" id="sidebar">
    <button class="toggle-btn" onclick="toggleSidebar()">〈</button>
    <div class="menu">
      <button onclick="window.location.href='index.php'"><span class="icon">👤</span><span class="label">User Profile</span></button>
      <button onclick="window.location.href='mainpage.php'"><span class="icon">🏠</span><span
          class="label">Home</span></button>
      <button onclick="window.location.href='comu.php'"><span class="icon">📋</span><span class="label">Community
          Board</span></button>
      <button style="background-color: #324463; border-radius: 8px;"><span class="icon">➕</span><span
          class="label">Create New</span></button>
      <button onclick="window.location.href='my_listings.php'"><span class="icon">📦</span><span class="label">My
          Listings</span></button>
      <button onclick="window.location.href='cart_session.php'"><span class="icon">🛒</span><span class="label">My
          Cart</span></button>
      <button><span class="icon">📥</span><span class="label"><a href="borrowed.php">Borrowed/Lent Items</a></span></button>
      <button><span class="icon">📄</span><span class="label"><a href="transaction_history.php">Transaction History</a></span></button>
    </div>
  </div>

  <div class="form-container">
    <h2>Post a New Listing</h2>
    <form method="POST" action="php/listing2.php" enctype="multipart/form-data">
      <div class="form-row">
        <div class="form-group">
          <label>Title</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>College Department</label>
          <select name="college" required>
            <option value="">-- Select College --</option>
            <option>College of Engineering</option>
            <option>College of Communication Information Technology</option>
            <option>College of Management And Business Technology</option>
            <option>College of Education</option>
            <option>College of Criminology</option>
            <option>College of Public Administration And Disaster Management</option>
            <option>College of Arts And Science</option>
            <option>College of Nursing</option>
            <option>College of Architecture</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Category</label>
          <select name="category" required>
            <option value="">-- Select Category --</option>
            <option>Books</option>
            <option>Uniform</option>
            <option>Electronics</option>
            <option>Food & Drinks</option>
            <option>School Supplies</option>
            <option>Flashcards/Reviewers</option>
            <option>Beauty Products</option>
            <option>Sports</option>
          </select>
        </div>
        <div class="form-group">
          <label>Contact No.</label>
          <input type="text" name="contact" required pattern="\d{11}" maxlength="11" title="Enter exactly 11 digits">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Sale/Borrow</label>
          <select name="saleBorrow" required>
            <option value="">-- Select --</option>
            <option>Sale</option>
            <option>Borrow</option>
          </select>
        </div>
        <div class="form-group">
          <label>Meet-up Place</label>
          <input type="text" name="meetup" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group" style="flex: 1;">
          <label>Description</label>
          <textarea name="description" rows="4"></textarea>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Condition</label>
          <select name="condition" required>
            <option value="">-- Select Condition --</option>
            <option>New</option>
            <option>Used - Like New</option>
            <option>Used - Good</option>
            <option>Used - Fair</option>
          </select>
        </div>
        <div class="form-group">
          <label>Price</label>
          <input type="number" name="price" required step="1">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group" style="flex: 1;">
          <label>Upload Image</label>
          <input type="file" name="image" accept="image/*" required>
        </div>
      </div>

      <div class="form-buttons">
        <button type="submit">Save Listing</button>
      </div>
    </form>
  </div>


  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('open');
    }

    function toggleSettingsMenu() {
      const menu = document.getElementById('settingsMenu');
      menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
    }

    window.addEventListener('click', function (e) {
      const settings = document.querySelector('.settings-wrapper');
      const menu = document.getElementById('settingsMenu');
      if (!settings.contains(e.target)) {
        menu.style.display = 'none';
      }
    });
  </script>
<script src="notification.js"></script>
</body>

</html>