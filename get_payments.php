<?php
require_once 'db_connect.php';
$conn = connectDB();
if (!$conn) {
    echo json_encode([]);
    exit();
}
header('Content-Type: application/json');
// Add cache-busting headers to prevent stale data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Get all payment records, joining with students table to get correct enrollment date
$sql = "SELECT p.id, p.jeja_no, 
        COALESCE(s.full_name, p.fullname) as fullname,
        p.date_paid, p.amount_paid, p.payment_type, p.discount,
        COALESCE(s.date_enrolled, p.date_enrolled) as date_enrolled,
        p.status, p.created_at, p.updated_at
        FROM payments p
        LEFT JOIN students s ON p.jeja_no = s.jeja_no";

if ($search !== '') {
    $sql .= " WHERE p.jeja_no LIKE '%$search%' OR p.fullname LIKE '%$search%' OR COALESCE(s.full_name, p.fullname) LIKE '%$search%' OR p.payment_type LIKE '%$search%' OR p.status LIKE '%$search%'";
}
$sql .= " ORDER BY p.id DESC";

$result = $conn->query($sql);
$payments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
echo json_encode($payments); 