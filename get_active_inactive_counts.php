<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $conn = connectDB();
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    // Get the latest payment record for each student by highest id
    $sql = "
        SELECT p.status, COUNT(*) as count
        FROM payments p
        INNER JOIN (
            SELECT jeja_no, MAX(id) as max_id
            FROM payments
            GROUP BY jeja_no
        ) latest
        ON p.jeja_no = latest.jeja_no AND p.id = latest.max_id
        GROUP BY p.status
    ";
    $res = $conn->query($sql);

    $counts = ['active' => 0, 'inactive' => 0];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (strtolower($row['status']) === 'active') $counts['active'] = (int)$row['count'];
            if (strtolower($row['status']) === 'inactive') $counts['inactive'] = (int)$row['count'];
        }
    }
    
    $conn->close();
    echo json_encode($counts);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>