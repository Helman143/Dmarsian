<?php
// Set execution time limit to prevent timeouts
set_time_limit(10); // 10 seconds max - fail fast

// Set JSON header first to ensure proper response type
header('Content-Type: application/json');

// Start output buffering to catch any errors
ob_start();

// Error handling wrapper
try {
    // Configure session settings to prevent hangs
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.save_path', sys_get_temp_dir());
    
    // Start session with timeout protection
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // Quick session check - fail fast if not logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }

    // Load database connection
    require_once 'db_connect.php';

    // Get database connection with timeout
    $conn = connectDB();
    
    // Check database connection
    if (!$conn) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed'
        ]);
        exit;
    }
    
    if (isset($conn->connect_error) && $conn->connect_error) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection error: ' . $conn->connect_error
        ]);
        exit;
    }
    
    // Set query timeout
    $conn->query("SET SESSION max_execution_time = 5");

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
        // Optimized query: Fetch only essential columns and limit results
        // Use simple ordering - no complex functions
        $result = $conn->query("SELECT * FROM students ORDER BY jeja_no ASC LIMIT 1000");
        if (!$result) {
            throw new Exception("Failed to execute query: " . $conn->error);
        }
    }

    // DISABLED: Belt rank recovery causes 504 timeouts
    // Recovery can be done via a separate background job or admin action
    // For now, just return the data as-is to ensure fast response
    
    if ($result && $result->num_rows > 0) {
        // Fetch rows efficiently
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Sort students by numeric STD number in PHP (faster than SQL CAST)
        // Only sort if we have students
        if (count($students) > 0) {
            usort($students, function($a, $b) {
                $numA = (int) str_replace('STD-', '', $a['jeja_no'] ?? '0');
                $numB = (int) str_replace('STD-', '', $b['jeja_no'] ?? '0');
                return $numA <=> $numB;
            });
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