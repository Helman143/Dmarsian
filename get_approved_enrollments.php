<?php
require 'db_connect.php';
header('Content-Type: application/json');

// Optimized query for immediate results - prioritize recent enrollments first
// 1. Students enrolled today (most recent - catches newly approved immediately)
// 2. Students from activity_log with "Enrollment (Approval)" action
// 3. Students that match approved enrollment requests (case-insensitive, trimmed)
$sql = "(SELECT DISTINCT s.id, s.jeja_no, s.date_enrolled, s.full_name, s.phone, s.created_at
        FROM students s
        WHERE s.status = 'Active' 
        AND s.date_enrolled = CURDATE()
        ORDER BY s.created_at DESC)
        
        UNION
        
        (SELECT DISTINCT s.id, s.jeja_no, s.date_enrolled, s.full_name, s.phone, s.created_at
        FROM students s
        INNER JOIN activity_log al ON s.jeja_no = al.student_id
        WHERE al.action_type = 'Enrollment (Approval)'
        AND s.status = 'Active'
        AND s.date_enrolled < CURDATE())
        
        UNION
        
        (SELECT DISTINCT s.id, s.jeja_no, s.date_enrolled, s.full_name, s.phone, s.created_at
        FROM students s
        INNER JOIN enrollment_requests er 
            ON TRIM(LOWER(s.full_name)) = TRIM(LOWER(er.full_name)) 
            AND TRIM(s.phone) = TRIM(er.phone)
        WHERE er.status = 'approved' 
        AND s.status = 'Active'
        AND s.date_enrolled < CURDATE())
        
        ORDER BY date_enrolled DESC, created_at DESC";

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