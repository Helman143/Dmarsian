<?php
session_start();
require_once 'db_connect.php';

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
    <title>Admin Profile</title>
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
            <div class="container py-3 py-md-4">
                <div class="row g-4 justify-content-center align-items-start">
                    <div class="col-12 col-sm-10 col-md-5 col-lg-4">
                        <div class="profile-image card bg-transparent border-0 p-3 p-md-4">
                            <div class="ratio ratio-1x1 rounded-3 overflow-hidden border border-success">
                                <img src="1.png" alt="Profile image" class="w-100 h-100" style="object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-10 col-md-7 col-lg-6">
                        <div class="profile-card card bg-transparent border-0 p-3 p-md-4">
                            <h1 class="h3 text-white mb-3">Profile</h1>
                            <h2 class="h5 text-success mb-3"><i class="fas fa-user-shield me-2"></i>Super Admin</h2>
                            <?php if ($success): ?>
                                <div class="alert alert-success py-2" role="alert"><?php echo $success; ?></div>
                            <?php endif; ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger py-2" role="alert"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <form id="profileForm" method="post" autocomplete="off" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" <?php echo $admin_exists ? 'disabled' : ''; ?> required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" <?php echo $admin_exists ? 'disabled' : ''; ?> required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" value="" placeholder="Enter new password<?php echo $admin_exists ? ' or leave blank' : ''; ?>" <?php echo $admin_exists ? 'disabled' : ''; ?> <?php echo $admin_exists ? '' : 'required'; ?>>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if ($admin_exists): ?>
                                        <button type="button" id="editProfileBtn" class="btn btn-success"><i class="fas fa-pen"></i> Edit Profile</button>
                                        <button type="submit" id="saveProfileBtn" class="btn btn-primary" style="display:none;"><i class="fas fa-save"></i> Save</button>
                                        <button type="button" id="cancelEditBtn" class="btn btn-outline-light" style="display:none;"><i class="fas fa-times"></i> Cancel</button>
                                    <?php else: ?>
                                        <button type="submit" id="createProfileBtn" class="btn btn-success"><i class="fas fa-user-plus"></i> Create Account</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
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