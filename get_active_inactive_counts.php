<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $conn = connectDB();
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    // Get student counts by status from students table
    $sql = "
        SELECT status, COUNT(*) as count
        FROM students
        WHERE status IN ('Active', 'Inactive', 'Freeze')
        GROUP BY status
    ";
    $res = $conn->query($sql);

    $counts = ['active' => 0, 'inactive' => 0, 'freeze' => 0];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $status = strtolower(trim($row['status']));
            if ($status === 'active') {
                $counts['active'] = (int)$row['count'];
            } elseif ($status === 'inactive') {
                $counts['inactive'] = (int)$row['count'];
            } elseif ($status === 'freeze') {
                $counts['freeze'] = (int)$row['count'];
            }
        }
    }
    
    $conn->close();
    echo json_encode($counts);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>