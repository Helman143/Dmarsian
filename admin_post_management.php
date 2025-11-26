<?php
require_once 'post_operations.php';

// Fetch posts for display
$conn = connectDB();
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$sql = "SELECT * FROM posts WHERE YEAR(post_date) = ? AND status = 'active'";
$params = [$year_filter];
$types = "i";

if ($category_filter) {
    if ($category_filter === 'achievement') {
        $sql .= " AND (category = 'achievement' OR category = 'achievement_event')";
    } elseif ($category_filter === 'event') {
        $sql .= " AND (category = 'event' OR category = 'achievement_event')";
    } else {
        $sql .= " AND category = ?";
        $params[] = $category_filter;
        $types .= "s";
    }
}

$sql .= " ORDER BY post_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Management | D'MARSIANS TAEKWONDO SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="Styles/admin_post_management.css">
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
        <?php $active = 'posts'; include 'partials/admin_sidebar.php'; ?>

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
            <div class="header">
                <h1>POST MANAGEMENT</h1>
                <button class="add-post-btn" onclick="openModal()"><i class="fas fa-edit"></i></button>
            </div>
            <div class="filters">
                <div class="filter-dropdown">
                    <select id="year-filter" onchange="filterPosts()">
                        <option value="2025" <?php echo $year_filter == 2025 ? 'selected' : ''; ?>>2025</option>
                        <option value="2024" <?php echo $year_filter == 2024 ? 'selected' : ''; ?>>2024</option>
                        <option value="2023" <?php echo $year_filter == 2023 ? 'selected' : ''; ?>>2023</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="filter-dropdown">
                    <select id="category-filter" onchange="filterPosts()">
                        <option value="" <?php echo $category_filter == '' ? 'selected' : ''; ?>>ALL CATEGORIES</option>
                        <option value="achievement" <?php echo $category_filter == 'achievement' ? 'selected' : ''; ?>>Achievement</option>
                        <option value="event" <?php echo $category_filter == 'event' ? 'selected' : ''; ?>>Event</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <div class="post-grid" id="post-grid">
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-bullhorn"></i>
                        <p>No posts found for the selected filters.</p>
                        <button onclick="openModal()" class="add-first-post-btn">Create Your First Post</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card" data-post-id="<?php echo $post['id']; ?>">
                            <div class="post-image" style="background-image: url('<?php echo !empty($post['image_path']) ? $post['image_path'] : 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=No+Image'; ?>');">
                                <span class="post-tag <?php echo $post['category']; ?>"><?php echo $post['category'] === 'achievement_event' ? 'Achievement/Event' : ucfirst($post['category']); ?></span>
                                <div class="post-actions">
                                    <button class="edit-post-btn" onclick="editPost(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="archive-post-btn" onclick="archivePost(<?php echo $post['id']; ?>)">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="post-content">
                                <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                <p class="post-date">Posted on: <?php echo date('F j, Y', strtotime($post['post_date'])); ?></p>
                                <p class="post-description"><?php echo htmlspecialchars($post['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add/Edit Post Modal -->
        <div id="post-modal" class="modal-overlay" role="dialog" aria-modal="true">
            <div class="modal-content" tabindex="-1">
                <div class="modal-header">
                    <h2 id="modal-title">Create New Post</h2>
                    <button class="close-btn" onclick="closeModal()">&times;</button>
                </div>
                <form id="post-form" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="post-id" name="post_id">
                        <input type="hidden" id="action-type" name="action" value="create">
                        
                        <div class="image-uploader" id="image-uploader">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Upload Image</p>
                            <span>Drag & Drop or Click to upload</span>
                            <input type="file" id="image-upload" name="image" accept="image/*" hidden>
                            <div id="image-preview" class="image-preview" style="display: none;">
                                <img id="preview-img" src="" alt="Preview">
                                <button type="button" id="remove-image" onclick="removeImage()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-fields">
                            <div class="form-group">
                                <label for="post-title">Title</label>
                                <input type="text" id="post-title" name="title" placeholder="Enter post title" required>
                            </div>
                            <div class="form-group">
                                <label for="post-date">Date</label>
                                <input type="date" id="post-date" name="post_date" required>
                            </div>
                            <div class="form-group">
                                <label for="post-category">Category</label>
                                <select id="post-category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="achievement">Achievement</option>
                                    <option value="event">Event</option>
                                    <option value="achievement_event">Achievement/Event</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="post-description">Description/Details</label>
                                <textarea id="post-description" name="description" rows="5" maxlength="200" placeholder="Enter post description" required></textarea>
                                <span id="char-count">0/200</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-btn update-btn" onclick="updatePost()" style="display: none;">Save Changes</button>
                        <button type="submit" class="modal-btn post-btn">POST</button>
                        <button type="button" class="modal-btn archive-btn" onclick="archiveCurrentPost()" style="display: none;">ARCHIVE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="Scripts/admin_post_management.js"></script>
    <!-- Bootstrap 5 JS bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
    // Mobile-safe dropdown: avoid touch+click double-trigger
    (function(){
        const dropdown = document.querySelector('.sidebar .dropdown');
        const toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;
        if(!dropdown || !toggle) return;

        function open(){ dropdown.classList.add('open'); }
        function close(){ dropdown.classList.remove('open'); }

        let touched = false;
        toggle.addEventListener('click', function(e){
            if (touched) { e.preventDefault(); touched = false; return; }
            e.preventDefault();
            dropdown.classList.toggle('open');
        });
        toggle.addEventListener('touchstart', function(e){
            e.preventDefault();
            touched = true;
            open();
            setTimeout(function(){ touched = false; }, 300);
        }, {passive:false});

        dropdown.addEventListener('mouseenter', open);
        dropdown.addEventListener('mouseleave', close);
        document.addEventListener('click', function(e){ if(!dropdown.contains(e.target)) close(); });
    })();
    </script>
</body>
</html> 