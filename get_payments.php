<?php
require_once 'db_connect.php';
$conn = connectDB();
if (!$conn) {
    echo json_encode([]);
    exit();
}
header('Content-Type: application/json');
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT * FROM payments";
if ($search !== '') {
    $sql .= " WHERE jeja_no LIKE '%$search%' OR fullname LIKE '%$search%' OR payment_type LIKE '%$search%' OR status LIKE '%$search%'";
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
$payments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
echo json_encode($payments); 