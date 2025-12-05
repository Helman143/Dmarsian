<?php
// Load environment variables (gracefully handle missing file)
if (file_exists(__DIR__ . '/env-loader.php')) {
    require_once(__DIR__ . '/env-loader.php');
}

// Get database credentials from environment variables (with safe fallbacks)
$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER') ?: 'root';
$password   = getenv('DB_PASS') ?: '';
$database   = getenv('DB_NAME') ?: 'capstone_db';
$portStr    = getenv('DB_PORT') ?: '3306';
$port       = (int)$portStr;

// Check if template variables are unresolved (Digital Ocean App Platform issue)
// Template variables like ${db.HOSTNAME} should be resolved, but if they're not,
// we'll detect them and log an error
$templateVars = ['${db.HOSTNAME}', '${db.USERNAME}', '${db.PASSWORD}', '${db.DATABASE}', '${db.PORT}'];
$unresolved = [];

if (strpos($servername, '${') === 0) {
    $unresolved[] = 'DB_HOST';
    error_log("ERROR: DB_HOST contains unresolved template variable: {$servername}");
}
if (strpos($username, '${') === 0) {
    $unresolved[] = 'DB_USER';
    error_log("ERROR: DB_USER contains unresolved template variable: {$username}");
}
if (strpos($password, '${') === 0) {
    $unresolved[] = 'DB_PASS';
    error_log("ERROR: DB_PASS contains unresolved template variable");
}
if (strpos($database, '${') === 0) {
    $unresolved[] = 'DB_NAME';
    error_log("ERROR: DB_NAME contains unresolved template variable: {$database}");
}
if (strpos($portStr, '${') === 0) {
    $unresolved[] = 'DB_PORT';
    error_log("ERROR: DB_PORT contains unresolved template variable: {$portStr}");
    $port = 3306; // Use default port if unresolved
}

// If we have unresolved template variables, don't attempt connection
if (!empty($unresolved)) {
    error_log("CRITICAL: Database environment variables not resolved. Unresolved: " . implode(', ', $unresolved));
    error_log("This usually means the database component is not linked or app.yaml configuration is incorrect.");
    error_log("Please check your Digital Ocean App Platform configuration:");
    error_log("1. Ensure the database component is created and running");
    error_log("2. Verify the database component is linked to your app");
    error_log("3. Check that app.yaml references the database component correctly");
    $conn = false;
} else {
    // Create connection using the same logic as db_connect.php
    $conn = mysqli_init();
    if ($conn === false) {
        error_log('mysqli_init failed');
    } else {
        mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        mysqli_ssl_set($conn, null, null, null, null, null);
        $flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;

        if (!$conn->real_connect($servername, $username, $password, $database, $port, null, $flags)) {
            $errno = mysqli_connect_errno();
            $error = mysqli_connect_error();
            error_log("Database connection failed ({$servername}:{$port}) [{$errno}]: {$error}");
            $conn = false;
        } else {
            mysqli_set_charset($conn, 'utf8mb4');
        }
    }
}

// Legacy constants for backward compatibility (if needed by other files)
// Only set constants if we have valid values (not unresolved template variables)
if (!defined('DB_SERVER')) {
    // Use empty string if template variable unresolved, otherwise use actual value
    $serverValue = (!empty($unresolved) && in_array('DB_HOST', $unresolved)) ? '' : $servername;
    $userValue = (!empty($unresolved) && in_array('DB_USER', $unresolved)) ? '' : $username;
    $passValue = (!empty($unresolved) && in_array('DB_PASS', $unresolved)) ? '' : $password;
    $dbValue = (!empty($unresolved) && in_array('DB_NAME', $unresolved)) ? '' : $database;
    
    define('DB_SERVER', $serverValue);
    define('DB_USERNAME', $userValue);
    define('DB_PASSWORD', $passValue);
    define('DB_NAME', $dbValue);
}

// Ensure port constant is available everywhere (db_connect.php, legacy helpers, etc.)
if (!defined('DB_PORT')) {
    $portValue = (!empty($unresolved) && in_array('DB_PORT', $unresolved)) ? 3306 : $port;
    define('DB_PORT', $portValue);
}

// SMTP2GO HTTP API credentials and email settings
if (!defined('SMTP2GO_API_KEY')) {
    define('SMTP2GO_API_KEY', getenv('SMTP2GO_API_KEY') ?: '');
    define('SMTP2GO_SENDER_EMAIL', getenv('SMTP2GO_SENDER_EMAIL') ?: '');
    define('SMTP2GO_SENDER_NAME', getenv('SMTP2GO_SENDER_NAME') ?: "D'Marsians Taekwondo Gym");
    define('ADMIN_BCC_EMAIL', getenv('ADMIN_BCC_EMAIL') ?: '');
}
?>
