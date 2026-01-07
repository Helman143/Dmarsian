<?php
require 'db_connect.php';
header('Content-Type: application/json');
$result = $conn->query("SELECT * FROM enrollment_requests WHERE status = 'pending' ORDER BY created_at DESC");
$pending = [];
while ($row = $result->fetch_assoc()) {
    $pending[] = [
        'id' => $row['id'],
        'std_no' => '', // No std_no in table, leave blank or generate if needed
        'date_registered' => $row['created_at'],
        'full_name' => $row['full_name'],
        'address' => $row['address'],
        'phone' => $row['phone'],
        'email' => $row['email'],
        'school' => $row['school'],
        'parent_name' => $row['parent_name'],
        'parent_phone' => $row['parent_phone'],
        'parent_email' => $row['parent_email'],
        'rank' => '', // No rank in table, leave blank or generate if needed
        'belt_rank' => $row['belt_rank'],
        'class' => $row['class'],
        'schedule' => '', // No schedule in table, leave blank or generate if needed
    ];
}
echo json_encode(['status' => 'success', 'data' => $pending]);
$conn->close(); 