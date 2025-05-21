<?php
// Define BASEPATH to bypass direct script access restriction
define('BASEPATH', true);

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

// Define the table name
$table_name = $db_config['dbprefix'] . 'order_items';

// Check if the table exists
$table_exists = $mysqli->query("SHOW TABLES LIKE '$table_name'")->num_rows > 0;

if (!$table_exists) {
    // Create the table if it doesn't exist
    $create_table_sql = "
        CREATE TABLE `$table_name` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL,
            `product_variation_id` INT(11) NULL DEFAULT NULL,
            `qty` INT(11) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            INDEX `order_id` (`order_id`),
            INDEX `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    
    if ($mysqli->query($create_table_sql) === TRUE) {
        echo "Table '$table_name' created successfully!<br>";
    } else {
        echo "Error creating table: " . $mysqli->error . "<br>";
    }
} else {
    echo "Table '$table_name' already exists.<br>";
    
    // Check for required columns and add them if they don't exist
    $required_columns = [
        'product_id' => 'INT(11) NOT NULL',
        'product_variation_id' => 'INT(11) NULL DEFAULT NULL',
        'order_id' => 'INT(11) NOT NULL'
    ];
    
    // Check if either qty or quantity exists, and add one if neither exists
    $result_qty = $mysqli->query("SHOW COLUMNS FROM `$table_name` LIKE 'qty'");
    $result_quantity = $mysqli->query("SHOW COLUMNS FROM `$table_name` LIKE 'quantity'");
    
    if ($result_qty->num_rows == 0 && $result_quantity->num_rows == 0) {
        // Neither exists, add qty
        $required_columns['qty'] = 'INT(11) NOT NULL DEFAULT 1';
    }
    
    $columns_added = 0;
    
    foreach ($required_columns as $column_name => $definition) {
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
    
    echo "<br>Process completed. Added $columns_added new columns to existing table.";
}

// Close connection
$mysqli->close();
echo "<br>Database connection closed.";
?> 