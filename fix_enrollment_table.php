<?php
require 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS enrollment_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255),
    phone VARCHAR(50),
    school VARCHAR(100),
    belt_rank VARCHAR(50),
    address VARCHAR(255),
    email VARCHAR(100),
    class VARCHAR(50),
    parent_name VARCHAR(255),
    parent_phone VARCHAR(50),
    parent_email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'enrollment_requests' is ready!";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 