<?php
// Define BASEPATH to bypass direct script access restriction
define('BASEPATH', __DIR__);

// Load database config
include('application/config/database.php');

// Print database configuration
echo "Database Configuration:\n";
echo "Hostname: " . $db['default']['hostname'] . "\n";
echo "Username: " . $db['default']['username'] . "\n";
echo "Password: " . (empty($db['default']['password']) ? "(empty)" : "(set)") . "\n";
echo "Database: " . $db['default']['database'] . "\n";
echo "DB Prefix: " . $db['default']['dbprefix'] . "\n"; 