<?php
require 'db_connect.php';
header('Content-Type: application/json');
$result = $conn->query("SELECT * FROM students WHERE status = 'Active' ORDER BY date_enrolled DESC, created_at DESC");
$approved = [];
while ($row = $result->fetch_assoc()) {
    $approved[] = [
        'id' => $row['id'],
        'jeja_no' => $row['jeja_no'],
        'date_enrolled' => $row['date_enrolled'],
        'full_name' => $row['full_name'],
        'phone' => $row['phone'],
        'amount_paid' => '', // No amount_paid in table, leave blank or set default
        'payment_type' => '', // No payment_type in table, leave blank or set default
    ];
}
echo json_encode(['status' => 'success', 'data' => $approved]);
$conn->close(); 