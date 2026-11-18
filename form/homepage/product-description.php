<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hirabay_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$item = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT items.*, CONCAT(users.firstName, ' ', users.lastName) AS seller_name, users.contactNum AS sellerContact
        FROM items 
        JOIN users ON items.user_id = users.id
        WHERE items.id = $id";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $sellerId = $item['user_id']; 
        $sellerName = $item['seller_name'];
        $sellerContact = $item['sellerContact'];
    }
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = intval($_POST['item_id']);
    if (!in_array($item_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $item_id;
        header("Location: cart_session.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="chat.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
  <title>Product Description</title>
  <style>
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    body {
      background-color: #b9c1cd;
      padding: 40px;
    }
    .container {
      max-width: 1200px;
      margin: auto;
      background: #fff;
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .product-wrapper {
      display: flex;
      gap: 60px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .product-image {
      flex: 1 1 400px;
    }
    .product-image img {
      width: 100%;
      height: auto;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .product-info {
      flex: 1 1 500px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      margin-left: 60px;
    }
    .product-info h2 {
      margin-bottom: 15px;
      font-size: 28px;
      color: #1c2738;
    }
    .product-info p {
      margin: 8px 0;
      font-size: 16px;
    }
    .product-info .price {
      font-size: 22px;
      font-weight: bold;
      margin-top: 12px;
    }
    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 40px;
      justify-content: center;
    }

    .actions > * {
      flex: 1 1 200px;
    }

    .actions form button {
      width: 100%;
    }
    button {
      padding: 12px 18px;
      background-color: #1f2a44;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      width: 100%; /* Keep this if buttons inside forms should span their parent fully */
    }
    button:hover {
      background-color: #324463;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 30px;
      text-decoration: none;
      background: #ccc;
      color: black;
      padding: 10px 20px;
      border-radius: 6px;
    }
  </style>
</head>
<body>
<div id="seller-data" 
     data-id="<?php echo $sellerId; ?>" 
     data-name="<?php echo htmlspecialchars($sellerName); ?>" 
     data-status="Active now">
</div>
<div class="container">
  <?php if ($item): ?>
    <a class="back-btn" href="mainpage.php">← Back</a>
    <div class="product-wrapper">
      <!-- Image -->
      <div class="product-image">
        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item">
      </div>

      <!-- Product info -->
      <div class="product-info">
        <h2><?php echo htmlspecialchars($item['title']); ?></h2>
        <p><strong>College:</strong> <?php echo htmlspecialchars($item['college']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
        <p><strong>Condition:</strong> <?php echo htmlspecialchars($item['conditionItem']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
        <p><strong>Posted By:</strong> <?php echo htmlspecialchars($sellerName); ?></p>
        <p><strong>Meetup Place:</strong> <?php echo htmlspecialchars($item['meetup']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($item['saleBorrow']); ?></p>
        <p class="price">₱<?php echo number_format($item['price'], 2); ?></p>
      </div>
    </div>

    <div class="actions">
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
      <button >📞 <?php echo htmlspecialchars($sellerContact); ?></button>
      <button onclick="startChatWith(<?php echo $sellerId; ?>)">💬 Chat Seller</button>

      <?php if (strtolower($item['saleBorrow']) === 'borrow'): ?>
        <button onclick="sendBorrowRequest(<?php echo $item['id']; ?>, <?php echo $_SESSION['id']; ?>, <?php echo $sellerId; ?>)">📄 Borrow Request</button>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
        <button type="submit">🛒 Add to Cart</button>
      </form>
    </div>

  <?php else: ?>
    <p>Item not found.</p>
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
  function startChatWith(sellerId) {
    const chatPanel = document.querySelector('.chat.chat-overlay');
    const incomingInput = chatPanel.querySelector('input[name="incoming_id"]');
    incomingInput.value = sellerId;

    // Show the chat overlay
    chatPanel.classList.remove('hidden');

    // Load seller info from the hidden div
    const sellerData = document.getElementById('seller-data');
    const name = sellerData.getAttribute('data-name');
    const status = sellerData.getAttribute('data-status');

    // Update chat header with seller info
    const headerDetails = chatPanel.querySelector('header .details');
    if (headerDetails) {
      headerDetails.innerHTML = `
        <span>${name}</span>
        <p>${status}</p>
      `;
    }
  }

  function sendBorrowRequest(itemId, borrowerId, sellerId) {
    fetch('php/send_borrow_request.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `item_id=${itemId}&borrower_id=${borrowerId}&seller_id=${sellerId}`
    })
    .then(res => res.text())
    .then(response => {
      alert("Borrow request sent!");
      // Optional: change button to "Requested"
    });
  }
</script>
<script src="template.js"></script>

</body>
</html>
