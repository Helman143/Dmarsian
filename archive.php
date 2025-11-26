<?php
require_once 'post_operations.php';

$conn = connectDB();
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

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
if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR post_date LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
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
    <title>ARCHIVE HISTORY | D'MARSIANS TAEKWONDO GYM</title>
    <link rel="stylesheet" href="Styles/webpage.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #111;
            background-image: 
                linear-gradient(45deg, rgba(0, 255, 0, 0.1) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(0, 255, 0, 0.1) 25%, transparent 25%);
            color: #fff;
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .archive-header { background: #17831b; padding: 1.5rem 0; text-align: center; }
        .archive-header h1 { font-size: 2.5rem; letter-spacing: 0.1em; color: #fff; margin: 0; font-family: 'Impact', 'Montserrat', Arial, sans-serif; }
        .archive-controls { display: flex; flex-wrap: wrap; justify-content: center; gap: 1.5rem; margin: 2rem 0 1.5rem; }
        .archive-controls select, .archive-controls input { padding: 0.5rem 1rem; border-radius: 6px; border: none; font-size: 1rem; background: #222; color: #fff; }
        .archive-controls input { width: 250px; }
        .archive-grid { margin: 0 auto; }
        .archive-card { background: #000; border: 2px solid #0f0; border-radius: 12px; box-shadow: 0 2px 12px #0008; width: 100%; max-width: 380px; overflow: hidden; display: flex; flex-direction: column; margin: 0 auto 1.5rem auto; }
        .archive-card img {
            width: 100%;
            height: auto;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            object-position: center top;
            background: #000;
            border-bottom: 2px solid #0f0;
            border-radius: 10px 10px 0 0;
        }
        .archive-card .card-content { padding: 1rem; flex: 1; display: flex; flex-direction: column; }
        .archive-card .card-title { font-weight: bold; color: #7fff00; font-size: 1.1rem; margin-bottom: 0.5rem; }
        .archive-card .card-badge { display: inline-block; background: #0f0; color: #222; font-size: 0.8rem; font-weight: bold; border-radius: 4px; padding: 2px 8px; margin-bottom: 0.5rem; }
        .archive-card .card-date { font-size: 0.85rem; color: #aaa; margin-bottom: 0.5rem; }
        .archive-card .card-desc { font-size: 0.97rem; color: #eee; }
        .load-more-btn { display: block; margin: 2rem auto 3rem; background: #0f0; color: #222; font-weight: bold; border: none; border-radius: 8px; padding: 0.7rem 2.5rem; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
        .load-more-btn:hover { background: #7fff00; }
        @media (max-width: 900px) { .archive-controls input { width: 90vw; max-width: 360px; } }
        .back-btn {
            position: absolute;
            top: 32px;
            left: 32px;
            background: #111;
            border: 2px solid #0f0;
            color: #0f0;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            cursor: pointer;
            z-index: 10;
            transition: background 0.2s, color 0.2s;
        }
        .back-btn:hover {
            background: #0f0;
            color: #111;
        }
        @media (max-width: 600px) {
            .back-btn { top: 12px; left: 12px; width: 40px; height: 40px; font-size: 1.2rem; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <a href="webpage.php" class="back-btn" title="Back to Home"><i class="fas fa-arrow-left"></i></a>
    <div class="archive-header" style="display: flex; align-items: center; justify-content: space-between; position: relative;">
        <div style="width:48px;"></div> <!-- Placeholder for back button space -->
        <h1 style="flex:1; text-align:center; margin:0;">ARCHIVE HISTORY</h1>
        <div style="display: flex; align-items: center; gap: 10px; margin-right: 24px;">
            <div style="text-align: right; line-height: 1; font-size: 0.95rem; font-family: 'Montserrat', Arial, sans-serif; color: #fff; font-weight: bold;">
                D'MARSIANS<br>TAEKWONDO<br>TEAM
            </div>
            <span style="display: flex; align-items: center; justify-content: center; background: #111; border: 2px solid #0f0; border-radius: 50%; width: 54px; height: 54px;">
                <img src="Picture/Logo2.png" alt="Logo" style="height: 38px; width: 38px; border-radius: 50%; object-fit: contain; background: transparent;">
            </span>
        </div>
    </div>
    <form class="archive-controls container" method="get">
        <label>Category:
            <select name="category" onchange="this.form.submit()">
                <option value="" <?= $category_filter == '' ? 'selected' : '' ?>>All</option>
                <option value="achievement" <?= $category_filter == 'achievement' ? 'selected' : '' ?>>Achievement</option>
                <option value="event" <?= $category_filter == 'event' ? 'selected' : '' ?>>Event</option>
            </select>
        </label>
        <label>Year:
            <select name="year" onchange="this.form.submit()">
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                    echo "<option value=\"$y\"".($year_filter == $y ? ' selected' : '').">$y</option>";
                }
                ?>
            </select>
        </label>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Title or Date">
        <button type="submit" style="display:none;">Filter</button>
    </form>
    <div class="archive-grid container">
        <div class="row g-3 justify-content-center">
            <?php if (empty($posts)): ?>
                <div style="color:#fff;text-align:center;width:100%;">No posts found.</div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-12 col-sm-6 col-md-4 d-flex">
                        <div class="archive-card w-100">
                            <img class="img-fluid w-100" src="<?= !empty($post['image_path']) ? htmlspecialchars($post['image_path']) : 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=No+Image' ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                            <div class="card-content">
                                <div class="card-title"><?= htmlspecialchars($post['title']) ?></div>
                                <div class="card-badge" style="<?= $post['category'] === 'event' ? 'background:#00eaff;color:#222;' : '' ?>">
                                    <?= $post['category'] === 'achievement_event' ? 'Achievement/Event' : ucfirst($post['category']) ?>
                                </div>
                                <div class="card-date">Posted on: <?= date('F j, Y', strtotime($post['post_date'])) ?></div>
                                <div class="card-desc"><?= htmlspecialchars($post['description']) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- <button class="load-more-btn">LOAD MORE</button> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 