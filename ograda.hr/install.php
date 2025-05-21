<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create .htaccess file
function create_htaccess() {
    $htaccess_content = "RewriteEngine On\n";
    $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
    $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
    $htaccess_content .= "RewriteRule ^(.*)$ index.php?/$1 [L]\n";
    
    if (file_put_contents('.htaccess', $htaccess_content)) {
        return "<div style='color:green;'>.htaccess file created successfully.</div>";
    } else {
        return "<div style='color:red;'>Failed to create .htaccess file. Please check file permissions.</div>";
    }
}

// Function to check and set directory permissions
function set_directory_permissions() {
    $result = '';
    $directories = [
        'application/logs',
        'application/cache',
        'temp',
        'uploads'
    ];
    
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            if (chmod($dir, 0777)) {
                $result .= "<div style='color:green;'>Set permissions for {$dir} to 777.</div>";
            } else {
                $result .= "<div style='color:red;'>Failed to set permissions for {$dir}. Please set manually to 777.</div>";
            }
        } else {
            $result .= "<div style='color:orange;'>Directory {$dir} does not exist. Skipping.</div>";
        }
    }
    
    return $result;
}

// Function to update database configuration
function update_database_config($hostname, $username, $password, $database) {
    $config_file_path = 'application/config/database.php';
    
    if (!file_exists($config_file_path)) {
        return "<div style='color:red;'>Error: Database configuration file not found at {$config_file_path}</div>";
    }
    
    // Read the current config file
    $config_contents = file_get_contents($config_file_path);
    
    // Replace database settings
    $config_contents = preg_replace("/'hostname' => '.*?'/", "'hostname' => '{$hostname}'", $config_contents);
    $config_contents = preg_replace("/'username' => '.*?'/", "'username' => '{$username}'", $config_contents);
    $config_contents = preg_replace("/'password' => '.*?'/", "'password' => '{$password}'", $config_contents);
    $config_contents = preg_replace("/'database' => '.*?'/", "'database' => '{$database}'", $config_contents);
    
    // Write the updated config file
    if (file_put_contents($config_file_path, $config_contents)) {
        return "<div style='color:green;'>Database configuration updated successfully.</div>";
    } else {
        return "<div style='color:red;'>Failed to update database configuration. Please check file permissions.</div>";
    }
}

// Process database configuration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'db_config') {
    $hostname = $_POST['hostname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $database = $_POST['database'];
    
    $result = update_database_config($hostname, $username, $password, $database);
    
    // Test database connection
    try {
        $conn = new mysqli($hostname, $username, $password, $database);
        
        if ($conn->connect_error) {
            $result .= "<div style='color:red;'>Warning: Database connection failed: " . $conn->connect_error . "</div>";
        } else {
            $result .= "<div style='color:green;'>Database connection successful!</div>";
            $conn->close();
        }
    } catch (Exception $e) {
        $result .= "<div style='color:red;'>Error testing database connection: " . $e->getMessage() . "</div>";
    }
}

// Process additional setup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'additional_setup') {
    $create_htaccess = isset($_POST['create_htaccess']) ? true : false;
    $set_permissions = isset($_POST['set_permissions']) ? true : false;
    
    $result = '';
    
    if ($create_htaccess) {
        $result .= create_htaccess();
    }
    
    if ($set_permissions) {
        $result .= set_directory_permissions();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #0066cc;
        }
        .container {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 16px;
        }
        button {
            background: #0066cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0052a3;
        }
        .note {
            background: #ffffcc;
            padding: 10px;
            border-left: 4px solid #ffcc00;
            margin: 15px 0;
        }
        .steps {
            background: #e6f7ff;
            padding: 10px;
            border-left: 4px solid #0099cc;
            margin: 15px 0;
        }
        .checkbox-group {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Website Installation</h1>
    <div class="steps">
        <h3>Installation Steps:</h3>
        <ol>
            <li>Configure your database connection</li>
            <li>Set up file permissions and .htaccess</li>
            <li>Visit your website to complete setup</li>
        </ol>
    </div>
    
    <?php if (isset($result)): ?>
    <div class="container">
        <h3>Results:</h3>
        <?php echo $result; ?>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <h2>1. Database Configuration</h2>
        <div class="note">
            <p><strong>Note:</strong> You need to create a MySQL database before proceeding with this step.</p>
        </div>
        
        <form method="post" action="">
            <input type="hidden" name="action" value="db_config">
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
            
            <button type="submit">Update Database Configuration</button>
        </form>
    </div>
    
    <div class="container">
        <h2>2. Additional Setup</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="additional_setup">
            
            <div class="checkbox-group">
                <input type="checkbox" id="create_htaccess" name="create_htaccess" checked>
                <label for="create_htaccess">Create .htaccess file for URL rewriting</label>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="set_permissions" name="set_permissions" checked>
                <label for="set_permissions">Set directory permissions (777 for logs, cache, temp, uploads)</label>
            </div>
            
            <button type="submit">Perform Additional Setup</button>
        </form>
    </div>
    
    <div class="container">
        <h2>3. Complete Installation</h2>
        <p>After configuring your database and setting up the server environment, you can visit your website to complete the installation:</p>
        <p><a href="/" style="color: #0066cc; font-weight: bold;">Go to your website</a></p>
        <div class="note">
            <p><strong>Important:</strong> For security reasons, delete this file (install.php) after completing the installation.</p>
        </div>
    </div>
</body>
</html> 