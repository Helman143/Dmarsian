<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - D'MARSIANS Taekwondo System</title>
    <link rel="stylesheet" href="Styles/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="Picture/Logo2.png" alt="Logo">
            </div>
            <h2>ADMIN LOGIN</h2>
            <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <p class="error-message">Invalid username/email or password</p>
            <?php endif; ?>
            <form action="login_process.php" method="POST">
                <input type="hidden" name="login_type" value="user">
                <div class="input-group">
                    <input id="username" type="text" name="username" required>
                    <label>Username or Email</label>
                </div>
                <div class="input-group">
                    <input id="password" type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>
            <!-- <p style="margin-top: 20px; color: #666;">Don't have an account? <a href="signup.php" style="color: #0f0; text-decoration: none;">Sign up here</a></p> -->
        </div>
    </div>
</body>
</html> 