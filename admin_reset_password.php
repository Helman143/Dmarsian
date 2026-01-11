<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'db_connect.php';

date_default_timezone_set('Asia/Manila');

// Safety net: ensure reset table exists (in case migration hasn't run yet)
@$conn->query("CREATE TABLE IF NOT EXISTS `admin_password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `otp_expires_at` datetime NOT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 0,
  `last_sent_at` datetime NOT NULL,
  `consumed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_active` (`email`,`consumed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_verify_otp.php');
    exit();
}

$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if ($email === '' || $otp === '' || $newPassword === '' || $confirmPassword === '' || $newPassword !== $confirmPassword) {
    error_log("OTP Verification Failed: Missing fields or password mismatch. Email: " . ($email ?: 'empty') . ", OTP length: " . strlen($otp));
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

// Fetch latest active reset for this email (case-insensitive)
$reset = null;
if ($stmt = $conn->prepare("SELECT id, email, otp_hash, otp_expires_at, attempt_count FROM admin_password_resets WHERE LOWER(email) = LOWER(?) AND consumed = 0 ORDER BY id DESC LIMIT 1")) {
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $reset = $res->fetch_assoc();
            // Normalize email from database to lowercase for consistency
            $email = strtolower($reset['email']);
        }
    } else {
        error_log("Database query error in admin_reset_password.php: " . $stmt->error);
    }
    $stmt->close();
}

if (!$reset) {
    error_log("OTP Verification Failed: No active reset found for email: " . $email);
    // Check if there are any resets at all for debugging
    if ($stmt = $conn->prepare("SELECT id, email, consumed, otp_expires_at FROM admin_password_resets WHERE LOWER(email) = LOWER(?) ORDER BY id DESC LIMIT 5")) {
        $stmt->bind_param('s', $email);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                error_log("Found " . $res->num_rows . " reset record(s) for email, but none are active:");
                while ($row = $res->fetch_assoc()) {
                    error_log("  - ID: {$row['id']}, Email: {$row['email']}, Consumed: {$row['consumed']}, Expires: {$row['otp_expires_at']}");
                }
            } else {
                error_log("No reset records found at all for email: " . $email);
            }
        }
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

$resetId = intval($reset['id']);
$attempts = intval($reset['attempt_count']);
$expiresAt = strtotime($reset['otp_expires_at']);
$currentTime = time();

// If too many attempts, consume and block
if ($attempts >= 5) {
    error_log("OTP Verification Failed: Too many attempts ({$attempts}) for reset ID: {$resetId}, email: {$email}");
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

// Check expiry with detailed logging
if (!$expiresAt) {
    error_log("OTP Verification Failed: Invalid expiry time for reset ID: {$resetId}, email: {$email}, expires_at: " . $reset['otp_expires_at']);
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

$timeRemaining = $expiresAt - $currentTime;
if ($currentTime > $expiresAt) {
    error_log("OTP Verification Failed: OTP expired for reset ID: {$resetId}, email: {$email}. Expired " . abs($timeRemaining) . " seconds ago. Expires at: " . date('Y-m-d H:i:s', $expiresAt) . ", Current time: " . date('Y-m-d H:i:s', $currentTime));
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

// Verify OTP
$validOtp = password_verify($otp, $reset['otp_hash']);
if (!$validOtp) {
    error_log("OTP Verification Failed: Invalid OTP for reset ID: {$resetId}, email: {$email}, attempts: {$attempts}, time remaining: {$timeRemaining} seconds");
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET attempt_count = attempt_count + 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

error_log("OTP Verification Success: Valid OTP for reset ID: {$resetId}, email: {$email}, time remaining: {$timeRemaining} seconds");

// OTP is valid: update admin password
// Ensure admin exists for this email (case-insensitive)
$admin = null;
if ($stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE LOWER(email) = LOWER(?) LIMIT 1")) {
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $admin = $res->fetch_assoc();
        }
    } else {
        error_log("Database query error when checking admin account: " . $stmt->error);
    }
    $stmt->close();
}

if (!$admin) {
    error_log("OTP Verification Failed: Admin account not found for email: " . $email);
    // Consume token anyway to avoid reuse
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
if ($stmt = $conn->prepare("UPDATE admin_accounts SET password = ? WHERE LOWER(email) = LOWER(?)")) {
    $stmt->bind_param('ss', $newHash, $email);
    if ($stmt->execute()) {
        error_log("Password reset successful for admin ID: {$admin['id']}, email: {$email}");
    } else {
        error_log("Failed to update password for email: {$email}, error: " . $stmt->error);
    }
    $stmt->close();
}

// Consume this reset and any other active ones for the same email (case-insensitive)
if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE LOWER(email) = LOWER(?) AND consumed = 0")) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
}

// Optional: invalidate sessions (logout if logged in)
$_SESSION = [];

header('Location: admin_verify_otp.php?ok=1');
exit();


