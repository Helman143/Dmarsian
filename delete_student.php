<?php
session_start();
require_once 'db_connect.php';
require_once 'auth_helpers.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$jeja_no = isset($data['jeja_no']) ? $data['jeja_no'] : null;

if (!$jeja_no) {
    die(json_encode(['status' => 'error', 'message' => 'Jeja number is required']));
}

$conn = connectDB();

// Prepare and execute the delete statement
$stmt = $conn->prepare("DELETE FROM students WHERE jeja_no = ?");
$stmt->bind_param("s", $jeja_no);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Log deletion
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Student Delete';
        $student_id = $jeja_no;
        $details = 'Deleted student with ID: ' . $jeja_no;
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Student deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error deleting student: ' . $stmt->error]);
}

$stmt->close();
$conn->close(); 