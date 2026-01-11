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
                <?php
                $errorMessages = [
                    'missing' => 'Please fill in all fields.',
                    'mismatch' => 'Passwords do not match. Please try again.',
                    'notfound' => 'No OTP found for this email. Please request a new OTP.',
                    'consumed' => 'This OTP has already been used. Please request a new OTP.',
                    'expired' => 'This OTP has expired. Please request a new OTP.',
                    'invalid' => 'Invalid OTP code. Please check and try again.',
                    'toomany' => 'Too many failed attempts. Please request a new OTP.',
                    'noaccount' => 'Admin account not found for this email.',
                    '1' => 'Invalid or expired OTP. Please try again.'
                ];
                $errorType = $_GET['error'] ?? '1';
                $errorMsg = $errorMessages[$errorType] ?? $errorMessages['1'];
                ?>
                <p class="error-message"><?php echo htmlspecialchars($errorMsg); ?></p>
            <?php endif; ?>
            <form action="admin_reset_password.php" method="POST" onsubmit="return validateForm()">
                <div class="input-group">
                    <input id="email" type="email" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
                    <label>Admin Email</label>
                </div>
                <div class="input-group">
                    <input id="otp" type="text" name="otp" pattern="[0-9]{6}" inputmode="numeric" maxlength="6" required>
                    <label>OTP (6 digits)</label>
                </div>
                <div class="input-group">
                    <input id="new_password" type="password" name="new_password" required>
                    <label>New Password</label>
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword('new_password', this)">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                <div class="input-group">
                    <input id="confirm_password" type="password" name="confirm_password" required>
                    <label>Confirm Password</label>
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility" onclick="togglePassword('confirm_password', this)">
                        <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
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
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.classList.add('is-visible');
            } else {
                input.type = 'password';
                button.classList.remove('is-visible');
            }
        }

        function validateForm() {
            const email = document.getElementById('email').value.trim();
            const otp = document.getElementById('otp').value.trim();
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!email || !otp || !newPassword || !confirmPassword) {
                alert('Please fill in all fields.');
                return false;
            }

            if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
                alert('OTP must be exactly 6 digits.');
                return false;
            }

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match. Please try again.');
                return false;
            }

            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters long.');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>


