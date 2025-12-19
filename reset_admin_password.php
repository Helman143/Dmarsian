<?php
/**
 * Script to reset the password for the Mr.Mars admin account
 * SECURITY: This should be deleted after use
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SECURITY: Set a secret key to prevent unauthorized access
$SECRET_KEY = 'RESET_PASSWORD_KEY_12345';

// Check for secret key
$provided_key = $_GET['key'] ?? '';
if ($provided_key !== $SECRET_KEY) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>Access Denied</title></head><body><h1>403 - Access Denied</h1><p>Invalid or missing security key.</p><p>Usage: reset_admin_password.php?key=RESET_PASSWORD_KEY_12345&new_password=YOUR_NEW_PASSWORD</p></body></html>');
}

require_once 'db_connect.php';

// Check database connection
if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
    die("ERROR: Database connection failed\n");
}

// Get new password from URL parameter or use default
$new_password = $_GET['new_password'] ?? 'admin123';

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update the password for Mr.Mars account
$update_stmt = $conn->prepare("UPDATE admin_accounts SET password = ? WHERE username = 'Mr.Mars' OR email = 'mars@gmail.com'");
$update_stmt->bind_param("s", $hashed_password);

if ($update_stmt->execute()) {
    if ($update_stmt->affected_rows > 0) {
        echo "<!DOCTYPE html><html><head><title>Password Reset</title>";
        echo "<style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
            .success-box { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .success { color: #28a745; font-weight: bold; font-size: 18px; margin-bottom: 20px; }
            .info { background: #e7f3ff; padding: 15px; border-radius: 3px; margin: 15px 0; }
            .warning { background: #fff3cd; padding: 15px; border-radius: 3px; margin: 15px 0; color: #856404; }
            .btn { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; margin-top: 20px; }
            .btn:hover { background: #218838; }
            table { width: 100%; margin: 15px 0; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f8f9fa; }
        </style></head><body>";
        echo "<div class='success-box'>";
        echo "<div class='success'>✓ Password Reset Successfully!</div>";
        echo "<div class='info'>";
        echo "<h3>New Login Credentials:</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>Username</td><td>Mr.Mars</td></tr>";
        echo "<tr><td>Email</td><td>mars@gmail.com</td></tr>";
        echo "<tr><td>New Password</td><td><strong>" . htmlspecialchars($new_password) . "</strong></td></tr>";
        echo "</table>";
        echo "</div>";
        echo "<div class='warning'>";
        echo "<strong>⚠️ Security Warning:</strong><br>";
        echo "1. Delete this file (reset_admin_password.php) after use<br>";
        echo "2. Change your password after logging in<br>";
        echo "3. Use a strong password for production";
        echo "</div>";
        echo "<a href='admin_login.php' class='btn'>Go to Login Page</a>";
        echo "</div></body></html>";
    } else {
        echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
        echo "<h2 style='color: red;'>No account found to update</h2>";
        echo "<p>The Mr.Mars account was not found in the database.</p>";
        echo "</body></html>";
    }
} else {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h2 style='color: red;'>Error resetting password</h2>";
    echo "<p>Error: " . htmlspecialchars($update_stmt->error) . "</p>";
    echo "</body></html>";
}

$update_stmt->close();
$conn->close();
?>














