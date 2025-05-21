<?php
// Load the database configuration
require_once 'application/config/database.php';

// Get database credentials
$db_config = $db['default'];

// Connect to database
$mysqli = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database successfully!<br>";

// Define the columns to add with their definitions
$columns = [
    'payment_method' => 'VARCHAR(50) NULL DEFAULT NULL',
    'address_data' => 'TEXT NULL DEFAULT NULL',
    'order_note' => 'TEXT NULL DEFAULT NULL',
    'email' => 'VARCHAR(100) NULL DEFAULT NULL',
    'status' => 'VARCHAR(40) NULL DEFAULT NULL',
    'order_date' => 'DATE NULL DEFAULT NULL',
    'subtotal' => 'DECIMAL(15,2) NULL DEFAULT 0.00',
    'total' => 'DECIMAL(15,2) NULL DEFAULT 0.00'
];

// Add columns if they don't exist
$table_name = $db_config['dbprefix'] . 'order_master';
$columns_added = 0;

foreach ($columns as $column_name => $definition) {
    // Check if column exists
    $result = $mysqli->query("SHOW COLUMNS FROM `$table_name` LIKE '$column_name'");
    
    if ($result->num_rows == 0) {
        // Column doesn't exist, so add it
        $query = "ALTER TABLE `$table_name` ADD COLUMN `$column_name` $definition";
        
        if ($mysqli->query($query) === TRUE) {
            echo "Column '$column_name' added successfully!<br>";
            $columns_added++;
        } else {
            echo "Error adding column '$column_name': " . $mysqli->error . "<br>";
        }
    } else {
        echo "Column '$column_name' already exists.<br>";
    }
}

echo "<br>Process completed. Added $columns_added new columns.";

// Close connection
$mysqli->close();
echo "<br>Database connection closed.";
?> 