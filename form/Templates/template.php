<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HiraBay</title>
  <link rel="stylesheet" href="template.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
</head>
<body>
    <div class="navbar">
    <div class="navbar-left">
      <img src="pics/hiraBay.png" alt="HiraBay Logo" class="logo-img">
      <span class="brand">
        <span class="orange">Hira</span><span class="blue">Bay</span>
      </span>
    </div>
  
    <div class="navbar-center">
      <div class="search-container">
        <span class="search-icon">🔍</span> 
        <input type="text" placeholder="Search for anything you need...">
        <span class="filter-icon">⚙️</span>
      </div>
    </div>
  
    <div class="navbar-right">
      <div class="icon notification-icon">
        🔔
        <span class="notification-count">3</span>
      </div>
      <div class="icon settings-wrapper" onclick="toggleSettingsMenu()">
        ⚙️
        <div class="settings-menu" id="settingsMenu">
          <button><span>❓</span> Help & Support</button>
          <button><span>⚙️</span> Settings</button>
          <a href="php/logout.php"><span>↩️</span> Log Out</a>
        </div>
      </div>
    </div>
  </div>
  <div class="sidebar open" id="sidebar">
    <button class="toggle-btn" onclick="toggleSidebar()">〈</button>
    <div class="menu">
      <button><span class="icon">👤</span><span class="label">USER PROFILE</span></button>
      <button><span class="icon">🏠</span><span class="label">Home</span></button>
      <button><span class="icon">📋</span><span class="label">Community Board</span></button>
      <button><span class="icon">➕</span><span class="label">Create New</span></button>
      <button><span class="icon">📦</span><span class="label">My Listings</span></button>
      <button><span class="icon">🛒</span><span class="label">My Cart</span></button>
      <button><span class="icon">📥</span><span class="label">Borrowed/Lent Items</span></button>
      <button><span class="icon">📄</span><span class="label">Transaction History</span></button>
    </div>
  </div>
  
  <div class="chatBtn">
    <button>Chat <i class="fas fa-comments"></i></button>
  </div>

  <div class="chat chat-panel hidden">
    <div class="chat-wrapper">
      <section class="users">
          <header>
            <?php
              session_start();
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

<script src="template.js"></script>

</body>
</html>