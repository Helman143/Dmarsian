<?php
session_start();
require_once 'db_connect.php';
require_once 'auth_helpers.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectDB();
    header('Content-Type: application/json');
    
    // Prepare the data
    $jeja_no = mysqli_real_escape_string($conn, $_POST['jeja_no']);
    // Normalize jeja_no: allow values like 57 or 00057 and convert to STD-00057
    $jeja_no = trim($jeja_no);
    if ($jeja_no !== '') {
        // Remove existing STD- if any, then pad
        $raw = preg_replace('/^STD-?/i', '', $jeja_no);
        if (ctype_digit($raw)) {
            $raw = ltrim($raw, '0');
            if ($raw === '') { $raw = '0'; }
            $jeja_no = 'STD-' . str_pad($raw, 5, '0', STR_PAD_LEFT);
        } else {
            // If it wasn't numeric after removing prefix, ensure it has STD- prefix
            if (stripos($jeja_no, 'STD-') !== 0) {
                $jeja_no = 'STD-' . $raw;
            } else {
                $jeja_no = 'STD-' . $raw;
            }
        }
    }
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $school = mysqli_real_escape_string($conn, $_POST['school']);
    $parent_name = mysqli_real_escape_string($conn, $_POST['parent_name']);
    $parent_phone = mysqli_real_escape_string($conn, $_POST['parent_phone']);
    $parent_email = mysqli_real_escape_string($conn, $_POST['parent_email']);

    // Normalize belt rank to canonical label values before saving
    $raw_belt_rank = $_POST['belt_rank'] ?? '';
    $normalizeBelt = function ($value) {
        $allowed = ['White', 'Yellow', 'Green', 'Blue', 'Red', 'Black'];
        $map = [
            '0' => 'White',
            '1' => 'Yellow',
            '2' => 'Green',
            '3' => 'Blue',
            '4' => 'Red',
            '5' => 'Black',
        ];
        $val = trim((string)$value);
        if ($val === '') return '';
        // If numeric code provided, map to label
        if (array_key_exists($val, $map)) return $map[$val];
        // If already a label, pass-through only if allowed
        return in_array($val, $allowed, true) ? $val : '';
    };
    $belt_rank = mysqli_real_escape_string($conn, $normalizeBelt($raw_belt_rank));
    $class = mysqli_real_escape_string($conn, $_POST['class'] ?? '');
    $discount = floatval($_POST['discount']);
    $schedule = mysqli_real_escape_string($conn, $_POST['schedule']);
    $enroll_type = isset($_POST['enroll_type']) && $_POST['enroll_type'] !== '' ? mysqli_real_escape_string($conn, $_POST['enroll_type']) : 'Enroll';
    // Normalize status to allowed values
    $raw_status = $_POST['status'] ?? '';
    $statusAllowed = ['Active','Inactive','Freeze'];
    $status = in_array($raw_status, $statusAllowed, true) ? $raw_status : 'Active';
    // Normalize gender
    $raw_gender = $_POST['gender'] ?? '';
    $genderAllowed = ['Male','Female'];
    $gender = in_array($raw_gender, $genderAllowed, true) ? $raw_gender : '';

    // Helper: check if a column exists to avoid SQL errors when DB isn't migrated yet
    $hasClassColumn = false;
    $hasGenderColumn = false;
    // Check for optional columns
    $colCheck = $conn->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME IN ('class','gender')");
    if ($colCheck && $colCheck->execute()) {
        $res = $colCheck->get_result();
        while ($row = $res->fetch_assoc()) {
            if ($row['COLUMN_NAME'] === 'class') $hasClassColumn = true;
            if ($row['COLUMN_NAME'] === 'gender') $hasGenderColumn = true;
        }
    }
    if ($colCheck) { $colCheck->close(); }

    // Check if this is an update (if jeja_no already exists)
    $check_sql = "SELECT id FROM students WHERE jeja_no = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $jeja_no);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing student
        if ($hasClassColumn && $hasGenderColumn) {
            $sql = "UPDATE students SET 
                    full_name = ?, 
                    address = ?, 
                    phone = ?, 
                    email = ?, 
                    school = ?, 
                    parent_name = ?, 
                    parent_phone = ?, 
                    parent_email = ?, 
                    belt_rank = ?, 
                    discount = ?, 
                    schedule = ?,
                    class = ?,
                    gender = ?,
                    status = ?
                    WHERE jeja_no = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdsssss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $class,
                $gender,
                $status,
                $jeja_no
            );
        } elseif ($hasClassColumn && !$hasGenderColumn) {
            $sql = "UPDATE students SET 
                    full_name = ?, 
                    address = ?, 
                    phone = ?, 
                    email = ?, 
                    school = ?, 
                    parent_name = ?, 
                    parent_phone = ?, 
                    parent_email = ?, 
                    belt_rank = ?, 
                    discount = ?, 
                    schedule = ?,
                    class = ?,
                    status = ?
                    WHERE jeja_no = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdssss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $class,
                $status,
                $jeja_no
            );
        } elseif (!$hasClassColumn && $hasGenderColumn) {
            $sql = "UPDATE students SET 
                    full_name = ?, 
                    address = ?, 
                    phone = ?, 
                    email = ?, 
                    school = ?, 
                    parent_name = ?, 
                    parent_phone = ?, 
                    parent_email = ?, 
                    belt_rank = ?, 
                    discount = ?, 
                    schedule = ?,
                    gender = ?,
                    status = ?
                    WHERE jeja_no = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdssss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $gender,
                $status,
                $jeja_no
            );
        } else {
            $sql = "UPDATE students SET 
                    full_name = ?, 
                    address = ?, 
                    phone = ?, 
                    email = ?, 
                    school = ?, 
                    parent_name = ?, 
                    parent_phone = ?, 
                    parent_email = ?, 
                    belt_rank = ?, 
                    discount = ?, 
                    schedule = ?,
                    status = ?
                    WHERE jeja_no = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdsss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $status,
                $jeja_no
            );
        }
        
        $message = "Student updated successfully";
    } else {
        // Insert new student without jeja_no
        if ($hasClassColumn && $hasGenderColumn) {
            $sql = "INSERT INTO students (
                    full_name, address, phone, email, school, 
                    parent_name, parent_phone, parent_email, 
                    belt_rank, discount, schedule, class, gender, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdssss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $class,
                $gender,
                $status
            );
        } elseif ($hasClassColumn && !$hasGenderColumn) {
            $sql = "INSERT INTO students (
                    full_name, address, phone, email, school, 
                    parent_name, parent_phone, parent_email, 
                    belt_rank, discount, schedule, class, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdsss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $class,
                $status
            );
        } elseif (!$hasClassColumn && $hasGenderColumn) {
            $sql = "INSERT INTO students (
                    full_name, address, phone, email, school, 
                    parent_name, parent_phone, parent_email, 
                    belt_rank, discount, schedule, gender, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdsss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $gender,
                $status
            );
        } else {
            $sql = "INSERT INTO students (
                    full_name, address, phone, email, school, 
                    parent_name, parent_phone, parent_email, 
                    belt_rank, discount, schedule, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssdss",
                $full_name,
                $address,
                $phone,
                $email,
                $school,
                $parent_name,
                $parent_phone,
                $parent_email,
                $belt_rank,
                $discount,
                $schedule,
                $status
            );
        }
        
        $message = "Student saved successfully";
    }
    
    if ($stmt->execute()) {
        // If new student, update jeja_no after insert
        if (!isset($jeja_no) || empty($jeja_no)) {
            $new_id = $conn->insert_id;
            $new_jeja_no = 'STD-' . str_pad($new_id, 5, '0', STR_PAD_LEFT);
            $update = $conn->prepare("UPDATE students SET jeja_no = ? WHERE id = ?");
            $update->bind_param("si", $new_jeja_no, $new_id);
            $update->execute();
            $update->close();
            // Log to activity_log for new student
            $admin_account = getAdminAccountName($conn);
            $action_type = 'Student Enrollment';
            $student_id = $new_jeja_no;
            $details = "Enrolled student: $full_name";
            $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
            $log_stmt->bind_param("ssss", $action_type, $admin_account, $student_id, $details);
            $log_stmt->execute();
            $log_stmt->close();
        } else {
            // Log update action
            $admin_account = getAdminAccountName($conn);
            $action_type = 'Student Update';
            $student_id = $jeja_no;
            $details = "Updated student: $full_name";
            $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
            $log_stmt->bind_param("ssss", $action_type, $admin_account, $student_id, $details);
            $log_stmt->execute();
            $log_stmt->close();
        }
        // Insert registration data (best-effort, do not break JSON on failure)
        $regStmt = $conn->prepare("INSERT INTO registrations (student_name, address, parents_name, phone, email, parent_phone, school, class, parent_email, belt_rank, enroll_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($regStmt) {
            $regStmt->bind_param('sssssssssss', $full_name, $address, $parent_name, $phone, $email, $parent_phone, $school, $class, $parent_email, $belt_rank, $enroll_type);
            $regStmt->execute();
            $regStmt->close();
        }
        $conn->close();
        echo json_encode(['status' => 'success', 'message' => $message]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
} 