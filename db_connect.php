<?php
require_once 'config.php';

function connectDB() {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $conn;
}

// Automatically establish connection when this file is included
// This ensures backward compatibility with all existing files
$conn = connectDB();

// Note: Tables are now created automatically by Docker using the Database/db.sql file
// This eliminates the need for manual table creation and avoids SQL syntax compatibility issues
?>