<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Facebook Pixel Helper
 * 
 * Helper functions for server-side Facebook Pixel events using the Conversion API
 */

/**
 * Send server-side Facebook event
 * 
 * @param string $event_name The event name (e.g., 'PageView', 'Purchase', etc.)
 * @param array $user_data User data like email, phone, etc.
 * @param array $custom_data Custom event data
 * @return bool Success or failure
 */
function send_facebook_event($event_name, $user_data = [], $custom_data = [])
{
    // Facebook Pixel ID and Conversion API access token
    $pixel_id = '1571811730359530';
    $access_token = 'EAAGrZBiyUrnYBO0yONUZCNzoew4HfGuw1g7RaBrxhGZBgsHiENFYnARpyd2VvvQfjG1cdG1VJqgfZBA8GvDdEfdioZBdgyZAZBdpEe9CLL9NDCZCxggIPtxVSd9TUqyWwZBArrXU6n7HEsp2mwhJBdaOW72tkxGZALMPCVbkvWVY4Gjd2JZBSiPd43ZCgnh8tnz42gZDZD';
    
    // Get IP address and user agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Generate event ID - must be unique for each event
    $event_id = uniqid('fb_', true);
    
    // Prepare event data
    $event_data = [
        'event_name' => $event_name,
        'event_time' => time(),
        'event_id' => $event_id,
        'event_source_url' => current_url(),
        'action_source' => 'website',
    ];
    
    // Add user data if provided
    if (!empty($user_data)) {
        $event_data['user_data'] = $user_data;
    } else {
        // Set minimal user data
        $event_data['user_data'] = [
            'client_ip_address' => $ip_address,
            'client_user_agent' => $user_agent,
        ];
    }
    
    // Add custom data if provided
    if (!empty($custom_data)) {
        $event_data['custom_data'] = $custom_data;
    }
    
    // Prepare API request
    $request_data = [
        'data' => [$event_data],
        'access_token' => $access_token,
    ];
    
    // Send request to Facebook Conversion API
    $ch = curl_init("https://graph.facebook.com/v17.0/{$pixel_id}/events");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log response for debugging
    log_message('debug', "Facebook Conversion API Response: " . $response);
    
    // Consider 200 status code as success
    return $status_code === 200;
}

/**
 * Track page view event
 * 
 * @param array $user_data Optional user data
 * @return bool Success or failure
 */
function track_facebook_page_view($user_data = [])
{
    return send_facebook_event('PageView', $user_data);
}

/**
 * Track purchase event
 * 
 * @param float $value Purchase value
 * @param string $currency Currency code (default: EUR)
 * @param array $products Products array
 * @param array $user_data User data
 * @return bool Success or failure
 */
function track_facebook_purchase($value, $currency = 'EUR', $products = [], $user_data = [])
{
    $custom_data = [
        'currency' => $currency,
        'value' => $value,
    ];
    
    if (!empty($products)) {
        $custom_data['content_ids'] = array_column($products, 'id');
        $custom_data['content_type'] = 'product';
        $custom_data['contents'] = array_map(function($product) {
            return [
                'id' => $product['id'],
                'quantity' => $product['quantity'] ?? 1,
                'item_price' => $product['price'] ?? 0,
            ];
        }, $products);
    }
    
    return send_facebook_event('Purchase', $user_data, $custom_data);
}

/**
 * Track add to cart event
 * 
 * @param float $value Cart value
 * @param string $currency Currency code (default: EUR)
 * @param array $product Product data
 * @param array $user_data User data
 * @return bool Success or failure
 */
function track_facebook_add_to_cart($value, $currency = 'EUR', $product = [], $user_data = [])
{
    $custom_data = [
        'currency' => $currency,
        'value' => $value,
    ];
    
    if (!empty($product)) {
        $custom_data['content_ids'] = [$product['id']];
        $custom_data['content_type'] = 'product';
        $custom_data['contents'] = [
            [
                'id' => $product['id'],
                'quantity' => $product['quantity'] ?? 1,
                'item_price' => $product['price'] ?? 0,
            ]
        ];
    }
    
    return send_facebook_event('AddToCart', $user_data, $custom_data);
}

/**
 * Track initiate checkout event
 * 
 * @param float $value Checkout value
 * @param string $currency Currency code (default: EUR)
 * @param array $products Products array
 * @param array $user_data User data
 * @return bool Success or failure
 */
function track_facebook_initiate_checkout($value, $currency = 'EUR', $products = [], $user_data = [])
{
    $custom_data = [
        'currency' => $currency,
        'value' => $value,
    ];
    
    if (!empty($products)) {
        $custom_data['content_ids'] = array_column($products, 'id');
        $custom_data['content_type'] = 'product';
        $custom_data['contents'] = array_map(function($product) {
            return [
                'id' => $product['id'],
                'quantity' => $product['quantity'] ?? 1,
                'item_price' => $product['price'] ?? 0,
            ];
        }, $products);
    }
    
    return send_facebook_event('InitiateCheckout', $user_data, $custom_data);
}

/**
 * Track lead event (e.g., form submission)
 * 
 * @param string $form_name Form name or identifier
 * @param array $user_data User data
 * @return bool Success or failure
 */
function track_facebook_lead($form_name = '', $user_data = [])
{
    $custom_data = [];
    
    if (!empty($form_name)) {
        $custom_data['form_name'] = $form_name;
    }
    
    return send_facebook_event('Lead', $user_data, $custom_data);
}

/**
 * Hash user data for Facebook CAPI
 * 
 * @param array $user_data Associative array of user data
 * @return array Hashed user data
 */
function hash_facebook_user_data($user_data)
{
    $hashed_data = [];
    
    foreach ($user_data as $key => $value) {
        // Skip empty values
        if (empty($value)) {
            continue;
        }
        
        // Normalize and hash the value
        $normalized = strtolower(trim($value));
        $hashed_data[$key] = hash('sha256', $normalized);
    }
    
    return $hashed_data;
} 