<?php
// Session Keep-Alive script
// Called periodically by dashboard JS to prevent session timeout
session_start();

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Return success as JSON so fetch() doesn't fail
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'timestamp' => $_SESSION['last_activity']
]);
?>
