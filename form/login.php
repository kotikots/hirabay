<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
</head> 
<body>
    <div class="login-container">
        <div class="loginForm">
            <h1><span class="orange">Log</span> <span class="blue">In</span></h1>
            <div class="login-form">
                <form action="reg.php" method="post" onsubmit="loginAlert()">
                    <div class="login-fields">
                        <div class="input-box">
                            <input type="email" name="email" id="email" placeholder="Email" required>
                            <i class="bx bxs-user"></i>
                        </div>
                        <div class="input-box">
                            <input type="password" name="password" id="password" placeholder="Password" required>
                            <i class="bx bxs-lock-alt"></i>
                        </div>
                    </div>
                    <div class="others">
                    <div class="others">
                        <input type="checkbox" id="showPass" onclick="showPassword()">Show Password
                        <a href="recovery.php">Forget Password?</a>
                    </div>
                    </div>
                    <button type="submit" class="loginBtn" name="loginSubmit"><b>Log In</b></button>
                    <p class="register">Don't have an account? <a href="register.php">Register</a></p>   
                </form>
            </div>
        </div>
        <div class="message-container">
            <div class="header">
                <img src="pictures/hiraBayLogo.png" alt="">
                <h1><span class="orange">Hira</span><span class="blue">Bay</span></h1>
            </div>
            <h3>Hustle, Haggle, High-Five.</h3>
            <p>Buy and sell textbooks, gadgets, dorm essentials, and more — right on campus.</p>
        </div>
    </div>
    <script src="login.js"></script>
</body>
</html>