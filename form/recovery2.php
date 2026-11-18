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
    <style>
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Quicksand', sans-serif;
        }

        html, body {
            width: 100%;
            height: 100%;
            background: #c3d2e0;
        }

        a {
            text-decoration: none;
            color: white;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .recovery-container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        .left-container {
            flex: 1;
            background: #c3d2e0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 40px;
            position: relative;
        }

        .left-container h1 {
            font-size: 48px;
            -webkit-text-stroke-width: 1px;
            -webkit-text-stroke-color: #000;
            margin-bottom: 50px;
        }

        .left-container .orange {
            color: #f19028;
        }

        .left-container .blue {
            color: #3a86ff;
        }

        .left-form-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            gap: 15px;
            margin-top: -70px;
        }

        .left-form-content h3 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            text-align: center;
        }

        .subtext {
            font-size: 16px;
            font-weight: normal;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .input-wrapper {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #000;
            border-radius: 10px;
            font-size: 16px;
        }

        .input-wrapper i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #333;
        }

        .button {
            width: 200px;
            padding: 12px;
            background-color: #f19028;
            border: 2px solid black;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #d97811;
        }

        .right-container {
            flex: 1;
            background-image: url('pictures/sign.png');
            background-size: cover;
            background-position: center;
            border-radius: 60px 0 0 60px;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .header {
            display: flex;
            align-items: center;
            position: absolute;
            top: 30px;
            left: 30px;
            gap: 10px;
        }

        .right-container img {
            width: 70px;
            height: auto;
        }

        .right-container .header h1 {
            font-size: 28px;
            margin: 0;
        }

        .right-container h1 .orange {
            color: #f19028;
        }

        .right-container h1 .blue {
            color: #3a86ff;
        }

        .right-container button:hover {
            background: #d97811;
        }

        .right-container h3 {
            font-size: 40px;
            margin-top: 10px;
        }

        .right-container p {
            margin-top: 10px;
            max-width: 400px;
            text-align: center;
            font-size: 20px;
        }

        .check-container {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            width: 100%;
            max-width: 400px;
            margin-bottom: 15px;
            font-size: 14px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="left-container">
            <h1><span class="orange">Account</span> <span class="blue">Recovery</span></h1>
        
            <div class="left-form-content">
                <h3>Enter a New Password</h3>
                <p class="subtext">Make sure to not forget it next time.</p>
                <form action="updatePass.php" method="post">
                    <div class="input-wrapper">
                        <input type="email" name="email" placeholder="Email" required>
                        <i class="bx bxs-user"></i>
                    </div>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <i class="bx bxs-lock"></i>
                    </div>
                    <div class="input-wrapper">
                        <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm Password" required>
                        <i class="bx bxs-lock"></i>
                        <br>
                    </div>
                    <div class="check-container">
                        <input type="checkbox" id="showPass" onclick="showPassword()">
                        <label for="showPass">Show Password</label>
                    </div>
                    <button type="submit" class="button" name="confirm">Confirm</button>
                </form>
            </div>
        </div>
        <div class="right-container">
            <div class="header">
                <img src="pictures/hiraBayLogo.png" alt="">
                <h1><span class="orange">Hira</span><span class="blue">Bay</span></h1>
            </div>
            <h3>Hustle, Haggle, High-Five.</h3>
            <p>Buy and sell textbooks, gadgets, dorm essentials, and more — right on campus.</p>
        </div>
    </div>
    <script>
        document.querySelector('.button').onclick = function() {
            let password = document.querySelector('#password').value,
            confirmPassword = document.querySelector('#confirm-password').value;

            if(password != confirmPassword) {
                alert("Password must match. Please try again")
                return false
            }
        }

        function showPassword() {
            let fields = ["password", "confirm-password"];
            fields.forEach(id => {
                let input = document.getElementById(id);
                if (input) {
                    input.type = input.type === "password" ? "text" : "password";
                }
            });
        }
    </script>
</body>
</html>