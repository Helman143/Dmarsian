<?php
require 'db_connect.php';
header('Content-Type: application/json');
// Prevent caching to ensure fresh data on DigitalOcean
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Simplified query: Get all active students that either:
// 1. Were enrolled today (catches newly approved immediately), OR
// 2. Match an approved enrollment request
// This ensures instant visibility of newly approved enrollments
$sql = "SELECT DISTINCT s.id, s.jeja_no, s.date_enrolled, s.full_name, s.phone, s.created_at
        FROM students s
        WHERE s.status = 'Active' 
        AND (
            s.date_enrolled >= CURDATE() 
            OR EXISTS (
                SELECT 1 FROM enrollment_requests er 
                WHERE er.status = 'approved' 
                AND TRIM(LOWER(er.full_name)) = TRIM(LOWER(s.full_name)) 
                AND TRIM(er.phone) = TRIM(s.phone)
            )
        )
        ORDER BY s.date_enrolled DESC, s.created_at DESC";

$result = $conn->query($sql);
$approved = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $approved[] = [
            'id' => $row['id'],
            'jeja_no' => $row['jeja_no'],
            'date_enrolled' => $row['date_enrolled'],
            'full_name' => $row['full_name'],
            'phone' => $row['phone'],
            'amount_paid' => '',
            'payment_type' => '',
        ];
    }
}

echo json_encode(['status' => 'success', 'data' => $approved]);
$conn->close(); 