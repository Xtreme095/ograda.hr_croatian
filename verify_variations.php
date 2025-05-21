<?php
/**
 * Script to verify material variants for the test product
 */

// Database configuration 
$host = 'localhost';
$dbname = 'tkfdkvrpwt';
$username = 'tkfdkvrpwt';
$password = '2rYHDsuUCF';
$table_prefix = 'tbl'; // Your database table prefix

// Product ID to check
$product_id = 1; // Test product ID

try {
    // Connect to database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Get all variations for the test product
    $query = "SELECT pv.id, pm.product_name, v.name as variation_name, vv.value as variation_value, pv.rate
             FROM {$table_prefix}product_variations pv 
             JOIN {$table_prefix}product_master pm ON pm.id = pv.product_id 
             JOIN {$table_prefix}variations v ON v.id = pv.variation_id 
             JOIN {$table_prefix}variation_values vv ON vv.id = pv.variation_value_id 
             WHERE pm.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$product_id]);
    $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Variations for product ID $product_id:\n\n";
    
    if (empty($variations)) {
        echo "No variations found for this product.\n";
    } else {
        echo "ID | Product Name | Variation Name | Variation Value | Rate\n";
        echo str_repeat('-', 80) . "\n";
        
        foreach ($variations as $variation) {
            echo $variation['id'] . " | " . 
                 $variation['product_name'] . " | " . 
                 $variation['variation_name'] . " | " . 
                 $variation['variation_value'] . " | " . 
                 $variation['rate'] . "\n";
        }
    }
    
    // Also check if the product is marked as a variation product
    $stmt = $conn->prepare("SELECT is_variation FROM {$table_prefix}product_master WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nProduct is_variation status: " . ($product['is_variation'] ? 'YES' : 'NO') . "\n";
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 