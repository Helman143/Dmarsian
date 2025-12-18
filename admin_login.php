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
                    <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false">
                        <span class="eye eye-open" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path fill="currentColor" d="M12 5c-5.5 0-9.7 4.4-11 6.6a1 1 0 0 0 0 .8C2.3 14.6 6.5 19 12 19s9.7-4.4 11-6.6a1 1 0 0 0 0-.8C21.7 9.4 17.5 5 12 5zm0 12c-3.3 0-6-2.7-6-5s2.7-5 6-5 6 2.7 6 5-2.7 5-6 5zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                            </svg>
                        </span>
                        <span class="eye eye-closed" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path fill="currentColor" d="M2.3 4.3a1 1 0 0 1 1.4 0l16 16a1 1 0 1 1-1.4 1.4l-2.1-2.1c-1.3.5-2.7.8-4.2.8-5.5 0-9.7-4.4-11-6.6a1 1 0 0 1 0-.8c.7-1.3 2.4-3.5 4.9-5.1L2.3 5.7a1 1 0 0 1 0-1.4zM8 10.2a4 4 0 0 0 5.8 5.8l-1.5-1.5a2 2 0 0 1-2.8-2.8L8 10.2zM12 7c1.5 0 2.9.3 4.2.8l-2 2A3.9 3.9 0 0 0 12 8c-.6 0-1.2.1-1.7.3l-1.6-1.6C9.8 6.3 10.9 6 12 6.9V7zM20.8 12c-.6 1.1-2 2.9-4 4.2l-1.4-1.4c1.3-.9 2.2-1.9 2.7-2.8-1.2-2-3.6-4-6.1-4-.4 0-.8 0-1.2.1L9.3 5.6c.9-.3 1.8-.5 2.7-.5 5.5 0 9.7 4.4 11 6.6a1 1 0 0 1 0 .8z"/>
                            </svg>
                        </span>
                    </button>
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
    <script src="Scripts/password-toggle.js"></script>
</body>
</html> 