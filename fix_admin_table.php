<?php
/**
 * Script to fix the missing admin_accounts table
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
    <title>Fix Admin Accounts Table</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f0f2f5;
            color: #333;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 { color: #1a73e8; margin-top: 0; }
        .result {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 5px solid;
        }
        .success { background: #e6f4ea; border-color: #34a853; color: #1e8e3e; }
        .error { background: #fce8e6; border-color: #ea4335; color: #d93025; }
        .info { background: #e8f0fe; border-color: #1a73e8; color: #1967d2; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #1a73e8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-right: 10px;
        }
        .btn:hover { background: #1557b0; }
        .btn-secondary { background: #5f6368; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Admin Table Fixer</h1>
        
        <?php
        if (!isset($conn) || $conn === false) {
            echo '<div class="result error"><strong>Error:</strong> Database connection failed. Please check your .env file.</div>';
            exit;
        }

        echo '<div class="result success"><strong>Success:</strong> Connected to database: ' . (defined('DB_NAME') ? DB_NAME : 'unknown') . '</div>';

        // 1. Create the table
        $sql = "CREATE TABLE IF NOT EXISTS `admin_accounts` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(255) NOT NULL,
          `username` varchar(100) NOT NULL,
          `password` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`),
          UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if ($conn->query($sql)) {
            echo '<div class="result success"><strong>Success:</strong> <code>admin_accounts</code> table is ready (created or already exists).</div>';
        } else {
            echo '<div class="result error"><strong>Error creating table:</strong> ' . $conn->error . '</div>';
            exit;
        }

        // 2. Insert default admin if table is empty
        $check = $conn->query("SELECT COUNT(*) as count FROM admin_accounts");
        $row = $check->fetch_assoc();
        
        if ($row['count'] == 0) {
            $email = 'mars@gmail.com';
            $username = 'Mr.Mars';
            // Default password hash from db.sql
            $password = '$2y$10$bj2TuZzvyBVU5Kc/GWnvx.G3zXMHElokV04wNpg9FPJ3XgRLfvrBG';
            
            $stmt = $conn->prepare("INSERT INTO admin_accounts (email, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $username, $password);
            
            if ($stmt->execute()) {
                echo '<div class="result success"><strong>Success:</strong> Default admin account created: <code>' . $username . '</code></div>';
            } else {
                echo '<div class="result error"><strong>Error creating admin account:</strong> ' . $stmt->error . '</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="result info"><strong>Info:</strong> Admin accounts already exist. No new account created.</div>';
        }

        // 3. Show current accounts
        $result = $conn->query("SELECT id, username, email FROM admin_accounts");
        if ($result && $result->num_rows > 0) {
            echo "<h3>Current Admin Accounts:</h3>";
            echo "<table><tr><th>ID</th><th>Username</th><th>Email</th></tr>";
            while($admin = $result->fetch_assoc()) {
                echo "<tr><td>{$admin['id']}</td><td>{$admin['username']}</td><td>{$admin['email']}</td></tr>";
            }
            echo "</table>";
        }
        ?>

        <div style="margin-top: 30px;">
            <p>You can now try to log in again.</p>
            <a href="admin_login.php" class="btn">Go to Login Page</a>
            <a href="index.php" class="btn btn-secondary">Home</a>
        </div>
    </div>
</body>
</html>
