<?php
/**
 * Quick script to generate password hash
 * This will show you the hashed password for SQL insertion
 */
$password = 'YAMY@M143';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<!DOCTYPE html><html><head><title>Password Hash Generator</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0; }
    .hash { background: #f8f9fa; padding: 15px; border-radius: 3px; font-family: monospace; word-break: break-all; margin: 10px 0; }
    .info { background: #e7f3ff; padding: 15px; border-radius: 3px; margin: 15px 0; }
    .success { color: #28a745; font-weight: bold; }
    h2 { color: #333; }
</style></head><body>";

echo "<div class='box'>";
echo "<h2>Password Hash Generated</h2>";
echo "<div class='info'>";
echo "<strong>Original Password:</strong> " . htmlspecialchars($password) . "<br>";
echo "<strong>Generated Hash:</strong>";
echo "</div>";
echo "<div class='hash'>" . htmlspecialchars($hash) . "</div>";

echo "<div class='info'>";
echo "<h3>SQL Insert Statement:</h3>";
echo "<div class='hash'>";
echo "INSERT INTO admin_accounts (email, username, password) VALUES (<br>";
echo "&nbsp;&nbsp;'helmandacuma5@gmail.com',<br>";
echo "&nbsp;&nbsp;'helmandacuma5',<br>";
echo "&nbsp;&nbsp;'" . htmlspecialchars($hash) . "'<br>";
echo ");";
echo "</div>";
echo "</div>";

echo "<div class='info'>";
echo "<p class='success'>✓ Copy the hash above and use it in your SQL query</p>";
echo "<p><strong>Note:</strong> Each time you run this script, a different hash will be generated (this is normal and expected). All hashes will work with the same password.</p>";
echo "</div>";

echo "</div></body></html>";
?>











