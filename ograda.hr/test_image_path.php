<?php
// This is just a test file to verify the image path exists
$image_path = 'assets/front/assets/images/ograde/celicneograde.jpg';
$full_path = __DIR__ . '/' . $image_path;

echo "Testing image path: $image_path<br>";
echo "Full path: $full_path<br>";
echo "File exists: " . (file_exists($full_path) ? 'Yes' : 'No') . "<br>";

// Check for case sensitivity issues
$dir = dirname($full_path);
echo "Directory exists: " . (is_dir($dir) ? 'Yes' : 'No') . "<br>";

if (is_dir($dir)) {
    echo "Files in directory:<br>";
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        echo "- $file<br>";
    }
} 