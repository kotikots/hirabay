<?php
$conn = new mysqli("localhost", "root", "", "hirabay_db");
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
  header("Location: ../login.php");
  exit;
}

$loggedInUserId = $_SESSION['id'];

$sql = "SELECT br.*, 
               u.firstName, u.lastName, 
               i.title, i.description, i.image,
               br.borrower_id, br.seller_id
        FROM borrow_requests br
        JOIN users u ON u.id = IF(br.borrower_id = $loggedInUserId, br.seller_id, br.borrower_id)
        JOIN items i ON br.item_id = i.id
        WHERE (br.seller_id = $loggedInUserId OR br.borrower_id = $loggedInUserId) 
          AND br.status = 'approved'
        ORDER BY br.request_date DESC";

$result = $conn->query($sql);
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
  <title>Borrowed/Lent Items</title>
  <link rel="stylesheet" href="borrowed.css" />
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
      <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Search your items...">
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
      <button><span class="icon">👤</span><span class="label"><a href="index.php">User Profile</a></span></button>
      <button><span class="icon">🏠</span><span class="label"><a href="mainpage.php">Home</a></span></button>
      <button><span class="icon">📋</span><span class="label"><a href="comu.php">Community Board</a></span></button>
      <button><span class="icon">➕</span><span class="label"><a href="listing2.php">Create New</a></span></button>
      <button><span class="icon">📦</span><span class="label"><a href="my_listings.php">My Listings</a></span></button>
      <button><span class="icon">🛒</span><span class="label"><a href="cart_session.php">My Cart</a></span></button>
      <button style="background-color: #324463; border-radius: 8px;"><span class="icon">📥</span><span class="label">Borrowed/Lent Items</span></button>
      <button><span class="icon">📄</span><span class="label"><a href="transaction_history.php">Transaction History</a></span></button>
    </div>
  </div>

  <div class="main-content">
    <h2>📥 My Borrowed/Lent Items</h2>
    <?php if ($result->num_rows == 0) {
        echo "<p style='margin-top:100px;'>No confirmed borrow records found.</p>";
    } else {
    while ($row = $result->fetch_assoc()): ?>
      <div class="borrow-card">
        <div class="borrow-image">
          <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Item Image">
        </div>
        <div class="borrow-details">
          <p><strong>Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
          <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
          <?php
            $roleText = ($row['borrower_id'] == $loggedInUserId) ? "Borrowed from" : "Lent to";
            $otherName = $row['firstName'] . ' ' . $row['lastName'];
          ?>
          <p><strong><?php echo $roleText; ?>:</strong> <?php echo $otherName; ?></p>
        </div>
        <div class="borrow-actions">
          <button class="returned-btn" onclick="requestReturn(<?php echo $row['id']; ?>)">Returned</button>
          <button class="report-btn" onclick="openReportOverlay('<?php echo $row['title']; ?>', '<?php echo $row['firstName'] . " " . $row['lastName']; ?>')">Report</button>
        </div>
      </div>
    <?php endwhile; }?>
  </div>

  <div id="reportOverlay" class="overlay hidden">
    <div class="report-form">
      <h3>Report</h3>
      <label>Title</label>
      <input type="text" id="reportTitle" readonly>

      <label>User Account</label>
      <input type="text" id="reportUser" readonly>

      <label>Select a Reason</label>
      <select id="reportReason">
        <option value="">-- Choose Reason --</option>
        <option value="Damaged">Damaged</option>
        <option value="Lost">Lost</option>
        <option value="Scam">Scam</option>
      </select>

      <div class="report-buttons">
        <button class="submit-btn" onclick="submitReport()">Submit</button>
        <button class="cancel-btn" onclick="closeReportOverlay()">Cancel</button>
      </div>
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

                $userResult = $conn->query("SELECT * FROM users WHERE id = {$_SESSION['id']}");
                if ($userResult && $userResult->num_rows > 0) {
                    $userRow = $userResult->fetch_assoc();
                }
              ?>
              <div class="content">
                  <img src="pics\hirabayLogo.png" alt="">
                  <div class="details">
                  <span><?php echo $userRow['firstName'] . " " . $userRow['lastName']?></span>
                  <p><?php echo $userRow['status']?></p>
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
    function openReportOverlay(title, user) {
      document.getElementById('reportTitle').value = title;
      document.getElementById('reportUser').value = user;
      document.getElementById('reportOverlay').classList.remove('hidden');
    }
    function closeReportOverlay() {
      document.getElementById('reportOverlay').classList.add('hidden');
    }
    function submitReport() {
      const title = document.getElementById('reportTitle').value;
      const user = document.getElementById('reportUser').value;
      const reason = document.getElementById('reportReason').value;

      if (!reason) {
        alert("Please select a reason.");
        return;
      }

      // Example: POST the report to PHP
      fetch('submit_report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `title=${encodeURIComponent(title)}&user=${encodeURIComponent(user)}&reason=${encodeURIComponent(reason)}`
      })
      .then(res => res.text())
      .then(data => {
        alert('Report submitted!');
        closeReportOverlay();
      });
    }
    function requestReturn(borrowId) {
      if (!confirm("Mark as returned? This will notify the seller for confirmation.")) return;

      fetch('php/request_return.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `borrow_id=${borrowId}`
      })
      .then(res => res.text())
      .then(msg => {
        if (msg === "success") {
          alert("Return request sent. Waiting for seller confirmation.");
          location.reload();
        } else {
          alert("Failed: " + msg);
        }
      });
    }
  </script>
  <script src="comu.js"></script>
  <script src="template.js"></script>
  <script src="notification.js"></script>
</body>
</html>
