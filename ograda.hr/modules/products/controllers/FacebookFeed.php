<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FacebookFeed extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->helper('url');
    }

    /**
     * Generate XML feed for Facebook catalog
     */
    public function index()
    {
        // Set headers for XML document
        header('Content-Type: application/xml; charset=utf-8');
        
        // Get all products
        $products = $this->products_model->get_by_id_product();
        
        // Start XML document
        echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        echo '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL;
        echo '<channel>' . PHP_EOL;
        echo '<title>Product Catalog</title>' . PHP_EOL;
        echo '<link>' . base_url() . '</link>' . PHP_EOL;
        echo '<description>Product feed for Facebook Catalog</description>' . PHP_EOL;
        
        // Add products to feed
        foreach ($products as $product) {
            // Skip products with no name or price
            if (empty($product['product_name']) || empty($product['rate'])) {
                continue;
            }
            
            // Generate product URL from slug
            $product_slug = url_title($product['product_name'], '-', true);
            $product_url = site_url('product/' . $product_slug);
            
            // Get product image URL
            $image_url = base_url('modules/products/uploads/image-not-available.png'); // Default image
            if (!empty($product['product_image'])) {
                $image_path = 'modules/products/uploads/' . $product['product_image'];
                if (file_exists(FCPATH . $image_path)) {
                    $image_url = base_url($image_path);
                }
            }
            
            // Get category name
            $category = isset($product['p_category_name']) ? $product['p_category_name'] : 'Ograde';
            
            // Get product variations if any
            $variations = [];
            if (isset($product['is_variation']) && $product['is_variation'] && isset($product['variations'])) {
                foreach ($product['variations'] as $variation) {
                    $variations[] = $variation->variation_name . ': ' . $variation->variation_value;
                }
            }
            
            // Generate unique product ID
            $product_id = $product['id'];
            
            // Format price (Facebook requires decimal point, not comma)
            $price = number_format((float)$product['rate'], 2, '.', '') . ' EUR';
            
            // Get availability
            $availability = ($product['quantity_number'] > 0) ? 'in stock' : 'out of stock';
            
            // Get product description
            $description = isset($product['product_description']) ? $product['product_description'] : '';
            
            // Generate brand (using category as fallback)
            $brand = 'Profili Zagreb';
            
            // Output product item
            echo '<item>' . PHP_EOL;
            echo '  <g:id>' . htmlspecialchars($product_id) . '</g:id>' . PHP_EOL;
            echo '  <g:title>' . htmlspecialchars($product['product_name']) . '</g:title>' . PHP_EOL;
            echo '  <g:description>' . htmlspecialchars($description) . '</g:description>' . PHP_EOL;
            echo '  <g:link>' . htmlspecialchars($product_url) . '</g:link>' . PHP_EOL;
            echo '  <g:image_link>' . htmlspecialchars($image_url) . '</g:image_link>' . PHP_EOL;
            echo '  <g:availability>' . $availability . '</g:availability>' . PHP_EOL;
            echo '  <g:price>' . $price . '</g:price>' . PHP_EOL;
            echo '  <g:brand>' . htmlspecialchars($brand) . '</g:brand>' . PHP_EOL;
            echo '  <g:condition>new</g:condition>' . PHP_EOL;
            
            // Add category
            if (!empty($category)) {
                echo '  <g:product_type>' . htmlspecialchars($category) . '</g:product_type>' . PHP_EOL;
            }
            
            // Add variations as custom labels
            if (!empty($variations)) {
                $i = 0;
                foreach ($variations as $variation) {
                    if ($i < 5) { // Facebook supports up to 5 custom labels
                        echo '  <g:custom_label_' . $i . '>' . htmlspecialchars($variation) . '</g:custom_label_' . $i . '>' . PHP_EOL;
                        $i++;
                    }
                }
            }
            
            echo '</item>' . PHP_EOL;
        }
        
        // Close XML document
        echo '</channel>' . PHP_EOL;
        echo '</rss>';
    }

    /**
     * Generate static XML file 
     * This method can be called via CRON to update the feed daily
     */
    public function generate_static_feed()
    {
        // Make sure this is not a direct web request
        if (!$this->input->is_cli_request() && !has_permission('products', '', 'admin')) {
            show_404();
            return;
        }
        
        // Get all products
        $products = $this->products_model->get_by_id_product();
        
        // Start building XML content
        $xml_content = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml_content .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . PHP_EOL;
        $xml_content .= '<channel>' . PHP_EOL;
        $xml_content .= '<title>Product Catalog</title>' . PHP_EOL;
        $xml_content .= '<link>' . base_url() . '</link>' . PHP_EOL;
        $xml_content .= '<description>Product feed for Facebook Catalog</description>' . PHP_EOL;
        
        // Add products to feed
        foreach ($products as $product) {
            // Skip products with no name or price
            if (empty($product['product_name']) || empty($product['rate'])) {
                continue;
            }
            
            // Generate product URL from slug
            $product_slug = url_title($product['product_name'], '-', true);
            $product_url = site_url('product/' . $product_slug);
            
            // Get product image URL
            $image_url = base_url('modules/products/uploads/image-not-available.png'); // Default image
            if (!empty($product['product_image'])) {
                $image_path = 'modules/products/uploads/' . $product['product_image'];
                if (file_exists(FCPATH . $image_path)) {
                    $image_url = base_url($image_path);
                }
            }
            
            // Get category name
            $category = isset($product['p_category_name']) ? $product['p_category_name'] : 'Ograde';
            
            // Get product variations if any
            $variations = [];
            if (isset($product['is_variation']) && $product['is_variation'] && isset($product['variations'])) {
                foreach ($product['variations'] as $variation) {
                    $variations[] = $variation->variation_name . ': ' . $variation->variation_value;
                }
            }
            
            // Generate unique product ID
            $product_id = $product['id'];
            
            // Format price (Facebook requires decimal point, not comma)
            $price = number_format((float)$product['rate'], 2, '.', '') . ' EUR';
            
            // Get availability
            $availability = ($product['quantity_number'] > 0) ? 'in stock' : 'out of stock';
            
            // Get product description
            $description = isset($product['product_description']) ? $product['product_description'] : '';
            
            // Generate brand (using category as fallback)
            $brand = 'Profili Zagreb';
            
            // Add product to XML content
            $xml_content .= '<item>' . PHP_EOL;
            $xml_content .= '  <g:id>' . htmlspecialchars($product_id) . '</g:id>' . PHP_EOL;
            $xml_content .= '  <g:title>' . htmlspecialchars($product['product_name']) . '</g:title>' . PHP_EOL;
            $xml_content .= '  <g:description>' . htmlspecialchars($description) . '</g:description>' . PHP_EOL;
            $xml_content .= '  <g:link>' . htmlspecialchars($product_url) . '</g:link>' . PHP_EOL;
            $xml_content .= '  <g:image_link>' . htmlspecialchars($image_url) . '</g:image_link>' . PHP_EOL;
            $xml_content .= '  <g:availability>' . $availability . '</g:availability>' . PHP_EOL;
            $xml_content .= '  <g:price>' . $price . '</g:price>' . PHP_EOL;
            $xml_content .= '  <g:brand>' . htmlspecialchars($brand) . '</g:brand>' . PHP_EOL;
            $xml_content .= '  <g:condition>new</g:condition>' . PHP_EOL;
            
            // Add category
            if (!empty($category)) {
                $xml_content .= '  <g:product_type>' . htmlspecialchars($category) . '</g:product_type>' . PHP_EOL;
            }
            
            // Add variations as custom labels
            if (!empty($variations)) {
                $i = 0;
                foreach ($variations as $variation) {
                    if ($i < 5) { // Facebook supports up to 5 custom labels
                        $xml_content .= '  <g:custom_label_' . $i . '>' . htmlspecialchars($variation) . '</g:custom_label_' . $i . '>' . PHP_EOL;
                        $i++;
                    }
                }
            }
            
            $xml_content .= '</item>' . PHP_EOL;
        }
        
        // Close XML document
        $xml_content .= '</channel>' . PHP_EOL;
        $xml_content .= '</rss>';
        
        // Save XML content to files with different names for better accessibility
        // Save with the original name
        $file_path = FCPATH . 'facebook_feed.xml';
        file_put_contents($file_path, $xml_content);
        
        // Also save with a consistent name that's easier to remember
        $alt_file_path = FCPATH . 'product-feed.xml';
        file_put_contents($alt_file_path, $xml_content);
        
        if ($this->input->is_cli_request()) {
            echo 'Facebook feed generated successfully at: ' . $file_path . PHP_EOL;
            echo 'Also available at: ' . $alt_file_path . PHP_EOL;
        } else {
            // For browser view
            echo '<h1>Feed Generated</h1>';
            echo '<p>Facebook feed has been generated successfully at: ' . $file_path . '</p>';
            echo '<p><a href="' . base_url('facebook_feed.xml') . '" target="_blank">View the feed (original name)</a></p>';
            echo '<p><a href="' . base_url('product-feed.xml') . '" target="_blank">View the feed (alternate name)</a></p>';
        }
    }
} 