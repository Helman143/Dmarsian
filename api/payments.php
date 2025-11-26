<?php
require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 0);
ob_start();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit();
    }

    $conn = connectDB();
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit();
    }

    // Accept form-data or JSON
    if (empty($_POST)) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) {
            foreach ($json as $k => $v) {
                $_POST[$k] = $v;
            }
        }
    }

    $jeja_no = $conn->real_escape_string($_POST['jeja_no'] ?? '');
    $numericPart = preg_replace('/\D/', '', $jeja_no);
    if ($numericPart !== '') {
        $jeja_no = 'STD-' . str_pad($numericPart, 5, '0', STR_PAD_LEFT);
    }
    $fullname = $conn->real_escape_string($_POST['full_name'] ?? '');
    $date_paid = $conn->real_escape_string($_POST['date_paid'] ?? '');
    $amount_paid = $conn->real_escape_string($_POST['amount_paid'] ?? '');
    $payment_type = $conn->real_escape_string($_POST['payment_type'] ?? '');
    $status = $conn->real_escape_string($_POST['status'] ?? '');
    $discount = isset($_POST['discount']) ? $conn->real_escape_string($_POST['discount']) : '0.00';
    // Optional: period (YYYY-MM) not stored in current schema, accepted but ignored
    $period = isset($_POST['period']) ? $_POST['period'] : null;

    if ($jeja_no && $fullname && $date_paid && $amount_paid && $payment_type && $status) {
        $sql = "INSERT INTO payments (jeja_no, fullname, date_paid, amount_paid, payment_type, discount, date_enrolled, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $date_enrolled = date('Y-m-d');
        $stmt->bind_param('ssssssss', $jeja_no, $fullname, $date_paid, $amount_paid, $payment_type, $discount, $date_enrolled, $status);
        if ($stmt->execute()) {
            // Activity log
            $admin_account = getAdminAccountName($conn);
            $action_type = 'Payments';
            $student_id = $jeja_no;
            $details = "Amount: $amount_paid\nPayment Type: $payment_type\nStatus: $status\nDiscount: $discount";
            $logStmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
            $logStmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
            @$logStmt->execute();
            @$logStmt->close();

            // Note: Advance Payment coverage is applied by dues computation (due date +2 months)
            echo json_encode(['success' => true, 'message' => 'Payment saved successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    }

} catch (Exception $e) {
    if (ob_get_length()) { ob_clean(); }
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
exit();
?>


