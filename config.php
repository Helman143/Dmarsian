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
$port       = (int)(getenv('DB_PORT') ?: 3306);

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

// Legacy constants for backward compatibility (if needed by other files)
if (!defined('DB_SERVER')) {
    define('DB_SERVER', $servername);
    define('DB_USERNAME', $username);
    define('DB_PASSWORD', $password);
    define('DB_NAME', $database);
}

// Ensure port constant is available everywhere (db_connect.php, legacy helpers, etc.)
if (!defined('DB_PORT')) {
    define('DB_PORT', $port);
}

// SMTP2GO HTTP API credentials and email settings
if (!defined('SMTP2GO_API_KEY')) {
    define('SMTP2GO_API_KEY', getenv('SMTP2GO_API_KEY') ?: '');
    define('SMTP2GO_SENDER_EMAIL', getenv('SMTP2GO_SENDER_EMAIL') ?: '');
    define('SMTP2GO_SENDER_NAME', getenv('SMTP2GO_SENDER_NAME') ?: "D'Marsians Taekwondo Gym");
    define('ADMIN_BCC_EMAIL', getenv('ADMIN_BCC_EMAIL') ?: '');
}
?>
