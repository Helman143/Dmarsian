<?php
/**
 * Simple script to import admin_accounts data
 * Run this via browser or command line
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

// Check database connection
if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
    die("ERROR: Database connection failed\n");
}

// Check if admin_accounts table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admin_accounts'");
if (!$table_check || $table_check->num_rows === 0) {
    die("ERROR: admin_accounts table does not exist. Please create it first.\n");
}

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
    echo "INFO: Admin account already exists:\n";
    echo "  ID: " . $existing['id'] . "\n";
    echo "  Username: " . $existing['username'] . "\n";
    echo "  Email: " . $existing['email'] . "\n";
    echo "\nNo import needed.\n";
    $check_stmt->close();
    exit(0);
}
$check_stmt->close();

// Import the data
try {
    // Try to insert with the specific ID first
    $insert_stmt = $conn->prepare("INSERT INTO admin_accounts (id, email, username, password) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("isss", $admin_data['id'], $admin_data['email'], $admin_data['username'], $admin_data['password']);
    
    if ($insert_stmt->execute()) {
        echo "SUCCESS: Admin account imported successfully!\n";
        echo "  ID: " . $admin_data['id'] . "\n";
        echo "  Username: " . $admin_data['username'] . "\n";
        echo "  Email: " . $admin_data['email'] . "\n";
        echo "\nNOTE: The password is hashed. If you don't know the original password,\n";
        echo "use the 'Forgot Password' feature on the login page.\n";
        $insert_stmt->close();
        exit(0);
    } else {
        // If inserting with ID fails, try without ID
        $insert_stmt->close();
        $insert_stmt = $conn->prepare("INSERT INTO admin_accounts (email, username, password) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $admin_data['email'], $admin_data['username'], $admin_data['password']);
        
        if ($insert_stmt->execute()) {
            $new_id = $conn->insert_id;
            echo "SUCCESS: Admin account imported successfully!\n";
            echo "  ID: " . $new_id . " (auto-assigned)\n";
            echo "  Username: " . $admin_data['username'] . "\n";
            echo "  Email: " . $admin_data['email'] . "\n";
            echo "\nNOTE: The password is hashed. If you don't know the original password,\n";
            echo "use the 'Forgot Password' feature on the login page.\n";
            $insert_stmt->close();
            exit(0);
        } else {
            throw new Exception("Insert failed: " . $insert_stmt->error);
        }
    }
} catch (Exception $e) {
    echo "ERROR: Failed to import admin account\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>





















