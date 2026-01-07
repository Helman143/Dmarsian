<?php
require_once __DIR__ . '/db_connect.php';

function getAdminAccountName($conn) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!empty($_SESSION['admin_username'])) {
        return $_SESSION['admin_username'];
    }
    if (!empty($_SESSION['username'])) {
        return $_SESSION['username'];
    }
    if (!empty($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    }
    if (!empty($_SESSION['user_id'])) {
        $uid = intval($_SESSION['user_id']);
        $stmt = $conn->prepare('SELECT username FROM admin_accounts WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $uid);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($res && $row = $res->fetch_assoc()) {
                    return $row['username'];
                }
            }
            $stmt->close();
        }
    }
    return 'unknown';
}
?>


