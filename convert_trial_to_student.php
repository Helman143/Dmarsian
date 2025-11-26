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
    // Insert into students table (jeja_no will be set after insert)
    $sql = "INSERT INTO students (full_name, address, phone, email, school, parent_name, parent_phone, parent_email, belt_rank, discount, schedule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, '')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssss',
        $reg['student_name'],
        $reg['address'],
        $reg['phone'],
        $reg['email'],
        $reg['school'],
        $reg['parents_name'],
        $reg['parent_phone'],
        $reg['parent_email'],
        $reg['belt_rank']
    );
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $new_jeja_no = 'STD-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
        $update = $conn->prepare("UPDATE students SET jeja_no = ? WHERE id = ?");
        $update->bind_param('si', $new_jeja_no, $new_id);
        $update->execute();
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
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert student.', 'mysql_error' => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}