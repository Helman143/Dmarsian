<?php
require_once 'db_connect.php';
require_once 'auth_helpers.php';
require_once 'spaces_helper.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only set JSON headers when handling AJAX requests, not when file is included
    // Prevent caching of API responses - ensure no browser or proxy caching
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json');
    // Add ETag prevention
    header('ETag: ' . md5(uniqid()));
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createPost();
            break;
        case 'update':
            updatePost();
            break;
        case 'archive':
            archivePost();
            break;
        case 'fetch':
            fetchPosts();
            break;
        case 'fetch_single':
            fetchSinglePost();
            break;
        case 'delete':
            deletePost();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function createPost() {
    $conn = connectDB();
    
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $post_date = mysqli_real_escape_string($conn, $_POST['post_date']);
    
    // Handle image upload
    $image_path = null;
    $upload_error = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $file_name = uniqid() . '.' . $file_extension;
            
            // Upload to Spaces (or local fallback)
            $uploadResult = uploadImageToSpaces($_FILES['image']['tmp_name'], $file_name, 'posts');
            
            if ($uploadResult['success']) {
                $image_path = $uploadResult['path'];
            } else {
                $upload_error = $uploadResult['error'] ?? 'Failed to upload image';
            }
        } else {
            $upload_error = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions);
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // File upload error (but not "no file" error)
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $upload_error = $upload_errors[$_FILES['image']['error']] ?? 'Unknown upload error';
    }
    
    // If there was an upload error and an image was attempted, fail the operation
    if (!empty($upload_error) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $upload_error]);
        mysqli_close($conn);
        return;
    }
    
    // Use empty string for image_path if no image was uploaded (database will store as NULL if column allows)
    $image_path_value = $image_path !== null ? $image_path : '';
    
    $sql = "INSERT INTO posts (title, description, image_path, category, post_date, status) 
            VALUES (?, ?, ?, ?, ?, 'active')";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $title, $description, $image_path_value, $category, $post_date);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log activity
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Post Create';
        $student_id = '';
        $details = 'Created post: ' . $title . ($image_path ? ' (with image: ' . $image_path . ')' : ' (no image)');
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();
        
        $message = 'Post created successfully';
        if ($image_path) {
            $message .= ' with image';
        }
        echo json_encode(['success' => true, 'message' => $message, 'image_path' => $image_path]);
    } else {
        // If database insert fails, delete the uploaded file
        if ($image_path !== null) {
            deleteImageFromSpaces($image_path);
        }
        echo json_encode(['success' => false, 'message' => 'Error creating post: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

function updatePost() {
    $conn = connectDB();
    
    $id = (int)$_POST['id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $post_date = mysqli_real_escape_string($conn, $_POST['post_date']);
    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';
    
    // Get old image path for deletion
    $old_image_path = null;
    $getOldImage = mysqli_prepare($conn, "SELECT image_path FROM posts WHERE id = ?");
    mysqli_stmt_bind_param($getOldImage, "i", $id);
    mysqli_stmt_execute($getOldImage);
    $oldImageResult = mysqli_stmt_get_result($getOldImage);
    if ($oldImageRow = mysqli_fetch_assoc($oldImageResult)) {
        $old_image_path = $oldImageRow['image_path'];
    }
    mysqli_stmt_close($getOldImage);
    
    // Handle image upload or removal
    $image_path = null;
    $update_image = false;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // New image uploaded
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_extension;
        
        // Upload to Spaces (or local fallback)
        $uploadResult = uploadImageToSpaces($_FILES['image']['tmp_name'], $file_name, 'posts');
        
        if ($uploadResult['success']) {
            $image_path = $uploadResult['path'];
            $update_image = true;
            
            // Delete old image if exists
            if ($old_image_path) {
                deleteImageFromSpaces($old_image_path);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image: ' . ($uploadResult['error'] ?? 'Unknown error')]);
            mysqli_close($conn);
            return;
        }
    } elseif ($remove_image) {
        // Image should be removed
        if ($old_image_path) {
            deleteImageFromSpaces($old_image_path);
        }
        $image_path = null;
        $update_image = true;
    }
    
    if ($update_image) {
        // Handle NULL image_path for removal
        if ($image_path === null) {
            $sql = "UPDATE posts SET title=?, description=?, image_path=NULL, category=?, post_date=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $category, $post_date, $id);
        } else {
            $sql = "UPDATE posts SET title=?, description=?, image_path=?, category=?, post_date=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $image_path, $category, $post_date, $id);
        }
    } else {
        $sql = "UPDATE posts SET title=?, description=?, category=?, post_date=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $category, $post_date, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Log activity
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Post Update';
        $student_id = '';
        $details = 'Updated post ID: ' . $id . ' (' . $title . ')';
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();
        echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating post: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

function archivePost() {
    $conn = connectDB();
    
    $id = (int)$_POST['id'];
    
    // First, verify the post exists and get image path for deletion
    $check_sql = "SELECT id, status, image_path, category FROM posts WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $post = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    if (!$post) {
        mysqli_close($conn);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        return;
    }
    
    // Check if post is already archived
    if ($post['status'] === 'archived') {
        mysqli_close($conn);
        // Treat as success since the desired state is already achieved
        echo json_encode([
            'success' => true, 
            'message' => 'Post is already archived',
            'already_archived' => true
        ]);
        return;
    }
    
    // Get image path before archiving (for deletion)
    $image_path = $post['image_path'];
    $category = $post['category'];
    
    // Update the post status - status is ENUM('active','archived'), so no NULL check needed
    $sql = "UPDATE posts SET status='archived' WHERE id=? AND status='active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Check if any rows were actually affected
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        
        if ($affected_rows > 0) {
            // Delete image file if it exists
            if (!empty($image_path) && trim($image_path) !== '') {
                deleteImageFromSpaces($image_path);
            }
            
            // Explicitly verify the status was changed to 'archived'
            $verify_sql = "SELECT status FROM posts WHERE id = ?";
            $verify_stmt = mysqli_prepare($conn, $verify_sql);
            mysqli_stmt_bind_param($verify_stmt, "i", $id);
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            $updated_post = mysqli_fetch_assoc($verify_result);
            mysqli_stmt_close($verify_stmt);
            
            // Verify status is actually 'archived'
            if (!$updated_post || $updated_post['status'] !== 'archived') {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                error_log("Archive verification failed: Post ID {$id} status is '{$updated_post['status']}' instead of 'archived'");
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to archive post. Status verification failed.',
                    'debug' => ['post_id' => $id, 'actual_status' => $updated_post['status'] ?? 'unknown']
                ]);
                return;
            }
            
            // Log activity
            $admin_account = getAdminAccountName($conn);
            $action_type = 'Post Archive';
            $student_id = '';
            $details = 'Archived post ID: ' . $id;
            $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
            $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
            $log_stmt->execute();
            $log_stmt->close();
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Post archived successfully',
                'category' => $category,
                'post_id' => $id
            ]);
        } else {
            // No rows affected - post might have been archived by another request
            // Verify current status
            $verify_sql = "SELECT status FROM posts WHERE id = ?";
            $verify_stmt = mysqli_prepare($conn, $verify_sql);
            mysqli_stmt_bind_param($verify_stmt, "i", $id);
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            $current_post = mysqli_fetch_assoc($verify_result);
            mysqli_stmt_close($verify_stmt);
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            // If already archived, treat as success
            if ($current_post && $current_post['status'] === 'archived') {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Post is already archived',
                    'already_archived' => true,
                    'category' => $category,
                    'post_id' => $id
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to archive post. Please try again.',
                    'debug' => ['post_id' => $id, 'current_status' => $current_post['status'] ?? 'unknown']
                ]);
            }
        }
    } else {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo json_encode(['success' => false, 'message' => 'Error archiving post: ' . $error]);
    }
}

function fetchPosts() {
    $conn = connectDB();
    
    $year_filter = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
    $category_filter = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';
    
    // Status is ENUM('active','archived'), so it cannot be NULL - only check for 'active'
    $sql = "SELECT * FROM posts WHERE YEAR(post_date) = ? AND status = 'active'";
    $params = [$year_filter];
    $types = "i";
    
    if ($category_filter) {
        $sql .= " AND category = ?";
        $params[] = $category_filter;
        $types .= "s";
    }
    
    $sql .= " ORDER BY post_date DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'image_path' => $row['image_path'] ?: 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=No+Image',
            'category' => $row['category'],
            'post_date' => $row['post_date'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'posts' => $posts]);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Function to get a single post by ID
function getPostById($id) {
    $conn = connectDB();
    
    $sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $post;
}

function fetchSinglePost() {
    $conn = connectDB();
    
    $id = (int)$_POST['id'];
    
    $sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
    
    if ($post) {
        echo json_encode(['success' => true, 'post' => $post]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

function deletePost() {
    $conn = connectDB();
    
    $id = (int)$_POST['id'];
    
    // First, get post data including image path and category before deletion
    $get_sql = "SELECT id, image_path, category, title FROM posts WHERE id = ?";
    $get_stmt = mysqli_prepare($conn, $get_sql);
    mysqli_stmt_bind_param($get_stmt, "i", $id);
    mysqli_stmt_execute($get_stmt);
    $get_result = mysqli_stmt_get_result($get_stmt);
    $post = mysqli_fetch_assoc($get_result);
    mysqli_stmt_close($get_stmt);
    
    if (!$post) {
        mysqli_close($conn);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        return;
    }
    
    $image_path = $post['image_path'];
    $category = $post['category'];
    $title = $post['title'];
    
    // Delete the post from database
    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        
        if ($affected_rows > 0) {
            // Delete image file if it exists
            if (!empty($image_path) && trim($image_path) !== '') {
                deleteImageFromSpaces($image_path);
            }
            
            // Explicitly verify the post was deleted
            $verify_sql = "SELECT id FROM posts WHERE id = ?";
            $verify_stmt = mysqli_prepare($conn, $verify_sql);
            mysqli_stmt_bind_param($verify_stmt, "i", $id);
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            $still_exists = mysqli_fetch_assoc($verify_result);
            mysqli_stmt_close($verify_stmt);
            
            if ($still_exists) {
                // Post still exists - deletion failed
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                error_log("Delete verification failed: Post ID {$id} still exists after DELETE");
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to delete post. Post still exists in database.'
                ]);
                return;
            }
            
            // Log activity
            $admin_account = getAdminAccountName($conn);
            $action_type = 'Post Delete';
            $student_id = '';
            $details = 'Deleted post ID: ' . $id . ' (' . $title . ')';
            $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
            $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
            $log_stmt->execute();
            $log_stmt->close();
            
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Post deleted successfully',
                'category' => $category,
                'post_id' => $id
            ]);
        } else {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            echo json_encode(['success' => false, 'message' => 'Failed to delete post. Post may not exist.']);
        }
    } else {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo json_encode(['success' => false, 'message' => 'Error deleting post: ' . $error]);
    }
}
?> 