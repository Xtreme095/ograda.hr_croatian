<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function my_cart()
    {
        $ids = $this->input->get('id');
        if (!empty($ids)) {
            foreach ($ids as $product_id) {
                $cart_data = $newdata['cart_data'] = $this->session->cart_data;
                $qty = 1;
                if (!empty($cart_data)) {
                    foreach ($cart_data as $index => $value) {
                        if ($value['product_id'] == $product_id) {
                            $newdata['cart_data'][$index]['quantity'] = $value['quantity'] + 1;
                        }
                    }
                }
                $this->session->set_userdata($newdata);
                $cart_data = $this->session->cart_data;
            }
        }
        redirect('products/client/place_order');
    }
	
    public function manualorder() {
        $product_id = $this->input->get('id');  // Fetch product ID from GET parameter
        $quantity = '1';  // Fetch quantity from GET parameter
        //$variation_id = $this->input->get('variation');  // Fetch variation ID from GET parameter
        
        // Perform validation if needed
        if (!$product_id || !$quantity) {
            // Handle validation error, perhaps return an error response
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }

        // Logic to add the product to the cart
        // Example logic:
        $cart_data = $this->session->cart_data;  // Get existing cart data from session

        // Check if the cart data is empty or initialize if it's not set
        if (empty($cart_data)) {
            $cart_data = [];
        }

        // Check if the product already exists in the cart
        $product_exists_in_cart = false;
        foreach ($cart_data as $index => $item) {
            if ($item['product_id'] == $product_id) {
                // Product found in cart, increase quantity
                $cart_data[$index]['quantity'] += $quantity;
                $product_exists_in_cart = true;
                break;
            }
        }

        // If product does not exist in cart, add it
        if (!$product_exists_in_cart) {
            $new_item = [
                'product_id' => $product_id,
                'quantity' => $quantity,
            ];

            // Add variation ID if provided
           // if ($variation_id) {
           //     $new_item['variation_id'] = $variation_id;
           // }

            // Push new item to cart data array
            $cart_data[] = $new_item;
        }

        // Update session with new cart data
        $this->session->set_userdata('cart_data', $cart_data);

        // Redirect to place_order method or route
        redirect('products/client/place_order');
    }


    public function get_my_cart()
    {
        echo json_encode($this->session->cart_data);
    }

    private function get_cart_product($product_id)
    {
        $cart_data     = $this->session->cart_data;
        if (!empty($cart_data)) {
            foreach ($cart_data as $cart_item) {
                if ($cart_item['product_id'] == $product_id) {
                    return $cart_item;
                }
            }
        }

        return [];
    }

    private function get_cart_product_ids()
    {
        $cart_data     = $this->session->cart_data;
        $cart_product_ids = [];
        if (!empty($cart_data)) {
            foreach ($cart_data as $cart_item) {
                $cart_product_ids[] = $cart_item['product_id'];
            }
        }

        return $cart_product_ids;
    }

    public function index()
    {
        if (0 != get_option('product_menu_disabled')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $this->load->model('product_category_model');
        $data['title']              = _l('products');
        $data['products']           = $this->products_model->get_by_id_product();
        $data['product_categories'] = $this->product_category_model->get();
        $this->data($data);
        $this->view('clients/products');
        $this->layout();
    }

    public function filter()
    {
        $p_category_id = $this->input->post('p_category_id');
        $cart_data     = $this->session->cart_data;
        $products      = $this->products_model->get_category_filter($p_category_id);
        $base_currency = $this->currencies_model->get_base_currency();
        foreach ($products as $key => $value) {
            $products[$key]['cart_data']          = $this->get_cart_product($value['id']);
            $products[$key]['product_image_url']  = module_dir_url('products', 'uploads') . '/' . $value['product_image'];
            $products[$key]['no_image_url']       = module_dir_url('products', 'uploads') . '/image-not-available.png';
            $products[$key]['base_currency_name'] = $base_currency->name;
            $taxes                                = unserialize($value['taxes']);
            $total_tax                            = 0;
            if (!empty($taxes)) {
                foreach ($taxes as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $total_tax += $tax_array[1];
                }
            }
            $products[$key]['total_tax'] = $total_tax;
            $products[$key]['qty'] = _l('qty');
            $products[$key]['add_to_cart'] = _l('add_to_cart');
            $products[$key]['update_cart'] = _l('update_cart');
            $products[$key]['out_of_stock'] = _l('out_of_stock');
        }
        echo json_encode($products);
    }

    private function sort_cart($cart_data)
    {
        $cart_data_keys = array_keys($cart_data);
        $first_index = 0;
        while ($first_index < count($cart_data_keys) - 1) {
            $sorted_count = 0;
            for ($second_index = $first_index + 2; $second_index < count($cart_data_keys); $second_index++) {
                if ($cart_data[$cart_data_keys[$first_index]]['product_id'] == $cart_data[$cart_data_keys[$second_index]]['product_id']) {
                    $replace_cart_item = $cart_data[$cart_data_keys[$second_index]];
                    for ($third_index = $second_index; $third_index > $first_index + $sorted_count + 1; $third_index--) {
                        $cart_data[$cart_data_keys[$third_index]] = $cart_data[$cart_data_keys[$third_index - 1]];
                    }
                    $cart_data[$cart_data_keys[$first_index + $sorted_count + 1]] = $replace_cart_item;
                    $sorted_count = $sorted_count + 1;
                }
            }
            $first_index = $first_index + $sorted_count + 1;
        }
        return $cart_data;
    }

    public function add_cart()
    {
        $product_id           = $this->input->post('product_id');
        $product_variation_id = $this->input->post('product_variation_id');
        $quantity             = $this->input->post('quantity');
        $selected_material    = $this->input->post('selected_material');
        $selected_height      = $this->input->post('selected_height');
        $calculated_price     = $this->input->post('calculated_price');
        
        // Log received data for debugging
        log_message('debug', 'ADD_CART: Received data - product_id: ' . $product_id . 
                            ', variation_id: ' . $product_variation_id . 
                            ', calculated_price: ' . $calculated_price);
        
        $newdata['cart_data'] = $this->session->cart_data;
        if (empty($newdata['cart_data'])) {
            $newdata['cart_data'] = [
                [
                    'product_id' => $product_id, 
                    'product_variation_id' => $product_variation_id, 
                    'quantity' => $quantity,
                    'selected_material' => $selected_material,
                    'selected_height' => $selected_height,
                    'calculated_price' => $calculated_price
                ]
            ];
            $this->session->set_userdata($newdata);
            log_message('debug', 'ADD_CART: Created new cart with item, price: ' . $calculated_price);
        } else {
            $cart_item_exist = false;
            foreach ($newdata['cart_data'] as $cart_item_index => $cart_item) {
                if ($cart_item['product_id'] == $product_id && $cart_item['product_variation_id'] == $product_variation_id) {
                    $newdata['cart_data'][$cart_item_index]['quantity'] = $quantity;
                    $newdata['cart_data'][$cart_item_index]['selected_material'] = $selected_material;
                    $newdata['cart_data'][$cart_item_index]['selected_height'] = $selected_height;
                    $newdata['cart_data'][$cart_item_index]['calculated_price'] = $calculated_price;
                    $cart_item_exist = true;
                    log_message('debug', 'ADD_CART: Updated existing cart item, price: ' . $calculated_price);
                }
            }
            if (!$cart_item_exist) {
                $newdata['cart_data'][] = [
                    'product_id' => $product_id, 
                    'product_variation_id' => $product_variation_id, 
                    'quantity' => $quantity,
                    'selected_material' => $selected_material,
                    'selected_height' => $selected_height,
                    'calculated_price' => $calculated_price
                ];
                log_message('debug', 'ADD_CART: Added new item to existing cart, price: ' . $calculated_price);
            }
            $newdata['cart_data'] = $this->sort_cart($newdata['cart_data']);
            $this->session->set_userdata($newdata);
        }
        
        // Track Facebook Pixel AddToCart event if the helper is available
        $this->load->helper('facebook_pixel');
        if (function_exists('track_facebook_add_to_cart')) {
            // Get product details for tracking
            $this->load->model('products_model');
            $product = $this->products_model->get_by_id_product($product_id);
            
            if (!empty($product) && is_array($product)) {
                // Convert from array to object for single product
                if (isset($product[0])) {
                    $product = $product[0];
                }
                
                // Calculate price to track
                $price = !empty($calculated_price) ? $calculated_price : $product['rate'];
                $total_value = $price * $quantity;
                
                // Create product data for tracking
                $product_data = [
                    'id' => $product_id,
                    'name' => $product['product_name'],
                    'category' => $product['p_category_name'] ?? 'Uncategorized',
                    'price' => $price,
                    'quantity' => $quantity
                ];
                
                // Track AddToCart event
                track_facebook_add_to_cart($total_value, 'EUR', $product_data);
                log_message('debug', 'Facebook Pixel: Tracked AddToCart event for product ID ' . $product_id);
            }
        }
        
        echo json_encode($this->session->cart_data);
    }

    public function remove_cart($product_id = null, $product_variation_id = null, $return = false)
    {
        if (empty($product_id)) {
            $product_id = $this->input->post('product_id');
        }
        if (empty($product_variation_id)) {
            $product_variation_id = $this->input->post('product_variation_id');
        }
        $newdata['cart_data'] = $this->session->cart_data;
        foreach ($newdata['cart_data'] as $key => $value) {
            if ($product_id == $value['product_id'] && $product_variation_id == $value['product_variation_id']) {
                unset($newdata['cart_data'][$key]);
            }
        }
        $cart_data = [];
        foreach ($newdata['cart_data'] as $value) {
            $cart_data[] = $value;
        }
        $newdata['cart_data'] = $cart_data;
        $this->session->set_userdata($newdata);
        
        // Always return success even if cart is empty now
        // This fixes the "Unable to remove product from cart" error
        $res['status'] = true;
        $res['cart_data'] = $newdata['cart_data'];
        
        // Set an alert only if this is not an AJAX call
        if (empty($newdata['cart_data']) && !$this->input->is_ajax_request()) {
            set_alert('info', _l('Cart is empty'));
        }
        
        if ($return) {
            return json_encode($res);
        }
        echo json_encode($res);
    }

    public function get_currency($id)
    {
        echo json_encode(get_currency($id));
    }

    public function place_order($product_id = false)
    {
        if (0 != get_option('product_menu_disabled')) {
            $this->session->unset_userdata('cart_data');
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        $this->load->model('products/order_model');
        if (!is_client_logged_in()) {
            set_alert('warning', _l('clients_login_heading_no_register'));
            redirect(site_url(''));
        }
        $message          = '';
        $post = $this->input->post();
        unset($post['taxes']);
        unset($post['shipping_cost']);
        if (!empty($post)) {
            $post['product_items'] = $this->sort_cart($post['product_items']);
            $return_data = $this->order_model->add_invoice_order($post);
            if ($return_data['status']) {
                $this->session->unset_userdata('cart_data');
                set_alert('success', _l('order_success'));
                if ($return_data['single_invoice']) {
                    redirect(site_url('invoice/' . $return_data['invoice_id'] . '/' . $return_data['invoice_hash']), 'refresh');
                }
                redirect(site_url('clients/invoices'), 'refresh');
            }
            if (!$return_data['status']) {
                set_alert('error', _l('order_fail'));
                $message .= $return_data['message'];
            }
        }
        if (empty($this->session->cart_data)) {
            set_alert('danger', _l('Cart is empty'));
            redirect(site_url('products/client/'));
        }
        $cart_data = $this->sort_cart($this->session->cart_data);
        if (empty($cart_data)) {
            set_alert('danger', _l('Cart is empty'));
            redirect(site_url('products/client/'));
        }
        $data['products'] = $product = $this->products_model->get_by_cart_product($cart_data);
        if (empty($product)) {
            set_alert('danger', _l('Products in Cart not found'));
            redirect(site_url('products/client/'));
        }
        $all_taxes        = [];
        $init_tax         = [];
        $apply_shipping   = false;
        foreach ($product as $value) {
            if (!$value->is_digital) {
                if ((int) $value->quantity_number < 1) {
                    $this->remove_cart($value->id, $value->product_variation_id ?? '', true);
                    $message .= $value->product_name . ' is out of stock so removed from cart <br>';
                    continue;
                }
                if ((int) $value->quantity > (int) $value->quantity_number) {
                    $value->quantity = $value->quantity_number;
                    $message         .= $value->product_name . ' is only ' . $value->quantity_number . ' in stock so quantity reduced to that quantity <br>';
                }
            }
            $value->apply_shipping = false;
            if (!$value->recurring && !$value->is_digital) {
                $value->apply_shipping = true;
                $apply_shipping = true;
            }
            $taxes_arr       = [];
            $value->taxname  = $taxes  = unserialize($value->taxes);
            if ($taxes) {
                foreach ($taxes as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $init_tax[$tmp_taxname][]  = ($value->rate * $value->quantity) / 100 * $tax_array[1];
                    $all_taxes[$tmp_taxname]   = $taxes_arr[]   = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1], 'taxname' => $tax_array[0]];
                }
            }
            $value->taxes = $taxes_arr;
        }
        $shipping_cost = 0;
        $base_shipping_cost = 0;
        $shipping_tax = 0;
        if ($apply_shipping) {
            $taxname = (!empty((get_option('product_tax_for_shipping_cost')))) ? unserialize(get_option('product_tax_for_shipping_cost')) : '';
            $shipping_cost = $base_shipping_cost = get_option('product_flat_rate_shipping');
            $shipping_tax = 0;
            if ($taxname) {
                foreach ($taxname as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $shipping_tax  += $tax_array[1];
                    $shipping_cost += ($base_shipping_cost) / 100 * $tax_array[1];
                }
            }
        }
        $data['shipping_cost']    = $shipping_cost;
        $data['shipping_base']    = $base_shipping_cost;
        $data['shipping_tax']     = $shipping_tax;
        $data['all_taxes']        = $all_taxes;
        $data['init_tax']         = $init_tax;
        $data['message']          = $message;
        $data['title']            = _l('confirm') . ' ' . _l('place_order');
        $data['base_currency']    = $this->currencies_model->get_base_currency();
        $this->data($data);
        $this->view('clients/place_order');
        $this->layout();
    }

    public function variation_values()
    {
        $product_id = $this->input->post('product_id');
        $variation_id = $this->input->post('variation_id');
        $variations = $this->products_model->get_by_id_variation_values($product_id, $variation_id);
        
        echo json_encode($variations);
    }

    private function get_tax_shipping()
    {
        $cart_data = $this->session->cart_data;
        if (empty($cart_data)) {
            set_alert('danger', _l('Cart is empty'));
            redirect(site_url('products/client/'));
        }
        $product = $this->products_model->get_by_cart_product($cart_data);
        if (empty($product)) {
            set_alert('danger', _l('Products in Cart not found'));
            redirect(site_url('products/client/'));
        }

        $all_taxes        = [];
        $init_tax         = [];
        $apply_shipping   = false;
        foreach ($product as $value) {
            $value->apply_shipping = false;
            if (!$value->recurring && !$value->is_digital) {
                $value->apply_shipping = true;
                $apply_shipping = true;
            }
            $taxes_arr       = [];
            $value->taxname  = $taxes  = unserialize($value->taxes);
            if ($taxes) {
                foreach ($taxes as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $init_tax[$tmp_taxname][]  = ($value->rate * $value->quantity) / 100 * $tax_array[1];
                    $all_taxes[$tmp_taxname]   = $taxes_arr[]   = ['name' => $tmp_taxname, 'taxrate' => $tax_array[1], 'taxname' => $tax_array[0]];
                }
            }
            $value->taxes = $taxes_arr;
        }
        $shipping_cost = 0;
        $base_shipping_cost = 0;
        $shipping_tax = 0;
        if ($apply_shipping) {
            $taxname = (!empty((get_option('product_tax_for_shipping_cost')))) ? unserialize(get_option('product_tax_for_shipping_cost')) : '';
            $shipping_cost = $base_shipping_cost = get_option('product_flat_rate_shipping');
            $shipping_tax = 0;
            if ($taxname) {
                foreach ($taxname as $tax) {
                    if (!is_array($tax)) {
                        $tmp_taxname = $tax;
                        $tax_array   = explode('|', $tax);
                    } else {
                        $tax_array   = explode('|', $tax['taxname']);
                        $tmp_taxname = $tax['taxname'];
                        if ('' == $tmp_taxname) {
                            continue;
                        }
                    }
                    $shipping_tax  += $tax_array[1];
                    $shipping_cost += ($base_shipping_cost) / 100 * $tax_array[1];
                }
            }
        }

        return [
            'product' => $product,
            'all_taxes' => $all_taxes,
            'init_tax' => $init_tax,
            'apply_shipping' => $apply_shipping,
            'shipping_cost' => $shipping_cost,
            'base_shipping_cost' => $base_shipping_cost,
            'shipping_tax' => $shipping_tax,
        ];
    }

    public function apply_coupon($coupon_code = null)
    {
        if (0 != get_option('coupons_disabled')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        if (empty($coupon_code)) {
            $coupon_code = $this->input->post('coupon_code');
        }
        
        $this->load->model('products/products_model');
        
        $base_currency = $this->currencies_model->get_base_currency();

        $this->load->model('products/coupons_model');
        $coupon = $this->coupons_model->get_by_code($coupon_code);

        if ($coupon) {
            if ($this->coupons_model->is_available($coupon->id)) {
                $tax_shipping_data = $this->get_tax_shipping();
                
                // Calculate subtotal (products only, no tax)
                $subtotal = 0;
                foreach ($tax_shipping_data['product'] as $value) {
                    $subtotal += $value->quantity * $value->rate;
                }
                
                // Calculate tax at a fixed 25%
                $tax_amount = $subtotal * 0.25;
                
                // Add shipping cost if applicable
                $shipping_cost = !empty($tax_shipping_data['shipping_cost']) ? $tax_shipping_data['shipping_cost'] : 0;
                
                // Calculate coupon discount based on subtotal only (not including tax)
                if ($coupon->type == '%') {
                    $coupon_discount = $subtotal * $coupon->amount / 100;
                } else {
                    $coupon_discount = $coupon->amount;
                }
                
                // Calculate final total
                $total = $subtotal - $coupon_discount + $tax_amount + $shipping_cost;
                
                // Store coupon info in session
                $_SESSION['coupon_id'] = $coupon->id;
                $_SESSION['coupon_discount'] = $coupon_discount;
                
                // Format final HTML
                $coupon_html = '-' . app_format_money($coupon_discount, $base_currency->name);
                $total_html = '<strong>' . app_format_money($total, $base_currency->name) . '</strong>';
                
                $res = [
                    'status' => true,
                    'html' => $coupon_html,
                    'coupon_id' => $coupon->id,
                    'total_html' => $total_html
                ];
            } else {
                $res = [
                    'status' => false,
                    'message' => _l('coupon_can_not_apply')
                ];
            }
        } else {
            $res = [
                'status' => false,
                'message' => _l('coupon_does_not_exist')
            ];
        }
        echo json_encode($res);
    }

    public function remove_coupon()
    {
        if (0 != get_option('coupons_disabled')) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }
        
        $this->load->model('products/products_model');
        
        $base_currency = $this->currencies_model->get_base_currency();

        $tax_shipping_data = $this->get_tax_shipping();
        
        // Calculate subtotal (products only, no tax)
        $subtotal = 0;
        foreach ($tax_shipping_data['product'] as $value) {
            $subtotal += $value->quantity * $value->rate;
        }
        
        // Calculate tax at a fixed 25%
        $tax_amount = $subtotal * 0.25;
        
        // Add shipping cost if applicable
        $shipping_cost = !empty($tax_shipping_data['shipping_cost']) ? $tax_shipping_data['shipping_cost'] : 0;
        
        // Calculate final total without coupon
        $total = $subtotal + $tax_amount + $shipping_cost;
        
        // Remove coupon from session
        unset($_SESSION['coupon_id']);
        unset($_SESSION['coupon_discount']);
        
        $res = [
            'status' => true,
            'total_html' => '<strong>' . app_format_money($total, $base_currency->name) . '</strong>'
        ];
        
        echo json_encode($res);
    }

    /**
     * Handle offer request submissions
     * Creates a lead and a draft proposal
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
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($product_id)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }
        
        // Load required models
        $this->load->model('leads_model');
        $this->load->model('proposals_model');
        $this->load->model('staff_model');
        
        // Transaction start - ensure all operations complete or none
        $this->db->trans_start();
        
        // 1. Create lead
        $lead_data = [
            'name' => $name,
            'email' => $email,
            'phonenumber' => $phone,
            'company' => $company,
            'address' => $address,
            'city' => $city,
            'zip' => $zip,
            'description' => "Ponuda zatražena preko web stranice za proizvod: {$product_name}\n\nDetalji proizvoda:\n" . 
                             "- Proizvod: {$product_name}\n" .
                             "- Količina: {$quantity}\n" .
                             ($selected_material ? "- Materijal: {$selected_material}\n" : '') .
                             ($selected_glass ? "- Staklo: {$selected_glass}\n" : '') .
                             ($selected_height ? "- Visina: {$selected_height}\n" : '') .
                             "\nDodatna poruka kupca:\n{$message}",
            'status' => 1, // Open - adjust based on your lead status IDs
            'source' => 2, // Website - adjust based on your source IDs
            'assigned' => 0, // Will be auto-assigned to the first admin
            'addedfrom' => 0, // System
            'is_public' => 1,
            'dateadded' => date('Y-m-d H:i:s')
        ];
        
        // Get the first admin for lead assignment
        $admins = $this->staff_model->get('', ['admin' => 1, 'active' => 1]);
        if (count($admins) > 0) {
            $lead_data['assigned'] = $admins[0]['staffid'];
        }
        
        // Insert lead
        $lead_id = $this->leads_model->add($lead_data);
        
        if (!$lead_id) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to create lead']);
            return;
        }
        
        // 2. Create draft proposal
        // Get the product details
        $this->load->model('products_model');
        $product = $this->products_model->get($product_id);
        
        // Create proposal data
        $total = floatval($product_price) * intval($quantity);
        
        $proposal_data = [
            'subject' => "Ponuda za {$product_name}",
            'rel_id' => $lead_id,
            'rel_type' => 'lead',
            'proposal_to' => $name,
            'address' => $address,
            'city' => $city,
            'zip' => $zip,
            'country' => 0, // Set default country if needed
            'phone' => $phone,
            'email' => $email,
            'status' => 6, // Draft status (6 = Draft in most installations)
            'assigned' => $lead_data['assigned'],
            'date' => date('Y-m-d'),
            'open_till' => date('Y-m-d', strtotime('+30 days')),
            'currency' => get_base_currency()->id,
            'datecreated' => date('Y-m-d H:i:s'),
            'addedfrom' => 0, // System
            'hash' => app_generate_hash(),
            'content' => 'Ponuda za naručene proizvode'
        ];
        
        // Add items to proposal
        $product_description = $product_name;
        if ($selected_material || $selected_glass || $selected_height) {
            $product_description .= " (";
            if ($selected_material) $product_description .= "Materijal: {$selected_material}, ";
            if ($selected_glass) $product_description .= "Staklo: {$selected_glass}, ";
            if ($selected_height) $product_description .= "Visina: {$selected_height}, ";
            $product_description = rtrim($product_description, ", ") . ")";
        }
        
        // Add item
        $proposal_data['newitems'][0] = [
            'description' => $product_description,
            'long_description' => $message,
            'qty' => $quantity,
            'unit' => '',
            'rate' => $product_price,
            'order' => 1
        ];
        
        // Create proposal
        $proposal_id = $this->proposals_model->add($proposal_data);
        
        if (!$proposal_id) {
            $this->db->trans_rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to create proposal']);
            return;
        }
        
        // Commit transaction
        $this->db->trans_commit();
        
        // Log activity
        log_activity('New offer request from website - Lead ID: ' . $lead_id . ', Proposal ID: ' . $proposal_id);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Offer request submitted successfully',
            'lead_id' => $lead_id,
            'proposal_id' => $proposal_id
        ]);
    }
}