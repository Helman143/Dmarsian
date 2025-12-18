<?php
/**
 * Quick test script to check admin login credentials
 * This will help identify why login is failing
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Admin Login Test Tool</h1>
    
    <?php
    // Check database connection
    if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
        echo '<div class="test-box">';
        echo '<p class="error">✗ Database connection FAILED</p>';
        echo '<p>Error: ' . (isset($conn->connect_error) ? htmlspecialchars($conn->connect_error) : 'Connection not established') . '</p>';
        echo '</div>';
        exit;
    }
    
    echo '<div class="test-box">';
    echo '<p class="success">✓ Database connection successful</p>';
    echo '</div>';
    
    // Check if admin_accounts table exists and has data
    $count_result = $conn->query("SELECT COUNT(*) as count FROM admin_accounts");
    if ($count_result) {
        $count = $count_result->fetch_assoc()['count'];
        echo '<div class="test-box">';
        if ($count > 0) {
            echo '<p class="success">✓ Found ' . $count . ' admin account(s) in database</p>';
            
            // Show admin accounts (without passwords)
            $accounts = $conn->query("SELECT id, username, email FROM admin_accounts");
            if ($accounts && $accounts->num_rows > 0) {
                echo '<h3>Available Admin Accounts:</h3>';
                echo '<ul>';
                while ($row = $accounts->fetch_assoc()) {
                    echo '<li><strong>Username:</strong> ' . htmlspecialchars($row['username']) . ' | <strong>Email:</strong> ' . htmlspecialchars($row['email']) . '</li>';
                }
                echo '</ul>';
            }
        } else {
            echo '<p class="error">✗ No admin accounts found in database</p>';
            echo '<p class="warning">The admin_accounts table is empty. You need to create an admin account first.</p>';
        }
        echo '</div>';
    }
    
    // Test login if credentials provided
    if (isset($_POST['test_username']) && isset($_POST['test_password'])) {
        $test_username = $_POST['test_username'];
        $test_password = $_POST['test_password'];
        
        echo '<div class="test-box">';
        echo '<h2>Login Test Results</h2>';
        echo '<p class="info">Testing login for: <strong>' . htmlspecialchars($test_username) . '</strong></p>';
        
        try {
            $stmt = $conn->prepare("SELECT id, username, password, email FROM admin_accounts WHERE username = ? OR email = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ss", $test_username, $test_username);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo '<p class="error">✗ No account found with username/email: ' . htmlspecialchars($test_username) . '</p>';
                echo '<p class="warning">Make sure you\'re using the exact username or email (case-sensitive).</p>';
            } else if ($result->num_rows > 1) {
                echo '<p class="error">✗ Multiple accounts found (this should not happen)</p>';
            } else {
                $admin = $result->fetch_assoc();
                echo '<p class="success">✓ Account found!</p>';
                echo '<p><strong>ID:</strong> ' . htmlspecialchars($admin['id']) . '</p>';
                echo '<p><strong>Username:</strong> ' . htmlspecialchars($admin['username']) . '</p>';
                echo '<p><strong>Email:</strong> ' . htmlspecialchars($admin['email']) . '</p>';
                
                // Check password
                $password_hash = $admin['password'];
                $is_hashed = (strpos($password_hash, '$2y$') === 0 || strpos($password_hash, '$2a$') === 0 || strpos($password_hash, '$2b$') === 0);
                
                echo '<p><strong>Password Type:</strong> ' . ($is_hashed ? 'Hashed (bcrypt)' : 'Plain Text') . '</p>';
                
                $password_valid = false;
                if ($is_hashed) {
                    $password_valid = password_verify($test_password, $password_hash);
                    echo '<p><strong>Password Verification (hashed):</strong> ' . ($password_valid ? '<span class="success">✓ SUCCESS</span>' : '<span class="error">✗ FAILED</span>') . '</p>';
                } else {
                    $password_valid = ($test_password === $password_hash);
                    echo '<p><strong>Password Verification (plain text):</strong> ' . ($password_valid ? '<span class="success">✓ SUCCESS</span>' : '<span class="error">✗ FAILED</span>') . '</p>';
                }
                
                if ($password_valid) {
                    echo '<p class="success" style="font-size:18px;margin-top:15px;">✓ LOGIN WOULD SUCCEED!</p>';
                    echo '<p class="info">The credentials are correct. If login still fails, check:</p>';
                    echo '<ul>';
                    echo '<li>Form submission (check browser console for errors)</li>';
                    echo '<li>Session configuration</li>';
                    echo '<li>PHP error logs</li>';
                    echo '</ul>';
                } else {
                    echo '<p class="error" style="font-size:18px;margin-top:15px;">✗ PASSWORD INCORRECT</p>';
                    echo '<p class="warning">The password you entered does not match the stored password.</p>';
                    if ($is_hashed) {
                        echo '<p class="info">Since the password is hashed, you need to know the original password or reset it.</p>';
                    }
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
    }
    ?>
    
    <div class="test-box">
        <h2>Test Login Credentials</h2>
        <form method="POST">
            <label>Username or Email:</label>
            <input type="text" name="test_username" placeholder="Enter username or email" required>
            
            <label>Password:</label>
            <input type="password" name="test_password" placeholder="Enter password" required>
            
            <button type="submit" style="margin-top:10px">Test Login</button>
        </form>
    </div>
    
    <div class="test-box">
        <h2>Next Steps</h2>
        <ul>
            <li>If no accounts found: Create an admin account using <code>create_admin_account.php</code> or SQL</li>
            <li>If password incorrect: Reset the password or create a new account</li>
            <li>If account found but login still fails: Check <a href="diagnose_admin_login.php">diagnose_admin_login.php</a> for more details</li>
            <li>Check PHP error logs for detailed login attempt information</li>
        </ul>
    </div>
    
    <div class="test-box">
        <p><a href="admin_login.php">← Back to Admin Login</a> | <a href="diagnose_admin_login.php">Full Diagnostic Tool →</a></p>
    </div>
</body>
</html>











