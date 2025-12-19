<?php
require_once 'db_connect.php';
require_once 'auth_helpers.php';
require_once 'spaces_helper.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    $sql = "UPDATE posts SET status='archived' WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log activity
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Post Archive';
        $student_id = '';
        $details = 'Archived post ID: ' . $id;
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();
        echo json_encode(['success' => true, 'message' => 'Post archived successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error archiving post: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

function fetchPosts() {
    $conn = connectDB();
    
    $year_filter = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
    $category_filter = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';
    
    $sql = "SELECT * FROM posts WHERE YEAR(post_date) = ? AND (status = 'active' OR status IS NULL)";
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
?> 