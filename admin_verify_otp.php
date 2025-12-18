<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Super Admin</title>
    <link rel="stylesheet" href="Styles/admin_login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <img src="Picture/Logo2.png" alt="Logo">
            </div>
            <h2>Verify OTP</h2>
            <?php if (isset($_GET['ok'])): ?>
                <p class="error-message" style="color:#2e7d32">Password has been reset. You may now log in.</p>
            <?php elseif (isset($_GET['error'])): ?>
                <p class="error-message">Invalid or expired OTP. Please try again.</p>
            <?php endif; ?>
            <form action="admin_reset_password.php" method="POST">
                <div class="input-group">
                    <input id="email" type="email" name="email" required>
                    <label>Admin Email</label>
                </div>
                <div class="input-group">
                    <input id="otp" type="text" name="otp" pattern="[0-9]{6}" inputmode="numeric" maxlength="6" required>
                    <label>OTP (6 digits)</label>
                </div>
                <div class="input-group">
                    <input id="new_password" type="password" name="new_password" required>
                    <label>New Password</label>
                </div>
                <div class="input-group">
                    <input id="confirm_password" type="password" name="confirm_password" required>
                    <label>Confirm Password</label>
                </div>
                <button type="submit" class="login-btn">Reset Password</button>
            </form>
            <div style="margin-top:12px;text-align:center">
                <a href="forgot_admin_password.php" style="text-decoration:none;color:#1976d2">Need a new OTP? Request again</a>
            </div>
            <div style="margin-top:8px;text-align:center">
                <a href="admin_login.php" style="text-decoration:none;color:#555">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>


