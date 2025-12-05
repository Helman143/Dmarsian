<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Set execution time limit to prevent timeouts
set_time_limit(30);
ini_set('max_execution_time', 30);

ob_start();
session_start();
require_once 'db_connect.php';

// Check database connection before proceeding
if (!isset($conn) || $conn === false || (isset($conn->connect_error) && $conn->connect_error)) {
    error_log("Database connection failed in login_process.php");
    ob_clean();
    http_response_code(503);
    header("Location: index.php?error=db_connection_failed");
    ob_end_flush();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';

    if ($login_type === 'admin') {
        // Super Admin login - check admin_accounts table
        try {
            $stmt = $conn->prepare("SELECT id, username, password, email FROM admin_accounts WHERE username = ? OR email = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ss", $username, $username);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                // Use password_verify for hashed passwords
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $admin['username'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['user_name'] = $admin['username'];
                    $_SESSION['user_type'] = 'super_admin';

                    $stmt->close();
                    $conn->close();

                    header("Location: admin_dashboard.php");
                    ob_end_flush();
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Login error (admin): " . $e->getMessage());
            if (isset($stmt)) {
                $stmt->close();
            }
            ob_clean();
            header("Location: admin_login.php?error=1");
            ob_end_flush();
            exit();
        }
        // If login fails
        if (isset($stmt)) {
            $stmt->close();
        }
        ob_clean();
        header("Location: admin_login.php?error=1");
        ob_end_flush();
        exit();
    } else {
        // Regular Admin login - check users table with user_type = 'admin'
        try {
            $stmt = $conn->prepare("SELECT id, username, password, email, user_type FROM users WHERE (username = ? OR email = ?) AND user_type = 'admin'");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ss", $username, $username);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Passwords are stored in plain text for users
                if ($password === $user['password']) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['user_type'] = $user['user_type'];

                    $stmt->close();
                    $conn->close();
                    header("Location: dashboard.php");
                    ob_end_flush();
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Login error (user): " . $e->getMessage());
            if (isset($stmt)) {
                $stmt->close();
            }
            ob_clean();
            header("Location: index.php?error=1");
            ob_end_flush();
            exit();
        }
        // If login fails
        if (isset($stmt)) {
            $stmt->close();
        }
        ob_clean();
        header("Location: index.php?error=1");
        ob_end_flush();
        exit();
    }
} else {
    header("Location: index.php");
    ob_end_flush();
    exit();
}
?> 