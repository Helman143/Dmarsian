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
            // Log login attempt (without password)
            error_log("Admin login attempt - Username/Email: " . $username);
            
            $stmt = $conn->prepare("SELECT id, username, password, email FROM admin_accounts WHERE username = ? OR email = ?");
            if (!$stmt) {
                $error_msg = "Prepare failed: " . $conn->error;
                error_log("Admin login error: " . $error_msg);
                throw new Exception($error_msg);
            }
            $stmt->bind_param("ss", $username, $username);
            if (!$stmt->execute()) {
                $error_msg = "Execute failed: " . $stmt->error;
                error_log("Admin login error: " . $error_msg);
                throw new Exception($error_msg);
            }
            $result = $stmt->get_result();

            // Log number of rows found
            error_log("Admin login query found " . $result->num_rows . " matching account(s)");

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                error_log("Admin account found - ID: " . $admin['id'] . ", Username: " . $admin['username'] . ", Email: " . $admin['email']);
                
                // Check if password is hashed (starts with $2y$ or $2a$ or $2b$)
                $password_hash = $admin['password'];
                $is_hashed = (strpos($password_hash, '$2y$') === 0 || strpos($password_hash, '$2a$') === 0 || strpos($password_hash, '$2b$') === 0);
                
                $password_valid = false;
                if ($is_hashed) {
                    // Use password_verify for hashed passwords
                    $password_valid = password_verify($password, $password_hash);
                    error_log("Password verification (hashed): " . ($password_valid ? "SUCCESS" : "FAILED"));
                } else {
                    // Plain text password comparison
                    $password_valid = ($password === $password_hash);
                    error_log("Password verification (plain text): " . ($password_valid ? "SUCCESS" : "FAILED"));
                }
                
                if ($password_valid) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $admin['username'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['user_name'] = $admin['username'];
                    $_SESSION['user_type'] = 'super_admin';

                    $stmt->close();
                    error_log("Admin login SUCCESS - Redirecting to dashboard");
                    
                    header("Location: admin_dashboard.php");
                    ob_end_flush();
                    exit();
                } else {
                    error_log("Admin login FAILED - Password mismatch");
                }
            } else if ($result->num_rows === 0) {
                error_log("Admin login FAILED - No account found with username/email: " . $username);
            } else {
                error_log("Admin login ERROR - Multiple accounts found (should not happen): " . $result->num_rows);
            }
        } catch (Exception $e) {
            error_log("Login error (admin): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
        error_log("Admin login FAILED - Redirecting to login page with error");
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