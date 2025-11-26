<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Sign Up</title>
    <link rel="stylesheet" href="Styles/signup.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="Picture/Logo2.png" alt="Logo">
            </div>
            <h2>SIGN UP</h2>
            <form action="signup_process.php" method="POST">
                <div class="input-group">
                    <input id="name" type="text" name="name" required>
                    <label>Name</label>
                </div>
                <div class="input-group">
                    <input id="email" type="email" name="email" required>
                    <label>Email</label>
                </div>
                <div class="input-group">
                    <input id="password" type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit" class="login-btn">SIGN UP</button>
            </form>
            <p style="margin-top: 20px; color: #666;">Already have an account? <a href="index.php" style="color: #0f0; text-decoration: none;">Login here</a></p>
        </div>
    </div>
</body>
</html> 