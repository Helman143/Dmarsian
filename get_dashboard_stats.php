<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Set timezone to Asia/Manila (change if needed)
date_default_timezone_set('Asia/Manila');

try {
    $conn = connectDB();
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit();
    }

    $today = date('Y-m-d');
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));

    // Today's Enrollees - using prepared statement for security
    // Include only students with valid date_enrolled that equals today
    $stmt_today = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE date_enrolled IS NOT NULL AND DATE(date_enrolled) = ?");
    $stmt_today->bind_param("s", $today);
    $stmt_today->execute();
    $res_today_enrollees = $stmt_today->get_result();
    $todayEnrollees = $res_today_enrollees ? (int)$res_today_enrollees->fetch_assoc()['count'] : 0;
    $stmt_today->close();

    // Weekly Enrollees - using prepared statement for security
    // Include only students with valid date_enrolled within the week range
    $stmt_weekly = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE date_enrolled IS NOT NULL AND DATE(date_enrolled) >= ? AND DATE(date_enrolled) <= ?");
    $stmt_weekly->bind_param("ss", $startOfWeek, $today);
    $stmt_weekly->execute();
    $res_weekly_enrollees = $stmt_weekly->get_result();
    $weeklyEnrollees = $res_weekly_enrollees ? (int)$res_weekly_enrollees->fetch_assoc()['count'] : 0;
    $stmt_weekly->close();
    
    // Debug: Check for students with NULL date_enrolled (these won't be counted)
    $stmt_null_check = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE date_enrolled IS NULL");
    $stmt_null_check->execute();
    $res_null_check = $stmt_null_check->get_result();
    $nullDateCount = $res_null_check ? (int)$res_null_check->fetch_assoc()['count'] : 0;
    $stmt_null_check->close();
    
    // Debug: Check for students with dates before the week start (these won't be counted in weekly)
    $stmt_old_check = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE date_enrolled IS NOT NULL AND DATE(date_enrolled) < ?");
    $stmt_old_check->bind_param("s", $startOfWeek);
    $stmt_old_check->execute();
    $res_old_check = $stmt_old_check->get_result();
    $oldDateCount = $res_old_check ? (int)$res_old_check->fetch_assoc()['count'] : 0;
    $stmt_old_check->close();
    
    // Get total student count for comparison
    $stmt_total = $conn->prepare("SELECT COUNT(*) AS count FROM students");
    $stmt_total->execute();
    $res_total = $stmt_total->get_result();
    $totalStudents = $res_total ? (int)$res_total->fetch_assoc()['count'] : 0;
    $stmt_total->close();
    
    // If there are students with NULL dates or old dates, include debug info
    $debugInfo = [];
    if ($nullDateCount > 0) {
        $debugInfo['null_date_enrolled_count'] = $nullDateCount;
    }
    if ($oldDateCount > 0) {
        $debugInfo['old_date_enrolled_count'] = $oldDateCount;
    }
    if ($totalStudents > 0) {
        $debugInfo['total_students'] = $totalStudents;
        $debugInfo['expected_weekly'] = $totalStudents - $nullDateCount - $oldDateCount;
    }

    // Today's Collected Amount - using prepared statement for security
    $stmt_today_collected = $conn->prepare("SELECT SUM(amount_paid) AS total FROM payments WHERE DATE(date_paid) = ?");
    $stmt_today_collected->bind_param("s", $today);
    $stmt_today_collected->execute();
    $res_today_collected = $stmt_today_collected->get_result();
    $todayCollected = $res_today_collected ? (float)$res_today_collected->fetch_assoc()['total'] : 0.00;
    $stmt_today_collected->close();

    // Weekly Collected Amount - using prepared statement for security
    $stmt_weekly_collected = $conn->prepare("SELECT SUM(amount_paid) AS total FROM payments WHERE DATE(date_paid) >= ? AND DATE(date_paid) <= ?");
    $stmt_weekly_collected->bind_param("ss", $startOfWeek, $today);
    $stmt_weekly_collected->execute();
    $res_weekly_collected = $stmt_weekly_collected->get_result();
    $weeklyCollected = $res_weekly_collected ? (float)$res_weekly_collected->fetch_assoc()['total'] : 0.00;
    $stmt_weekly_collected->close();

    // Get latest status per student from payments (by highest id)
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

    $activePayments = 0;
    $inactivePayments = 0;
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (strtolower($row['status']) === 'active') {
                $activePayments = (int)$row['count'];
            } elseif (strtolower($row['status']) === 'inactive') {
                $inactivePayments = (int)$row['count'];
            }
        }
    }

    $conn->close();

    $response = [
        'status' => 'success',
        'todayEnrollees' => $todayEnrollees,
        'weeklyEnrollees' => $weeklyEnrollees,
        'todayCollected' => $todayCollected,
        'weeklyCollected' => $weeklyCollected,
        'activePayments' => $activePayments,
        'inactivePayments' => $inactivePayments
    ];
    
    // Include debug info if there are students with NULL dates (for troubleshooting)
    if (isset($debugInfo) && !empty($debugInfo)) {
        $response['debug'] = $debugInfo;
    }
    
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>