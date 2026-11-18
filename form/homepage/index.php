<?php
  session_start();

  // Ensure the user is logged in
  if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
  }

  $conn = new mysqli("localhost", "root", "", "hirabay_db");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $loggedInUserId = $_SESSION['id'];

  $userQuery = $conn->query("SELECT * FROM users WHERE id = $loggedInUserId");
  if ($userQuery && $userQuery->num_rows > 0) {
      $userData = $userQuery->fetch_assoc();
  } else {
      $userData = null;
  }

  $sql = "SELECT id, description, image, created_at FROM community_board 
          WHERE user_id = $loggedInUserId 
          ORDER BY created_at DESC";
  $result = $conn->query($sql);

  $posts = [];
  if ($result && $result->num_rows > 0) {
    while ($postRow = $result->fetch_assoc()) {
      $posts[] = $postRow;
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Profile</title>
  <link rel="stylesheet" href="user.css" />
  <link rel="stylesheet" href="chat.css" />
  <link rel="stylesheet" href="notification.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
</head>
<body>

  <!-- Navbar -->
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
        <?php while ($borrow = $result2->fetch_assoc()): ?>
          <div class="notif-item">
            <img src="pics/hirabayLogo.png" class="notif-pic">
            <div class="notif-content">
              <strong><?php echo $borrow['firstName'] . ' ' . $borrow['lastName']; ?></strong>
              <p>wants to borrow <em><?php echo $borrow['title']; ?></em></p>
              <div class="notif-actions">
                <button class="confirm-btn" data-id="<?php echo $borrow['id']; ?>">Confirm</button>
                <button class="delete-btn" data-id="<?php echo $borrow['id']; ?>">Delete</button>
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
      <button style="background-color: #324463; border-radius: 8px;"><span class="icon">👤</span><span class="label">User Profile</span></button>
      <button onclick="window.location.href='mainpage.php'"><span class="icon">🏠</span><span class="label">Home</span></button>
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
    <div class="profile-header">
      <img src="<?php echo isset($userData['profilePic']) ? 'uploads/' . htmlspecialchars($userData['profilePic']) : 'pics/hirabayLogo.png'; ?>" alt="Profile Picture" class="profile-pic">
        <div class="profile-info">
          <h2><?php echo htmlspecialchars($userData['firstName'] . " " . $userData['lastName']); ?></h2>
          <p><?php echo htmlspecialchars($userData['college']); ?></p>
          <p style="font-style: italic; color: gray;"><?php echo htmlspecialchars($userData['bio']); ?></p>
          <button onclick="openEditOverlay()">Edit Profile</button>
        </div>
      </div>
    <div class="section">
      <div class="section-title">My Posts</div>
      <div class="card-row" id="communityBoardContainer">
        <?php foreach ($posts as $post): ?>
          <div class="card">
            <p><?= htmlspecialchars($post['description']) ?></p>
            <?php if (!empty($post['image']) && file_exists("uploads-comu/" . $post['image'])): ?>
              <img src="uploads-comu/<?= htmlspecialchars($post['image']) ?>" 
                  style="width:100%; height:100px; object-fit:cover; border-radius:10px; margin-top:10px;">
            <?php else: ?>
              <div style="width:100%; height:100px; background:#ccc; border-radius:10px; margin-top:10px; display:flex; align-items:center; justify-content:center;">
                <span style="color:#666;">No Image</span>
              </div>
            <?php endif; ?>
            <div class="timestamp">
              <?= date("F j, Y g:i A", strtotime($post['created_at'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Edit Profile Overlay -->
  <div id="editProfileOverlay" class="overlay hidden">
    <div class="edit-form">
      <h3>Edit Profile</h3>
      <form id="profileEditForm" action="php/update_profile.php" method="POST" enctype="multipart/form-data">
        <label for="profilePic">Profile Picture</label>
        <input type="file" name="profilePic" accept="image/*">

        <label for="firstName">First Name</label>
        <input type="text" name="firstName" value="<?php echo isset($userData['firstName']) ? htmlspecialchars($userData['firstName']) : ''; ?>" required>


        <label for="lastName">Last Name</label>
        <input type="text" name="lastName" value="<?php echo isset($userData['lastName']) ? htmlspecialchars($userData['lastName']) : ''; ?>" required>

        <label for="college">College</label>
        <input type="text" name="college" value="<?php echo isset($userData['college']) ? htmlspecialchars($userData['college']) : ''; ?>">

        <label for="bio">Bio (max 50 characters)</label>
        <textarea name="bio" maxlength="50" rows="2"><?php echo isset($userData['bio']) ? htmlspecialchars($userData['bio']) : ''; ?></textarea>

        <div class="form-actions">
          <button type="submit">Save Changes</button>
          <button type="button" onclick="closeEditOverlay()">Cancel</button>
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
      document.getElementById('sidebar').classList.toggle('open');
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
      function openEditOverlay() {
        document.getElementById('editProfileOverlay').classList.remove('hidden');
      }

      function closeEditOverlay() {
        document.getElementById('editProfileOverlay').classList.add('hidden');
      }
      function openEditOverlay() {
        document.getElementById('editProfileOverlay').classList.remove('hidden');
      }

      function closeEditOverlay() {
        document.getElementById('editProfileOverlay').classList.add('hidden');
      }
  </script>
  
  <script src="template.js"></script>
  <script src="notification.js"></script>

</body>
</html>
