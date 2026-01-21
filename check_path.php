<?php
$files = glob("admin/uploads/posts/*");
echo "Checking admin/uploads/posts/: <br>";
print_r($files);
echo "<br><br>Checking uploads/posts/: <br>";
print_r(glob("uploads/posts/*"));
?>

