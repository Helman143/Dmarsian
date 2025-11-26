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

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if ($email === '' || $otp === '' || $newPassword === '' || $confirmPassword === '' || $newPassword !== $confirmPassword) {
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

// Fetch latest active reset for this email
$reset = null;
if ($stmt = $conn->prepare("SELECT id, otp_hash, otp_expires_at, attempt_count FROM admin_password_resets WHERE email = ? AND consumed = 0 ORDER BY id DESC LIMIT 1")) {
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $reset = $res->fetch_assoc();
        }
    }
    $stmt->close();
}

if (!$reset) {
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

$resetId = intval($reset['id']);
$attempts = intval($reset['attempt_count']);
$expiresAt = strtotime($reset['otp_expires_at']);

// If too many attempts, consume and block
if ($attempts >= 5) {
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

// Check expiry
if (!$expiresAt || time() > $expiresAt) {
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

$validOtp = password_verify($otp, $reset['otp_hash']);
if (!$validOtp) {
    if ($stmt = $conn->prepare("UPDATE admin_password_resets SET attempt_count = attempt_count + 1 WHERE id = ?")) {
        $stmt->bind_param('i', $resetId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: admin_verify_otp.php?error=1');
    exit();
}

// OTP is valid: update admin password
// Ensure admin exists for this email
$admin = null;
if ($stmt = $conn->prepare("SELECT id FROM admin_accounts WHERE email = ? LIMIT 1")) {
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $admin = $res->fetch_assoc();
        }
    }
    $stmt->close();
}

if (!$admin) {
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
if ($stmt = $conn->prepare("UPDATE admin_accounts SET password = ? WHERE email = ?")) {
    $stmt->bind_param('ss', $newHash, $email);
    $stmt->execute();
    $stmt->close();
}

// Consume this reset and any other active ones for the same email
if ($stmt = $conn->prepare("UPDATE admin_password_resets SET consumed = 1 WHERE email = ? AND consumed = 0")) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
}

// Optional: invalidate sessions (logout if logged in)
$_SESSION = [];

header('Location: admin_verify_otp.php?ok=1');
exit();


