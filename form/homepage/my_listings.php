<?php
session_start();

$userId = $_SESSION['id'] ?? null;
if (!$userId) {
  header("Location: ../login.php");
  exit;
}

echo "<script>const CURRENT_USER_ID = " . json_encode($userId) . ";</script>";

$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user listings
$sql = "SELECT items.*, 
            CONCAT(users.firstName, ' ', users.lastName) AS author, 
            users.id AS author_id
        FROM items 
        JOIN users ON items.user_id = users.id 
        WHERE users.id = ?
        ORDER BY items.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch pending borrow requests (as seller)
$sql2 = "SELECT br.*, u.firstName, u.lastName, i.title
        FROM borrow_requests br
        JOIN users u ON br.borrower_id = u.id
        JOIN items i ON br.item_id = i.id
        WHERE br.seller_id = $userId AND br.status = 'pending'
        ORDER BY br.request_date DESC";
$result2 = $conn->query($sql2);

// Fetch unread notifications (as borrower)
$notifSQL = "SELECT * FROM notifications WHERE user_id = $userId AND is_read = 0 ORDER BY created_at DESC";
$notifResult = $conn->query($notifSQL);

// Total unread notifications
$notifCount = 0;
$notifCount += ($notifResult && $notifResult->num_rows > 0) ? $notifResult->num_rows : 0;
$notifCount += ($result2 && $result2->num_rows > 0) ? $result2->num_rows : 0;
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="notification.css" />
  <link rel="stylesheet" href="chat.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
  <title>My Listings</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body, html {
      height: 100%;
      background-color: #b9c1cd;
      overflow-x: hidden;
    }

    a {
      text-decoration: none;
      color: white;
    }

    /* Navbar */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      height: 70px;
      width: 100%;
      background-color: #1c2738;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      z-index: 1000;
    }

    .navbar-left, .navbar-right {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-img {
      height: 40px;
    }

    .brand {
      font-size: 32px;
      font-weight: bold;
    }

    .orange {
      color: #ff9000;
    }

    .blue {
      color: #7cbcff;
    }

    .navbar-center {
      flex: 1;
      display: flex;
      justify-content: center;
    }

    .search-container {
      background-color: white;
      display: flex;
      align-items: center;
      border-radius: 50px;
      padding: 5px 15px;
      gap: 10px;
      width: 400px;
    }

    .search-container input {
      border: none;
      outline: none;
      font-size: 14px;
      flex: 1;
      padding: 6px 0;
    }

    .icon {
      font-size: 20px;
      color: white;
      cursor: pointer;
      position: relative;
    }

    .notification-icon .notification-count {
      position: absolute;
      top: -6px;
      right: -10px;
      background-color: red;
      color: white;
      font-size: 11px;
      padding: 2px 6px;
      border-radius: 50%;
      font-weight: bold;
    }

    .settings-wrapper {
      position: relative;
    }

    .settings-menu {
      position: absolute;
      top: 30px;
      right: 0;
      background-color: #e0e0e0;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 10px;
      display: none;
      flex-direction: column;
      gap: 10px;
      width: 170px;
      z-index: 2000;
    }

    .settings-menu a,
    .settings-menu button {
      display: flex;
      align-items: center;
      gap: 10px;
      background-color: #d3d3d3;
      border: none;
      border-radius: 25px;
      padding: 8px 12px;
      font-size: 14px;
      cursor: pointer;
      width: 100%;
      transition: background 0.2s;
      text-decoration: none;
      color: black;
      font-weight: 500;
    }

    .settings-menu a {
      color: #000;
    }

    .settings-menu a:hover,
    .settings-menu button:hover {
      background-color: #cfcfcf;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 70px;
      left: 0;
      width: 220px;
      height: calc(100% - 70px);
      background-color: #1f2a44;
      color: white;
      padding: 20px 10px;
      transition: width 0.3s;
      overflow: visible;
      z-index: 999;
      white-space: nowrap; 
    }

    .sidebar:not(.open) {
      width: 70px;
    }

    .sidebar .toggle-btn {
      position: absolute;
      top: 50%;
      left: 100%;
      transform: translate(-50%, -50%);
      background-color: rgb(254, 86, 39);
      border: none;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      color: white;
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 4px rgba(0,0,0,0.3);
      z-index: 1001;
    }

    .sidebar .menu {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 30px;
    }

    .sidebar .menu button {
      display: flex;
      align-items: center;
      gap: 10px;
      background: none;
      border: none;
      color: white;
      padding: 10px;
      width: 100%;
      font-size: 16px;
      text-align: left;
      cursor: pointer;
      transition: background 0.2s;
    }

    .sidebar .menu button:hover {
      background-color: #2f3e52;
    }

    .sidebar .menu .icon {
      font-size: 18px;
      text-align: center;
      width: 30px;
    }

    .label {
      display: inline;
      transition: opacity 0.3s ease;
    }

    .sidebar:not(.open) .label {
      display: none;
    }
    .main-content { margin-left: 220px; padding: 110px 40px 40px 40px; min-height: calc(100vh - 70px); transition: margin-left 0.3s ease; flex: 1; }
    .sidebar:not(.open) ~ .main-content { margin-left: 70px; }
    h2 { margin-bottom: 30px; font-size: 28px; color: #1c2738; position: relative; }
    h2::after { content: '⬅ You are here'; font-size: 14px; color: #888; position: absolute; right: 10px; bottom: -22px; }
    .listing { background-color: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; gap: 20px; align-items: flex-start; }
    .listing img { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; }
    .listing-info { flex: 1; }
    .listing-info p { margin: 5px 0; line-height: 1.5; color: #333; }
    .listing-actions { display: flex; flex-direction: column; gap: 10px; justify-content: center; }
    .listing-actions a { text-decoration: none; padding: 8px 12px; background-color: #007bff; color: #fff; border-radius: 4px; text-align: center; transition: 0.2s; }
    .listing-actions a:hover { background-color: #0056b3; }
    .listing-actions a.delete { background-color: #dc3545; }
    .listing-actions a.delete:hover { background-color: #c82333; }
  </style>
</head>
<body>
<div class="navbar">
  <div class="navbar-left">
    <img src="pics/logo.png" alt="HiraBay Logo" class="logo-img">
    <span class="brand">
      <span class="brand"><span class="orange">Hira</span><span class="blue">Bay</span></span>
    </span>
  </div>

  <div class="navbar-center">
    <div class="search-container">
      <span class="search-icon">🔍</span>
      <input type="text" placeholder="Search your listings...">
      <span class="filter-icon">⚙️</span>
    </div>
  </div>

  <div class="navbar-right">
    <div class="icon notification-icon" onclick="toggleNotifications()">🔔
      <span class="notification-count">5</span>
    </div>

    <div id="notificationDropdown" class="notification-dropdown hidden">
      <div class="dropdown-header">Notifications</div>
      <div class="notif-item">
        <img src="pics/hirabayLogo.png" class="notif-pic">
        <div class="notif-content">
          <p>User wants to borrow an item</p>
          <div class="notif-actions">
            <button class="confirm-btn">Confirm</button>
            <button class="delete-btn">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <div class="icon settings-wrapper" onclick="toggleSettingsMenu()">⚙️
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
      <button onclick="window.location.href='mainpage.php'"><span class="icon">🏠</span><span class="label">Home</span></button>
      <button onclick="window.location.href='comu.php'"><span class="icon">📋</span><span class="label">Community Board</span></button>
      <button onclick="window.location.href='listing2.php'"><span class="icon">➕</span><span class="label">Create New</span></button>
      <button style="background-color: #324463; border-radius: 8px;"><span class="icon">📦</span><span class="label">My Listings</span></button>
      <button onclick="window.location.href='cart_session.php'"><span class="icon">🛒</span><span class="label">My Cart</span></button>
      <button><span class="icon">📥</span><span class="label"><a href="borrowed.php">Borrowed/Lent Items</a></span></button>
      <button><span class="icon">📄</span><span class="label"><a href="transaction_history.php">Transaction History</a></span></button>
    </div>
  </div>

<div class="main-content">
  <h2>📦 My Listings</h2>
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="listing">
        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Listing Image">
        <div class="listing-info">
          <p><strong>Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
          <p><strong>Status:</strong> <?php echo htmlspecialchars($row['saleBorrow']); ?></p>
          <p><strong>College:</strong> <?php echo htmlspecialchars($row['college']); ?></p>
          <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
          <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></p>
          <p><strong>Meetup:</strong> <?php echo htmlspecialchars($row['meetup']); ?></p>
          <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
          <p><strong>Condition:</strong> <?php echo htmlspecialchars($row['conditionItem']); ?></p>
          <p><strong>Price:</strong> ₱<?php echo number_format($row['price'], 2); ?></p>
          <p class="posted"><strong>Posted By:</strong> <?php echo ($row['author_id'] == $userId) ? 'You' : htmlspecialchars($row['author']); ?></p>

        </div>
        <div class="listing-actions">
          <a href="edit_listing.php?id=<?php echo $row['id']; ?>">Edit</a>
          <a class="delete" href="delete_listing.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this listing?');">Delete</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; font-size: 18px;">You have no listings.</p>
  <?php endif; ?>
</div>

  <div class="chat-module">
    <div class="chatBtn">
      <button>Chat <i class="fas fa-comments"></i></button>
    </div>

    <div class="chat chat-panel hidden">
      <div class="chat-wrapper">
        <section class="users">
            <header>
              <?php
                if (!isset($_SESSION['id'])) {
                    header("Location: login.php");
                    exit;
                }

                $conn = new mysqli('localhost', 'root', '', 'hirabay_db');
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $result = $conn->query("SELECT * FROM users WHERE id = {$_SESSION['id']}");
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                }
              ?>
              <div class="content">
                  <img src="pics/hirabayLogo.png" alt="">
                  <div class="details">
                      <span><?php echo $row['firstName'] . " " . $row['lastName']?></span>
                      <p><?php echo $row['status']?></p>
                  </div>
              </div>
              <button class="close">X</button>
            </header>
            <div class="search">
                <span class="text">Select an user to start chat</span>
                <input type="text" placeholder="Enter name to search...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="users-list">
            </div>
        </section>
      </div>
    </div>
    
    <div class="chat chat-overlay hidden">
      <div class="chat-wrapper">
        <section class="chat-area">
            <header>
              <?php
                if (!isset($_SESSION['id'])) {
                  header("Location: login.php");
                  exit;
                }
                
                $conn = new mysqli('localhost', 'root', '', 'hirabay_db');
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                $id = mysqli_real_escape_string($conn, $_SESSION['id']);
                
                $result = $conn->query("SELECT * FROM users WHERE id = $id");
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
              }
              ?>
              <a href="#" class="back-icon"><i class="fas fa-arrow-left"></i></a>
                <img src="pics\hirabayLogo.png" alt="">
                <div class="details">
                  <span><?php echo $row['firstName'] . " " . $row['lastName']?></span>
                  <p><?php echo $row['status']?></p>
                </div>
            </header>
            <div class="chat-box">

            </div>
            <form action="#" class="typing-area" autocomplete="off">
              <input type="hidden" name="outgoing_id" value="<?php echo $_SESSION['id']; ?>">
              <input type="hidden" name="incoming_id" value="">
              <input type="text" name="message" class="input-field" placeholder="Type a message here..">
              <button><i class="fab fa-telegram-plane"></i></button>
            </form>
        </section>
      </div>
    </div>
  </div>

<script>
  function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("open");
  }
  function toggleSettingsMenu() {
    const menu = document.getElementById("settingsMenu");
    menu.style.display = (menu.style.display === "flex") ? "none" : "flex";
  }
  window.addEventListener("click", function (e) {
    const settings = document.querySelector(".settings-wrapper");
    const menu = document.getElementById("settingsMenu");
    if (!settings.contains(e.target)) {
      menu.style.display = "none";
    }
  });
</script>
<script src="notification.js"></script>
<script src="template.js"></script>
</body>
</html>
