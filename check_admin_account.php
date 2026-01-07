<?php
/**
 * Check admin account details
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

// Check database connection
if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
    die("ERROR: Database connection failed\n");
}

echo "=== Admin Account Check ===\n\n";

// Get all admin accounts
$result = $conn->query("SELECT id, username, email, 
    CASE 
        WHEN password LIKE '$2y$%' OR password LIKE '$2a$%' OR password LIKE '$2b$%' THEN 'Hashed (bcrypt)'
        ELSE 'Plain Text'
    END as password_type,
    LENGTH(password) as password_length
    FROM admin_accounts");

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " admin account(s):\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "Account #" . $row['id'] . ":\n";
        echo "  Username: " . $row['username'] . "\n";
        echo "  Email: " . $row['email'] . "\n";
        echo "  Password Type: " . $row['password_type'] . "\n";
        echo "  Password Length: " . $row['password_length'] . " characters\n";
        echo "\n";
    }
    
    echo "=== Login Instructions ===\n\n";
    echo "To log in, use:\n";
    echo "  Username: Mr.Mars\n";
    echo "  OR\n";
    echo "  Email: mars@gmail.com\n\n";
    echo "⚠️  IMPORTANT: The password is hashed (encrypted).\n";
    echo "You need to know the ORIGINAL password that was used to create this hash.\n\n";
    echo "If you don't know the password:\n";
    echo "1. Use the 'Forgot Password' feature on the login page\n";
    echo "2. Or create a new admin account with a known password\n";
    echo "3. Or reset the password directly in the database\n";
} else {
    echo "No admin accounts found in database.\n";
    echo "Run import_admin_data.php to import the default account.\n";
}

$conn->close();
?>























