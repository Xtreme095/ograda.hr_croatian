<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $db_hostname = $_POST['hostname'];
    $db_username = $_POST['username'];
    $db_password = $_POST['password'];
    $db_name = $_POST['database'];
    
    $config_file_path = 'application/config/database.php';
    
    // Check if config file exists
    if (!file_exists($config_file_path)) {
        echo "<div style='color:red;'>Error: Database configuration file not found at {$config_file_path}</div>";
        goto display_form;
    }
    
    // Read the current config file
    $config_contents = file_get_contents($config_file_path);
    
    // Replace database settings
    $config_contents = preg_replace("/'hostname' => '.*?'/", "'hostname' => '$db_hostname'", $config_contents);
    $config_contents = preg_replace("/'username' => '.*?'/", "'username' => '$db_username'", $config_contents);
    $config_contents = preg_replace("/'password' => '.*?'/", "'password' => '$db_password'", $config_contents);
    $config_contents = preg_replace("/'database' => '.*?'/", "'database' => '$db_name'", $config_contents);
    
    // Write the updated config file
    if (file_put_contents($config_file_path, $config_contents)) {
        echo "<div style='color:green;'>Database configuration updated successfully!</div>";
        
        // Test database connection
        try {
            $conn = new mysqli($db_hostname, $db_username, $db_password, $db_name);
            
            if ($conn->connect_error) {
                echo "<div style='color:red;'>Warning: Database connection failed: " . $conn->connect_error . "</div>";
            } else {
                echo "<div style='color:green;'>Database connection successful!</div>";
                $conn->close();
                
                // Create .htaccess file for security if it doesn't exist
                if (!file_exists('.htaccess')) {
                    $htaccess_content = "RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php?/$1 [L]";
                    file_put_contents('.htaccess', $htaccess_content);
                    echo "<div style='color:green;'>.htaccess file created for URL rewriting.</div>";
                }
                
                echo "<div style='text-align:center;margin-top:20px;'>";
                echo "<h3>Installation Complete!</h3>";
                echo "<p>You can now <a href='/'>visit your website</a>.</p>";
                echo "<p>For security, please delete extract.php and dbconfig.php files from your server.</p>";
                echo "</div>";
                exit;
            }
        } catch (Exception $e) {
            echo "<div style='color:red;'>Error testing database connection: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='color:red;'>Error: Could not update database configuration file. Check file permissions.</div>";
    }
}

display_form:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Configuration</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #0066cc; text-align: center; }
        .container { background: #f9f9f9; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 16px; }
        button { background: #0066cc; color: white; border: none; padding: 10px 20px; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0052a3; }
        .note { background: #ffffcc; padding: 10px; border-left: 4px solid #ffcc00; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>Database Configuration</h1>
    <div class="container">
        <div class="note">
            <p><strong>Note:</strong> This script updates your database configuration in the application/config/database.php file. 
            Make sure you have your database created and your database credentials ready.</p>
        </div>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="hostname">Database Hostname:</label>
                <input type="text" id="hostname" name="hostname" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label for="username">Database Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Database Password:</label>
                <input type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="database">Database Name:</label>
                <input type="text" id="database" name="database" required>
            </div>
            
            <button type="submit">Update Configuration</button>
        </form>
    </div>
</body>
</html> 