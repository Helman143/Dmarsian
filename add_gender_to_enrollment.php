<?php
// Migration: Add gender column to enrollment_requests
require 'db_connect.php';

$alterEnrollmentRequests = "ALTER TABLE enrollment_requests ADD COLUMN IF NOT EXISTS gender VARCHAR(10) DEFAULT NULL";

if ($conn->query($alterEnrollmentRequests)) {
    echo "SUCCESS: gender column added (or already exists) in enrollment_requests.<br>";
} else {
    echo "ERROR: " . $conn->error . "<br>";
}

$conn->close();
echo "Migration complete.";
?>
