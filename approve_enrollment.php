<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'db_connect.php';
require_once 'auth_helpers.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // First, check if enrollment request exists (regardless of status)
    $stmt = $conn->prepare("SELECT * FROM enrollment_requests WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $enrollment = $result->fetch_assoc();
    $stmt->close();

    if (!$enrollment) {
        echo json_encode(['status' => 'error', 'message' => 'Enrollment request not found.']);
        $conn->close();
        exit();
    }

    // Check if already approved
    if ($enrollment['status'] === 'approved') {
        // Check if student already exists
        $check_stmt = $conn->prepare("SELECT jeja_no FROM students WHERE full_name = ? AND phone = ? LIMIT 1");
        $check_stmt->bind_param('ss', $enrollment['full_name'], $enrollment['phone']);
        $check_stmt->execute();
        $existing_student = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($existing_student) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Enrollment was already approved. Student exists: ' . $existing_student['jeja_no']
            ]);
        } else {
            echo json_encode([
                'status' => 'info', 
                'message' => 'Enrollment was already approved, but student record not found. You may need to create the student manually.'
            ]);
        }
        $conn->close();
        exit();
    }

    // Check if student with same name and phone already exists
    $check_stmt = $conn->prepare("SELECT jeja_no FROM students WHERE full_name = ? AND phone = ? LIMIT 1");
    $check_stmt->bind_param('ss', $enrollment['full_name'], $enrollment['phone']);
    $check_stmt->execute();
    $existing_student = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($existing_student) {
        // Student already exists, just update enrollment request status
        $update = $conn->prepare("UPDATE enrollment_requests SET status = 'approved' WHERE id = ?");
        $update->bind_param('i', $id);
        $update->execute();
        $update->close();

        // Log to activity_log
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Enrollment (Approval)';
        $student_id = $existing_student['jeja_no'];
        $details = 'Enrollment approved - Student already exists: ' . $enrollment['full_name'];
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param("ssss", $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Enrollment approved. Student already exists: ' . $existing_student['jeja_no']
        ]);
        $conn->close();
        exit();
    }

    // Set discount based on school
    $school = $enrollment['school'];
    if (strcasecmp($school, 'SCC-Junior High School') === 0 || 
        strcasecmp($school, 'SCC - Junior High School') === 0 ||
        strcasecmp($school, 'Saint Columban College-Junior High School') === 0 ||
        strcasecmp($school, 'Saint Columban College - Junior High School') === 0 ||
        strcasecmp($school, 'SCC Junior High School') === 0 ||
        strcasecmp($school, 'Saint Columban College Junior High School') === 0) {
        $discount = 500.00;
    } elseif (strcasecmp($school, 'ZSSAT') === 0 || strcasecmp($school, 'Zamboanga School of Science and Technology') === 0) {
        $discount = 1000.00;
    } else {
        $discount = 0.00;   
    }
    
    // Generate jeja_no (STD No.) - use a temporary value first since it's NOT NULL
    $temp_jeja_no = 'TEMP-' . time() . '-' . rand(1000, 9999);
    $date_enrolled = date('Y-m-d');
    $status = 'Active';
    $schedule = !empty($enrollment['class']) ? $enrollment['class'] : 'MWF-PM'; // Use class as schedule or default
    
    // Insert into students table with all required fields
    $stmt = $conn->prepare("INSERT INTO students (jeja_no, full_name, address, phone, email, school, parent_name, parent_phone, parent_email, belt_rank, discount, schedule, date_enrolled, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'ssssssssssdsss',
        $temp_jeja_no,
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
        $schedule,
        $date_enrolled,
        $status
    );
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $jeja_no = 'STD-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
        
        // Update jeja_no with correct value
        $update_jeja = $conn->prepare("UPDATE students SET jeja_no = ? WHERE id = ?");
        $update_jeja->bind_param('si', $jeja_no, $new_id);
        if (!$update_jeja->execute()) {
            // If update fails, rollback
            $delete_stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            $delete_stmt->bind_param('i', $new_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            echo json_encode(['status' => 'error', 'message' => 'Failed to set student number: ' . $update_jeja->error]);
            $update_jeja->close();
            $stmt->close();
            $conn->close();
            exit();
        }
        $update_jeja->close();
        
        // Update status in enrollment_requests
        $update = $conn->prepare("UPDATE enrollment_requests SET status = 'approved' WHERE id = ?");
        $update->bind_param('i', $id);
        $update->execute();
        $update->close();

        // Log to activity_log
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Enrollment (Approval)';
        $student_id = $jeja_no;
        $details = 'Enrolled (Approved): ' . $enrollment['full_name'];
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param("ssss", $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();

        // Return the newly created student data for immediate display
        echo json_encode([
            'status' => 'success', 
            'message' => 'Enrollment approved and student added!',
            'student' => [
                'id' => $new_id,
                'jeja_no' => $jeja_no,
                'date_enrolled' => $date_enrolled,
                'full_name' => $enrollment['full_name'],
                'phone' => $enrollment['phone']
            ]
        ]);
    } else {
        $error_msg = $stmt->error;
        echo json_encode(['status' => 'error', 'message' => 'Failed to add student: ' . $error_msg]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
} 