<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login - D'MARSIANS Taekwondo System</title>
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
            <h2>SUPER ADMIN LOGIN</h2>
            <?php if (isset($_GET['error'])): ?>
                <?php if ($_GET['error'] == 1): ?>
                    <p class="error-message">Invalid username/email or password</p>
                <?php elseif ($_GET['error'] == 'db_connection_failed'): ?>
                    <p class="error-message">Database connection failed. Please contact administrator.</p>
                <?php else: ?>
                    <p class="error-message">Login failed. Please try again.</p>
                <?php endif; ?>
            <?php endif; ?>
            <form action="login_process.php" method="POST">
                <input type="hidden" name="login_type" value="admin">
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
            <div style="margin-top:12px;text-align:center">
                <a href="forgot_admin_password.php" style="text-decoration:none;color:#1976d2">Forgot password?</a>
            </div>
            <div style="margin-top:8px;text-align:center;font-size:12px">
                <a href="create_new_admin.php" style="text-decoration:none;color:#28a745">➕ Create New Admin Account</a>
            </div>
            <div style="margin-top:8px;text-align:center;font-size:12px">
                <a href="diagnose_admin_login.php" style="text-decoration:none;color:#888">🔧 Troubleshoot Login Issues</a>
            </div>
        </div>
    </div>
</body>
</html> 