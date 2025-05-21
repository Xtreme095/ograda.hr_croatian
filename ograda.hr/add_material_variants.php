<?php
/**
 * Script to add missing "Vrsta materijala" variants to the test product
 */

// Database configuration 
$host = 'localhost';
$dbname = 'tkfdkvrpwt';
$username = 'tkfdkvrpwt';
$password = '2rYHDsuUCF';
$table_prefix = 'tbl'; // Your database table prefix

// Product ID to update
$product_id = 1; // Test product ID

try {
    // Connect to database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Step 1: Check if "Vrsta materijala" variation exists
    $stmt = $conn->prepare("SELECT id FROM {$table_prefix}variations WHERE name = 'Vrsta materijala'");
    $stmt->execute();
    $variation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$variation) {
        // Create "Vrsta materijala" variation if it doesn't exist
        $stmt = $conn->prepare("INSERT INTO {$table_prefix}variations (name, description) VALUES ('Vrsta materijala', 'Tip materijala za ogradu')");
        $stmt->execute();
        $variation_id = $conn->lastInsertId();
        echo "Created 'Vrsta materijala' variation with ID: $variation_id\n";
    } else {
        $variation_id = $variation['id'];
        echo "Found existing 'Vrsta materijala' variation with ID: $variation_id\n";
    }
    
    // Step 2: Define the variation values to add
    $materials = [
        ['Aluminij', 1, 'Aluminijski materijal', 50], // name, order, description, rate
        ['Pocinčani čelik', 2, 'Pocinčani čelik materijal', 60],
        ['Nehrđajući čelik-inox', 3, 'Nehrđajući čelik (inox) materijal', 70]
    ];
    
    // Step 3: Add the variation values if they don't exist
    foreach ($materials as $material) {
        // Check if this variation value already exists
        $stmt = $conn->prepare("SELECT id FROM {$table_prefix}variation_values 
                               WHERE variation_id = ? AND value = ?");
        $stmt->execute([$variation_id, $material[0]]);
        $variation_value = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$variation_value) {
            // Create the variation value
            $stmt = $conn->prepare("INSERT INTO {$table_prefix}variation_values 
                                  (variation_id, value, value_order, description) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$variation_id, $material[0], $material[1], $material[2]]);
            $variation_value_id = $conn->lastInsertId();
            echo "Created variation value '$material[0]' with ID: $variation_value_id\n";
        } else {
            $variation_value_id = $variation_value['id'];
            echo "Found existing variation value '$material[0]' with ID: $variation_value_id\n";
        }
        
        // Step 4: Check if the product variation already exists
        $stmt = $conn->prepare("SELECT id FROM {$table_prefix}product_variations 
                               WHERE product_id = ? AND variation_id = ? AND variation_value_id = ?");
        $stmt->execute([$product_id, $variation_id, $variation_value_id]);
        $product_variation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product_variation) {
            // Create the product variation
            $stmt = $conn->prepare("INSERT INTO {$table_prefix}product_variations 
                                  (product_id, variation_id, variation_value_id, rate, quantity_number) 
                                  VALUES (?, ?, ?, ?, 100)");
            $stmt->execute([$product_id, $variation_id, $variation_value_id, $material[3]]);
            $product_variation_id = $conn->lastInsertId();
            echo "Created product variation '$material[0]' for product ID $product_id with rate {$material[3]}\n";
        } else {
            $product_variation_id = $product_variation['id'];
            echo "Found existing product variation for '$material[0]' with ID: $product_variation_id\n";
        }
    }
    
    // Step 5: Make sure the product is marked as a variation product
    $stmt = $conn->prepare("UPDATE {$table_prefix}product_master SET is_variation = 1 WHERE id = ?");
    $stmt->execute([$product_id]);
    echo "Updated product ID $product_id to be a variation product\n";
    
    echo "\nFinished adding material variants successfully!\n";
    echo "You should now see 'Vrsta materijala' variants on your product page.\n";
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 