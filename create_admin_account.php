<?php
/**
 * Script to create a new admin account in admin_accounts table
 * This is a one-time use script - delete it after use for security
 * 
 * SECURITY: Add a secret key to prevent unauthorized access
 * Usage: https://your-app.ondigitalocean.app/create_admin_account.php?key=YOUR_SECRET_KEY
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SECURITY: Set a secret key to prevent unauthorized access
// Change this to a random string and delete the file after use!
$SECRET_KEY = 'CHANGE_THIS_TO_A_RANDOM_STRING_12345';

// Check for secret key
$provided_key = $_GET['key'] ?? '';
if ($provided_key !== $SECRET_KEY) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>Access Denied</title></head><body><h1>403 - Access Denied</h1><p>Invalid or missing security key.</p><p><small>This script requires a secret key parameter for security.</small></p></body></html>');
}

require_once 'db_connect.php';

// Configuration
$email = 'helmandacuma5@gmail.com';
$username = 'helmandacuma5'; // Extract username from email or set custom
$default_password = 'YAMY@M143'; // Password for the admin account

// Check if account already exists
$check_stmt = $conn->prepare("SELECT id, username, email FROM admin_accounts WHERE email = ? OR username = ?");
$check_stmt->bind_param("ss", $email, $username);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $existing = $check_result->fetch_assoc();
    echo "<!DOCTYPE html><html><head><title>Account Exists</title></head><body>";
    echo "<h2>Account Already Exists</h2>";
    echo "<p>An admin account with this email or username already exists:</p>";
    echo "<ul>";
    echo "<li>ID: " . htmlspecialchars($existing['id']) . "</li>";
    echo "<li>Username: " . htmlspecialchars($existing['username']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($existing['email']) . "</li>";
    echo "</ul>";
    echo "<p><a href='admin_login.php'>Go to Login Page</a></p>";
    echo "</body></html>";
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// Hash the password
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Insert the new admin account
$insert_stmt = $conn->prepare("INSERT INTO admin_accounts (email, username, password) VALUES (?, ?, ?)");
$insert_stmt->bind_param("sss", $email, $username, $hashed_password);

if ($insert_stmt->execute()) {
    $new_id = $conn->insert_id;
    echo "<!DOCTYPE html><html><head><title>Account Created</title>";
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
    echo "<div class='success'>✓ Admin Account Created Successfully!</div>";
    echo "<div class='info'>";
    echo "<h3>Account Details:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    echo "<tr><td>ID</td><td>" . htmlspecialchars($new_id) . "</td></tr>";
    echo "<tr><td>Email</td><td>" . htmlspecialchars($email) . "</td></tr>";
    echo "<tr><td>Username</td><td>" . htmlspecialchars($username) . "</td></tr>";
    echo "<tr><td>Password</td><td>" . htmlspecialchars($default_password) . " <em>(default - change after first login)</em></td></tr>";
    echo "</table>";
    echo "</div>";
    echo "<div class='warning'>";
    echo "<strong>⚠️ Security Warning:</strong><br>";
    echo "1. Delete this file (create_admin_account.php) after use<br>";
    echo "2. Change your password after first login<br>";
    echo "3. Use a strong password for production";
    echo "</div>";
    echo "<a href='admin_login.php' class='btn'>Go to Login Page</a>";
    echo "</div></body></html>";
} else {
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h2 style='color: red;'>Error Creating Account</h2>";
    echo "<p>Error: " . htmlspecialchars($insert_stmt->error) . "</p>";
    echo "<p><a href='admin_login.php'>Go to Login Page</a></p>";
    echo "</body></html>";
}

$insert_stmt->close();
$conn->close();
?>

