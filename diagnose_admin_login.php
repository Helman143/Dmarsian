<?php
/**
 * Diagnostic script to check admin login issues
 * Run this script in your browser to diagnose admin login problems
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Diagnostic Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .diagnostic-box {
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
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <h1>Admin Login Diagnostic Tool</h1>
    
    <?php
    require_once 'db_connect.php';
    
    // Test 1: Database Connection
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 1: Database Connection</h2>';
    if (isset($conn) && $conn !== false && !(isset($conn->connect_error) && $conn->connect_error)) {
        echo '<p class="success">✓ Database connection successful</p>';
        echo '<p class="info">Database: ' . (defined('DB_NAME') ? DB_NAME : 'capstone_db') . '</p>';
        echo '<p class="info">Host: ' . (defined('DB_SERVER') ? DB_SERVER : 'localhost') . '</p>';
    } else {
        echo '<p class="error">✗ Database connection FAILED</p>';
        if (isset($conn->connect_error)) {
            echo '<p class="error">Error: ' . htmlspecialchars($conn->connect_error) . '</p>';
        }
        echo '</div>';
        exit;
    }
    echo '</div>';
    
    // Test 2: Check if admin_accounts table exists
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 2: admin_accounts Table</h2>';
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_accounts'");
    if ($table_check && $table_check->num_rows > 0) {
        echo '<p class="success">✓ admin_accounts table exists</p>';
        
        // Check table structure
        $structure = $conn->query("DESCRIBE admin_accounts");
        if ($structure) {
            echo '<h3>Table Structure:</h3>';
            echo '<table>';
            echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
            while ($row = $structure->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['Field']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Key']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Default'] ?? 'NULL') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    } else {
        echo '<p class="error">✗ admin_accounts table does NOT exist</p>';
        echo '<p class="warning">You need to create the admin_accounts table. Check Database/db.sql for the CREATE TABLE statement.</p>';
    }
    echo '</div>';
    
    // Test 3: Check admin_accounts data
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 3: Admin Accounts Data</h2>';
    $count_result = $conn->query("SELECT COUNT(*) as count FROM admin_accounts");
    if ($count_result) {
        $count = $count_result->fetch_assoc()['count'];
        if ($count > 0) {
            echo '<p class="success">✓ Found ' . $count . ' admin account(s) in database</p>';
            
            // Display all admin accounts (without passwords)
            $accounts = $conn->query("SELECT id, username, email, 
                CASE 
                    WHEN password LIKE '$2y$%' OR password LIKE '$2a$%' OR password LIKE '$2b$%' THEN 'Hashed'
                    ELSE 'Plain Text'
                END as password_type,
                LENGTH(password) as password_length
                FROM admin_accounts");
            
            if ($accounts && $accounts->num_rows > 0) {
                echo '<h3>Admin Accounts:</h3>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Type</th><th>Password Length</th></tr>';
                while ($row = $accounts->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['password_type']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['password_length']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } else {
            echo '<p class="error">✗ No admin accounts found in database</p>';
            echo '<p class="warning">The admin_accounts table is empty. You need to create an admin account.</p>';
            echo '<p class="info">You can create one using admin_profile.php or by running this SQL:</p>';
            echo '<pre>INSERT INTO admin_accounts (email, username, password) VALUES 
(\'admin@example.com\', \'admin\', \'$2y$10$YourHashedPasswordHere\');</pre>';
        }
    } else {
        echo '<p class="error">✗ Error checking admin_accounts: ' . htmlspecialchars($conn->error) . '</p>';
    }
    echo '</div>';
    
    // Test 4: Test login query
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 4: Login Query Test</h2>';
    if (isset($_GET['test_username'])) {
        $test_username = $_GET['test_username'];
        echo '<p class="info">Testing login query for: ' . htmlspecialchars($test_username) . '</p>';
        
        $stmt = $conn->prepare("SELECT id, username, password, email FROM admin_accounts WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $test_username, $test_username);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                echo '<p class="info">Query executed successfully</p>';
                echo '<p class="info">Found ' . $result->num_rows . ' matching account(s)</p>';
                
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    echo '<h3>Account Details:</h3>';
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Value</th></tr>';
                    echo '<tr><td>ID</td><td>' . htmlspecialchars($admin['id']) . '</td></tr>';
                    echo '<tr><td>Username</td><td>' . htmlspecialchars($admin['username']) . '</td></tr>';
                    echo '<tr><td>Email</td><td>' . htmlspecialchars($admin['email']) . '</td></tr>';
                    echo '<tr><td>Password Hash</td><td>' . htmlspecialchars(substr($admin['password'], 0, 20)) . '... (truncated)</td></tr>';
                    echo '<tr><td>Password Type</td><td>' . (strpos($admin['password'], '$2y$') === 0 ? 'Hashed (bcrypt)' : 'Plain Text') . '</td></tr>';
                    echo '</table>';
                }
            } else {
                echo '<p class="error">Query execution failed: ' . htmlspecialchars($stmt->error) . '</p>';
            }
            $stmt->close();
        } else {
            echo '<p class="error">Query preparation failed: ' . htmlspecialchars($conn->error) . '</p>';
        }
    } else {
        echo '<p class="info">Enter a username or email to test the login query:</p>';
        echo '<form method="GET">';
        echo '<input type="text" name="test_username" placeholder="Username or Email" required>';
        echo '<button type="submit">Test Query</button>';
        echo '</form>';
    }
    echo '</div>';
    
    // Test 5: Check PHP password functions
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 5: PHP Password Functions</h2>';
    if (function_exists('password_hash') && function_exists('password_verify')) {
        echo '<p class="success">✓ password_hash() and password_verify() functions are available</p>';
        
        // Test password hashing
        $test_password = 'test123';
        $test_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $verify_result = password_verify($test_password, $test_hash);
        
        if ($verify_result) {
            echo '<p class="success">✓ Password hashing and verification test PASSED</p>';
        } else {
            echo '<p class="error">✗ Password verification test FAILED</p>';
        }
    } else {
        echo '<p class="error">✗ password_hash() or password_verify() functions are NOT available</p>';
        echo '<p class="warning">You need PHP 5.5+ for password hashing functions</p>';
    }
    echo '</div>';
    
    // Test 6: Check session configuration
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 6: Session Configuration</h2>';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo '<p class="info">Session Status: ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '</p>';
    echo '<p class="info">Session Save Path: ' . session_save_path() . '</p>';
    echo '<p class="info">Session Name: ' . session_name() . '</p>';
    echo '</div>';
    
    // Test 7: Check file permissions
    echo '<div class="diagnostic-box">';
    echo '<h2>Test 7: File Permissions</h2>';
    $files_to_check = [
        'login_process.php',
        'admin_login.php',
        'db_connect.php',
        'config.php'
    ];
    
    foreach ($files_to_check as $file) {
        if (file_exists($file)) {
            $readable = is_readable($file) ? '✓' : '✗';
            echo '<p>' . $readable . ' ' . $file . ' - ' . (is_readable($file) ? 'Readable' : 'NOT Readable') . '</p>';
        } else {
            echo '<p class="error">✗ ' . $file . ' - FILE NOT FOUND</p>';
        }
    }
    echo '</div>';
    
    $conn->close();
    ?>
    
    <div class="diagnostic-box">
        <h2>Recommendations</h2>
        <ul>
            <li>Check PHP error logs for detailed login attempt information</li>
            <li>Ensure admin_accounts table has at least one account</li>
            <li>Verify passwords are properly hashed using password_hash()</li>
            <li>Check that the username/email you're using matches exactly (case-sensitive)</li>
            <li>If password is hashed, use password_verify() for comparison</li>
            <li>If password is plain text, ensure exact match (not recommended for production)</li>
        </ul>
    </div>
</body>
</html>
























