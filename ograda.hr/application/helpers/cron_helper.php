<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Update the Facebook product feed
 * This function is meant to be called by a CRON job daily
 * @return void
 */
function update_facebook_product_feed()
{
    $CI = &get_instance();
    
    // Load required models
    $CI->load->model('products/products_model');
    
    // Log that the feed update process has started
    log_message('info', 'Starting Facebook product feed update');
    
    try {
        // Include the Facebook Feed controller
        require_once(FCPATH . 'modules/products/controllers/FacebookFeed.php');
        
        // Create an instance of the controller
        $feed_controller = new FacebookFeed();
        
        // Generate the static feed
        $feed_controller->generate_static_feed();
        
        // Log success
        log_message('info', 'Facebook product feed updated successfully');
    } catch (Exception $e) {
        // Log error
        log_message('error', 'Failed to update Facebook product feed: ' . $e->getMessage());
    }
} 