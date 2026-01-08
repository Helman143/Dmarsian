<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Check if user is logged in and is super admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'super_admin') {
    header("Location: index.php");
    exit();
}
require_once 'db_connect.php';

// Get user's name from session
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

// Assume Super Admin has id=1
$admin_id = 1;

// Initialize variables
$email = '';
$username = '';
$password = '';
$success = '';
$error = '';

// Fetch admin info
$stmt = $conn->prepare('SELECT email, username, password FROM admin_accounts WHERE id = ?');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->bind_result($email, $username, $hashed_password);
$admin_exists = false;
if ($stmt->fetch()) {
    $admin_exists = true;
} else {
    $error = 'Admin account not found.';
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email']);
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password'];
    $update_password = false;

    // Validate input (basic)
    if (empty($new_email) || empty($new_username) || (!$admin_exists && empty($new_password))) {
        $error = 'Email, Username, and Password cannot be empty.';
    } else {
        if ($admin_exists) {
            // Check if password changed
            if (!empty($new_password) && !password_verify($new_password, $hashed_password)) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password = true;
            } else {
                $hashed_new_password = $hashed_password;
            }
            // Update DB
            $stmt = $conn->prepare('UPDATE admin_accounts SET email=?, username=?, password=? WHERE id=?');
            $stmt->bind_param('sssi', $new_email, $new_username, $hashed_new_password, $admin_id);
            if ($stmt->execute()) {
                $success = 'Profile updated successfully!';
                $email = $new_email;
                $username = $new_username;
                $hashed_password = $hashed_new_password;
            } else {
                $error = 'Failed to update profile.';
            }
            $stmt->close();
        } else {
            // Create new admin account
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO admin_accounts (id, email, username, password) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $admin_id, $new_email, $new_username, $hashed_new_password);
            if ($stmt->execute()) {
                $success = 'Admin account created successfully!';
                $email = $new_email;
                $username = $new_username;
                $hashed_password = $hashed_new_password;
                $admin_exists = true;
                $error = '';
            } else {
                $error = 'Failed to create admin account.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D'MARSIANS Taekwondo System - Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjIS5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Match style include order used by other admin pages -->
    <link rel="stylesheet" href="Styles/admin_dashboard.css">
    <link rel="stylesheet" href="Styles/admin_profile.css">
    <link rel="stylesheet" href="Styles/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Source+Serif+Pro:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Styles/typography.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Sidebar -->
        <?php $active = 'profile'; include 'partials/admin_sidebar.php'; ?>
        <!-- Mobile topbar with toggle button -->
        <div class="mobile-topbar d-flex d-md-none align-items-center justify-content-between p-2">
            <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <span class="text-success fw-bold">D'MARSIANS</span>
            <span></span>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="welcome-header">
                <h1>Admin Profile</h1>
            </div>
            <div class="profile-container row g-4 justify-content-center">
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="profile-image-card">
                        <div class="profile-image-wrapper">
                            <img src="1.png" alt="Profile image" class="profile-image">
                        </div>
                        <div class="profile-badge">
                            <i class="fas fa-user-shield"></i>
                            <span>Super Admin</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-7 col-lg-6">
                    <div class="profile-card">
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <form id="profileForm" method="post" autocomplete="off" class="needs-validation" novalidate>
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control profile-input" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" <?php echo $admin_exists ? 'disabled' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Username
                                </label>
                                <input type="text" class="form-control profile-input" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" <?php echo $admin_exists ? 'disabled' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control profile-input" id="password" name="password" value="" placeholder="Enter new password<?php echo $admin_exists ? ' or leave blank' : ''; ?>" <?php echo $admin_exists ? 'disabled' : ''; ?> <?php echo $admin_exists ? '' : 'required'; ?>>
                            </div>
                            <div class="profile-actions">
                                <?php if ($admin_exists): ?>
                                    <button type="button" id="editProfileBtn" class="btn btn-profile-edit">
                                        <i class="fas fa-pen me-2"></i>Edit Profile
                                    </button>
                                    <button type="submit" id="saveProfileBtn" class="btn btn-profile-save" style="display:none;">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                    <button type="button" id="cancelEditBtn" class="btn btn-profile-cancel" style="display:none;">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                <?php else: ?>
                                    <button type="submit" id="createProfileBtn" class="btn btn-profile-create">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="Scripts/admin_profile.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    (function(){
        const dropdown = document.querySelector('.sidebar .dropdown');
        const toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        if(!dropdown || !toggle) return;

        function open(){ dropdown.classList.add('open'); }
        function close(){ dropdown.classList.remove('open'); }

        toggle.addEventListener('click', function(e){
            e.preventDefault();
            dropdown.classList.toggle('open');
        });
        toggle.addEventListener('touchstart', function(e){ e.preventDefault(); open(); }, {passive:false});
        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
        document.addEventListener('click', function(e){ if(!dropdown.contains(e.target)) close(); });
    })();
    </script>
</body>
</html> 