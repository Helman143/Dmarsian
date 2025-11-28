<?php
// Set JSON header first to ensure proper response type
header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

// Error handling wrapper
try {
    session_start();
    require_once 'db_connect.php';

    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }

    $conn = connectDB();
    
    // Check database connection
    if (!$conn || (isset($conn->connect_error) && $conn->connect_error)) {
        ob_clean();
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Unknown error')
        ]);
        exit;
    }

// Helper to decide if belt rank is missing/invalid
function isMissingBelt($belt)
{
    if ($belt === null) return true;
    $val = trim((string)$belt);
    return $val === '' || $val === '0' || $val === '-';
}

// Attempt recovery from registrations table using email, then phone, then full name
// OPTIMIZED: Only recover if absolutely necessary to avoid performance issues
function recoverBeltRank(mysqli $conn, array $student)
{
    // 1) By email
    if (!empty($student['email'])) {
        $stmt = $conn->prepare("SELECT belt_rank FROM registrations WHERE email = ? AND belt_rank IS NOT NULL AND belt_rank <> '' AND belt_rank <> '0' ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $student['email']);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    return $row['belt_rank'];
                }
            }
            $stmt->close();
        }
    }

    // 2) By phone
    if (!empty($student['phone'])) {
        $stmt = $conn->prepare("SELECT belt_rank FROM registrations WHERE phone = ? AND belt_rank IS NOT NULL AND belt_rank <> '' AND belt_rank <> '0' ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $student['phone']);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    return $row['belt_rank'];
                }
            }
            $stmt->close();
        }
    }

    // 3) By full name
    if (!empty($student['full_name'])) {
        $stmt = $conn->prepare("SELECT belt_rank FROM registrations WHERE student_name = ? AND belt_rank IS NOT NULL AND belt_rank <> '' AND belt_rank <> '0' ORDER BY id DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $student['full_name']);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $stmt->close();
                    return $row['belt_rank'];
                }
            }
            $stmt->close();
        }
    }

    return null;
}

    $students = [];

    // If a specific student is requested, fetch only that student
    if (isset($_GET['jeja_no']) && $_GET['jeja_no'] !== '') {
        $jejaNo = trim($_GET['jeja_no']);
        $stmt = $conn->prepare("SELECT * FROM students WHERE jeja_no = ? ORDER BY date_enrolled DESC");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        $stmt->bind_param('s', $jejaNo);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute query: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Order by the numeric part of jeja_no so STD numbers appear 1,2,3,...
        $result = $conn->query("SELECT * FROM students ORDER BY CAST(REPLACE(jeja_no, 'STD-', '') AS UNSIGNED) ASC");
        if (!$result) {
            throw new Exception("Failed to execute query: " . $conn->error);
        }
    }

    // Limit belt rank recovery to avoid timeout - only recover for first 50 students
    $recoveryCount = 0;
    $maxRecoveries = 50;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Attempt to recover belt rank if missing (but limit to avoid timeout)
            if (isMissingBelt($row['belt_rank']) && $recoveryCount < $maxRecoveries) {
                $recovered = recoverBeltRank($conn, $row);
                if (!isMissingBelt($recovered)) {
                    $row['belt_rank'] = $recovered;
                    // Persist recovered value (but don't wait if it fails)
                    $upd = $conn->prepare("UPDATE students SET belt_rank = ? WHERE id = ?");
                    if ($upd) {
                        $upd->bind_param('si', $recovered, $row['id']);
                        @$upd->execute(); // Use @ to suppress errors if update fails
                        $upd->close();
                    }
                    $recoveryCount++;
                }
            }
            $students[] = $row;
        }
    }

    // Clear any output before sending JSON
    ob_clean();
    echo json_encode(['status' => 'success', 'data' => $students]);

} catch (Exception $e) {
    // Clear any output and send error as JSON
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} catch (Throwable $e) {
    // Catch any other errors
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} finally {
    // Clean up
    if (isset($conn) && $conn) {
        @$conn->close();
    }
    ob_end_flush();
}