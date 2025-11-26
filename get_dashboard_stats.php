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

    // Today's Enrollees
    $sql_today_enrollees = "SELECT COUNT(*) AS count FROM students WHERE DATE(date_enrolled) = '$today'";
    $res_today_enrollees = $conn->query($sql_today_enrollees);
    $todayEnrollees = $res_today_enrollees ? (int)$res_today_enrollees->fetch_assoc()['count'] : 0;

    // Weekly Enrollees
    $sql_weekly_enrollees = "SELECT COUNT(*) AS count FROM students WHERE DATE(date_enrolled) >= '$startOfWeek' AND DATE(date_enrolled) <= '$today'";
    $res_weekly_enrollees = $conn->query($sql_weekly_enrollees);
    $weeklyEnrollees = $res_weekly_enrollees ? (int)$res_weekly_enrollees->fetch_assoc()['count'] : 0;

    // Today's Collected Amount
    $sql_today_collected = "SELECT SUM(amount_paid) AS total FROM payments WHERE DATE(date_paid) = '$today'";
    $res_today_collected = $conn->query($sql_today_collected);
    $todayCollected = $res_today_collected ? (float)$res_today_collected->fetch_assoc()['total'] : 0.00;

    // Weekly Collected Amount
    $sql_weekly_collected = "SELECT SUM(amount_paid) AS total FROM payments WHERE DATE(date_paid) >= '$startOfWeek' AND DATE(date_paid) <= '$today'";
    $res_weekly_collected = $conn->query($sql_weekly_collected);
    $weeklyCollected = $res_weekly_collected ? (float)$res_weekly_collected->fetch_assoc()['total'] : 0.00;

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