<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$conn = connectDB();

// Helper to decide if belt rank is missing/invalid
function isMissingBelt($belt)
{
    if ($belt === null) return true;
    $val = trim((string)$belt);
    return $val === '' || $val === '0' || $val === '-';
}

// Attempt recovery from registrations table using email, then phone, then full name
function recoverBeltRank(mysqli $conn, array $student)
{
    // 1) By email
    if (!empty($student['email'])) {
        $stmt = $conn->prepare("SELECT belt_rank FROM registrations WHERE email = ? AND belt_rank IS NOT NULL AND belt_rank <> '' AND belt_rank <> '0' ORDER BY id DESC LIMIT 1");
        $stmt->bind_param('s', $student['email']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['belt_rank'];
        }
        $stmt->close();
    }

    // 2) By phone
    if (!empty($student['phone'])) {
        $stmt = $conn->prepare("SELECT belt_rank FROM registrations WHERE phone = ? AND belt_rank IS NOT NULL AND belt_rank <> '' AND belt_rank <> '0' ORDER BY id DESC LIMIT 1");
        $stmt->bind_param('s', $student['phone']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['belt_rank'];
        }
        $stmt->close();
    }

    // 3) By full name
    if (!empty($student['full_name'])) {
        $stmt = $conn->prepare("SELECT belt_rank FROM registrations WHERE student_name = ? AND belt_rank IS NOT NULL AND belt_rank <> '' AND belt_rank <> '0' ORDER BY id DESC LIMIT 1");
        $stmt->bind_param('s', $student['full_name']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['belt_rank'];
        }
        $stmt->close();
    }

    return null;
}

$students = [];

// If a specific student is requested, fetch only that student
if (isset($_GET['jeja_no']) && $_GET['jeja_no'] !== '') {
    $jejaNo = $_GET['jeja_no'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE jeja_no = ? ORDER BY date_enrolled DESC");
    $stmt->bind_param('s', $jejaNo);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Order by the numeric part of jeja_no so STD numbers appear 1,2,3,...
    $result = $conn->query("SELECT * FROM students ORDER BY CAST(REPLACE(jeja_no, 'STD-', '') AS UNSIGNED) ASC");
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Attempt to recover belt rank if missing
        if (isMissingBelt($row['belt_rank'])) {
            $recovered = recoverBeltRank($conn, $row);
            if (!isMissingBelt($recovered)) {
                $row['belt_rank'] = $recovered;
                // Persist recovered value
                $upd = $conn->prepare("UPDATE students SET belt_rank = ? WHERE id = ?");
                $upd->bind_param('si', $recovered, $row['id']);
                $upd->execute();
                $upd->close();
            }
        }
        $students[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $students]);

$conn->close();