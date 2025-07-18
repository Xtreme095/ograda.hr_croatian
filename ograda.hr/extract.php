<?php
// Increase memory limit and execution time for large zip files
ini_set('memory_limit', '1024M');
set_time_limit(0);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting unzip process...<br>";
flush();

// Check if zip file exists
if (!file_exists('perfex.zip')) {
    echo "Error: perfex.zip file not found!<br>";
    exit;
}

echo "Zip file found. Size: " . round(filesize('perfex.zip')/1024/1024, 2) . " MB<br>";
flush();

$zip = new ZipArchive;
$res = $zip->open('perfex.zip');

if ($res === TRUE) {
    echo "Zip file opened successfully. Starting extraction...<br>";
    echo "This may take several minutes for large files. Please do not close this page.<br>";
    flush();
    
    try {
        // Extract to the current directory
        $zip->extractTo('.');
        $zip->close();
        echo "Extraction complete.<br>";
        
        // Check if important directories exist
        if (is_dir('application') && is_dir('assets')) {
            echo "Critical folders verified.<br>";
        } else {
            echo "Warning: Some critical folders may not have extracted properly.<br>";
        }
        
        echo "Removing zip file to save space...<br>";
        if (unlink('perfex.zip')) {
            echo "Zip file removed successfully.<br>";
        } else {
            echo "Failed to remove zip file. You may need to delete it manually.<br>";
        }
        
        echo "<h2>Installation Complete!</h2>";
        echo "<p>You can now <a href='/'>visit your website</a>.</p>";
        echo "<p>Important: Make sure to update your database configuration in application/config/database.php</p>";
    } catch (Exception $e) {
        echo "Error during extraction: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Failed to open zip file. Error code: " . $res . "<br>";
    echo "Common error codes:<br>";
    echo "- 19: PHP has no permission to write to the directory<br>";
    echo "- 18: Zip file is corrupt or invalid<br>";
    echo "- 10: Temporary file couldn't be created<br>";
}
?>
