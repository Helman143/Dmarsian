<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connect.php';
require_once 'config.php';

function sendEmailViaSMTP2GO(array $payload): array {
    $ch = curl_init('https://api.smtp2go.com/v3/email/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    return ['http_code' => $httpCode, 'body' => $response, 'error' => $curlErr];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['student_name'] ?? '';
    $address = $_POST['address'] ?? '';
    $parent_name = $_POST['parent_name'] ?? ($_POST['parents_name'] ?? '');
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $parent_phone = $_POST['parent_phone'] ?? '';
    $school = $_POST['school'] ?? '';
    $class = $_POST['class'] ?? '';
    $parent_email = $_POST['parent_email'] ?? '';
    $belt_rank = $_POST['belt_rank'] ?? '';
    $enroll_type = $_POST['enroll_type'] ?? '';
    if ($enroll_type !== 'Enroll') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid enrollment type.']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO enrollment_requests (full_name, phone, school, belt_rank, address, email, class, parent_name, parent_phone, parent_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssssssss", $full_name, $phone, $school, $belt_rank, $address, $email, $class, $parent_name, $parent_phone, $parent_email);
    if ($stmt->execute()) {
        // Notify Super Admin via SMTP2Go (non-blocking: ignore response)
        $adminTo = (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL) ? ADMIN_BCC_EMAIL : SMTP2GO_SENDER_EMAIL;
        $subject = 'New Enrollment Request Submitted';
        $textBody =
            "A new enrollment request has been submitted.\n\n" .
            "Student: $full_name\n" .
            "Parent: $parent_name\n" .
            "Class: $class | Belt: $belt_rank\n" .
            "Student Email: $email | Parent Email: $parent_email\n" .
            "Student Phone: $phone | Parent Phone: $parent_phone\n" .
            "School: $school\n" .
            "Address: $address\n" .
            "Submitted At: " . date('Y-m-d H:i:s');
        $htmlBody =
            '<div style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#222">' .
                '<h3 style="margin:0 0 10px">New Enrollment Request</h3>' .
                '<p><strong>Student:</strong> ' . htmlspecialchars($full_name) . '</p>' .
                '<p><strong>Parent:</strong> ' . htmlspecialchars($parent_name) . '</p>' .
                '<p><strong>Class/Belt:</strong> ' . htmlspecialchars($class) . ' | ' . htmlspecialchars($belt_rank) . '</p>' .
                '<p><strong>Student Email:</strong> ' . htmlspecialchars($email) . '<br>' .
                   '<strong>Parent Email:</strong> ' . htmlspecialchars($parent_email) . '</p>' .
                '<p><strong>Student Phone:</strong> ' . htmlspecialchars($phone) . '<br>' .
                   '<strong>Parent Phone:</strong> ' . htmlspecialchars($parent_phone) . '</p>' .
                '<p><strong>School:</strong> ' . htmlspecialchars($school) . '</p>' .
                '<p><strong>Address:</strong> ' . htmlspecialchars($address) . '</p>' .
                '<p><em>Submitted At: ' . date('Y-m-d H:i:s') . '</em></p>' .
            '</div>';

        $payload = [
            'api_key'     => SMTP2GO_API_KEY,
            'to'          => [$adminTo],
            'sender'      => SMTP2GO_SENDER_EMAIL,
            'sender_name' => SMTP2GO_SENDER_NAME ?: "D'Marsians Taekwondo Gym",
            'subject'     => $subject,
            'text_body'   => $textBody,
            'html_body'   => $htmlBody
        ];
        if (defined('ADMIN_BCC_EMAIL') && ADMIN_BCC_EMAIL && ADMIN_BCC_EMAIL !== $adminTo) {
            $payload['bcc'] = [ADMIN_BCC_EMAIL];
        }
        sendEmailViaSMTP2GO($payload);
        echo json_encode(['status' => 'success', 'message' => 'Enrollment request submitted! Please wait for approval.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit request.']);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
} 