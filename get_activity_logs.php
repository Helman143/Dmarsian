<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Authentication check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'super_admin') {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

require_once 'db_connect.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM activity_log";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get paginated data
$sql = "SELECT * FROM activity_log ORDER BY datetime DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$html = "";
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($row['action_type']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['datetime']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['admin_account']) . "</td>";
        $html .= "<td>" . htmlspecialchars(str_replace('STD-', '', $row['student_id'])) . "</td>";
        $html .= "<td>" . nl2br(htmlspecialchars($row['details'])) . "</td>";
        $html .= "</tr>";
    }
} else {
    $html .= "<tr><td colspan='5'>No activity found.</td></tr>";
}

echo json_encode([
    'status' => 'success',
    'html' => $html,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_rows' => $total_rows
]);

$stmt->close();
$conn->close();
?>
