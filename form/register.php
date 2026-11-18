<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="registration-container">
        <div class="left-side">
            <div class="logo">
                <img src="pictures\hiraBayLogo.png" alt="">
                <h1><span class="orange">Hira</span><span class="blue">Bay</span></h1>
            </div>
            <div class="description-container">
                <div class="description-header">
                    <h1>Welcome Students</h1>
                </div>
                <div class="description">
                    <h3>Buy and sell textbooks, gadgets, dorm essentials, and more - right on campus</h3>
                </div>
            </div>
        </div>
        <div class="right-side">
            <h1><span class="orange">Sign</span> <span class="blue">Up</span></h1>
            <div class="register-container">
                <div class="reg-form">
                    <form action="reg.php" method="post" enctype="multipart/form-data" id="regForm">
                        <input type="text" name="lastName" id="lastName" placeholder="Last Name" required>
                        <br>
                        <input type="text" name="firstName" id="firstName" placeholder="First Name" required>
                        <br>
                        <input type="text" name="studNum" id="studNum" placeholder="Student Number" required>
                        <br>
                        <input type="email" name="email" id="email" placeholder="Email Address" required>
                        <br>
                        <input type="tel" name="contactNum" id="contactNum" placeholder="Contact Number" required>
                        <br>
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <br>
                        <input type="password" name="password" id="confirm-password" placeholder="Confirm Password" required>
                        <br>
                        <input type="checkbox" id="showPass" onclick="showPassword()">Show Password
                        <br>
                        <div class="uploadCOR">
                            <i class="bx bx-upload"></i>
                            <label for="uploadCOR" class="custom-file-upload" id="file-label">
                                Upload COR
                            </label>
                            <input id="uploadCOR" type="file" name="uploadCOR" required>
                        </div>
                        <button type="submit" name="regSubmit" class="button"><b>Sign In</b></button>
                        <p class="login">Already have an account? <a href="login.php">Click Here</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function showPassword() {
            let fields = ["password", "confirm-password"];
            fields.forEach(id => {
                let input = document.getElementById(id);
                if (input) {
                    input.type = input.type === "password" ? "text" : "password";
                }
            });
        }

        document.getElementById('uploadCOR').addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Upload COR';
            document.getElementById('file-label').textContent = fileName;
        });

        document.querySelector('.button').onclick = function() {
            let password = document.querySelector('#password').value,
            confirmPassword = document.querySelector('#confirm-password').value;

            if(password != confirmPassword) {
                alert("Password must match. Please try again")
                return false
            }
        }
    </script>
</body>
</html>