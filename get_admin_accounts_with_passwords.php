<?php
require_once 'db_connect.php';

// This file is specifically for super admin to view admin accounts with passwords
// In a real application, you would add proper authentication checks here

$conn = connectDB();
$sql = "SELECT id, email, username, password FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td class='password-cell'>";
        echo "<span class='password-masked'>[Click to Show Password]</span>";
        echo "<span class='password-actual' style='display:none;'>" . htmlspecialchars($row['password']) . "</span>";
        echo "<button type='button' class='toggle-password-btn' title='Show/Hide Password'><i class='fas fa-eye'></i></button>";
        echo "<button type='button' class='reset-password-btn' data-user-id='" . $row['id'] . "' title='Reset Password'><i class='fas fa-key'></i></button>";
        echo "</td>";
        echo '<td>';
        echo '<button type="button" class="action-btn edit-admin" data-id="' . $row['id'] . '" data-email="' . htmlspecialchars($row['email']) . '" data-username="' . htmlspecialchars($row['username']) . '" data-password="' . htmlspecialchars($row['password']) . '"><i class="fas fa-edit"></i></button>';
        echo '<button type="button" class="action-btn delete-admin" data-id="' . $row['id'] . '"><i class="fas fa-trash-alt"></i></button>';
        echo '</td>';
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan=\"4\">No admin accounts found.</td></tr>";
}

$conn->close();
?> 