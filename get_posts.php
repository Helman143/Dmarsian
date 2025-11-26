<?php
require_once 'db_connect.php'; // Use your actual DB connection file

$conn = connectDB();
$category = isset($_GET['category']) ? $_GET['category'] : '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$sql = "SELECT * FROM posts WHERE YEAR(post_date) = ? AND status = 'active'";
$params = [$year];
$types = "i";

if ($category) {
    if ($category === 'achievement') {
        $sql .= " AND (category = 'achievement' OR category = 'achievement_event')";
    } elseif ($category === 'event') {
        $sql .= " AND (category = 'event' OR category = 'achievement_event')";
    } else {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
}

$sql .= " ORDER BY post_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($posts);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?> 