<?php
// Simple test file to verify dashboard fetching functionality
echo "<h1>Dashboard Fetching Test</h1>";

// Test 1: Dashboard Stats
echo "<h2>Test 1: Dashboard Stats</h2>";
$stats_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/get_dashboard_stats.php";
echo "<p>Testing: <a href='$stats_url' target='_blank'>$stats_url</a></p>";

// Test 2: Dues Data
echo "<h2>Test 2: Dues Data</h2>";
$dues_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/get_dues.php";
echo "<p>Testing: <a href='$dues_url' target='_blank'>$dues_url</a></p>";

// Test 3: Payments Data
echo "<h2>Test 3: Payments Data</h2>";
$payments_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/get_payments.php";
echo "<p>Testing: <a href='$payments_url' target='_blank'>$payments_url</a></p>";

// Test 4: Database Connection
echo "<h2>Test 4: Database Connection</h2>";
require_once 'db_connect.php';
$conn = connectDB();
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test tables
    $tables = ['students', 'payments', 'posts', 'admin_accounts'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }
    
    $conn->close();
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Click on the links above to test each API endpoint</li>";
echo "<li>You should see JSON responses for each endpoint</li>";
echo "<li>If you see errors, check your database connection and table structure</li>";
echo "<li>Once all tests pass, your dashboard fetching should work correctly</li>";
echo "</ol>";

echo "<p><a href='dashboard.php'>← Back to Dashboard</a></p>";
?>