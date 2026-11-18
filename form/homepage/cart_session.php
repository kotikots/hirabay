<?php
session_start();

if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit;
}

$loggedInUserId = $_SESSION['id'];

$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Remove item if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $remove_id = intval($_POST['cart_id']);
    if (($key = array_search($remove_id, $_SESSION['cart'] ?? [])) !== false) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
    }
    header("Location: cart_session.php");
    exit();
}

// Load cart items grouped by seller (sample key: college as seller identifier)
$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', $_SESSION['cart']));
    $sql = "SELECT items.*, users.id AS seller_id, users.firstName, users.lastName 
        FROM items 
        JOIN users ON items.user_id = users.id 
        WHERE items.id IN ($ids)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
      $groupKey = $row['seller_id'];
      $cart_items[$groupKey]['name'] = $row['firstName'] . ' ' . $row['lastName'];
      $cart_items[$groupKey]['items'][] = $row;
    }
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
  <meta charset="UTF-8">
  <link rel="stylesheet" href="notification.css" />
  <link rel="stylesheet" href="chat.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
  <title>My Cart</title>
  <style>
    /* Reset */
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
    .main-content { margin-left: 220px; padding: 110px 40px 40px; min-height: calc(100vh - 70px); transition: margin-left 0.3s ease; }
    .sidebar:not(.open) ~ .main-content { margin-left: 70px; }
    .main-content h2 { margin-bottom: 30px; }
    .seller-group { background-color: #fff; border: 2px solid #ccc; border-radius: 10px; margin-bottom: 30px; padding: 20px; }
    .seller-group h3 { margin-bottom: 20px; color: #333; }
    .cart-item { display: flex; align-items: center; justify-content: space-between;
      background-color:rgb(238, 238, 238); margin-bottom: 15px; padding: 15px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    .cart-item img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-right: 20px; }
    .item-info { flex: 1; }
    .item-info h4 { margin: 0 0 5px; }
    .item-info p { margin: 3px 0; font-size: 14px; }
    .cart-actions { display: flex; flex-direction: row; gap: 10px; }
    .cart-actions button { padding: 8px 12px; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
    .chat-btn { background-color: #4CAF50; color: white; }
    .chat-btn:hover { background-color: #3e8e41; }
    .remove-btn { background-color: #ff4d4d; color: white; }
    .remove-btn:hover { background-color: #cc0000; }
  </style>
</head>
<body>

  <div class="navbar">
    <div class="navbar-left">
      <img src="pics/logo.png" alt="HiraBay Logo" class="logo-img">
      <span class="brand"><span class="orange">Hira</span><span class="blue">Bay</span></span>
    </div>

    <div class="navbar-center">
      <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Search your cart...">
        <button type="button" class="filter-btn" onclick="openFilterOverlay()">
          <i class="fas fa-sliders-h"></i>
        </button>
      </div>
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
    <button onclick="window.location.href='mainpage.php'"><span class="icon">🏠</span><span class="label">Home</span></button>
      <button onclick="window.location.href='comu.php'"><span class="icon">📋</span><span class="label">Community Board</span></button>
      <button onclick="window.location.href='listing2.php'"><span class="icon">➕</span><span class="label">Create New</span></button>
      <button onclick="window.location.href='my_listings.php'"><span class="icon">📦</span><span class="label">My Listings</span></button>
      <button style="background-color: #324463; border-radius: 8px;"><span class="icon">🛒</span><span class="label">My Cart</span></button>
      <button><span class="icon">📥</span><span class="label"><a href="borrowed.php">Borrowed/Lent Items</a></span></button>
      <button><span class="icon">📄</span><span class="label"><a href="transaction_history.php">Transaction History</a></span></button>
    </div>
  </div>

  <div class="main-content">
    <h2>🛒 My Cart</h2>
    <?php if (!empty($cart_items)): ?>
      <?php foreach ($cart_items as $sellerId => $sellerData): ?>
        <div class="seller-group">
          <h3>Seller: <?php echo htmlspecialchars($sellerData['name']); ?></h3>
          <?php foreach ($sellerData['items'] as $item): ?>
            <div class="cart-item">
              <div class="seller-data" 
                  data-id="<?php echo $item['seller_id']; ?>" 
                  data-name="<?php echo htmlspecialchars($item['firstName'] . ' ' . $item['lastName']); ?>" 
                  data-status="Active now">
              </div>
              <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image">
              <div class="item-info">
                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                <p><strong>Price:</strong> ₱<?php echo number_format($item['price'], 2); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
                <p><strong>Condition:</strong> <?php echo htmlspecialchars($item['conditionItem']); ?></p>
              </div>
              <div class="cart-actions">
              <button class="chat-btn" onclick="startChatWith(this)">💬 Chat Seller</button>
                <form method="POST">
                  <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                  <button type="submit" class="remove-btn">❌ Remove</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; font-size: 18px; margin-top: 40px;">Your cart is empty.</p>
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
    function startChatWith(button) {
      const container = button.closest('.cart-item').querySelector('.seller-data');
      const sellerId = container.getAttribute('data-id');
      const sellerName = container.getAttribute('data-name');
      const status = container.getAttribute('data-status');

      const chatPanel = document.querySelector('.chat.chat-overlay');
      chatPanel.classList.remove('hidden');
      chatPanel.querySelector('input[name="incoming_id"]').value = sellerId;

      const headerDetails = chatPanel.querySelector('header .details');
      if (headerDetails) {
        headerDetails.innerHTML = `<span>${sellerName}</span><p>${status}</p>`;
      }
    }

  </script>
  <script src="notification.js"></script>
  <script src="template.js"></script>

</body>
</html>
