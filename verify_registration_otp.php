<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connect.php';
require_once 'config.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
// Remove any non-numeric characters from OTP (in case of copy-paste issues)
$otp = preg_replace('/[^0-9]/', '', $otp);

if (empty($email) || empty($otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required']);
    exit();
}

if (!$conn || $conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Fetch latest active OTP for this email (case-insensitive)
$otpRecord = null;
if ($stmt = $conn->prepare("SELECT id, email, otp_hash, otp_expires_at, attempt_count FROM registration_otps WHERE LOWER(email) = LOWER(?) AND consumed = 0 ORDER BY id DESC LIMIT 1")) {
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $otpRecord = $res->fetch_assoc();
        }
    }
    $stmt->close();
}

if (!$otpRecord) {
    echo json_encode(['status' => 'error', 'message' => 'No active OTP found. Please request a new one.']);
    exit();
}

$otpId = $otpRecord['id'];
$attempts = intval($otpRecord['attempt_count']);

// Check if too many attempts
if ($attempts >= 5) {
    echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Please request a new OTP.']);
    exit();
}

// Check if expired
$expiresAt = strtotime($otpRecord['otp_expires_at']);
$currentTime = time();
if ($expiresAt < $currentTime) {
    echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one.']);
    exit();
}

// Verify OTP with detailed logging
error_log("OTP Verification - Attempting to verify OTP for registration ID: {$otpId}, email: {$email}, OTP length: " . strlen($otp));
$validOtp = password_verify($otp, $otpRecord['otp_hash']);
error_log("OTP Verification Result: " . ($validOtp ? 'VALID' : 'INVALID') . " for registration ID: {$otpId}");

if (!$validOtp) {
    // Try to verify with trimmed OTP (in case of extra spaces)
    $trimmedOtp = trim($otp);
    if ($trimmedOtp !== $otp) {
        error_log("OTP Verification - Retrying with trimmed OTP (had whitespace)");
        $validOtp = password_verify($trimmedOtp, $otpRecord['otp_hash']);
        if ($validOtp) {
            error_log("OTP Verification Success after trimming whitespace");
        }
    }
    
    if (!$validOtp) {
        // Increment attempt count
        if ($stmt = $conn->prepare("UPDATE registration_otps SET attempt_count = attempt_count + 1 WHERE id = ?")) {
            $stmt->bind_param('i', $otpId);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP code. Please check and try again.']);
        exit();
    }
}

error_log("OTP Verification Success: Valid OTP for registration ID: {$otpId}, email: {$email}");

// OTP is valid - mark as consumed and generate verification token
$verificationToken = bin2hex(random_bytes(32)); // 64-character hex string
$tokenExpiresAt = date('Y-m-d H:i:s', time() + 10 * 60); // 10 minutes for form submission

// Mark OTP as consumed
if ($stmt = $conn->prepare("UPDATE registration_otps SET consumed = 1 WHERE id = ?")) {
    $stmt->bind_param('i', $otpId);
    $stmt->execute();
    $stmt->close();
}

// Safety net: ensure registration_verifications table exists
@$conn->query("CREATE TABLE IF NOT EXISTS `registration_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `verification_token` varchar(64) NOT NULL,
  `token_expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_token` (`email`,`verification_token`),
  KEY `idx_token` (`verification_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Invalidate previous tokens for this email
if ($stmt = $conn->prepare("DELETE FROM registration_verifications WHERE email = ?")) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
}

// Store new verification token
if ($stmt = $conn->prepare("INSERT INTO registration_verifications (email, verification_token, token_expires_at) VALUES (?,?,?)")) {
    $stmt->bind_param('sss', $email, $verificationToken, $tokenExpiresAt);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'status' => 'success', 
    'message' => 'Email verified successfully',
    'verification_token' => $verificationToken
]);
?>
