<?php
require_once 'db_connect.php';
require_once 'auth_helpers.php';

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
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/posts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $upload_path;
        }
    }
    
    $sql = "INSERT INTO posts (title, description, image_path, category, post_date) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $title, $description, $image_path, $category, $post_date);
    
    if (mysqli_stmt_execute($stmt)) {
        // Log activity
        $admin_account = getAdminAccountName($conn);
        $action_type = 'Post Create';
        $student_id = '';
        $details = 'Created post: ' . $title;
        $log_stmt = $conn->prepare("INSERT INTO activity_log (action_type, datetime, admin_account, student_id, details) VALUES (?, NOW(), ?, ?, ?)");
        $log_stmt->bind_param('ssss', $action_type, $admin_account, $student_id, $details);
        $log_stmt->execute();
        $log_stmt->close();
        echo json_encode(['success' => true, 'message' => 'Post created successfully']);
    } else {
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
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/posts/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $upload_path;
        }
    }
    
    if ($image_path) {
        $sql = "UPDATE posts SET title=?, description=?, image_path=?, category=?, post_date=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $image_path, $category, $post_date, $id);
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
?> 