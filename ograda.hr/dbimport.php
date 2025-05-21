<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', '512M');

// Function to read database configuration
function get_db_config() {
    $config_file_path = 'application/config/database.php';
    
    if (!file_exists($config_file_path)) {
        return false;
    }
    
    $content = file_get_contents($config_file_path);
    
    // Extract hostname
    preg_match("/'hostname' => '(.*?)'/", $content, $hostname_match);
    $hostname = isset($hostname_match[1]) ? $hostname_match[1] : '';
    
    // Extract username
    preg_match("/'username' => '(.*?)'/", $content, $username_match);
    $username = isset($username_match[1]) ? $username_match[1] : '';
    
    // Extract password
    preg_match("/'password' => '(.*?)'/", $content, $password_match);
    $password = isset($password_match[1]) ? $password_match[1] : '';
    
    // Extract database
    preg_match("/'database' => '(.*?)'/", $content, $database_match);
    $database = isset($database_match[1]) ? $database_match[1] : '';
    
    return [
        'hostname' => $hostname,
        'username' => $username,
        'password' => $password,
        'database' => $database
    ];
}

// Function to create database
function create_database($db_config) {
    try {
        $conn = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password']);
        
        if ($conn->connect_error) {
            return "Failed to connect to MySQL: " . $conn->connect_error;
        }
        
        $sql = "CREATE DATABASE IF NOT EXISTS `" . $db_config['database'] . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if ($conn->query($sql) === true) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return "Error creating database: " . $conn->error;
        }
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
}

// Function to import SQL file
function import_sql_file($db_config, $file_path) {
    try {
        $conn = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
        
        if ($conn->connect_error) {
            return "Failed to connect to MySQL: " . $conn->connect_error;
        }
        
        // Set charset
        $conn->set_charset("utf8mb4");
        
        // Read SQL file
        $sql = file_get_contents($file_path);
        
        // Execute multi query
        if ($conn->multi_query($sql)) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return "Error importing SQL: " . $conn->error;
        }
    } catch (Exception $e) {
        return "Exception: " . $e->getMessage();
    }
}

// Handle file upload and import
$result = '';
$db_config = get_db_config();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for database config
    if (!$db_config) {
        $result = "<div style='color:red;'>Error: Could not read database configuration. Please configure your database first.</div>";
    } 
    // If create database option selected
    else if (isset($_POST['create_database']) && $_POST['create_database'] == 1) {
        $create_result = create_database($db_config);
        
        if ($create_result === true) {
            $result = "<div style='color:green;'>Database created successfully.</div>";
        } else {
            $result = "<div style='color:red;'>{$create_result}</div>";
        }
    }
    // If file upload
    else if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] == 0) {
        // Check file extension
        $file_info = pathinfo($_FILES['sql_file']['name']);
        $extension = strtolower($file_info['extension']);
        
        if ($extension != 'sql') {
            $result = "<div style='color:red;'>Error: Only SQL files are allowed.</div>";
        } else {
            // Move uploaded file
            $target_path = 'temp/' . basename($_FILES['sql_file']['name']);
            
            if (move_uploaded_file($_FILES['sql_file']['tmp_name'], $target_path)) {
                $import_result = import_sql_file($db_config, $target_path);
                
                if ($import_result === true) {
                    $result = "<div style='color:green;'>Database imported successfully.</div>";
                    // Delete uploaded file
                    unlink($target_path);
                } else {
                    $result = "<div style='color:red;'>{$import_result}</div>";
                }
            } else {
                $result = "<div style='color:red;'>Error: Failed to upload file.</div>";
            }
        }
    } else if ($_FILES['sql_file']['error'] > 0) {
        switch ($_FILES['sql_file']['error']) {
            case 1:
                $result = "<div style='color:red;'>Error: File exceeds the maximum upload size.</div>";
                break;
            case 2:
                $result = "<div style='color:red;'>Error: File exceeds the maximum upload size.</div>";
                break;
            case 3:
                $result = "<div style='color:red;'>Error: File was only partially uploaded.</div>";
                break;
            case 4:
                $result = "<div style='color:red;'>Error: No file was uploaded.</div>";
                break;
            case 6:
                $result = "<div style='color:red;'>Error: Missing a temporary folder.</div>";
                break;
            case 7:
                $result = "<div style='color:red;'>Error: Failed to write file to disk.</div>";
                break;
            case 8:
                $result = "<div style='color:red;'>Error: File upload stopped by extension.</div>";
                break;
            default:
                $result = "<div style='color:red;'>Error: Unknown upload error.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import</title>
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
        input[type="file"] {
            margin-top: 5px;
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
        .current-config {
            background: #e6f7ff;
            padding: 10px;
            border-left: 4px solid #0099cc;
            margin: 15px 0;
        }
        .warning {
            background: #ffe6e6;
            padding: 10px;
            border-left: 4px solid #cc0000;
            margin: 15px 0;
        }
        .checkbox-group {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Database Import Tool</h1>
    
    <?php if (isset($result)): ?>
    <div class="container">
        <h3>Results:</h3>
        <?php echo $result; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($db_config): ?>
    <div class="current-config">
        <h3>Current Database Configuration:</h3>
        <p><strong>Hostname:</strong> <?php echo $db_config['hostname']; ?></p>
        <p><strong>Username:</strong> <?php echo $db_config['username']; ?></p>
        <p><strong>Database:</strong> <?php echo $db_config['database']; ?></p>
    </div>
    <?php else: ?>
    <div class="warning">
        <h3>Warning:</h3>
        <p>Database configuration not found. Please configure your database first at the <a href="install.php">installation page</a>.</p>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <h2>Create Database</h2>
        <div class="note">
            <p><strong>Note:</strong> This will create the database if it doesn't exist already.</p>
        </div>
        
        <form method="post" action="">
            <input type="hidden" name="create_database" value="1">
            <button type="submit" <?php echo $db_config ? '' : 'disabled'; ?>>Create Database</button>
        </form>
    </div>
    
    <div class="container">
        <h2>Import SQL File</h2>
        <div class="warning">
            <p><strong>Warning:</strong> Importing an SQL file will overwrite any existing data in your database.</p>
        </div>
        
        <form method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="sql_file">Select SQL File:</label>
                <input type="file" id="sql_file" name="sql_file" accept=".sql" required <?php echo $db_config ? '' : 'disabled'; ?>>
            </div>
            
            <button type="submit" <?php echo $db_config ? '' : 'disabled'; ?>>Import SQL File</button>
        </form>
    </div>
    
    <div class="container">
        <h2>Next Steps</h2>
        <p>After importing your database, you can:</p>
        <ul>
            <li><a href="/" style="color: #0066cc; font-weight: bold;">Visit your website</a></li>
            <li><a href="install.php" style="color: #0066cc; font-weight: bold;">Go back to the installation page</a></li>
        </ul>
        <div class="note">
            <p><strong>Important:</strong> For security reasons, delete this file and install.php after completing the installation.</p>
        </div>
    </div>
</body>
</html> 