<?php
require_once 'db_connect.php';
header('Content-Type: text/plain');

if (!isset($conn) || $conn === false) {
    echo "CONNECTION_FAILED\n";
    if (isset($mysqli_connect_error)) echo "Error: " . mysqli_connect_error() . "\n";
    exit;
}

echo "CONNECTION_SUCCESS\n";
echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'unknown') . "\n";
echo "Host: " . (defined('DB_SERVER') ? DB_SERVER : 'unknown') . "\n";

$res = $conn->query("SHOW TABLES LIKE 'admin_accounts'");
if ($res && $res->num_rows > 0) {
    echo "TABLE_EXISTS\n";
    $res = $conn->query("SELECT id, username, email FROM admin_accounts");
    echo "ACCOUNTS_COUNT: " . $res->num_rows . "\n";
    while ($row = $res->fetch_assoc()) {
        echo "ACCOUNT: [" . $row['id'] . "] " . $row['username'] . " (" . $row['email'] . ")\n";
    }
} else {
    echo "TABLE_MISSING\n";
}

$res = $conn->query("SHOW TABLES LIKE 'users'");
if ($res && $res->num_rows > 0) {
    echo "USERS_TABLE_EXISTS\n";
    $res = $conn->query("SELECT id, username, user_type FROM users");
    echo "USERS_COUNT: " . $res->num_rows . "\n";
    while ($row = $res->fetch_assoc()) {
        echo "USER: " . $row['username'] . " [" . $row['user_type'] . "]\n";
    }
} else {
    echo "USERS_TABLE_MISSING\n";
}
?>
