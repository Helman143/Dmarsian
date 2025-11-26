<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'db_connect.php';
require_once 'auth_helpers.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Get the pending enrollment
    $stmt = $conn->prepare("SELECT * FROM enrollment_requests WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $enrollment = $result->fetch_assoc();
    $stmt->close();

    if ($enrollment) {
        // Set discount based on school
        $school = $enrollment['school'];
        if (strcasecmp($school, 'SCC') === 0 || strcasecmp($school, 'Saint Columban College') === 0) {
            $discount = 500.00;
        } elseif (strcasecmp($school, 'ZSSAT') === 0) {
            $discount = 1000.00;
        } else {
            $discount = 0.00;   
        }
        // Generate jeja_no (STD No.)
        $jeja_no = 'STD-' . str_pad($enrollment['id'], 5, '0', STR_PAD_LEFT);
        $date_enrolled = date('Y-m-d');
        $status = 'Active';
        // Insert into students table with all required fields
        $stmt = $conn->prepare("INSERT INTO students (jeja_no, full_name, address, phone, email, school, parent_name, parent_phone, parent_email, belt_rank, discount, schedule, date_enrolled, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            'ssssssssssdsss',
            $jeja_no,
            $enrollment['full_name'],
            $enrollment['address'],
            $enrollment['phone'],
            $enrollment['email'],
            $enrollment['school'],
            $enrollment['parent_name'],
            $enrollment['parent_phone'],
            $enrollment['parent_email'],
            $enrollment['belt_rank'],
            $discount,
            $enrollment['class'], // class is used as schedule
            $date_enrolled,
            $status
        );
        if ($stmt->execute()) {
            // Update status in enrollment_requests
            $update = $conn->prepare("UPDATE enrollment_requests SET status = 'approved' WHERE id = ?");
            $update->bind_param('i', $id);
            $update->execute();
            $update->close();

            // Log to activity_log
            $admin_account = getAdminAccountName($conn);
            $action_type = 'Enrollment (Approval)';
            $student_id = $jeja_no;
            $details = 'Enrolled (Approved)';
            $stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
            $stmt->bind_param("ssss", $action_type, $admin_account, $student_id, $details);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['status' => 'success', 'message' => 'Enrollment approved and student added!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add student. ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Enrollment request not found or already approved.']);
    }
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
} 