<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Order_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->model('products_model');
        $this->load->model('clients_model');
        $this->load->model('currencies_model');
        $this->load->model('payment_modes_model');
        
        // Ensure the order_master table has all required columns
        $this->ensure_order_master_columns();
        
        // Ensure the order_items table has the right structure
        $this->ensure_order_items_columns();
    }
    
    /**
     * Check if all required columns exist in order_master table and add them if they don't
     */
    private function ensure_order_master_columns()
    {
        // Define the columns to add with their definitions
        $columns = [
            'payment_method' => 'VARCHAR(50) NULL DEFAULT NULL',
            'address_data' => 'TEXT NULL DEFAULT NULL',
            'order_note' => 'TEXT NULL DEFAULT NULL',
            'email' => 'VARCHAR(100) NULL DEFAULT NULL',
            'status' => 'VARCHAR(40) NULL DEFAULT NULL',
            'order_date' => 'DATE NULL DEFAULT NULL',
            'subtotal' => 'DECIMAL(15,2) NULL DEFAULT 0.00',
            'total' => 'DECIMAL(15,2) NULL DEFAULT 0.00'
        ];
        
        $table_name = db_prefix() . 'order_master';
        
        foreach ($columns as $column_name => $definition) {
            // Check if column exists
            if (!$this->db->field_exists($column_name, $table_name)) {
                // Column doesn't exist, so add it
                $this->db->query("ALTER TABLE `$table_name` ADD COLUMN `$column_name` $definition");
                log_activity('Added missing column ' . $column_name . ' to order_master table');
            }
        }
    }

    /**
     * Check if order_items table has the right structure
     */
    private function ensure_order_items_columns()
    {
        $table_name = db_prefix() . 'order_items';
        
        // First check if the table exists, if not create it
        if (!$this->db->table_exists($table_name)) {
            $this->db->query("
                CREATE TABLE `$table_name` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `order_id` INT(11) NOT NULL,
                    `product_id` INT(11) NOT NULL,
                    `product_variation_id` INT(11) NULL DEFAULT NULL,
                    `qty` INT(11) NOT NULL DEFAULT 1,
                    PRIMARY KEY (`id`),
                    INDEX `order_id` (`order_id`),
                    INDEX `product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
            log_activity('Created missing order_items table');
            return; // Table created with all necessary columns
        }
        
        // Check if we need to rename quantity to qty or vice versa
        if (!$this->db->field_exists('qty', $table_name) && $this->db->field_exists('quantity', $table_name)) {
            // We have quantity but not qty, use quantity
        } else if (!$this->db->field_exists('quantity', $table_name) && $this->db->field_exists('qty', $table_name)) {
            // We have qty but not quantity, use qty
        } else if (!$this->db->field_exists('qty', $table_name) && !$this->db->field_exists('quantity', $table_name)) {
            // Neither exists, add qty
            $this->db->query("ALTER TABLE `$table_name` ADD COLUMN `qty` INT NOT NULL DEFAULT 1");
            log_activity('Added missing column qty to order_items table');
        }
        
        // Ensure product_id column exists
        if (!$this->db->field_exists('product_id', $table_name)) {
            $this->db->query("ALTER TABLE `$table_name` ADD COLUMN `product_id` INT NOT NULL");
            log_activity('Added missing column product_id to order_items table');
        }
        
        // Ensure product_variation_id column exists
        if (!$this->db->field_exists('product_variation_id', $table_name)) {
            $this->db->query("ALTER TABLE `$table_name` ADD COLUMN `product_variation_id` INT NULL DEFAULT NULL");
            log_activity('Added missing column product_variation_id to order_items table');
        }
    }

    public function add_order($data)
    {
        // First check if address_data column exists in order_master table
        if (!$this->db->field_exists('address_data', db_prefix() . 'order_master')) {
            // Column doesn't exist, so add it
            $this->db->query('ALTER TABLE ' . db_prefix() . 'order_master ADD `address_data` TEXT NULL');
        }
        
        $data['datecreated'] = date('Y-m-d H:i:s');
        $product_items       = $data['product_items'];
        unset($data['product_items']);
        unset($data['coupon_id']);
        
        // Initialize address_data array if it doesn't exist
        if (!isset($data['address_data'])) {
            $data['address_data'] = [];
        }
        
        // Store the contact info in address_data
        if (isset($data['contact_info'])) {
            $contact_info = $data['contact_info'];
            unset($data['contact_info']);
            if (isset($contact_info['billing_phone'])) {
                $data['address_data']['billing_phone'] = $contact_info['billing_phone'];
            }
            if (isset($contact_info['billing_email'])) {
                $data['address_data']['billing_email'] = $contact_info['billing_email'];
            }
        }
        
        // Store billing phone and email in address_data and remove from main data
        if (isset($data['billing_phone'])) {
            $data['address_data']['billing_phone'] = $data['billing_phone'];
            unset($data['billing_phone']);
        }
        
        if (isset($data['billing_email'])) {
            $data['address_data']['billing_email'] = $data['billing_email'];
            unset($data['billing_email']);
        }
        
        // Remove billing and shipping fields that might not exist in the order_master table
        $fields_to_remove = [
            'billing_street', 'billing_city', 'billing_state', 'billing_zip', 'billing_country',
            'shipping_street', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'
        ];
        
        foreach ($fields_to_remove as $field) {
            if (isset($data[$field])) {
                // Save these fields in address_data
                $data['address_data'][$field] = $data[$field];
                unset($data[$field]);
            }
        }
        
        // Encode address_data as JSON if it has data
        if (!empty($data['address_data'])) {
            $data['address_data'] = json_encode($data['address_data']);
        } else {
            unset($data['address_data']);
        }
        
        // Only include fields that we know exist in the order_master table
        $safe_fields = [
            'id', 'invoice_id', 'clientid', 'email', 'status', 'order_date', 
            'datecreated', 'subtotal', 'total', 'payment_method', 
            'address_data', 'order_note'
        ];
        
        $insert_data = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $safe_fields)) {
                $insert_data[$key] = $value;
            }
        }
        
        $this->db->insert(db_prefix() . 'order_master', $insert_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            // Process each product item to adapt to database structure
            $processed_items = [];
            foreach ($product_items as $item) {
                // Standardize the array structure
                $processed_item = [
                    'order_id' => $insert_id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => isset($item['product_variation_id']) && !empty($item['product_variation_id']) ? $item['product_variation_id'] : NULL
                ];
                
                // Handle qty/quantity based on what field exists in the database
                if ($this->db->field_exists('qty', db_prefix() . 'order_items')) {
                    $processed_item['qty'] = isset($item['qty']) ? $item['qty'] : (isset($item['quantity']) ? $item['quantity'] : 1);
                } else if ($this->db->field_exists('quantity', db_prefix() . 'order_items')) {
                    $processed_item['quantity'] = isset($item['quantity']) ? $item['quantity'] : (isset($item['qty']) ? $item['qty'] : 1);
                }
                
                $processed_items[] = $processed_item;
            }
            
            // Insert the processed items
            $this->db->insert_batch(db_prefix() . 'order_items', $processed_items);
            
            $this->db->select('staffid, email');
            $this->db->where('admin', 1);
            $this->db->where('active', 1);
            $system_admin = $this->db->get(db_prefix() . 'staff')->result_array();
            foreach ($system_admin as $staff) {
                add_notification([
                    'description' => _l('Order added'),
                    'touserid'    => $staff['staffid'],
                    'link'        => 'products/order_history/',
                ]);
            }

            return $insert_id;
        }

        return false;
    }

    public function update_status($invoice_id, $status)
    {
        $this->db->update(db_prefix() . 'order_master', ['status' => $status], ['invoice_id'=>$invoice_id]);
    }

    public function update_quantity_on_invoice($invoice_id)
    {
        $res         = null;
        $order_items = $this->get_order_items_from_invoice($invoice_id);
        if (empty($order_items)) {
            $this->load->model('invoices_model');
            $recurring_invoice = $this->invoices_model->get($invoice_id);
            if (!empty($recurring_invoice)) {
                $recurring_invoice_id = $recurring_invoice->is_recurring_from;
                $order_items          = $this->get_order_items_from_invoice($recurring_invoice_id);
            }
        }
        if (!empty($order_items)) {
            $order_id    = reset($order_items)['order_id'];
            $product_variations = $order_items;
            $order_items = array_map(function ($arr) {
                $quantity_arr['quantity_number'] = 'quantity_number - '.(isset($arr['qty']) ? $arr['qty'] : 1);
                $quantity_arr['id']              = $arr['product_id'];

                return $quantity_arr;
            }, $order_items);
            $this->db->set_update_batch($order_items, 'id', false);
            $this->db->where('is_digital', 0);
            $res = $this->db->update_batch(db_prefix() . 'product_master', null, 'id');
            if ($res) {
                $product_variations = array_filter($product_variations, function ($arr) {
                    if ($arr['product_variation_id']) {
                        return $arr;
                    }
                });
                $product_variations = array_map(function ($arr) {
                    $quantity_arr['quantity_number'] = 'quantity_number - ' . (isset($arr['qty']) ? $arr['qty'] : 1);
                    $quantity_arr['id']              = $arr['product_variation_id'];
    
                    return $quantity_arr;
                }, $product_variations);
                if (count($product_variations)) {
                    $this->db->set_update_batch($product_variations, 'id', false);
                    $variation_res = $this->db->update_batch(db_prefix() . 'product_variations', null, 'id');
                } else {
                    $variation_res = true;
                }
                if ($variation_res) {
                    $data = $this->get_by_id_order($order_id);
                    $this->db->select('staffid, email');
                    $this->db->where('admin', 1);
                    $this->db->where('active', 1);
                    $system_admin = $this->db->get(db_prefix() . 'staff')->result_array();
                    foreach ($system_admin as $staff) {
                        send_mail_template('order_paid_admin', 'products', $data, $staff);
                    }
                    send_mail_template('Order_paid_client', 'products', $data);
                }
            }
        }

        return $res;
    }

    public function get_order_items_from_invoice($invoice_id)
    {
        $this->db->where(db_prefix() . 'order_master.invoice_id', $invoice_id);
        $this->db->join('order_master', db_prefix() . 'order_master.id=' . db_prefix() . 'order_items.order_id', 'LEFT');
        $result      = $this->db->get(db_prefix() . 'order_items');

        return $order_items = $result->result_array();
    }

    public function get_by_id_order($id = false)
    {
        if ($id) {
            $this->db->where_in(db_prefix() . 'order_master.id', $id);
            if (is_array($id)) {
                $product = $this->db->get(db_prefix() . 'order_master')->result();
            } else {
                $product = $this->db->get(db_prefix() . 'order_master')->row();
            }

            return $product;
        }
        $products = $this->db->get(db_prefix() . 'order_master')->result_array();

        return $products;
    }

    public function get_order_with_items($id = false)
    {
        if (!empty($id)) {
            $this->db->where(db_prefix() . 'order_master.id', $id);
        }
        $this->db->join('order_master', db_prefix() . 'order_master.id=' . db_prefix() . 'order_items.order_id', 'LEFT');
        $this->db->join('product_master', db_prefix() . 'product_master.id=' . db_prefix() . 'order_items.product_id', 'LEFT');
        $result      = $this->db->get(db_prefix() . 'order_items');

        return $order_items = $result->result();
    }

    public function add_invoice_order($post)
    {
        if (empty($post)) {
            return ['status' => false, 'message' => "Post data cannot be empty`"];
        }

        // Save contact_info for later but don't pass to invoice
        $contact_info = null;
        if (isset($post['contact_info'])) {
            $contact_info = $post['contact_info'];
            unset($post['contact_info']);
        }

        $coupon_description = '';
        if (!$post['coupon_id']) {
            $post['coupon_id'] = NULL;
        }
        if ($post['coupon_id']) {
            $this->db->where('id', $post['coupon_id']);
            $coupon = $this->db->get(db_prefix() . 'coupons')->row();
            if ($coupon) {
                $coupon_description = '(Coupon' . ' ' . $coupon->code . ' applied)';
            }
        }

        $post['newitems'] = $post['product_items'];
        $product_items = [];
        foreach ($post['product_items'] as $product_item) {
            $product_items[] = [
                'product_id' => $product_item['product_id'],
                'product_variation_id' => isset($product_item['product_variation_id']) ? $product_item['product_variation_id'] : '',
            ];
            
            // Debug each product item to ensure direct_price is present
            log_message('debug', 'PRODUCT_ITEM: ' . json_encode($product_item));
        }
        $data['products'] = $product = $this->products_model->get_by_id_product_afflect_variation($product_items);
        $message          = '';
        foreach ($product as $key => $value) {
            unset($post['newitems'][$key]['product_id']);
            unset($post['newitems'][$key]['product_variation_id']);
            $post['newitems'][$key]['unit']             = '';
            $post['newitems'][$key]['order']            = $key + 1;
            
            // Start with base product name
            $description = $value->product_name;
            
            // Add variant details to the description
            $variant_details = [];
            
            // Get additional variant selections from product_items first
            if (isset($post['product_items'][$key]['selected_material']) && !empty($post['product_items'][$key]['selected_material'])) {
                $variant_details[] = "Materijal: " . $post['product_items'][$key]['selected_material'];
            } 
            // Fallback to product object if not in product_items
            else if (isset($value->selected_material) && !empty($value->selected_material)) {
                $variant_details[] = "Materijal: " . $value->selected_material;
            }
            
            if (isset($post['product_items'][$key]['selected_glass']) && !empty($post['product_items'][$key]['selected_glass'])) {
                $variant_details[] = "Staklo: " . $post['product_items'][$key]['selected_glass'];
            }
            // Fallback to product object if not in product_items
            else if (isset($value->selected_glass) && !empty($value->selected_glass)) {
                $variant_details[] = "Staklo: " . $value->selected_glass;
            }
            
            if (isset($post['product_items'][$key]['selected_height']) && !empty($post['product_items'][$key]['selected_height'])) {
                $variant_details[] = "Visina: " . $post['product_items'][$key]['selected_height'];
            }
            // Fallback to product object if not in product_items
            else if (isset($value->selected_height) && !empty($value->selected_height)) {
                $variant_details[] = "Visina: " . $value->selected_height;
            }
            
            // Add variant details to the description if there are any
            if (!empty($variant_details)) {
                $description .= " (" . implode(", ", $variant_details) . ")";
            }
            
            $post['newitems'][$key]['description'] = $description;
            $post['newitems'][$key]['long_description'] = $value->product_description . $coupon_description;
            $post['newitems'][$key]['taxname']          = unserialize($value->taxes);
            
            // DIRECT PRICE HANDLING
            // First, check if there's a direct_price in the original product_items
            // This is the most important price source and should override all others
            $calculated_price = null;
            
            if (isset($post['product_items'][$key]['direct_price']) && floatval($post['product_items'][$key]['direct_price']) > 0) {
                $calculated_price = floatval($post['product_items'][$key]['direct_price']);
                log_message('debug', 'INVOICE DIRECT: Using direct_price from product_items: ' . $calculated_price . ' for product ' . $value->id);
            } 
            // If no direct price, check for calculated_price in session cart data
            else if (isset($this->session->cart_data)) {
                foreach ($this->session->cart_data as $cart_item) {
                    if ($cart_item['product_id'] == $value->id) {
                        // Match by both product ID and variation ID if available
                        $variation_matches = true;
                        if (isset($value->product_variation_id) && isset($cart_item['product_variation_id'])) {
                            $variation_matches = ($value->product_variation_id == $cart_item['product_variation_id']);
                        }
                        
                        if ($variation_matches && isset($cart_item['calculated_price']) && !empty($cart_item['calculated_price'])) {
                            $calculated_price = $cart_item['calculated_price'];
                            log_message('debug', 'INVOICE DIRECT: Using calculated_price from cart data: ' . $calculated_price . ' for product ' . $value->id);
                            break;
                        }
                    }
                }
            }
            
            // Fallback to product object prices if nothing else available
            if (!$calculated_price) {
                if (isset($value->direct_price) && floatval($value->direct_price) > 0) {
                    $calculated_price = floatval($value->direct_price);
                    log_message('debug', 'INVOICE DIRECT: Using direct_price from product object: ' . $calculated_price);
                } else if (isset($value->variation_rate) && floatval($value->variation_rate) > 0) {
                    $calculated_price = floatval($value->variation_rate);
                    log_message('debug', 'INVOICE DIRECT: Using variation_rate: ' . $calculated_price);
                } else if (isset($value->rate) && floatval($value->rate) > 0) {
                    $calculated_price = floatval($value->rate);
                    log_message('debug', 'INVOICE DIRECT: Using base rate: ' . $calculated_price);
                }
            }
            
            // Set the rate explicitly using number_format to ensure proper decimal format
            if ($calculated_price > 0) {
                // The key line - ensure correct formatting for the database
                $formatted_rate = number_format(floatval($calculated_price), 2, '.', '');
                
                // Set the rate in all necessary places
                $post['newitems'][$key]['rate'] = $formatted_rate;
                $post['product_items'][$key]['rate'] = $formatted_rate;
                
                log_message('debug', 'INVOICE RATE SET: Product ' . $value->id . ' rate set to ' . $formatted_rate);
                
                // QUANTITY HANDLING - CRITICAL FIX
                // First check product_items for qty which comes directly from the cart form
                if (isset($post['product_items'][$key]['qty']) && (int)$post['product_items'][$key]['qty'] > 0) {
                    $post['newitems'][$key]['qty'] = (int)$post['product_items'][$key]['qty'];
                    log_message('debug', 'QTY SET: Using qty from product_items: ' . $post['newitems'][$key]['qty']);
                }
                // Then check object quantity (may come from cart data)
                else if (isset($value->quantity) && (int)$value->quantity > 0) {
                    $post['newitems'][$key]['qty'] = (int)$value->quantity;
                    log_message('debug', 'QTY SET: Using qty from product object: ' . $post['newitems'][$key]['qty']);
                }
                // Always ensure we have at least a quantity of 1
                else {
                    $post['newitems'][$key]['qty'] = 1;
                    log_message('debug', 'QTY SET: Defaulting to 1 as no qty found');
                }
                
                // CRITICAL: Also set the qty in product_items to ensure it's consistent
                $post['product_items'][$key]['qty'] = $post['newitems'][$key]['qty'];
            } else {
                log_message('error', 'INVOICE ERROR: No valid price found for product ' . $value->id);
            }
            
            $post['newitems'][$key]['recurring']        = $value->recurring;
            $post['newitems'][$key]['recurring_type']   = $value->recurring_type;
            $post['newitems'][$key]['custom_recurring'] = $value->custom_recurring;
            $post['newitems'][$key]['cycles']           = $value->cycles;
            if (!$value->is_digital) {
                if ((int) $value->quantity_number < 1) {
                    $message .= '- <u>'.$value->product_name.'</u> is out of stock <br>';
                    continue;
                }
                if ((int) $post['product_items'][$key]['qty'] > (int) $value->quantity_number) {
                    $message .= '- <u>'.$value->product_name.'</u> is only <u>'.$value->quantity_number.'</u> in stock <br>';
                }
            }
        }

        $order_data = $post;
        
        // Re-add contact_info for order
        if ($contact_info) {
            $order_data['contact_info'] = $contact_info;
        }

        if (!empty($message)) {
            return ['status' => false, 'message' => $message];
        }
        $billing_shipping = $this->clients_model->get_customer_billing_and_shipping_details($post['clientid']);
        $post             = array_merge($post, reset($billing_shipping));
        unset($post['billing_country']);
        unset($post['shipping_country']);
        $post['show_shipping_on_invoice'] = 'on';
        $post['number']                   = get_option('next_invoice_number');
        $order_data['order_date']         = $post['date']           = _d(date('Y-m-d'));
        $post['duedate']                  = _d(date('Y-m-d', strtotime('+'.get_option('invoice_due_after').' DAY', strtotime(date('Y-m-d')))));
        $post['show_quantity_as']         = 1;
        
        // Store payment method in order_data before removing it from post
        if (isset($post['payment_method'])) {
            $order_data['payment_method'] = $post['payment_method'];
        }
        
        // Store order note in order_data before removing it from post
        if (isset($post['order_note'])) {
            $order_data['order_note'] = $post['order_note'];
        }
        
        // Remove fields that don't exist in the invoices table
        $fields_to_remove = ['billing_phone', 'billing_email', 'payment_method', 'order_note', 'contact_info'];
        foreach ($fields_to_remove as $field) {
            if (isset($post[$field])) {
                unset($post[$field]);
            }
        }
        
        $this->load->model('payment_modes_model');
        $payment_modes = $this->payment_modes_model->get();
        foreach ($payment_modes as $modes) {
            if ($modes['selected_by_default']) {
                $post['allowed_payment_modes'][] = $modes['id'];
            }
        }
        unset($order_data['newitems']);
        unset($post['product_items']);
        $post['currency'] = $this->currencies_model->get_base_currency()->id;

        $invoice_insert_items = [];
        $invoice_order_items  = [];
        $result               = [];
        $init_tax             = [];
        $total                = $subtotal                = 0;

        foreach ($post['newitems'] as $key => $items) {
            if (0 != $items['recurring']) {
                $invoice_insert_items[$key] = $items;
                $invoice_order_items[$key]  = $order_data['product_items'][$key];
                unset($post['newitems'][$key]);
                unset($order_data['product_items'][$key]);
                continue;
            }
            $qty = isset($items['qty']) ? $items['qty'] : 1;
            $subtotal += $items['rate'] * $qty;
            $total = $subtotal;
            if (!empty($items['taxname'])) {
                foreach ($items['taxname'] as $tax) {
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
                    $total += ($items['rate'] * $qty) / 100 * $tax_array[1];
                }
            }

            unset($post['newitems'][$key]['recurring']);
            unset($post['newitems'][$key]['recurring_type']);
            unset($post['newitems'][$key]['custom_recurring']);
            unset($post['newitems'][$key]['cycles']);
        }
        
        // Clear out any existing recalculation
        unset($post['subtotal']);
        unset($post['total']);
        unset($post['coupon_discount']);
        
        // DEBUG: Log the original products array to ensure qty is being passed correctly
        foreach ($product as $idx => $prod_obj) {
            $prod_qty = isset($prod_obj->quantity) ? $prod_obj->quantity : 'NULL';
            $prod_rate = isset($prod_obj->direct_price) ? $prod_obj->direct_price : 
                       (isset($prod_obj->variation_rate) ? $prod_obj->variation_rate : $prod_obj->rate);
            log_message('debug', 'PRODUCT_OBJECT[' . $idx . ']: id=' . $prod_obj->id . 
                ', qty=' . $prod_qty . ', rate=' . $prod_rate);
        }
        
        // First fix the item rates and quantity in newitems
        foreach ($post['newitems'] as $idx => $item) {
            // Ensure rate is properly formatted as a float with 2 decimal places
            if (isset($item['rate'])) {
                $post['newitems'][$idx]['rate'] = number_format((float)$item['rate'], 2, '.', '');
            }
            
            // Ensure qty is properly set and is an integer
            // IMPORTANT: By this point, qty should already be correctly set in the previous block
            // This is just a safety measure to ensure it's an integer
            if (isset($post['newitems'][$idx]['qty'])) {
                $post['newitems'][$idx]['qty'] = (int)$post['newitems'][$idx]['qty'];
                if ($post['newitems'][$idx]['qty'] < 1) {
                    $post['newitems'][$idx]['qty'] = 1;
                }
            } else {
                $post['newitems'][$idx]['qty'] = 1;
            }
            
            // Double check that calculation values are valid
            $item_rate = (float)$post['newitems'][$idx]['rate'];
            $item_qty = (int)$post['newitems'][$idx]['qty'];
            $item_total = $item_rate * $item_qty;
            
            log_message('debug', 'FINAL_ITEM[' . $idx . ']: ' . 
                'rate=' . $post['newitems'][$idx]['rate'] . 
                ', qty=' . $post['newitems'][$idx]['qty'] . 
                ', total=' . $item_total . 
                ', product_id=' . (isset($product[$idx]->id) ? $product[$idx]->id : 'unknown'));
        }
        
        // Now recalculate subtotal and total based on fixed values
        $subtotal = 0;
        $total = 0;
        
        foreach ($post['newitems'] as $item) {
            // Safety validation - ensure we have numeric values
            $item_rate = isset($item['rate']) ? (float)$item['rate'] : 0;
            $item_qty = isset($item['qty']) ? (int)$item['qty'] : 1;
            
            // Calculate line subtotal
            $item_subtotal = $item_rate * $item_qty;
            
            log_message('debug', 'CALCULATION: Rate=' . $item_rate . ' * Qty=' . $item_qty . ' = Subtotal=' . $item_subtotal);
            
            $subtotal += $item_subtotal;
            
            // Calculate tax for this item
            $item_tax = 0;
            if (!empty($item['taxname'])) {
                foreach ($item['taxname'] as $tax) {
                    if (!is_array($tax)) {
                        $tax_array = explode('|', $tax);
                        if (count($tax_array) > 1) {
                            $tax_rate = (float)$tax_array[1];
                            $item_tax += ($item_subtotal * $tax_rate / 100);
                        }
                    }
                }
            }
            
            $total += $item_subtotal + $item_tax;
        }
        
        // Apply coupon discount
        $coupon_discount = 0;
        if (!empty($coupon)) {
            if ($coupon->type == '%') {
                $coupon_discount = $total * $coupon->amount / 100;
            } else {
                $coupon_discount = $coupon->amount;
            }
            $total = $total - $coupon_discount;
        }
        
        // Set exact values in the post data
        $post['subtotal'] = number_format($subtotal, 2, '.', '');
        $post['coupon_discount'] = number_format($coupon_discount, 2, '.', '');
        $post['total'] = number_format($total, 2, '.', '');
        
        // Update order data with the same exact values
        $order_data['subtotal'] = $post['subtotal'];
        $order_data['total'] = $post['total'];
        
        log_message('debug', 'FINAL_CALCULATION: Subtotal=' . $post['subtotal'] . ', Discount=' . $post['coupon_discount'] . ', Total=' . $post['total']);

        $count = 0;
        if (!empty($post['newitems'])) {
            $newitem_key = count($post['newitems']);
            $post['newitems'][$newitem_key]['unit']             = '';
            $post['newitems'][$newitem_key]['order']            = $key + 2;
            $post['newitems'][$newitem_key]['description']      = _l('flat_shipping');
            $post['newitems'][$newitem_key]['long_description'] = '';
            $post['newitems'][$newitem_key]['taxname']          = (!empty((get_option('product_tax_for_shipping_cost')))) ? unserialize(get_option('product_tax_for_shipping_cost')) : '';
            $post['newitems'][$newitem_key]['rate']             = get_option('product_flat_rate_shipping');
            $post['newitems'][$newitem_key]['qty']              = 1;
            $post['newitems'][$newitem_key]['recurring']        = 0;
            $post['newitems'][$newitem_key]['recurring_type']   = '';
            $post['newitems'][$newitem_key]['custom_recurring'] = '';
            $post['newitems'][$newitem_key]['cycles']           = 0;

            $subtotal += get_option('product_flat_rate_shipping');
            $total += get_option('product_flat_rate_shipping');
            $coupon_discount = 0;
            if (!empty($coupon)) {
                if ($coupon->type == '%') {
                    $coupon_discount = $total * $coupon->amount / 100;
                } else {
                    $coupon_discount = $coupon->amount;
                }
                $total = $total - $coupon_discount;
            }

            if (!empty($post['newitems'][$newitem_key]['taxname'])) {
                foreach ($post['newitems'][$newitem_key]['taxname'] as $tax) {
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
                    $total += (get_option('product_flat_rate_shipping')) / 100 * $tax_array[1];
                }
            }

            $post['subtotal']           = $subtotal;
            $post['coupon_discount']    = $coupon_discount;
            $post['total']              = $total;

            $count            = 1;
            
            // Debug log the full newitems array before invoice creation
            log_message('debug', 'BEFORE_INVOICE_CREATE: ' . json_encode($post['newitems']));
            
            // Dump the full item data for debugging
            foreach ($post['newitems'] as $idx => $item) {
                log_message('debug', 'INVOICE_ITEM[' . $idx . ']: ' . 
                    'description=' . $item['description'] . 
                    ', rate=' . $item['rate'] . 
                    ', qty=' . $item['qty']);
            }
            
            // Final check: Ensure all newitems have valid rates and quantities
            foreach ($post['newitems'] as $idx => $item) {
                if (!isset($item['rate']) || empty($item['rate'])) {
                    log_message('error', 'INVOICE_ERROR: Item #' . $idx . ' has no rate, setting default');
                    $post['newitems'][$idx]['rate'] = '0.00';
                }
                
                if (!isset($item['qty']) || empty($item['qty'])) {
                    log_message('error', 'INVOICE_ERROR: Item #' . $idx . ' has no quantity, setting default to 1');
                    $post['newitems'][$idx]['qty'] = 1;
                }
            }
            
            // Log the final total values being sent to the invoice
            log_message('debug', 'INVOICE_FINAL_VALUES: Subtotal=' . $post['subtotal'] . 
                ', Discount=' . $post['coupon_discount'] . 
                ', Total=' . $post['total']);
            
            $id = $this->invoices_model->add($post);
            if ($id) {
                $result[]                 = true;
                $res                      = $this->invoices_model->get($id);
                $order_data['status']     = $res->status;
                $order_data['invoice_id'] = $id;
                $this->add_order($order_data);
                
                // Debug log the created invoice info
                log_message('debug', 'INVOICE_CREATED: ID=' . $id . ', Total=' . $res->total);
                
                // Verify the created invoice values match expected values
                if ($res->total != $post['total']) {
                    log_message('error', 'INVOICE_TOTAL_MISMATCH: Expected=' . $post['total'] . ', Actual=' . $res->total);
                }
            }
        }
        
        if (!empty($invoice_insert_items)) {
            foreach ($invoice_insert_items as $key => $new_invoice_item) {
                $total = $subtotal = 0;

                $post['newitems']            = [];
                $order_data['product_items'] = [];
                $post['recurring']           = $new_invoice_item['recurring'];
                $post['recurring_type']      = $new_invoice_item['recurring_type'];
                $post['custom_recurring']    = $new_invoice_item['custom_recurring'];
                $post['cycles']              = $new_invoice_item['cycles'];

                unset($new_invoice_item['recurring']);
                unset($new_invoice_item['recurring_type']);
                unset($new_invoice_item['custom_recurring']);
                unset($new_invoice_item['cycles']);

                $post['number'] = get_option('next_invoice_number');

                $post['newitems'][$key]            = $new_invoice_item;
                $order_data['product_items'][$key] = $invoice_order_items[$key];

                // Make sure rate is correctly formatted
                if (isset($post['newitems'][$key]['rate'])) {
                    $post['newitems'][$key]['rate'] = number_format(floatval($post['newitems'][$key]['rate']), 2, '.', '');
                    log_message('debug', 'RECURRING_ITEM: Rate formatted to ' . $post['newitems'][$key]['rate']);
                }
                
                $qty = isset($new_invoice_item['qty']) ? $new_invoice_item['qty'] : 1;
                $subtotal += $new_invoice_item['rate'] * $qty;
                $total = $subtotal;
                $coupon_discount = 0;
                if (!empty($coupon)) {
                    if ($coupon->type == '%') {
                        $coupon_discount = $total * $coupon->amount / 100;
                    } else {
                        $coupon_discount = $coupon->amount;
                    }
                    $total = $total - $coupon_discount;
                }
                if (!empty($new_invoice_item['taxname'])) {
                    foreach ($new_invoice_item['taxname'] as $tax) {
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
                        $total += ($new_invoice_item['rate'] * $qty) / 100 * $tax_array[1];
                    }
                }

                $order_data['subtotal'] = $post['subtotal'] = $subtotal;
                $post['coupon_discount'] = $coupon_discount;
                $order_data['total']    = $post['total']    = $total;

                // Store payment method in order_data before removing it from post
                if (isset($post['payment_method'])) {
                    $order_data['payment_method'] = $post['payment_method'];
                }

                // Store order note in order_data before removing it from post
                if (isset($post['order_note'])) {
                    $order_data['order_note'] = $post['order_note'];
                }

                // Remove fields that don't exist in the invoices table
                $fields_to_remove = ['billing_phone', 'billing_email', 'payment_method', 'order_note', 'contact_info'];
                foreach ($fields_to_remove as $field) {
                    if (isset($post[$field])) {
                        unset($post[$field]);
                    }
                }

                $id               = $this->invoices_model->add($post);
                if ($id) {
                    $result[]                 = true;
                    $res                      = $this->invoices_model->get($id);
                    $order_data['status']     = $res->status;
                    $order_data['invoice_id'] = $id;
                    $this->add_order($order_data);
                }
            }
        }
        if (count($invoice_insert_items) + $count == count($result)) {
            if (1 == count($result)) {
                return ['status' => true, 'single_invoice' => true,'invoice_id' => $id, 'invoice_hash' => $res->hash];
            }
            return ['status' => true, 'single_invoice' => false];
        }
        return ['status' => false, 'message' => _l('order_fail')];
    }
}