<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Test config
require_once 'config.php';
echo "<p>✓ Config loaded successfully</p>";

// Test database connection
try {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    echo "<p>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test tables
$tables = ['users', 'admin_accounts', 'students', 'posts', 'payments'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
    }
}

// Test admin_accounts data
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_accounts");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>✓ Admin accounts table has " . $row['count'] . " records</p>";
} else {
    echo "<p style='color: red;'>✗ Error checking admin_accounts table</p>";
}

// Test users data
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>✓ Users table has " . $row['count'] . " records</p>";
} else {
    echo "<p style='color: red;'>✗ Error checking users table</p>";
}

mysqli_close($conn);
echo "<p>✓ Database connection closed</p>";

echo "<h3>Test Complete</h3>";
?> 