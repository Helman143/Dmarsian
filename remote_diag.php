<?php
/**
 * Database Connection Diagnostic Tool
 * This script will output EXACTLY why the connection is failing.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Diagnostic</h1>";

// 1. Check Environment Variables
echo "<h2>1. Environment Variables Check</h2>";
$env_keys = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME', 'DB_PORT'];
foreach ($env_keys as $key) {
    $val = getenv($key);
    $display_val = ($key === 'DB_PASS') ? (empty($val) ? '[EMPTY]' : '[SET - HIDDEN]') : ($val ?: '[NOT SET]');
    echo "<strong>$key</strong>: $display_val<br>";
}

// 2. Load Config/Loader
echo "<h2>2. Configuration Loading</h2>";
if (file_exists('env-loader.php')) {
    echo "Found env-loader.php... loading.<br>";
    require_once 'env-loader.php';
}
if (file_exists('config.php')) {
    echo "Found config.php... loading.<br>";
    require_once 'config.php';
}

// 3. Attempt Connection
echo "<h2>3. Connection Attempt (Raw Mysqli)</h2>";
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db = getenv('DB_NAME') ?: 'capstone_db';
$port = getenv('DB_PORT') ?: 3306;

echo "Attempting connection to <strong>$host:$port</strong> as user <strong>$user</strong>...<br>";

$mysqli = mysqli_init();
mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// Managed DBs typically require SSL
mysqli_ssl_set($mysqli, null, null, null, null, null);
$flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;

if (@$mysqli->real_connect($host, $user, $pass, $db, (int)$port, null, $flags)) {
    echo "<span style='color:green; font-weight:bold;'>SUCCESS!</span> Connection established.<br>";
    echo "Server Info: " . $mysqli->server_info . "<br>";
    
    // Check tables
    echo "<h3>Table Check:</h3>";
    $result = $mysqli->query("SHOW TABLES LIKE 'admin_accounts'");
    if ($result && $result->num_rows > 0) {
        echo "✅ 'admin_accounts' table exists.<br>";
        $count = $mysqli->query("SELECT COUNT(*) as count FROM admin_accounts")->fetch_assoc()['count'];
        echo "Found $count admin record(s).<br>";
    } else {
        echo "❌ 'admin_accounts' table MISSING.<br>";
    }
} else {
    echo "<span style='color:red; font-weight:bold;'>FAILED!</span><br>";
    echo "Error Number: " . mysqli_connect_errno() . "<br>";
    echo "Error Message: " . mysqli_connect_error() . "<br>";
    
    if (mysqli_connect_errno() == 2002) {
        echo "<p><strong>Probable Cause:</strong> Connection Timed Out. This usually means the DigitalOcean Database Firewall (Trusted Sources) is blocking this IP.</p>";
    }
}
?>
