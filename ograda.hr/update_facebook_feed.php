<?php
/**
 * This script is designed to be run as a cron job to update the Facebook product feed.
 * It can be scheduled to run once a day, e.g.:
 * 0 2 * * * php /path/to/your/website/update_facebook_feed.php
 */

// Define base path
define('BASEPATH', true);
define('FCPATH', __DIR__ . '/');

// Load CodeIgniter bootstrap file
require_once(__DIR__ . '/index.php');

// This script should only be accessible via CLI
if (php_sapi_name() !== 'cli') {
    echo "This script can only be run from the command line.";
    exit(1);
}

// Include required controller
require_once(FCPATH . 'modules/products/controllers/FacebookFeed.php');

// Create controller instance
$feed = new FacebookFeed();

// Generate the feed
$feed->generate_static_feed();

exit(0); 