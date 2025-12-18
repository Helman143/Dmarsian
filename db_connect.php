<?php
require_once 'config.php';

function connectDB() {
    // Use constants if defined, otherwise fall back to config.php globals/defaults
    $server = defined('DB_SERVER') ? DB_SERVER : ($GLOBALS['servername'] ?? 'localhost');
    $username = defined('DB_USERNAME') ? DB_USERNAME : ($GLOBALS['username'] ?? 'root');
    $password = defined('DB_PASSWORD') ? DB_PASSWORD : ($GLOBALS['password'] ?? '');
    $database = defined('DB_NAME') ? DB_NAME : ($GLOBALS['database'] ?? 'capstone_db');
    $port = defined('DB_PORT') ? DB_PORT : ($GLOBALS['port'] ?? 3306);

    // Initialize mysqli with sane defaults
    $conn = mysqli_init();
    if (!$conn) {
        error_log('mysqli_init failed');
        return false;
    }

    // Shorten connect timeout so failures bubble up quickly instead of hanging for 40s
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    // Managed DBs typically require SSL. Allow connection even if CA cert isn't provided
    // (DigitalOcean accepts TLS without verification inside the same VPC).
    mysqli_ssl_set($conn, null, null, null, null, null);

    $flags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;

    if (!$conn->real_connect($server, $username, $password, $database, (int)$port, null, $flags)) {
        $errno = mysqli_connect_errno();
        $error = mysqli_connect_error();
        error_log("Database connection failed ({$server}:{$port}) [{$errno}]: {$error}");
        return false;
    }

    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}

// Automatically establish connection when this file is included
// Use the connection from config.php if available, otherwise create new one
if (!isset($conn) || (isset($conn) && isset($conn->connect_error) && $conn->connect_error)) {
    $conn = connectDB();
}
?>