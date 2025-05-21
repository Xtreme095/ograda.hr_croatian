<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
   
   
    public function __construct()
    {
        parent::__construct();
        $this->load->model('shop_model');
        
        // Load the Facebook Pixel helper
        $this->load->helper('facebook_pixel');
        
        // Track page view for all pages using the server-side API
        track_facebook_page_view();
    }

    public function index()
    {
        //$this->load->view('front/home');
        $data['title'] = 'Profi Line Zagreb | Ograde | Metalne kontrukcije'; // Title for the page
        $data['keywords'] = 'Ograde, Metalne kontrukcije, Staklena ograde, WPC ograde, Metalne ograde, Profili Stakla, Aluminijumska ograda';
        
        // Get recently viewed products
        $this->load->model('products/products_model');
        $recently_viewed_ids = $this->session->userdata('recently_viewed') ?? [];
        $recently_viewed_products = [];
        
        // Get all products
        $all_products = $this->products_model->get_by_id_product();
        
        // Log for debugging
        log_message('debug', 'Home index: Total products found: ' . count($all_products));
        
        // Make sure we have products
        if (empty($all_products) || !is_array($all_products)) {
            $all_products = [];
            log_message('error', 'Home index: No products found or invalid format');
        }
        
        // If we have recently viewed products, get their details
        if (!empty($recently_viewed_ids)) {
            foreach ($recently_viewed_ids as $product_id) {
                foreach ($all_products as $product) {
                    if (!isset($product['id']) || !isset($product['product_name'])) {
                        continue; // Skip invalid products
                    }
                    
                    if ($product['id'] == $product_id) {
                        $recently_viewed_products[] = $product;
                        break;
                    }
                }
                
                // Limit to 4 products
                if (count($recently_viewed_products) >= 4) {
                    break;
                }
            }
        }
        
        // If no recently viewed products, use random products
        if (empty($recently_viewed_products)) {
            $recently_viewed_products = $this->get_random_products($all_products, 4);
            log_message('debug', 'Home index: Using random recently viewed products, count: ' . count($recently_viewed_products));
        } else {
            log_message('debug', 'Home index: Using session recently viewed products, count: ' . count($recently_viewed_products));
        }
        
        // Get recommended products (different from recently viewed)
        $exclude_ids = !empty($recently_viewed_products) ? array_column($recently_viewed_products, 'id') : [];
        $recommended_products = $this->get_random_products($all_products, 4, $exclude_ids);
        
        // Log for debugging
        log_message('debug', 'Home index: Recommended products count: ' . count($recommended_products));
        if (empty($recommended_products)) {
            log_message('error', 'Home index: No recommended products generated');
            // As a fallback, just use random products if we couldn't get recommended products
            $recommended_products = $this->get_random_products($all_products, 4);
            log_message('debug', 'Home index: Using fallback random recommended products, count: ' . count($recommended_products));
        }
        
        // Pass data to view
        $data['recently_viewed_products'] = $recently_viewed_products;
        $data['recommended_products'] = $recommended_products;
        
        $data['content'] = $this->load->view('front/home', $data, TRUE); // Load the page content
        $this->load->view('front/layouts/main', $data); // Load the main layout
    }
    
    /**
     * Get random products from list
     * 
     * @param array $products List of all products
     * @param int $count Number of products to return
     * @param array $exclude_ids Product IDs to exclude
     * @return array Random selection of products
     */
    private function get_random_products($products, $count = 4, $exclude_ids = [])
    {
        // Make sure products is an array
        if (!is_array($products)) {
            log_message('error', 'get_random_products: Products is not an array');
            return [];
        }
        
        // Filter out invalid products
        $valid_products = [];
        foreach ($products as $product) {
            if (!is_array($product)) {
                log_message('error', 'get_random_products: Product is not an array');
                continue;
            }
            
            if (!isset($product['id']) || !isset($product['product_name']) || empty($product['product_name'])) {
                log_message('error', 'get_random_products: Product is missing ID or name');
                continue;
            }
            
            $valid_products[] = $product;
        }
        
        // Replace original products with valid ones
        $products = $valid_products;
        
        // Log for debugging
        log_message('debug', 'get_random_products: Starting with ' . count($products) . ' valid products, excluding ' . count($exclude_ids) . ' products');
        
        // Filter out excluded products
        if (!empty($exclude_ids)) {
            $filtered_products = [];
            foreach ($products as $product) {
                if (!in_array($product['id'], $exclude_ids)) {
                    $filtered_products[] = $product;
                }
            }
            $products = $filtered_products;
            
            // If we excluded all products, use the valid products list
            if (empty($products)) {
                log_message('debug', 'get_random_products: All products were excluded, using original valid list');
                $products = $valid_products;
            }
        }
        
        // If we still have no products, return empty array
        if (empty($products)) {
            log_message('debug', 'get_random_products: No valid products available');
            return [];
        }
        
        // Log for debugging
        log_message('debug', 'get_random_products: After filtering, ' . count($products) . ' products remaining');
        
        // Shuffle products
        if (count($products) > 1) {
            shuffle($products);
        }
        
        // Limit to requested count or max available
        $available_count = min($count, count($products));
        $result = array_slice($products, 0, $available_count);
        
        // Log for debugging
        log_message('debug', 'get_random_products: Returning ' . count($result) . ' products');
        
        return $result;
    }

    public function about_us()
    {
        $data['title'] = 'O nama'; 
        $data['keywords'] = 'o nama, tvrtki, uslugama, Profi Line';
        $data['content'] = $this->load->view('front/about_us', [], TRUE); 
        $this->load->view('front/layouts/main', $data); 
    }

    public function products()
    {
        $data['q'] = $this->input->get('q');
        $data['category'] = $this->input->get('category');
        $data['title'] = 'Proizvodi | Profi Line Zagreb'; 
        $data['keywords'] = 'Ograde, Pergola, Staklena ograde, WPC ograde, Metalne ograde, Profili Stakla, Aluminijumska ograda, Dvorišna vrata, Želična ograda, Ekstrudirana masivna, Stubićna, Nadstrešnica';
        
        // Load products module model
        $this->load->model('products/products_model');
        
        // Get product categories from the products module
        $this->load->model('products/product_categories_model');
        $data['product_categories'] = $this->product_categories_model->get();
        
        // Get products from the products module with optional category filter
        if(!empty($data['category'])) {
            $data['products'] = $this->products_model->get_by_category($data['category']);
            
            // If a category is selected, get its name
            $category_found = false;
            foreach($data['product_categories'] as $category) {
                $cat_id = isset($category['id']) ? $category['id'] : (isset($category['p_category_id']) ? $category['p_category_id'] : 0);
                if($cat_id == $data['category']) {
                    $data['category_name'] = isset($category['name']) ? $category['name'] : (isset($category['p_category_name']) ? $category['p_category_name'] : 'Category ' . $data['category']);
                    $data['title'] = $data['category_name'] . ' | Profi Line Zagreb';
                    $category_found = true;
                    break;
                }
            }
            
            // If category not found, provide a fallback
            if (!$category_found) {
                $data['category_name'] = 'Category ' . $data['category'];
            }
        } else {
            $data['products'] = $this->products_model->get_by_id_product();
        }
        
        // Ensure the products array is valid
        if (!isset($data['products']) || !is_array($data['products'])) {
            $data['products'] = [];
            log_message('error', 'Products page: Products is not an array');
        }
        
        // Get recently viewed products
        $recently_viewed_ids = $this->session->userdata('recently_viewed') ?? [];
        $recently_viewed_products = [];
        
        // Get all products for recommended and recently viewed
        $all_products = $this->products_model->get_by_id_product();
        
        // Log for debugging
        log_message('debug', 'Products page: Total products found: ' . count($all_products));
        
        // Make sure we have products
        if (empty($all_products) || !is_array($all_products)) {
            $all_products = [];
            log_message('error', 'Products page: No products found or invalid format');
        }
        
        // If we have recently viewed products, get their details
        if (!empty($recently_viewed_ids)) {
            foreach ($recently_viewed_ids as $product_id) {
                foreach ($all_products as $product) {
                    if (!isset($product['id']) || !isset($product['product_name'])) {
                        continue; // Skip invalid products
                    }
                    
                    if ($product['id'] == $product_id) {
                        $recently_viewed_products[] = $product;
                        break;
                    }
                }
                
                // Limit to 4 products
                if (count($recently_viewed_products) >= 4) {
                    break;
                }
            }
        }
        
        // If no recently viewed products, use random products
        if (empty($recently_viewed_products)) {
            $recently_viewed_products = $this->get_random_products($all_products, 4);
            log_message('debug', 'Products page: Using random recently viewed products, count: ' . count($recently_viewed_products));
        } else {
            log_message('debug', 'Products page: Using session recently viewed products, count: ' . count($recently_viewed_products));
        }
        
        // Get recommended products (different from recently viewed)
        $exclude_ids = !empty($recently_viewed_products) ? array_column($recently_viewed_products, 'id') : [];
        $recommended_products = $this->get_random_products($all_products, 4, $exclude_ids);
        
        // Log for debugging
        log_message('debug', 'Products page: Recommended products count: ' . count($recommended_products));
        if (empty($recommended_products)) {
            log_message('error', 'Products page: No recommended products generated');
            // As a fallback, just use random products if we couldn't get recommended products
            $recommended_products = $this->get_random_products($all_products, 4);
            log_message('debug', 'Products page: Using fallback random recommended products, count: ' . count($recommended_products));
        }
        
        // Pass data to view
        $data['recently_viewed_products'] = $recently_viewed_products;
        $data['recommended_products'] = $recommended_products;
        
        $data['content'] = $this->load->view('front/products', $data, TRUE); // Load the page content
        $this->load->view('front/layouts/main', $data); // Load the main layout
    }

    public function product($slug)
    {
        // Debug information
        log_message('debug', 'Product method called with slug: ' . $slug);
        
        // Only use module products
        $this->load->model('products/products_model');
        $moduleProducts = $this->products_model->get_by_id_product();
        $product = null;
        $moduleProduct = null;
        
        // Debug information for products
        log_message('debug', 'Total products found: ' . count($moduleProducts));
        
        foreach ($moduleProducts as $mod_product) {
            $product_slug = slugify($mod_product['product_name']);
            log_message('debug', 'Comparing product slug: ' . $product_slug . ' with requested slug: ' . $slug);
            
            if($product_slug == $slug) {
                $moduleProduct = $mod_product;
                
                // Create compatible object for the view
                $product = new stdClass();
                $product->description = $mod_product['product_name'];
                $product->long_description = $mod_product['long_description'] ?? '';
                $product->rate = $mod_product['rate'] ?? 0;
                $product->category = $mod_product['p_category_name'] ?? 'Uncategorized';
                log_message('debug', 'Found matching product: ' . $mod_product['product_name']);
                
                // Add to recently viewed products
                $this->add_to_recently_viewed($mod_product['id']);
                
                // Track Facebook Pixel ViewContent event
                if (function_exists('send_facebook_event')) {
                    $custom_data = [
                        'content_type' => 'product',
                        'content_ids' => [$mod_product['id']],
                        'content_name' => $mod_product['product_name'],
                        'content_category' => $mod_product['p_category_name'] ?? 'Uncategorized',
                        'value' => $mod_product['rate'] ?? 0,
                        'currency' => 'EUR'
                    ];
                    send_facebook_event('ViewContent', [], $custom_data);
                }
                
                break;
            }
        }
        
        // If product is not found, redirect to products page
        if (!$product) {
            log_message('debug', 'No matching product found for slug: ' . $slug);
            redirect(site_url('home/products'));
            return;
        }

        $data['title'] = $product->description . ' | Profi Line Zagreb';
        $data['keywords'] = 'Ograde, Metalne ograde, Aluminij, Pocinčani čelik, Sivo staklo, Transparentno, Nehrđajući čelik-inox, Pergola, Nadstrešnica';
        $data['product'] = $product; 
        $data['is_module_product'] = true;
        $data['module_product'] = $moduleProduct;
        
        // If it's a variation product, get variations
        if (isset($moduleProduct['is_variation']) && $moduleProduct['is_variation'] == 1) {
            $data['variations'] = $this->products_model->get_by_id_variations($moduleProduct['id']);
        }
        
        $data['content'] = $this->load->view('front/product_details', $data, TRUE); 
        $this->load->view('front/layouts/main', $data); 
    }
    
    /**
     * Add product to recently viewed list in session
     * 
     * @param int $product_id The product ID to add to recently viewed
     * @return void
     */
    private function add_to_recently_viewed($product_id)
    {
        // Get current recently viewed products from session
        $recently_viewed = $this->session->userdata('recently_viewed');
        
        // If no recently viewed products yet, initialize empty array
        if (!$recently_viewed) {
            $recently_viewed = [];
        }
        
        // Remove the product if it's already in the list (to move it to the top)
        $recently_viewed = array_diff($recently_viewed, [$product_id]);
        
        // Add the current product to the beginning of the array
        array_unshift($recently_viewed, $product_id);
        
        // Limit to 10 products
        $recently_viewed = array_slice($recently_viewed, 0, 10);
        
        // Save back to session
        $this->session->set_userdata('recently_viewed', $recently_viewed);
        
        log_message('debug', 'Added product ID ' . $product_id . ' to recently viewed. Current list: ' . implode(',', $recently_viewed));
    }

    public function contact()
    {
        $data['title'] = 'Contact'; 
        $data['keywords'] = 'kontakt, korisnička podrška, Profi Line';
        $data['content'] = $this->load->view('front/contact', [], TRUE); 
        $this->load->view('front/layouts/main', $data); 
    }

    public function terms_of_use()
    {
        $data['title'] = 'Terms of Use'; 
        $data['keywords'] = 'uvjeti korištenja, korisnički ugovor, pravila web stranice, pravni uvjeti, politika privatnosti, uvjeti korištenja, Profi Line';
        $data['content'] = $this->load->view('front/terms_of_use', [], TRUE); 
        $this->load->view('front/layouts/main', $data); 
    }

    public function privacy_policy()
    {
        $data['title'] = 'Privacy Policy'; 
        $data['keywords'] = 'politika privatnosti, zaštita podataka, osobni podaci, privatnost korisnika, sigurnosna politika, Profi Line, politika kolacića';
        $data['content'] = $this->load->view('front/privacy_policy', [], TRUE); 
        $this->load->view('front/layouts/main', $data); 
    }

    public function proposal()
    {
        $this->load->model('proposals_model');
        
        if($this->input->post('proposal')) {
            
            $this->proposals_model->add($_POST);
            
            $this->session->set_flashdata('modal_message', [
                'type' => 'success',
                'text' => 'Uspješno ste izvršili narudžbu!'
            ]);

            redirect($_SERVER['HTTP_REFERER']);
         }
    }

    public function cart()
    {
        // Load required models
        $this->load->model('products/products_model');
        $this->load->model('products/order_model');
        $this->load->model('currencies_model');
        
        // Get cart data from session
        $cart_data = isset($_SESSION['cart_data']) ? $_SESSION['cart_data'] : [];
        
        // Prepare tracking data for Facebook Pixel
        $fb_products = [];
        
        // Prepare view data
        $data = [];
        $data['title'] = 'Košarica | Profi Line Zagreb';
        $data['keywords'] = 'Košarica, Kupovina, Ograde, Dostava, Web Shop';
        
        // Initialize variables
        $message = '';
        $total = 0;
        $products = [];
        $all_taxes = [];
        $init_tax = [];
        
        // Process cart data if not empty
        if (!empty($cart_data)) {
            // Get products from cart data
            $products = $this->products_model->get_by_cart_product($cart_data);
            
            // Process product data for display
            foreach ($products as $key => $product) {
                // Skip if quantity is zero
                if (empty($product->quantity)) continue;
                
                // Check stock for non-digital products
                if (!$product->is_digital) {
                    if ((int)$product->quantity_number < 1) {
                        $message .= $product->product_name . ' je rasprodano i uklonjeno iz košarice <br>';
                        continue;
                    }
                    
                    if ((int)$product->quantity > (int)$product->quantity_number) {
                        $product->quantity = $product->quantity_number;
                        $message .= $product->product_name . ' je dostupno samo ' . $product->quantity_number . ' komada <br>';
                    }
                }
                
                // Process taxes
                $taxes_arr = [];
                $product->taxname = $taxes = unserialize($product->taxes);
                
                if ($taxes) {
                    foreach ($taxes as $tax) {
                        if (!is_array($tax)) {
                            $tmp_taxname = $tax;
                            $tax_array = explode('|', $tax);
                        } else {
                            $tax_array = explode('|', $tax['taxname']);
                            $tmp_taxname = $tax['taxname'];
                            if ('' == $tmp_taxname) {
                                continue;
                            }
                        }
                        
                        // Make sure we're using the correct rate for tax calculations
                        $item_price = 0;
                        if (isset($product->direct_price) && $product->direct_price > 0) {
                            $item_price = $product->direct_price;
                            log_message('debug', 'CART: Using direct_price for tax: ' . $item_price);
                        } else if (isset($product->product_variation_id) && isset($product->variation_rate) && $product->variation_rate > 0) {
                            $item_price = $product->variation_rate;
                            log_message('debug', 'CART: Using variation_rate for tax: ' . $item_price);
                        } else if (isset($product->rate) && $product->rate > 0) {
                            $item_price = $product->rate;
                            log_message('debug', 'CART: Using base rate for tax: ' . $item_price);
                        }
                        
                        $init_tax[$tmp_taxname][] = ($item_price * $product->quantity) / 100 * $tax_array[1];
                        $all_taxes[$tmp_taxname] = $taxes_arr[] = [
                            'name' => $tmp_taxname,
                            'taxrate' => $tax_array[1],
                            'taxname' => $tax_array[0]
                        ];
                    }
                }
                
                $product->taxes = $taxes_arr;
                
                // Add to total - use the correct price field with proper fallbacks
                $item_price = 0;
                if (isset($product->direct_price) && $product->direct_price > 0) {
                    $item_price = $product->direct_price;
                    log_message('debug', 'CART: Using direct_price for total: ' . $item_price . ' for product: ' . $product->id);
                } else if (isset($product->product_variation_id) && isset($product->variation_rate) && $product->variation_rate > 0) {
                    $item_price = $product->variation_rate;
                    log_message('debug', 'CART: Using variation_rate for total: ' . $item_price . ' for product: ' . $product->id);
                } else if (isset($product->rate) && $product->rate > 0) {
                    $item_price = $product->rate;
                    log_message('debug', 'CART: Using base rate for total: ' . $item_price . ' for product: ' . $product->id);
                }
                
                // Debug log the product price
                log_message('debug', 'Cart product: ' . $product->product_name . ' - Price: ' . $item_price . ' - Quantity: ' . $product->quantity);
                
                $total += $product->quantity * $item_price;
                
                // Prepare product data for Facebook Pixel tracking
                if (is_array($product)) {
                    // Handle array format
                    $fb_products[] = [
                        'id' => $product['id'],
                        'name' => $product['product_name'],
                        'category' => $product['p_category_name'] ?? 'Uncategorized',
                        'price' => $item_price,
                        'quantity' => isset($product['quantity']) ? $product['quantity'] : $key['quantity']
                    ];
                } else {
                    // Handle object format
                    $fb_products[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'category' => isset($product->p_category_name) ? $product->p_category_name : 'Uncategorized',
                        'price' => $item_price,
                        'quantity' => $product->quantity
                    ];
                }
            }
            
            // Debug log the total
            log_message('debug', 'Cart total: ' . $total);
        }
        
        // Track InitiateCheckout event with Facebook Pixel
        if (function_exists('track_facebook_initiate_checkout') && !empty($fb_products)) {
            track_facebook_initiate_checkout($total, 'EUR', $fb_products);
            log_message('debug', 'Facebook Pixel: Tracked InitiateCheckout event with total: ' . $total);
        }
        
        // Calculate shipping if applicable
        $shipping_cost = 0;
        $base_shipping_cost = 0;
        $shipping_tax = 0;
        
        // Check if any product requires shipping
        $apply_shipping = false;
        foreach ($products as $product) {
            if (!$product->is_digital && !$product->recurring) {
                $apply_shipping = true;
                break;
            }
        }
        
        if ($apply_shipping) {
            $taxname = (!empty((get_option('product_tax_for_shipping_cost')))) ? unserialize(get_option('product_tax_for_shipping_cost')) : '';
            $shipping_cost = $base_shipping_cost = get_option('product_flat_rate_shipping');
            $shipping_tax = 0;
            
            if ($taxname) {
                foreach ($taxname as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array = explode('|', $tax);
                    } else {
                        $tax_array = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    
                    $shipping_tax += $tax_array[1];
                    $shipping_cost += ($base_shipping_cost) / 100 * $tax_array[1];
                }
            }
        }
        
        // Get client data for pre-filling checkout form if logged in
        $client = null;
        $contact = null;
        
        if (is_client_logged_in()) {
            $client = $this->clients_model->get($this->session->client_user_id);
            // Get contact data
            $contact = $this->clients_model->get_contact(get_primary_contact_user_id($this->session->client_user_id));
        }
        
        // Add data to view
        $data['products'] = $products;
        $data['all_taxes'] = $all_taxes;
        $data['init_tax'] = $init_tax;
        $data['total'] = $total;
        $data['shipping_cost'] = $shipping_cost;
        $data['base_shipping_cost'] = $base_shipping_cost;
        $data['shipping_tax'] = $shipping_tax;
        $data['message'] = $message;
        $data['client'] = $client;
        $data['contact'] = $contact;
        $data['is_logged_in'] = is_client_logged_in();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        
        $data['content'] = $this->load->view('front/cart_page', $data, TRUE);
        $this->load->view('front/layouts/main', $data);
    }

    /**
     * Process checkout form and create an order
     */
    public function checkout()
    {
        // Check if cart is empty
        if (!isset($_SESSION['cart_data']) || empty($_SESSION['cart_data'])) {
            redirect(site_url('home/cart'));
        }
        
        // Load models
        $this->load->model('currencies_model');
        $this->load->model('products/products_model');
        $this->load->model('products/order_model');
        
        // Load Facebook Pixel helper
        $this->load->helper('facebook_pixel');
        
        // Get cart data
        $cart_data = $_SESSION['cart_data'];
        $cart_total = 0;
        $products = [];
        
        // Prepare tracking data for Facebook Pixel
        $fb_products = [];
        
        // Initialize app_object_cache if it doesn't exist (for Currencies_model)
        if (!isset($this->app_object_cache)) {
            $this->app_object_cache = new stdClass();
        }
        
        // Debug logging for cart data
        log_message('debug', 'CHECKOUT START: Processing cart with ' . count($cart_data) . ' items');
        
        // Get base currency - avoid using get_base_currency which might be causing issues
        $this->db->where('isdefault', 1);
        $base_currency = $this->db->get(db_prefix() . 'currencies')->row();
        
        if (!$base_currency) {
            // Fallback if no default currency is set
            $base_currency = new stdClass();
            $base_currency->symbol = '???';
            $base_currency->name = 'EUR';
        }
        
        log_message('debug', 'CHECKOUT: Base currency found: ' . $base_currency->name);
        
        foreach ($cart_data as $item) {
            // Get product info
            $product = $this->products_model->get_by_id_product($item['product_id']);
            if (!$product) {
                continue;
            }
            
            // Add quantity
            $product->quantity = $item['quantity'];
            
            // Add variation details if applicable
            if (isset($item['product_variation_id']) && !empty($item['product_variation_id'])) {
                // Use aliases for cleaner queries
                $this->db->select('pv.*, v.name as variation_name, vv.value as variation_value');
                $this->db->from(db_prefix() . 'product_variations pv');
                $this->db->join(db_prefix() . 'variations v', 'v.id = pv.variation_id', 'left');
                $this->db->join(db_prefix() . 'variation_values vv', 'vv.id = pv.variation_value_id', 'left');
                $this->db->where('pv.id', $item['product_variation_id']);
                
                $variation = $this->db->get()->row();
                
                if ($variation) {
                    $product->product_variation_id = $variation->id;
                    $product->variation_name = $variation->variation_name;
                    $product->variation_value = $variation->variation_value;
                    $product->variation_rate = $variation->rate;
                }
            }
            
            // IMPORTANT: Correctly handle prices from cart data
            // Check for calculated_price first (from custom calculations)
            if (isset($item['calculated_price']) && !empty($item['calculated_price'])) {
                $product->direct_price = floatval($item['calculated_price']);
                log_message('debug', 'CHECKOUT: Using calculated_price from cart: ' . $product->direct_price . ' for product ' . $product->id);
            }
            // Then check for direct_price
            else if (isset($item['direct_price']) && !empty($item['direct_price'])) {
                $product->direct_price = floatval($item['direct_price']);
                log_message('debug', 'CHECKOUT: Using direct_price from cart: ' . $product->direct_price . ' for product ' . $product->id);
            }
            // Otherwise use variation or base rate
            else if (isset($product->variation_rate) && $product->variation_rate > 0) {
                // Don't override direct_price if already set
                if (!isset($product->direct_price)) {
                    $product->direct_price = floatval($product->variation_rate);
                    log_message('debug', 'CHECKOUT: Using variation_rate: ' . $product->direct_price . ' for product ' . $product->id);
                }
            } else if (isset($product->rate) && $product->rate > 0) {
                // Don't override direct_price if already set
                if (!isset($product->direct_price)) {
                    $product->direct_price = floatval($product->rate);
                    log_message('debug', 'CHECKOUT: Using base rate: ' . $product->direct_price . ' for product ' . $product->id);
                }
            }
            
            // Format direct_price with 2 decimal places for consistency
            if (isset($product->direct_price)) {
                $product->direct_price = number_format($product->direct_price, 2, '.', '');
            }
            
            // Check the final price being used
            $final_price = isset($product->direct_price) ? $product->direct_price : 
                          (isset($product->variation_rate) ? $product->variation_rate : $product->rate);
            log_message('debug', 'CHECKOUT: Final price for product ' . $product->id . ': ' . $final_price);
            
            // Add selected height if present
            if (isset($item['selected_height']) && !empty($item['selected_height'])) {
                $product->selected_height = $item['selected_height'];
            }
            
            // Add selected material if present
            if (isset($item['selected_material']) && !empty($item['selected_material'])) {
                $product->selected_material = $item['selected_material'];
            }
            
            // Add selected glass if present
            if (isset($item['selected_glass']) && !empty($item['selected_glass'])) {
                $product->selected_glass = $item['selected_glass'];
            }
            
            // Add to total using the correct price
            $item_price = isset($product->direct_price) ? $product->direct_price : 
                         (isset($product->variation_rate) ? $product->variation_rate : $product->rate);
            $cart_total += ($item_price * $item['quantity']);
            
            // Make sure quantity is an integer and at least 1
            $product->quantity = max(1, (int)$product->quantity);
            
            // CRITICAL: Also update the quantity in the original item to ensure it's passed correctly
            $item['quantity'] = $product->quantity;
            
            // Add qty alias for compatibility with other code
            $product->qty = $product->quantity;
            $item['qty'] = $item['quantity'];
            
            log_message('debug', 'CHECKOUT ITEM FINAL: Product ID=' . $product->id . 
                ', Quantity=' . $product->quantity . 
                ', Price=' . $item_price . 
                ', Line Total=' . ($item_price * $product->quantity));
            
            // Add to products array
            $products[] = $product;
            
            // Prepare product data for Facebook Pixel tracking
            if (is_array($product)) {
                // Handle array format
                $fb_products[] = [
                    'id' => $product['id'],
                    'name' => $product['product_name'],
                    'category' => $product['p_category_name'] ?? 'Uncategorized',
                    'price' => $item_price,
                    'quantity' => isset($product['quantity']) ? $product['quantity'] : $item['quantity']
                ];
            } else {
                // Handle object format
                $fb_products[] = [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'category' => isset($product->p_category_name) ? $product->p_category_name : 'Uncategorized',
                    'price' => $item_price,
                    'quantity' => $product->quantity
                ];
            }
        }
        
        // Get applied coupon from session
        $coupon_discount = 0;
        if ($this->session->has_userdata('applied_coupon')) {
            $coupon = $this->session->userdata('applied_coupon');
            $coupon_discount = isset($coupon['discount_amount']) ? $coupon['discount_amount'] : 0;
        }
        
        // Track InitiateCheckout event with Facebook Pixel
        if (function_exists('track_facebook_initiate_checkout') && !empty($fb_products)) {
            track_facebook_initiate_checkout($cart_total, 'EUR', $fb_products);
            log_message('debug', 'Facebook Pixel: Tracked InitiateCheckout event with total: ' . $cart_total);
        }
        
        // Calculate shipping cost - fixed for now
        $shipping_cost = 0;
        $shipping_tax = 0;
        $base_shipping_cost = 0;
        
        // Get base currency
        $base_currency = $this->currencies_model->get_base_currency();
        
        // Check if form is submitted
        if ($this->input->post()) {
            // Log that the form was submitted for debugging
            log_message('debug', 'CHECKOUT: Form submitted');
            
            // Get form data
            $billing_firstname = $this->input->post('billing_firstname');
            $billing_lastname = $this->input->post('billing_lastname');
            $billing_street = $this->input->post('billing_street');
            $billing_city = $this->input->post('billing_city');
            $billing_state = $this->input->post('billing_state');
            $billing_zip = $this->input->post('billing_zip');
            $billing_country = $this->input->post('billing_country');
            $billing_phone = $this->input->post('billing_phone');
            $billing_email = $this->input->post('billing_email');
            $order_notes = $this->input->post('order_notes');
            
            // Log form data for debugging
            log_message('debug', 'CHECKOUT FORM DATA: ' . 
                'First Name: ' . $billing_firstname . 
                ', Last Name: ' . $billing_lastname . 
                ', Email: ' . $billing_email . 
                ', Phone: ' . $billing_phone);
            
            // Validate required fields
            if (empty($billing_firstname) || empty($billing_lastname) || empty($billing_street) || 
                empty($billing_city) || empty($billing_zip) || empty($billing_country) || 
                empty($billing_phone) || empty($billing_email)) {
                $data['message'] = 'Molimo popunite sva obavezna polja.';
                log_message('error', 'CHECKOUT ERROR: Required fields missing');
                $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                return;
            }
            
            // Validate email
            if (!filter_var($billing_email, FILTER_VALIDATE_EMAIL)) {
                $data['message'] = 'Unesite ispravnu email adresu.';
                log_message('error', 'CHECKOUT ERROR: Invalid email format');
                $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                return;
            }
            
            // Get product items from form
            $product_items = $this->input->post('product_items');
            
            // Log product items for debugging
            log_message('debug', 'CHECKOUT PRODUCT ITEMS COUNT: ' . (is_array($product_items) ? count($product_items) : 'not an array'));
            
            // Check if there are items
            if (empty($product_items)) {
                $data['message'] = 'Vaša košarica je prazna.';
                log_message('error', 'CHECKOUT ERROR: No product items in form data');
                $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                return;
            }
            
            // Check if user wants to create an account
            $create_account = $this->input->post('create_account');
            $account_option = $this->input->post('account_option');
            $password = $this->input->post('password');
            
            // Load clients model
            $this->load->model('clients_model');
            
            // Create or find client
            $client_id = null;
            $contact = $this->clients_model->get_contact_by_email($billing_email);
            
            log_message('debug', 'CHECKOUT: Checking for existing contact with email: ' . $billing_email);
            
            if ($contact) {
                // Use existing client ID
                $client_id = $contact->userid;
                log_message('debug', 'CHECKOUT: Found existing client with ID: ' . $client_id);
            
                // Log user in if not logged in
                if (!is_client_logged_in()) {
                    // Check if this user should have an account
                    if ($account_option == 'register' && $create_account == 1) {
                        $data['message'] = 'Email adresa već postoji. Molimo prijavite se ili koristite drugu email adresu.';
                        log_message('error', 'CHECKOUT ERROR: Email already exists but user wants to register');
                        $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                        return;
                    }
                }
            } else {
                // Create new client
                log_message('debug', 'CHECKOUT: Creating new client');
                        $client_data = [
                    'company' => $billing_firstname . ' ' . $billing_lastname,
                    'billing_street' => $billing_street,
                    'billing_city' => $billing_city,
                    'billing_state' => $billing_state,
                    'billing_zip' => $billing_zip,
                    'billing_country' => $billing_country,
                    'shipping_street' => $billing_street,
                    'shipping_city' => $billing_city,
                    'shipping_state' => $billing_state,
                    'shipping_zip' => $billing_zip,
                    'shipping_country' => $billing_country,
                    'phonenumber' => $billing_phone,
                        ];
                        
                        $contact_data = [
                    'firstname' => $billing_firstname,
                    'lastname' => $billing_lastname,
                    'email' => $billing_email,
                    'phonenumber' => $billing_phone,
                ];
                
                // If user wants to create an account
                if ($account_option == 'register' && $create_account == 1) {
                    if (empty($password) || strlen($password) < 6) {
                        $data['message'] = 'Lozinka mora imati najmanje 6 znakova.';
                        log_message('error', 'CHECKOUT ERROR: Password too short');
                        $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                        return;
                    }
                    
                    $contact_data['password'] = $password;
                    $contact_data['donotsendwelcomeemail'] = false;
                    log_message('debug', 'CHECKOUT: Creating account with password');
                } else {
                    $contact_data['donotsendwelcomeemail'] = true;
                    log_message('debug', 'CHECKOUT: Creating client without account');
                }
                
                // Add client with contact
                $client_id = $this->clients_model->add($client_data, true);
                log_message('debug', 'CHECKOUT: Created new client with ID: ' . $client_id);
                
                if (!$client_id) {
                    $data['message'] = 'Došlo je do greške prilikom kreiranja korisničkog računa.';
                    log_message('error', 'CHECKOUT ERROR: Failed to create client');
                    $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                    return;
                }
                
                // Add primary contact
                $contact_id = $this->clients_model->add_contact($contact_data, $client_id, true);
                log_message('debug', 'CHECKOUT: Added contact with ID: ' . $contact_id);
                
                if (!$contact_id) {
                    $data['message'] = 'Došlo je do greške prilikom dodavanja kontakta.';
                    log_message('error', 'CHECKOUT ERROR: Failed to add contact');
                    $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                    return;
                }
            }
            
            // Format product items for order
            $cart_items = [];
            
            // Log full cart_data for debugging
            log_message('debug', 'CHECKOUT CART DATA: ' . json_encode($cart_data));
            
            foreach ($product_items as $key => $item) {
                $product_id = $item['product_id'];
                
                // Check if we have qty or quantity field
                $quantity = isset($item['qty']) ? $item['qty'] : (isset($item['quantity']) ? $item['quantity'] : 1);
                $quantity = max(1, (int)$quantity); // Ensure it's an integer and at least 1
                
                log_message('debug', 'CHECKOUT POST: Processing product ' . $product_id . ' with quantity ' . $quantity);
                
                // Get product details
                $product = $this->products_model->get_by_id_product($product_id);
                if (!$product) {
                    log_message('error', 'CHECKOUT ERROR: Product not found - ID: ' . $product_id);
                    continue;
                }
                
                $cart_item = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'qty' => $quantity,  // Include both qty and quantity for compatibility
                ];
                
                // Add variation if present
                if (isset($item['product_variation_id']) && !empty($item['product_variation_id'])) {
                    $cart_item['product_variation_id'] = $item['product_variation_id'];
                    log_message('debug', 'CHECKOUT POST: Added variation ID ' . $cart_item['product_variation_id'] . ' to cart item');
                }
                
                // Add selected variants if present
                if (isset($item['selected_height'])) {
                    $cart_item['selected_height'] = $item['selected_height'];
                }
                
                // Add selected material if present
                if (isset($item['selected_material'])) {
                    $cart_item['selected_material'] = $item['selected_material'];
                }
                
                // Add selected glass if present
                if (isset($item['selected_glass'])) {
                    $cart_item['selected_glass'] = $item['selected_glass'];
                }
                
                // Add any custom or calculated price if present
                if (isset($item['direct_price']) && !empty($item['direct_price'])) {
                    $cart_item['direct_price'] = number_format(floatval($item['direct_price']), 2, '.', '');
                    log_message('debug', 'ORDER: Added direct_price ' . $cart_item['direct_price'] . ' to cart_item for product ' . $product_id);
                } else if (isset($item['calculated_price']) && !empty($item['calculated_price'])) {
                    $cart_item['direct_price'] = number_format(floatval($item['calculated_price']), 2, '.', '');
                    log_message('debug', 'ORDER: Added calculated_price as direct_price ' . $cart_item['direct_price'] . ' to cart_item for product ' . $product_id);
                } else {
                    // If we don't have a direct_price in the form field, check the original cart data
                    foreach ($cart_data as $orig_item) {
                        if ($orig_item['product_id'] == $product_id) {
                            // Check for matching variation ID if both have one
                            $variation_matches = true;
                            if (isset($item['product_variation_id']) && isset($orig_item['product_variation_id'])) {
                                $variation_matches = ($item['product_variation_id'] == $orig_item['product_variation_id']);
                            }
                            
                            if ($variation_matches) {
                                // Check for calculated_price first, then direct_price
                                if (isset($orig_item['calculated_price']) && !empty($orig_item['calculated_price'])) {
                                    $cart_item['direct_price'] = number_format(floatval($orig_item['calculated_price']), 2, '.', '');
                                    log_message('debug', 'ORDER: Retrieved calculated_price ' . $cart_item['direct_price'] . ' from original cart data for product ' . $product_id);
                                    break;
                                } else if (isset($orig_item['direct_price']) && !empty($orig_item['direct_price'])) {
                                    $cart_item['direct_price'] = number_format(floatval($orig_item['direct_price']), 2, '.', '');
                                    log_message('debug', 'ORDER: Retrieved direct_price ' . $cart_item['direct_price'] . ' from original cart data for product ' . $product_id);
                                    break;
                                }
                            }
                        }
                    }
                }
                
                // Final log of the cart item before adding it
                log_message('debug', 'FINAL CART ITEM: ' . json_encode($cart_item));
                
                $cart_items[] = $cart_item;
            }
            
            // Calculate total with tax
            $subtotal = $cart_total;
            $tax_amount = $subtotal * 0.25; // 25% VAT
            $final_total = ($subtotal + $tax_amount + $shipping_cost) - $coupon_discount;
            
            // Format order data
            $order_data = [
                'clientid' => $client_id,
                'billing_street' => $billing_street,
                'billing_city' => $billing_city,
                'billing_state' => $billing_state,
                'billing_zip' => $billing_zip,
                'billing_country' => $billing_country,
                'billing_phone' => $billing_phone,
                'billing_email' => $billing_email,
                'shipping_street' => $billing_street,
                'shipping_city' => $billing_city,
                'shipping_state' => $billing_state,
                'shipping_zip' => $billing_zip,
                'shipping_country' => $billing_country,
                'payment_method' => 'bank',
                'product_items' => $cart_items,
                'coupon_id' => $this->input->post('coupon_id'),
                'order_note' => $order_notes,
                'total_tax' => $tax_amount,
                'subtotal' => $subtotal,
                'coupon_discount' => $coupon_discount,
                'total' => $final_total,
                'adminnote' => 'Quote Request', // Use adminnote instead of is_request_quote
            ];
            
            // Log order data for debugging
            log_message('debug', 'CHECKOUT: Order data prepared, client_id=' . $client_id . ', items_count=' . count($cart_items) . ', total=' . $final_total);
            
            // Process order
            $this->db->trans_start();
            log_message('debug', 'CHECKOUT: Transaction started');
            
            try {
                // Debug checkpoint before calling add_invoice_order
                log_message('debug', 'CHECKOUT: About to call add_invoice_order');
                
                $return_data = $this->order_model->add_invoice_order($order_data);
                
                // Debug after add_invoice_order
                log_message('debug', 'CHECKOUT: add_invoice_order returned: ' . json_encode($return_data));
                
                if (!$return_data['status']) {
                    $this->db->trans_rollback();
                    $data['message'] = 'Došlo je do greške prilikom obrade narudžbe: ' . $return_data['message'];
                    log_message('error', 'CHECKOUT ERROR: Order processing failed: ' . $return_data['message']);
                    $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                    return;
                }
                
                // Commit transaction
                $this->db->trans_commit();
                log_message('debug', 'CHECKOUT: Transaction committed successfully');
                
                // Debug checkpoint - order created successfully
                log_message('debug', 'CHECKOUT: Order created successfully with invoice ID: ' . $return_data['invoice_id']);
                
                // Track Purchase event with Facebook Pixel
                if (function_exists('track_facebook_purchase') && !empty($fb_products)) {
                    track_facebook_purchase($final_total, 'EUR', $fb_products, [
                        'email' => hash_facebook_user_data(['email' => $billing_email]),
                        'phone' => hash_facebook_user_data(['phone' => $billing_phone]),
                        'city' => hash_facebook_user_data(['city' => $billing_city]),
                        'country' => hash_facebook_user_data(['country' => $billing_country])
                    ]);
                    log_message('debug', 'Facebook Pixel: Tracked Purchase event with total: ' . $final_total);
                }
                
                // Log before redirect
                log_message('debug', 'CHECKOUT: About to redirect to success page, invoice_id=' . $return_data['invoice_id']);
                log_message('debug', 'CHECKOUT: Redirect URL: ' . site_url('home/quote_success?invoice=' . $return_data['invoice_id'] . '&hash=' . $return_data['invoice_hash']));
                
                // Clean up the output buffer only if it's started
                if (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Clear the session data before redirecting
                unset($_SESSION['cart_data']);
                $this->session->unset_userdata('applied_coupon');
                
                // Use CI's redirect function instead of header
                redirect(site_url('home/quote_success?invoice=' . $return_data['invoice_id'] . '&hash=' . $return_data['invoice_hash']), 'location', 302);
                exit; // Ensure execution stops here
                
            } catch (Exception $e) {
                // Catch any exceptions during order processing
                $this->db->trans_rollback();
                $data['message'] = 'Došlo je do greške prilikom obrade narudžbe. Pokušajte ponovno kasnije.';
                log_message('error', 'CHECKOUT EXCEPTION: ' . $e->getMessage());
                $this->render_checkout_page($data, $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
                return;
            }
        }
        
        // If not submitted, render form using helper method
        $this->render_checkout_page([], $products, $cart_total, $shipping_cost, $shipping_tax, $base_shipping_cost);
    }
    
    /**
     * Helper method to render checkout page
     */
    private function render_checkout_page($data = [], $products = [], $cart_total = 0, $shipping_cost = 0, $shipping_tax = 0, $base_shipping_cost = 0)
    {
        // Check if user is logged in
        $is_logged_in = is_client_logged_in();
        $contact = null;
        $client = null;
        
        if ($is_logged_in) {
            $contact = get_current_contact();
            $this->load->model('clients_model');
            $client = $this->clients_model->get($contact->userid);
        }
        
        // Load currencies model if not already loaded
        if (!isset($this->currencies_model)) {
            $this->load->model('currencies_model');
        }
        
        // Load base currency
        $base_currency = $this->currencies_model->get_base_currency();
        
        // Prepare view data
        $data['title'] = 'Ponuda | Profi Line Zagreb';
        $data['keywords'] = 'Ponuda, Narudžba, Web Shop';
        $data['products'] = $products;
        $data['total'] = $cart_total;
        $data['base_currency'] = $base_currency;
        $data['shipping_cost'] = $shipping_cost;
        $data['shipping_tax'] = $shipping_tax;
        $data['base_shipping_cost'] = $base_shipping_cost;
        $data['is_logged_in'] = $is_logged_in;
        $data['contact'] = $contact;
        $data['client'] = $client;
        
        $data['content'] = $this->load->view('front/checkout_page', $data, TRUE); 
        $this->load->view('front/layouts/main', $data); 
    }
    
    /**
     * Success page for quote requests
     */
    public function quote_success()
    {
        $invoice_id = $this->input->get('invoice');
        $hash = $this->input->get('hash');
        
        // Get the actual invoice number from the invoices table
        $invoice_number = '';
        if ($invoice_id) {
            $this->load->model('invoices_model');
            $invoice = $this->invoices_model->get($invoice_id);
            if ($invoice) {
                $invoice_number = $invoice->number;
            }
        }
        
        $data = [];
        $data['title'] = 'Zahtjev za ponudu | Profi Line Zagreb';
        $data['keywords'] = 'Ponuda, Zahtjev, Ograde, Web Shop';
        $data['invoice_id'] = $invoice_id;
        $data['invoice_number'] = $invoice_number;
        $data['hash'] = $hash;
        
        $data['content'] = $this->load->view('front/quote_success', $data, TRUE); 
        $this->load->view('front/layouts/main', $data);
    }
    
    /**
     * Update cart item from AJAX request
     * This method is called from the cart and checkout pages when quantity is changed
     */
    public function update_cart_item()
    {
        $product_id = $this->input->post('product_id');
        $variation_id = $this->input->post('variation_id');
        $quantity = $this->input->post('quantity');
        
        log_message('debug', 'update_cart_item called with product_id: ' . $product_id . ', variation_id: ' . $variation_id . ', quantity: ' . $quantity);
        
        if (!$product_id || !$quantity) {
            log_message('error', 'update_cart_item missing required data');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid product ID or quantity'
            ]);
            return;
        }
        
        if (!is_numeric($quantity) || $quantity <= 0) {
            log_message('error', 'update_cart_item invalid quantity: ' . $quantity);
            echo json_encode([
                'success' => false,
                'message' => 'Quantity must be a positive number'
            ]);
            return;
        }
        
        // Get current cart data directly from $_SESSION
        if (!isset($_SESSION['cart_data'])) {
            $_SESSION['cart_data'] = [];
        }
        
        $cart_data = $_SESSION['cart_data'];
        log_message('debug', 'Current cart data count: ' . count($cart_data));
        
        // Debug log all cart items
        foreach ($cart_data as $index => $item) {
            $item_variation = isset($item['product_variation_id']) ? $item['product_variation_id'] : 'none';
            log_message('debug', "CART ITEM [{$index}]: product_id={$item['product_id']}, variation_id={$item_variation}, quantity={$item['quantity']}");
        }
        
        // Normalize variation ID to avoid comparison issues
        $variation_id = empty($variation_id) ? '' : $variation_id;
        
        // Update the quantity for the matching product
        $updated = false;
        foreach ($cart_data as $key => $item) {
            $item_variation_id = isset($item['product_variation_id']) ? $item['product_variation_id'] : '';
            log_message('debug', 'Checking cart item: product_id=' . $item['product_id'] . ', variation_id=' . $item_variation_id);
            
            // Check if product IDs match
            if ($item['product_id'] == $product_id) {
                log_message('debug', 'Found matching product_id');
                
                // Simple variation matching logic: if either both have the same variation ID or both have no variation ID
                $variation_matches = ($item_variation_id == $variation_id);
                log_message('debug', 'Variation comparison: item=' . $item_variation_id . ', input=' . $variation_id . ', match=' . ($variation_matches ? 'yes' : 'no'));
                
                // If we have a match, update the quantity
                if ($variation_matches) {
                    log_message('debug', 'Updating quantity from ' . $item['quantity'] . ' to ' . $quantity);
                    
                    // Update quantity directly in the $_SESSION array
                    $_SESSION['cart_data'][$key]['quantity'] = (int)$quantity;
                    $updated = true;
                    break;
                }
            }
        }
        
        // Save updated cart data
        if ($updated) {
            log_message('debug', 'Cart updated successfully with new quantity');
            
            // Force session write
            session_write_close();
            session_start();
            
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_data' => $_SESSION['cart_data']
            ]);
        } else {
            log_message('error', 'Product not found in cart: product_id=' . $product_id . ', variation_id=' . $variation_id);
            
            // Debug log to help troubleshoot
            log_message('debug', 'CART DATA DUMP: ' . json_encode($_SESSION['cart_data']));
            
            echo json_encode([
                'success' => false,
                'message' => 'Product not found in cart'
            ]);
        }
    }

    /**
     * Validate coupon code via AJAX
     */
    public function validate_coupon()
    {
        // Check if request is AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model('coupons_model');
        $coupon_code = $this->input->post('coupon_code');
        
        if (empty($coupon_code)) {
            $response = [
                'success' => false,
                'message' => 'Unesite kod kupona'
            ];
        } else {
            // Validate coupon
            $coupon = $this->coupons_model->get_by_code($coupon_code);
            
            if (!$coupon) {
                $response = [
                    'success' => false,
                    'message' => 'Nevažeći kod kupona'
                ];
            } else if ($coupon->is_expired) {
                $response = [
                    'success' => false,
                    'message' => 'Kupon je istekao'
                ];
            } else if ($coupon->usage_limit > 0 && $coupon->times_used >= $coupon->usage_limit) {
                $response = [
                    'success' => false,
                    'message' => 'Kupon je iskorišten maksimalan broj puta'
                ];
            } else {
                // Get cart items from session
                $cart_items = $this->session->userdata('cart_items');
                $cart_total = $this->session->userdata('cart_total');
                
                if (empty($cart_items) || $cart_total <= 0) {
                    $response = [
                        'success' => false,
                        'message' => 'Vaša košarica je prazna'
                    ];
                } else {
                    // Check minimum order amount if applicable
                    if ($coupon->min_order_amount > 0 && $cart_total < $coupon->min_order_amount) {
                        $response = [
                            'success' => false,
                            'message' => 'Minimalni iznos narudžbe za ovaj kupon je ' . app_format_money($coupon->min_order_amount)
                        ];
                    } else {
                        // Calculate discount amount
                        $discount_amount = 0;
                        
                        if ($coupon->type == 'percentage') {
                            $discount_amount = ($cart_total * $coupon->discount_value) / 100;
                            if ($coupon->max_discount_amount > 0 && $discount_amount > $coupon->max_discount_amount) {
                                $discount_amount = $coupon->max_discount_amount;
                            }
                        } else { // Fixed amount
                            $discount_amount = $coupon->discount_value;
                            if ($discount_amount > $cart_total) {
                                $discount_amount = $cart_total;
                            }
                        }
                        
                        // Save coupon data to session
                        $this->session->set_userdata('applied_coupon', [
                            'id' => $coupon->id,
                            'code' => $coupon->code,
                            'discount_amount' => $discount_amount
                        ]);
                        
                        // Calculate grand total
                        $grand_total = $cart_total - $discount_amount;
                        
                        $response = [
                            'success' => true,
                            'message' => 'Kupon uspješno primijenjen',
                            'coupon_id' => $coupon->id,
                            'cart_total' => app_format_money($cart_total),
                            'discount_amount' => app_format_money($discount_amount),
                            'grand_total' => app_format_money($grand_total)
                        ];
                    }
                }
            }
        }
        
        echo json_encode($response);
    }
    
    /**
     * Reset applied coupon
     */
    public function reset_coupon()
    {
        // Check if request is AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        // Remove coupon from session
        $this->session->unset_userdata('applied_coupon');
        
        // Get cart total
        $cart_total = $this->session->userdata('cart_total');
        
        $response = [
            'success' => true,
            'cart_total' => app_format_money($cart_total),
            'grand_total' => app_format_money($cart_total)
        ];
        
        echo json_encode($response);
    }

    /**
     * Handle offer request submissions
     * Creates an order in the products module
     * @return void
     */
    public function submit_offer_request()
    {
        // Check if request is POST
        if (!$this->input->post()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        // Get post data
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $phone = $this->input->post('phone');
        $company = $this->input->post('company') ?: '';
        $message = $this->input->post('message') ?: '';
        $address = $this->input->post('address') ?: '';
        $city = $this->input->post('city') ?: '';
        $zip = $this->input->post('zip') ?: '';
        $product_id = $this->input->post('product_id');
        $product_name = $this->input->post('product_name');
        $product_price = $this->input->post('product_price');
        $selected_material = $this->input->post('selected_material') ?: '';
        $selected_glass = $this->input->post('selected_glass') ?: '';
        $selected_height = $this->input->post('selected_height') ?: '';
        $quantity = $this->input->post('quantity') ?: 1;

        // Check if this is an AJAX request
        $is_ajax = $this->input->is_ajax_request();
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($product_id)) {
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            } else {
                set_alert('danger', 'Missing required fields');
                redirect(site_url('home/products'));
                return;
            }
        }
        
        // Load required models
        $this->load->model('clients_model');
        $this->load->model('products/products_model');
        $this->load->model('products/order_model');
        $this->load->model('currencies_model');
        
        // Transaction start - ensure all operations complete or none
        $this->db->trans_start();
        
        // 1. Create or find client
        $client_id = null;
        $contact = $this->clients_model->get_contact_by_email($email);
        
        if ($contact) {
            // Use existing client ID
            $client_id = $contact->userid;
        } else {
            // Create new client
            $client_data = [
                'company' => !empty($company) ? $company : $name,
                'billing_street' => $address,
                'billing_city' => $city,
                'billing_state' => '',
                'billing_zip' => $zip,
                'billing_country' => 0,
                'shipping_street' => $address,
                'shipping_city' => $city,
                'shipping_state' => '',
                'shipping_zip' => $zip,
                'shipping_country' => 0,
                'phonenumber' => $phone,
            ];
            
            $contact_data = [
                'firstname' => explode(' ', $name)[0],
                'lastname' => count(explode(' ', $name)) > 1 ? substr(strstr($name, ' '), 1) : '',
                'email' => $email,
                'phonenumber' => $phone,
                'donotsendwelcomeemail' => true,
            ];
            
            // Add client with contact
            $client_id = $this->clients_model->add($client_data, true);
        }
        
        // 2. Format product items for order
        $cart_items = [];
        $cart_item = [
            'product_id' => $product_id,
            'product_variation_id' => '',
            'quantity' => $quantity,
            'calculated_price' => $product_price,
        ];
        
        // Add selected variations if they exist
        if (!empty($selected_material)) {
            $cart_item['selected_material'] = $selected_material;
        }
        if (!empty($selected_glass)) {
            $cart_item['selected_glass'] = $selected_glass;
        }
        if (!empty($selected_height)) {
            $cart_item['selected_height'] = $selected_height;
        }
        
        $cart_items[] = $cart_item;
        
        // Calculate total
        $subtotal = floatval($product_price) * intval($quantity);
        $tax_total = round($subtotal * 0.25, 2); // 25% standard tax
        $final_total = round($subtotal + $tax_total, 2);
        
        // 3. Format order data
        $order_data = [
            'clientid' => $client_id,
            'billing_street' => $address,
            'billing_city' => $city,
            'billing_state' => '',
            'billing_zip' => $zip,
            'billing_country' => 0,
            'billing_phone' => $phone,
            'billing_email' => $email,
            'shipping_street' => $address,
            'shipping_city' => $city,
            'shipping_state' => '',
            'shipping_zip' => $zip,
            'shipping_country' => 0,
            'payment_method' => 'bank',
            'product_items' => $cart_items,
            'coupon_id' => '',
            'order_note' => $message,
            'total_tax' => $tax_total,
            'subtotal' => $subtotal,
            'coupon_discount' => 0,
            'total' => $final_total,
            'adminnote' => 'Quote Request', // Use adminnote instead of is_request_quote
        ];
        
        // 4. Process order
        $return_data = $this->order_model->add_invoice_order($order_data);
        
        if (!$return_data['status']) {
            $this->db->trans_rollback();
            
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $return_data['message']]);
                return;
            } else {
                set_alert('danger', 'Failed to create order: ' . $return_data['message']);
                redirect(site_url('home/products'));
                return;
            }
        }
        
        // Commit transaction
        $this->db->trans_commit();
        
        // Log activity
        log_activity('New offer request submitted - Invoice ID: ' . $return_data['invoice_id']);
        
        // Handle response based on request type
        if ($is_ajax) {
            // Return JSON response for AJAX requests
            echo json_encode([
                'success' => true,
                'message' => 'Zahtjev za ponudu je uspješno poslan.',
                'invoice_id' => $return_data['invoice_id'],
                'invoice_hash' => isset($return_data['invoice_hash']) ? $return_data['invoice_hash'] : '',
                'redirect_url' => site_url('home/offer_success?invoice=' . $return_data['invoice_id'] . '&hash=' . $return_data['invoice_hash'])
            ]);
        } else {
            // Redirect to success page for form submissions
            redirect(site_url('home/offer_success?invoice=' . $return_data['invoice_id'] . '&hash=' . $return_data['invoice_hash']));
        }
    }
    
    /**
     * Success page for offer requests
     */
    public function offer_success()
    {
        $invoice_id = $this->input->get('invoice');
        $hash = $this->input->get('hash');
        
        // Get the actual invoice number from the invoices table
        $invoice_number = '';
        if ($invoice_id) {
            $this->load->model('invoices_model');
            $invoice = $this->invoices_model->get($invoice_id);
            if ($invoice) {
                $invoice_number = $invoice->number;
            }
        }
        
        $data = [];
        $data['title'] = 'Zahtjev za ponudu | Profi Line Zagreb';
        $data['keywords'] = 'Ponuda, Zahtjev, Ograde, Web Shop';
        $data['invoice_id'] = $invoice_id;
        $data['invoice_number'] = $invoice_number;
        $data['hash'] = $hash;
        
        $data['content'] = $this->load->view('front/offer_success', $data, TRUE); 
        $this->load->view('front/layouts/main', $data);
    }
}
