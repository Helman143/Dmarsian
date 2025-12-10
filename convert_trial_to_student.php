<?php
require_once 'db_connect.php';
require_once 'auth_helpers.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registration_id = $_POST['registration_id'] ?? '';
    if (!$registration_id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing registration ID.']);
        exit();
    }
    $conn = connectDB();
    // Fetch registration info
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ? AND status = 'complete'");
    $stmt->bind_param('i', $registration_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Registration not found or not complete.']);
        exit();
    }
    $reg = $result->fetch_assoc();
    $stmt->close();
    
    // Handle NULL values and prepare data
    $student_name = $reg['student_name'] ?? '';
    $address = $reg['address'] ?? '';
    $phone = $reg['phone'] ?? '';
    $email = $reg['email'] ?? null;
    $school = $reg['school'] ?? null;
    $parents_name = $reg['parents_name'] ?? null;
    $parent_phone = $reg['parent_phone'] ?? null;
    $parent_email = $reg['parent_email'] ?? null;
    $belt_rank = $reg['belt_rank'] ?? '';
    $schedule = 'MWF-PM'; // Default schedule for trial conversions
    
    // Validate required fields
    if (empty($student_name) || empty($address) || empty($phone) || empty($belt_rank)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required student information.']);
        $conn->close();
        exit();
    }
    
    // Insert into students table (jeja_no will be set after insert)
    // Use temporary jeja_no since it's NOT NULL, then update with actual value
    $temp_jeja_no = 'TEMP-' . time() . '-' . rand(1000, 9999);
    $sql = "INSERT INTO students (jeja_no, full_name, address, phone, email, school, parent_name, parent_phone, parent_email, belt_rank, discount, schedule, date_enrolled, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, CURDATE(), 'Active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssss',
        $temp_jeja_no,
        $student_name,
        $address,
        $phone,
        $email,
        $school,
        $parents_name,
        $parent_phone,
        $parent_email,
        $belt_rank,
        $schedule
    );
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $new_jeja_no = 'STD-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
        $update = $conn->prepare("UPDATE students SET jeja_no = ? WHERE id = ?");
        $update->bind_param('si', $new_jeja_no, $new_id);
        if (!$update->execute()) {
            // If update fails, rollback by deleting the inserted record
            $delete_stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            $delete_stmt->bind_param('i', $new_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to set student number: ' . $update->error
            ]);
            $update->close();
            $stmt->close();
            $conn->close();
            exit();
        }
        $update->close();
        // Optionally update registration status
        $reg_update = $conn->prepare("UPDATE registrations SET status = 'enrolled' WHERE id = ?");
        $reg_update->bind_param('i', $registration_id);
        $reg_update->execute();
        $reg_update->close();
        // Log activity
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Trial Conversion';
        $student_id = $new_jeja_no;
        $details = 'Converted trial registration #' . $registration_id . ' to student ' . $reg['student_name'];
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Student enrolled successfully.']);
    } else {
        $error_msg = $stmt->error;
        $error_code = $stmt->errno;
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to insert student: ' . $error_msg,
            'mysql_error' => $error_msg,
            'mysql_errno' => $error_code
        ]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}