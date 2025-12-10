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
    $stmt_today = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE DATE(date_enrolled) = ?");
    $stmt_today->bind_param("s", $today);
    $stmt_today->execute();
    $res_today_enrollees = $stmt_today->get_result();
    $todayEnrollees = $res_today_enrollees ? (int)$res_today_enrollees->fetch_assoc()['count'] : 0;
    $stmt_today->close();

    // Weekly Enrollees - using prepared statement for security
    $stmt_weekly = $conn->prepare("SELECT COUNT(*) AS count FROM students WHERE DATE(date_enrolled) >= ? AND DATE(date_enrolled) <= ?");
    $stmt_weekly->bind_param("ss", $startOfWeek, $today);
    $stmt_weekly->execute();
    $res_weekly_enrollees = $stmt_weekly->get_result();
    $weeklyEnrollees = $res_weekly_enrollees ? (int)$res_weekly_enrollees->fetch_assoc()['count'] : 0;
    $stmt_weekly->close();

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

    echo json_encode([
        'status' => 'success',
        'todayEnrollees' => $todayEnrollees,
        'weeklyEnrollees' => $weeklyEnrollees,
        'todayCollected' => $todayCollected,
        'weeklyCollected' => $weeklyCollected,
        'activePayments' => $activePayments,
        'inactivePayments' => $inactivePayments
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
exit();
?>