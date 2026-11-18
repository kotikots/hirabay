<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="recovery.css">
</head>
<body>
    <div class="recovery-container">
        <div class="left-container">
            <h1><span class="orange">Account</span> <span class="blue">Recovery</span></h1>
            <h3>Please enter your personal email to receive instructions to recover your account.</h3>
            <form action="getEmail.php" method="post">
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <i class="bx bxs-user"></i>
                  </div>
                <button type="submit" name="recovBtn">Submit</button>
            </form>
        </div>
        <div class="right-container">
            <div class="header">
                <img src="pictures/hiraBayLogo.png" alt="">
                <h1><span class="orange">Hira</span><span class="blue">Bay</span></h1>
            </div>
            <button><a href="login.php">Back to Login</a></button>
            <h3>Hustle, Haggle, High-Five.</h3>
            <p>Buy and sell textbooks, gadgets, dorm essentials, and more — right on campus.</p>
        </div>
    </div>
</body>
</html>