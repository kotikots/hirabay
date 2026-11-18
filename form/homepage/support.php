<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help & Support - HiraBay</title>
  
  <link rel="stylesheet" href="help.css">
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <div class="navbar-left">
      <img src="pics/logo.png" alt="Logo" class="logo-img" />
      <span class="brand"><span class="orange">Hira</span><span class="blue">Bay</span></span>
    </div>
    <div class="navbar-center">
      <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Search posts...">
        <span class="filter-icon">⚙️</span>
      </div>
    </div>
    <div class="navbar-right">
      <div class="icon notification-icon">🔔<span class="notification-count">3</span></div>
      <div class="icon settings-wrapper" onclick="toggleSettingsMenu()">⚙️
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
      <button onclick="window.location.href='mainpage.php'"><span class="icon">🏠</span><span class="label">Home</span></button>
      <button onclick="window.location.href='comu.php'"><span class="icon">📋</span><span class="label">Community Board</span></button>
      <button onclick="window.location.href='listing2.html'"><span class="icon">➕</span><span class="label">Create New</span></button>
      <button onclick="window.location.href='my_listings.php'"><span class="icon">📦</span><span class="label">My Listings</span></button>
      <button onclick="window.location.href='cart_session.php'"><span class="icon">🛒</span><span class="label">My Cart</span></button>
      <button><span class="icon">📥</span><span class="label">Borrowed/Lent Items</span></button>
      <button><span class="icon">📄</span><span class="label">Transaction History</span></button>
    </div>
  </div>

  <!-- Main Content -->
  <main class="main-content">
    <h2 class="section-title">Help & Support Center</h2>

    <div class="support-chat-container">
      <div class="chat-messages" id="chatMessages"></div>

      <div class="chat-input">
        <input type="text" id="userInput" placeholder="Type your message...">
        <button onclick="sendMessage()">Send</button>
      </div>
    </div>

  </main>

  <script src="support.js"></script>
</body>
</html>
