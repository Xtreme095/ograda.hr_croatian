<?php
// Database configuration
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'tkfdkvrpwt';
$dbprefix = 'tbl';

// Output the configuration
echo "Using database configuration:\n";
echo "Hostname: $hostname\n";
echo "Username: $username\n";
echo "Database: $database\n";
echo "DB Prefix: $dbprefix\n\n";

// Connect to database
$mysqli = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Connected to database successfully!\n";

// Fix order_master table
$order_master_table = $dbprefix . 'order_master';

// First check if the table exists
$tableExists = $mysqli->query("SHOW TABLES LIKE '$order_master_table'")->num_rows > 0;

if (!$tableExists) {
    // Create order_master table if it doesn't exist
    $createOrderMasterSql = "
        CREATE TABLE `$order_master_table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `invoice_id` INT(11) NULL DEFAULT NULL,
            `clientid` INT(11) NOT NULL,
            `email` VARCHAR(100) NULL DEFAULT NULL,
            `status` VARCHAR(40) NULL DEFAULT NULL,
            `order_date` DATE NULL DEFAULT NULL,
            `datecreated` DATETIME NOT NULL,
            `subtotal` DECIMAL(15,2) NULL DEFAULT 0.00,
            `total` DECIMAL(15,2) NULL DEFAULT 0.00,
            `payment_method` VARCHAR(50) NULL DEFAULT NULL,
            `address_data` TEXT NULL DEFAULT NULL,
            `order_note` TEXT NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            INDEX `clientid` (`clientid`),
            INDEX `invoice_id` (`invoice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    
    if ($mysqli->query($createOrderMasterSql) === TRUE) {
        echo "Table '$order_master_table' created successfully!\n";
    } else {
        echo "Error creating order_master table: " . $mysqli->error . "\n";
    }
} else {
    echo "Table '$order_master_table' already exists.\n";
    
    // Define columns to add to order_master
    $orderMasterColumns = [
        'payment_method' => 'VARCHAR(50) NULL DEFAULT NULL',
        'address_data' => 'TEXT NULL DEFAULT NULL',
        'order_note' => 'TEXT NULL DEFAULT NULL',
        'email' => 'VARCHAR(100) NULL DEFAULT NULL',
        'status' => 'VARCHAR(40) NULL DEFAULT NULL',
        'order_date' => 'DATE NULL DEFAULT NULL',
        'subtotal' => 'DECIMAL(15,2) NULL DEFAULT 0.00',
        'total' => 'DECIMAL(15,2) NULL DEFAULT 0.00'
    ];
    
    // Add columns to order_master table
    foreach ($orderMasterColumns as $columnName => $definition) {
        // Check if column exists
        $result = $mysqli->query("SHOW COLUMNS FROM `$order_master_table` LIKE '$columnName'");
        
        if ($result->num_rows == 0) {
            // Column doesn't exist, so add it
            $query = "ALTER TABLE `$order_master_table` ADD COLUMN `$columnName` $definition";
            
            if ($mysqli->query($query) === TRUE) {
                echo "Column '$columnName' added to order_master table successfully!\n";
            } else {
                echo "Error adding column '$columnName' to order_master: " . $mysqli->error . "\n";
            }
        } else {
            echo "Column '$columnName' already exists in order_master.\n";
        }
    }
}

// Fix order_items table
$order_items_table = $dbprefix . 'order_items';

// First check if the table exists
$tableExists = $mysqli->query("SHOW TABLES LIKE '$order_items_table'")->num_rows > 0;

if (!$tableExists) {
    // Create order_items table if it doesn't exist
    $createOrderItemsSql = "
        CREATE TABLE `$order_items_table` (
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
    
    if ($mysqli->query($createOrderItemsSql) === TRUE) {
        echo "Table '$order_items_table' created successfully!\n";
    } else {
        echo "Error creating order_items table: " . $mysqli->error . "\n";
    }
} else {
    echo "Table '$order_items_table' already exists.\n";
    
    // Check for required columns in order_items
    $orderItemsColumns = [
        'order_id' => 'INT(11) NOT NULL',
        'product_id' => 'INT(11) NOT NULL',
        'product_variation_id' => 'INT(11) NULL DEFAULT NULL'
    ];
    
    // Check if either qty or quantity exists
    $result_qty = $mysqli->query("SHOW COLUMNS FROM `$order_items_table` LIKE 'qty'");
    $result_quantity = $mysqli->query("SHOW COLUMNS FROM `$order_items_table` LIKE 'quantity'");
    
    if ($result_qty->num_rows == 0 && $result_quantity->num_rows == 0) {
        // Neither exists, add qty
        $orderItemsColumns['qty'] = 'INT(11) NOT NULL DEFAULT 1';
    }
    
    // Add columns to order_items table
    foreach ($orderItemsColumns as $columnName => $definition) {
        // Check if column exists
        $result = $mysqli->query("SHOW COLUMNS FROM `$order_items_table` LIKE '$columnName'");
        
        if ($result->num_rows == 0) {
            // Column doesn't exist, so add it
            $query = "ALTER TABLE `$order_items_table` ADD COLUMN `$columnName` $definition";
            
            if ($mysqli->query($query) === TRUE) {
                echo "Column '$columnName' added to order_items table successfully!\n";
            } else {
                echo "Error adding column '$columnName' to order_items: " . $mysqli->error . "\n";
            }
        } else {
            echo "Column '$columnName' already exists in order_items.\n";
        }
    }
}

echo "\nDatabase tables and columns created/updated successfully.\n";

// Close connection
$mysqli->close();
echo "Database connection closed.\n";
?> 