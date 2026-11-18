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

// Fetch all items
$sql = "SELECT id, title, college, category, contact, saleBorrow, meetup, description, conditionItem, price, image FROM items ORDER BY id DESC";  
$result = $conn->query($sql);
$items = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $items[] = $row;
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homepage</title>
  <link rel="stylesheet" href="main.css">
  <link rel="stylesheet" href="chat.css" />
  <link rel="stylesheet" href="notification.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
  <style>
    #mainListings {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      padding: 20px 40px;
      justify-content: center;
    }
    .listing-card {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      flex: 1 1 calc(20% - 20px);
      max-width: 200px;
      height: 330px;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      padding: 12px;
      transition: transform 0.2s ease;
    }

    .listing-card:hover {
      transform: scale(1.03);
    }

    .listing-card img {
      width: 100%;
      height: 130px;
      object-fit: cover;
      border-radius: 6px;
      margin-bottom: 10px;
    }

    .listing-details {
      font-size: 13px;
      line-height: 1.4;
      text-align: left;
    }

    .listing-details p {
      margin: 3px 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .top-buttons {
      text-align: center;
      margin-top: 40px;
    }

    .top-buttons button {
      margin: 10px;
      padding: 14px 28px;
      font-size: 25px;
      border: none;
      background-color: #1f2a44;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      width: 200px;
    }

    .top-buttons button:hover {
      background-color: #007bff;
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

    .sidebar {
      white-space: nowrap;
    }
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .overlay.hidden {
      display: none !important;
    }

    #filterOverlay .filter-form {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      width: 300px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    #filterOverlay .filter-form h3 {
      margin-bottom: 15px;
      text-align: center;
    }

    #filterOverlay .filter-form label {
      display: block;
      margin: 10px 0 5px;
      font-weight: bold;
    }

    #filterOverlay .filter-form input,
    #filterOverlay .filter-form select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    #filterOverlay .form-actions {
      margin-top: 15px;
      display: flex;
      justify-content: space-between;
    }

    #filterOverlay .form-actions button {
      padding: 8px 12px;
      border: none;
      border-radius: 6px;
      background-color: #324463;
      color: white;
      cursor: pointer;
    }

    #filterOverlay .form-actions button:hover {
      background-color: #007bff;
    }
    .filter-btn {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #1c2738; /* Make sure it's visible on white bg */
    }
    .filter-icon {
      font-size: 20px;
      cursor: pointer;
      padding: 10px;
      user-select: none;
    }
  </style>
</head>
<body>

<!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <img src="pics/logo.png" alt="HiraBay Logo" class="logo-img">
      <span class="brand"><span class="orange">Hira</span><span class="blue">Bay</span></span>
    </div>

    <div class="navbar-center">
      <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" id="itemSearch" placeholder="Search for anything you need...">
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

<!-- Sidebar -->
<div class="sidebar open" id="sidebar">
  <button class="toggle-btn" onclick="toggleSidebar()">〈</button>
  <div class="menu">
    <button onclick="window.location.href='index.php'"><span class="icon">👤</span><span class="label">User Profile</span></button>
    <button style="background-color: #324463; border-radius: 8px;"><span class="icon">🏠</span><span class="label">Home</span></button>
    <button onclick="window.location.href='comu.php'"><span class="icon">📋</span><span class="label">Community Board</span></button>
    <button onclick="window.location.href='listing2.php'"><span class="icon">➕</span><span class="label">Create New</span></button>
    <button onclick="window.location.href='my_listings.php'"><span class="icon">📦</span><span class="label">My Listings</span></button>
    <button onclick="window.location.href='cart_session.php'"><span class="icon">🛒</span><span class="label">My Cart</span></button>
    <button><span class="icon">📥</span><span class="label"><a href="borrowed.php">Borrowed/Lent Items</a></span></button>
    <button><span class="icon">📄</span><span class="label"><a href="transaction_history.php">Transaction History</a></span></button>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">

  <div class="top-buttons">
    <button onclick="scrollToSection('buy-section')">Buy</button>
    <button onclick="scrollToSection('borrow-section')">Borrow</button>
  </div>

  <h2 id="buy-section" style=" margin-top: 30px;">🛒 Buy Listings</h2>
  <div id="mainListings">
    <?php foreach ($items as $row): ?>
      <?php if (strtolower($row['saleBorrow']) === 'sale'): ?>
        <a href="product-description.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
        <div class="listing-card" data-title="<?php echo strtolower($row['title'] . ' ' . $row['category'] . ' ' . $row['description']); ?>">
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Item Image">
            <div class="listing-details">
              <p><strong>Category:</strong> <?php echo $row['category']; ?></p>
              <p><strong>Price:</strong> ₱<?php echo number_format($row['price'], 2); ?></p>
              <p><strong>Description:</strong> <?php echo $row['description']; ?></p>
              <p><strong>Status:</strong> <?php echo $row['saleBorrow']; ?></p>
            </div>
          </div>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <h2 id="borrow-section" style=" margin-top: 40px;">📥 Borrow Listings</h2>
  <div id="mainListings">
    <?php foreach ($items as $row): ?>
      <?php if (strtolower($row['saleBorrow']) === 'borrow'): ?>
        <a href="product-description.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
        <div class="listing-card" data-title="<?php echo strtolower($row['title'] . ' ' . $row['category'] . ' ' . $row['description']); ?>">
            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Item Image">
            <div class="listing-details">
              <p><strong>Category:</strong> <?php echo $row['category']; ?></p>
              <p><strong>Price:</strong> ₱<?php echo number_format($row['price'], 2); ?></p>
              <p><strong>Description:</strong> <?php echo $row['description']; ?></p>
              <p><strong>Status:</strong> <?php echo $row['saleBorrow']; ?></p>
            </div>
          </div>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<!-- Filter Overlay -->
<div id="filterOverlay" class="overlay hidden">
  <div class="filter-form">
    <h3>Filter Listings</h3>
    <form method="GET" action="mainpage.php">
      <label for="category">Category</label>
      <select name="category" id="category">
        <option value="">All</option>
        <option value="Books">Books</option>
        <option value="Electronics">Electronics</option>
        <option value="Uniform">Uniform</option>
        <option value="Others">Others</option>
      </select>

      <label for="college">College</label>
      <select name="college" id="college">
        <option value="">All</option>
        <option value="COE">COE</option>
        <option value="CBA">CBA</option>
        <option value="CAS">CAS</option>
        <option value="CCS">CCS</option>
      </select>

      <label for="price_min">Min Price</label>
      <input type="number" name="price_min" min="0">

      <label for="price_max">Max Price</label>
      <input type="number" name="price_max" min="0">

      <div class="form-actions">
        <button type="submit">Apply Filter</button>
        <button type="button" onclick="closeFilterOverlay()">Cancel</button>
      </div>
    </form>
  </div>
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
                  <img src="pics\hirabayLogo.png" alt="">
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
  function scrollToSection(id) {
    const section = document.getElementById(id);
    if (section) {
      section.scrollIntoView({ behavior: 'smooth' });
    }
  }
  function openFilterOverlay() {
    document.getElementById('filterOverlay').classList.remove('hidden');
  }
  function closeFilterOverlay() {
    document.getElementById('filterOverlay').classList.add('hidden');
  }
  document.getElementById('itemSearch').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const cards = document.querySelectorAll('.listing-card');

    cards.forEach(card => {
      const text = card.getAttribute('data-title');
      if (text.includes(query)) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  });
</script>
<script src="template.js"></script>
<script src="notification.js"></script>

</body>
</html>
