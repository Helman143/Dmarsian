<?php
/**
 * Script to import admin_accounts data from Database/db.sql
 * This will insert the default admin account if it doesn't exist
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
    <title>Import Admin Account Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result-box {
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 10px;
        }
        .btn:hover { background: #0056b3; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
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
    <h1>Import Admin Account Data</h1>
    
    <?php
    // Check database connection
    if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
        echo '<div class="result-box">';
        echo '<p class="error">✗ Database connection FAILED</p>';
        echo '<p>Error: ' . (isset($conn->connect_error) ? htmlspecialchars($conn->connect_error) : 'Connection not established') . '</p>';
        echo '<p>Please check your database configuration in config.php</p>';
        echo '</div>';
        exit;
    }
    
    echo '<div class="result-box">';
    echo '<p class="success">✓ Database connection successful</p>';
    echo '</div>';
    
    // Check if admin_accounts table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_accounts'");
    if (!$table_check || $table_check->num_rows === 0) {
        echo '<div class="result-box">';
        echo '<p class="error">✗ admin_accounts table does NOT exist</p>';
        echo '<p class="warning">You need to create the table first. Run this SQL:</p>';
        echo '<pre>CREATE TABLE `admin_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;</pre>';
        echo '</div>';
        exit;
    }
    
    echo '<div class="result-box">';
    echo '<p class="success">✓ admin_accounts table exists</p>';
    echo '</div>';
    
    // Data from Database/db.sql
    $admin_data = [
        'id' => 1,
        'email' => 'mars@gmail.com',
        'username' => 'Mr.Mars',
        'password' => '$2y$10$bj2TuZzvyBVU5Kc/GWnvx.G3zXMHElokV04wNpg9FPJ3XgRLfvrBG'
    ];
    
    // Check if account already exists
    $check_stmt = $conn->prepare("SELECT id, username, email FROM admin_accounts WHERE email = ? OR username = ? OR id = ?");
    $check_stmt->bind_param("ssi", $admin_data['email'], $admin_data['username'], $admin_data['id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        echo '<div class="result-box">';
        echo '<p class="warning">⚠ Admin account already exists in database</p>';
        echo '<h3>Existing Account:</h3>';
        echo '<table>';
        echo '<tr><th>Field</th><th>Value</th></tr>';
        echo '<tr><td>ID</td><td>' . htmlspecialchars($existing['id']) . '</td></tr>';
        echo '<tr><td>Username</td><td>' . htmlspecialchars($existing['username']) . '</td></tr>';
        echo '<tr><td>Email</td><td>' . htmlspecialchars($existing['email']) . '</td></tr>';
        echo '</table>';
        echo '<p class="info">No import needed. You can use this account to log in.</p>';
        echo '<p class="warning">Note: The password is hashed. If you don\'t know the original password, use the "Forgot Password" feature.</p>';
        echo '</div>';
        $check_stmt->close();
    } else {
        // Import the data
        try {
            // First, try to insert with the specific ID
            $insert_stmt = $conn->prepare("INSERT INTO admin_accounts (id, email, username, password) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("isss", $admin_data['id'], $admin_data['email'], $admin_data['username'], $admin_data['password']);
            
            if ($insert_stmt->execute()) {
                echo '<div class="result-box">';
                echo '<p class="success">✓ Admin account imported successfully!</p>';
                echo '<h3>Imported Account Details:</h3>';
                echo '<table>';
                echo '<tr><th>Field</th><th>Value</th></tr>';
                echo '<tr><td>ID</td><td>' . htmlspecialchars($admin_data['id']) . '</td></tr>';
                echo '<tr><td>Email</td><td>' . htmlspecialchars($admin_data['email']) . '</td></tr>';
                echo '<tr><td>Username</td><td>' . htmlspecialchars($admin_data['username']) . '</td></tr>';
                echo '<tr><td>Password</td><td>Hashed (bcrypt) - Original password unknown</td></tr>';
                echo '</table>';
                echo '<div style="background:#fff3cd;padding:15px;border-radius:3px;margin-top:15px">';
                echo '<p class="warning"><strong>⚠️ Important:</strong></p>';
                echo '<ul style="margin:10px 0;padding-left:20px">';
                echo '<li>The password is hashed, so you need to know the original password to log in</li>';
                echo '<li>If you don\'t know the password, use the "Forgot Password" feature on the login page</li>';
                echo '<li>Or create a new admin account with a known password</li>';
                echo '</ul>';
                echo '</div>';
                echo '</div>';
            } else {
                // If inserting with ID fails (maybe ID already exists), try without ID
                $insert_stmt->close();
                $insert_stmt = $conn->prepare("INSERT INTO admin_accounts (email, username, password) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $admin_data['email'], $admin_data['username'], $admin_data['password']);
                
                if ($insert_stmt->execute()) {
                    $new_id = $conn->insert_id;
                    echo '<div class="result-box">';
                    echo '<p class="success">✓ Admin account imported successfully!</p>';
                    echo '<p class="info">Note: A new ID (' . $new_id . ') was assigned instead of the original ID (1)</p>';
                    echo '<h3>Imported Account Details:</h3>';
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Value</th></tr>';
                    echo '<tr><td>ID</td><td>' . htmlspecialchars($new_id) . '</td></tr>';
                    echo '<tr><td>Email</td><td>' . htmlspecialchars($admin_data['email']) . '</td></tr>';
                    echo '<tr><td>Username</td><td>' . htmlspecialchars($admin_data['username']) . '</td></tr>';
                    echo '<tr><td>Password</td><td>Hashed (bcrypt) - Original password unknown</td></tr>';
                    echo '</table>';
                    echo '<div style="background:#fff3cd;padding:15px;border-radius:3px;margin-top:15px">';
                    echo '<p class="warning"><strong>⚠️ Important:</strong></p>';
                    echo '<ul style="margin:10px 0;padding-left:20px">';
                    echo '<li>The password is hashed, so you need to know the original password to log in</li>';
                    echo '<li>If you don\'t know the password, use the "Forgot Password" feature on the login page</li>';
                    echo '<li>Or create a new admin account with a known password</li>';
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    throw new Exception("Insert failed: " . $insert_stmt->error);
                }
            }
            $insert_stmt->close();
        } catch (Exception $e) {
            echo '<div class="result-box">';
            echo '<p class="error">✗ Error importing admin account</p>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
    }
    
    // Show all current admin accounts
    $all_accounts = $conn->query("SELECT id, username, email FROM admin_accounts ORDER BY id");
    if ($all_accounts && $all_accounts->num_rows > 0) {
        echo '<div class="result-box">';
        echo '<h3>All Admin Accounts in Database:</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Username</th><th>Email</th></tr>';
        while ($row = $all_accounts->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
    ?>
    
    <div class="result-box">
        <h3>Next Steps</h3>
        <ul>
            <li><strong>If account was imported:</strong> Try logging in with username <code>Mr.Mars</code> or email <code>mars@gmail.com</code></li>
            <li><strong>If you don't know the password:</strong> Use the "Forgot Password" feature on the login page</li>
            <li><strong>To create a new account with a known password:</strong> Use <a href="create_admin_account.php">create_admin_account.php</a></li>
            <li><strong>To test login:</strong> Visit <a href="test_admin_login.php">test_admin_login.php</a></li>
        </ul>
        <p style="margin-top:20px">
            <a href="admin_login.php" class="btn">Go to Admin Login</a>
            <a href="test_admin_login.php" class="btn" style="background:#28a745">Test Login</a>
        </p>
    </div>
</body>
</html>























