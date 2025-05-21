<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_product($data)
    {
        $variations = $data['variations'];
        unset($data['variations']);

        $this->db->insert(db_prefix() . 'product_master', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Product Added [ ID:' . $insert_id . ', '. $data['product_name'].', Staff id ' . get_staff_user_id() . ' ]');

            if (isset($variations['variation'])) {
                $variation_count = count($variations['variation']);
                for ($variation_index = 0; $variation_index < $variation_count; $variation_index++) {
                    $this->db->where('name', $variations['variation'][$variation_index]);
                    $variation_row = $this->db->get(db_prefix() . 'variations')->row();
                    if ($variation_row) {
                        $this->db->where('variation_id', $variation_row->id);
                        $this->db->where('value', $variations['variation_value'][$variation_index]);
                        $variation_value_row = $this->db->get(db_prefix() . 'variation_values')->row();
                        if ($variation_value_row) {
                            $product_variation_data = [
                                'product_id' => $insert_id,
                                'variation_id' => $variation_row->id,
                                'variation_value_id' => $variation_value_row->id,
                                'rate' => $variations['rate'][$variation_index],
                                'quantity_number' => $variations['quantity_number'][$variation_index],
                            ];
                            $this->db->insert(db_prefix() . 'product_variations', $product_variation_data);
                        }
                    }
                }
            }

            return $insert_id;
        }

        return false;
    }

    public function get_by_id_product($id = false)
    {
        $this->db->join('product_categories', db_prefix() . 'product_categories.p_category_id='.db_prefix() . 'product_master.product_category_id', 'LEFT');
        if ($id) {
            $this->db->where_in('id', $id);
            if (is_array($id)) {
                $product = $this->db->get(db_prefix() . 'product_master')->result();
                foreach ($product as $product_row) {
                    if ($product_row->is_variation) {
                        $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                        $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                        $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                        $this->db->where('product_id', $product_row->id);
                        $this->db->order_by('variation_id');
                        $product_row->variations = $this->db->get(db_prefix() . 'product_variations')->result();
                    }
                }
            } else {
                $product = $this->db->get(db_prefix() . 'product_master')->row();
                if ($product && $product->is_variation) {
                    $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                    $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                    $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                    $this->db->where('product_id', $product->id);
                    $this->db->order_by('variation_id');
                    $product->variations = $this->db->get(db_prefix() . 'product_variations')->result();
                }
            }

            return $product;
        }
        $products = $this->db->get(db_prefix() . 'product_master')->result_array();
        foreach ($products as $product_index => $product) {
            if ($product['is_variation']) {
                $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                $this->db->where('product_id', $product['id']);
                $this->db->order_by('variation_id');
                $products[$product_index]['variations'] = $this->db->get(db_prefix() . 'product_variations')->result();
            }
        }

        return $products;
    }

	public function get_by_cart_product($cart_data)
	{
		$products = [];
		// Debug log cart data at the start
		log_message('debug', 'CART DATA RECEIVED IN MODEL: ' . json_encode($cart_data));
		
		foreach ($cart_data as $key => $value) {
			// Use get_by_id_product instead of undefined get() method
			$products_model = $this->get_by_id_product($value['product_id']);
			
			// Handle both array and object returns from get_by_id_product
			if (is_array($products_model)) {
				$products_model = !empty($products_model) ? $products_model[0] : null;
			}
			
			if ($products_model) {
				// CRITICAL: Force the quantity to be the one from the cart_data
				// This ensures we always use the latest quantity value
				$products_model->quantity = (int)$value['quantity'];
				log_message('debug', 'CART_PRODUCT: Product ' . $products_model->id . ' - Setting quantity to ' . $products_model->quantity);
				
				// Handle calculated price from custom product calculations
				// This is the price that comes from the product page calculations
				if (isset($value['calculated_price']) && !empty($value['calculated_price']) && floatval($value['calculated_price']) > 0) {
					$products_model->direct_price = floatval($value['calculated_price']);
					log_message('debug', 'SET_PRICE: Product ' . $products_model->id . ' - Setting direct_price from calculated_price: ' . $products_model->direct_price);
				}
				
				// Handle selected height (custom field)
				if (isset($value['selected_height']) && !empty($value['selected_height'])) {
					$products_model->selected_height = $value['selected_height'];
				}
				
				// Handle selected material (custom field)
				if (isset($value['selected_material']) && !empty($value['selected_material'])) {
					$products_model->selected_material = $value['selected_material'];
				}
				
				// Always set default values for variation properties to avoid undefined property errors
				$products_model->variation_name = 'Vrsta materijala';  // Default name
				$products_model->variation_value = '';
				
				// Save the product variation ID from the cart data to ensure consistency
				if (isset($value['product_variation_id']) && !empty($value['product_variation_id'])) {
					$products_model->product_variation_id = $value['product_variation_id'];
					log_message('debug', 'SET_VARIATION: Product ' . $products_model->id . ' - Setting variation ID to ' . $products_model->product_variation_id);
					
					// Get variation details directly from the database
					$this->db->select('pv.*, v.name as variation_name, vv.value as variation_value');
					$this->db->from(db_prefix() . 'product_variations pv');
					$this->db->join(db_prefix() . 'variations v', 'v.id = pv.variation_id', 'left');
					$this->db->join(db_prefix() . 'variation_values vv', 'vv.id = pv.variation_value_id', 'left');
					$this->db->where('pv.id', $value['product_variation_id']);
					$variation = $this->db->get()->row();
					
					log_message('debug', 'VARIATION QUERY: ' . $this->db->last_query());
					
					if ($variation) {
						// Only override the defaults if we found actual values
						if (!empty($variation->variation_name)) {
							$products_model->variation_name = $variation->variation_name;
							log_message('debug', 'SET_VARIATION_NAME: ' . $products_model->variation_name);
						}
						if (!empty($variation->variation_value)) {
							$products_model->variation_value = $variation->variation_value;
							log_message('debug', 'SET_VARIATION_VALUE: ' . $products_model->variation_value);
						}
						if (isset($variation->rate) && $variation->rate > 0) {
							$products_model->variation_rate = floatval($variation->rate);
							log_message('debug', 'SET_VARIATION_RATE: ' . $products_model->variation_rate);
						}
					} else {
						log_message('error', 'Variation not found for ID: ' . $value['product_variation_id']);
						
						// Try to get variation by product_id
						$this->db->select('pv.*, v.name as variation_name, vv.value as variation_value');
						$this->db->from(db_prefix() . 'product_variations pv');
						$this->db->join(db_prefix() . 'variations v', 'v.id = pv.variation_id', 'left');
						$this->db->join(db_prefix() . 'variation_values vv', 'vv.id = pv.variation_value_id', 'left');
						$this->db->where('pv.product_id', $value['product_id']);
						$variation = $this->db->get()->row();
						
						if ($variation) {
							$products_model->variation_name = $variation->variation_name ?? 'Vrsta materijala';
							$products_model->variation_value = $variation->variation_value ?? '';
							log_message('debug', 'FALLBACK_VARIATION: Name=' . $products_model->variation_name . ', Value=' . $products_model->variation_value);
						}
					}
				}
				
				// Final price check - if we still have no valid price, use the base rate
				if ((!isset($products_model->direct_price) || floatval($products_model->direct_price) <= 0) && 
					(!isset($products_model->variation_rate) || floatval($products_model->variation_rate) <= 0) && 
					isset($products_model->rate) && floatval($products_model->rate) > 0) {
					log_message('debug', 'SET_PRICE: Product ' . $products_model->id . ' - Falling back to base rate: ' . $products_model->rate);
				}
				
				$products[] = $products_model;
			}
		}
        
        // Debug output all products and their prices
        foreach ($products as $product) {
            $directPrice = isset($product->direct_price) ? $product->direct_price : 'not set';
            $variationRate = isset($product->variation_rate) ? $product->variation_rate : 'not set';
            $baseRate = isset($product->rate) ? $product->rate : 'not set';
            
            log_message('debug', 'FINAL_PRODUCT: ' . $product->id . ' - Direct: ' . $directPrice . ', Variation: ' . $variationRate . ', Base: ' . $baseRate);
            
            // Also log variation data
            $variation_id = isset($product->product_variation_id) ? $product->product_variation_id : 'not set';
            $variation_name = isset($product->variation_name) ? $product->variation_name : 'not set';
            $variation_value = isset($product->variation_value) ? $product->variation_value : 'not set';
            
            log_message('debug', 'VARIATION_DATA: Product ' . $product->id . ' - ID: ' . $variation_id . ', Name: ' . $variation_name . ', Value: ' . $variation_value);
        }
		
		return $products;
	}

    public function get_by_id_product_afflect_variation($items)
    {
        $products = [];
        foreach ($items as $item) {
            $this->db->join('product_categories', db_prefix() . 'product_categories.p_category_id='.db_prefix() . 'product_master.product_category_id', 'LEFT');
            $this->db->where_in('id', $item['product_id']);
            $product = $this->db->get(db_prefix() . 'product_master')->row();
            
            // Log the item data for debugging
            log_message('debug', 'ITEM DATA IN AFFLECT_VARIATION: ' . json_encode($item));
            
            // Skip if product not found
            if (!$product) {
                log_message('error', 'Product not found for ID: ' . $item['product_id']);
                continue;
            }
            
            // Copy custom variant selections to the product object
            if (isset($item['selected_material']) && !empty($item['selected_material'])) {
                $product->selected_material = $item['selected_material'];
            }
            
            if (isset($item['selected_glass']) && !empty($item['selected_glass'])) {
                $product->selected_glass = $item['selected_glass'];
            }
            
            if (isset($item['selected_height']) && !empty($item['selected_height'])) {
                $product->selected_height = $item['selected_height'];
            }
            
            // Copy quantity from the item if available
            if (isset($item['qty'])) {
                $product->quantity = (int)$item['qty'];
                log_message('debug', 'Setting quantity from item[qty]: ' . $product->quantity);
            } else if (isset($item['quantity'])) {
                $product->quantity = (int)$item['quantity'];
                log_message('debug', 'Setting quantity from item[quantity]: ' . $product->quantity);
            } else {
                // Default to 1 if no quantity found
                $product->quantity = 1;
                log_message('debug', 'No quantity found, defaulting to 1');
            }
            
            // CRITICAL: Also ensure we have a qty property on each item - this is what the invoice uses
            $item['qty'] = $product->quantity;
            
            // PRICE HANDLING - Highest priority to lowest
            // 1. First check for direct_price in the item
            if (isset($item['direct_price']) && !empty($item['direct_price'])) {
                $product->direct_price = floatval($item['direct_price']);
                log_message('debug', 'PRICE SET: Using direct_price from item: ' . $product->direct_price);
            } 
            // 2. Then check for calculated_price in the item
            else if (isset($item['calculated_price']) && !empty($item['calculated_price'])) {
                $product->direct_price = floatval($item['calculated_price']);
                log_message('debug', 'PRICE SET: Using calculated_price from item: ' . $product->direct_price);
            }
            
            // Ensure the direct_price is correctly formatted as a float with 2 decimal places
            if (isset($product->direct_price)) {
                $product->direct_price = number_format(floatval($product->direct_price), 2, '.', '');
            }
            
            // Process database variation if set
            if (isset($item['product_variation_id']) && !empty($item['product_variation_id'])) {
                // Log that we're looking up a variation
                log_message('debug', 'Looking up variation: ' . $item['product_variation_id'] . ' for product: ' . $item['product_id']);
                
                // Use aliases for cleaner queries
                $this->db->select('pv.*, v.name as variation_name, vv.value as variation_value');
                $this->db->from(db_prefix() . 'product_variations pv');
                $this->db->join(db_prefix() . 'variations v', 'v.id = pv.variation_id', 'left');
                $this->db->join(db_prefix() . 'variation_values vv', 'vv.id = pv.variation_value_id', 'left');
                $this->db->where('pv.id', $item['product_variation_id']);
                
                $product_variation = $this->db->get()->row();
                log_message('debug', 'Variation query: ' . $this->db->last_query());
                
                if ($product_variation) {
                    $product->variation_name = $product_variation->variation_name;
                    $product->variation_value = $product_variation->variation_value;
                    $product->product_variation_id = $item['product_variation_id'];
                    
                    // Set the variation rate - but don't override direct_price if it's already set
                    if (!isset($product->direct_price) && $product_variation->rate > 0) {
                        $product->variation_rate = floatval($product_variation->rate);
                        log_message('debug', 'PRICE SET: Using variation_rate: ' . $product->variation_rate);
                    }
                    
                    $product->quantity_number = $product_variation->quantity_number;
                    
                    log_message('debug', 'Found variation: ' . $product_variation->variation_name . ' = ' . $product_variation->variation_value);
                    
                    // Append basic variation to product name
                    $product_name_parts = [$product->product_name];
                    $product_name_parts[] = '(' . $product_variation->variation_name . ': ' . $product_variation->variation_value . ')';
                    
                    // Build complete product name with all variations
                    $product->product_name = implode(' ', $product_name_parts);
                } else {
                    log_message('error', 'Variation not found for ID: ' . $item['product_variation_id']);
                }
            }
            
            // Final price check - ensure we have a valid price
            // If no direct_price or variation_rate, use the base rate
            if ((!isset($product->direct_price) || floatval($product->direct_price) <= 0) && 
                (!isset($product->variation_rate) || floatval($product->variation_rate) <= 0) && 
                isset($product->rate) && floatval($product->rate) > 0) {
                log_message('debug', 'PRICE SET: Falling back to base rate: ' . $product->rate);
            }
            
            // Final logging of the product data before adding to the array
            log_message('debug', 'FINAL PRODUCT DATA: ' . 
                ' id=' . $product->id .
                ' name=' . $product->product_name . 
                ' direct_price=' . (isset($product->direct_price) ? $product->direct_price : 'NULL') .
                ' variation_rate=' . (isset($product->variation_rate) ? $product->variation_rate : 'NULL') . 
                ' rate=' . $product->rate .
                ' quantity=' . (isset($product->quantity) ? $product->quantity : 'NULL')
            );
            
            $products[] = $product;
        }
        return $products;
    }

    public function get_by_id_variations($id)
    {
        if ($id) {
            $this->db->where('id', $id);
            $product = $this->db->get(db_prefix() . 'product_master')->row();
            if ($product) {
                $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                $this->db->where('product_id', $product->id);
                $this->db->order_by('variation_id');
                $product_variations = $this->db->get(db_prefix() . 'product_variations')->result();
                return $product_variations;
            }
        }

        return [];
    }

    public function get_by_id_variation_values($id, $variation_id = false)
    {
        if ($id) {
            $this->db->where('id', $id);
            $product = $this->db->get(db_prefix() . 'product_master')->row();
            if ($product) {
                $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                $this->db->where(db_prefix() . 'product_variations.product_id', $product->id);
                if ($variation_id) {
                    $this->db->where(db_prefix() . 'product_variations.variation_id', $variation_id);
                } else {
                    $this->db->order_by('variation_id');
                }
                $product_variations = $this->db->get(db_prefix() . 'product_variations')->result();
                return $product_variations;
            }
        }

        return [];
    }

    public function get_category_filter($p_category_id)
    {
        $this->db->where_in('p_category_id', $p_category_id);
        $this->db->order_by('product_master.product_category_id', 'ASC');

        return $this->get_by_id_product();
    }

    public function edit_product($data, $id)
    {
        $variations = [];
        if (isset($data['variations'])) {
            $variations = $data['variations'];
            unset($data['variations']);
        }

        $product = $this->get_by_id_product($id);
        $this->db->where('id', $id);
        $res = $this->db->update(db_prefix() . 'product_master', $data);
        if ($this->db->affected_rows() > 0) {
            if (!empty($data['quantity_number']) && $product->quantity_number != $data['quantity_number']) {
                log_activity('Product Quantity updated[ ID: '.$id.', From: '.$product->quantity_number.' To: '.$data['quantity_number'].' Staff id '.get_staff_user_id().']');
            }
            log_activity('Product Details updated[ ID: '.$id.', '.$product->product_name.', Staff id '.get_staff_user_id().' ]');
        }

        if (isset($variations['variation'])) {
            $variation_count = count($variations['variation']);
            for ($variation_index = 0; $variation_index < $variation_count; $variation_index++) {
                $this->db->where('name', $variations['variation'][$variation_index]);
                $variation_row = $this->db->get(db_prefix() . 'variations')->row();
                if ($variation_row) {
                    $this->db->where('variation_id', $variation_row->id);
                    $this->db->where('value', $variations['variation_value'][$variation_index]);
                    $variation_value_row = $this->db->get(db_prefix() . 'variation_values')->row();
                    if ($variation_value_row) {
                        $this->db->where('product_id', $id);
                        $this->db->where('variation_id', $variation_row->id);
                        $this->db->where('variation_value_id', $variation_value_row->id);
                        $product_variation_row = $this->db->get(db_prefix() . 'product_variations')->row();
                        if ($product_variation_row) {
                            $product_variation_data = [
                                'rate' => $variations['rate'][$variation_index],
                                'quantity_number' => $variations['quantity_number'][$variation_index],
                            ];
                            $this->db->where('id', $product_variation_row->id);
                            $this->db->update(db_prefix() . 'product_variations', $product_variation_data);
                            if ($this->db->affected_rows() > 0) {
                                log_activity('Product Variation Details Updated [ ID: ' . $product_variation_row->id . ', ' . $variation_row->name . ', ' . $variation_value_row->value . ' ]');
                            }
                        } else {
                            $product_variation_data = [
                                'product_id' => $id,
                                'variation_id' => $variation_row->id,
                                'variation_value_id' => $variation_value_row->id,
                                'rate' => $variations['rate'][$variation_index],
                                'quantity_number' => $variations['quantity_number'][$variation_index],
                            ];
                            $this->db->insert(db_prefix() . 'product_variations', $product_variation_data);
                            $insert_id = $this->db->insert_id();
                            log_activity('Product Variation Details Added [ ID: ' . $insert_id . ', ' . $variation_row->name . ', ' . $variation_value_row->value . ' ]');
                        }
                    }
                }
            }
            
            $this->db->where('product_id', $id);
            $product_variations = $this->db->get(db_prefix() . 'product_variations')->result_array();
            foreach ($product_variations as $product_variation) {
                $product_variation_exist = false;
                $this->db->where('id', $product_variation['variation_id']);
                $variation_row = $this->db->get(db_prefix() . 'variations')->row();
                $this->db->where('id', $product_variation['variation_value_id']);
                $variation_value_row = $this->db->get(db_prefix() . 'variation_values')->row();
                if ($variation_row && $variation_value_row) {
                    $variation_count = count($variations['variation']);
                    for ($variation_index = 0; $variation_index < $variation_count; $variation_index++) {
                        if ($variation_row->name == $variations['variation'][$variation_index] && $variation_value_row->value == $variations['variation_value'][$variation_index]) {
                            $product_variation_exist = true;
                            break;
                        }
                    }
                }
                if (!$product_variation_exist) {
                    $this->db->where('id', $product_variation['id']);
                    $this->db->delete(db_prefix() . 'product_variations');
                    log_activity('Product Variation Details Deleted [ ID: ' . $product_variation['id'] . ' ]');
                }
            }
        }

        if ($res) {
            return true;
        }

        return false;
    }

    public function delete_by_id_product($id)
    {
        $product  = $this->get_by_id_product($id);
        $relPath  = get_upload_path_by_type('products').'/';
        $fullPath = $relPath.$product->product_image;
        unlink($fullPath);
        if (!empty($id)) {
            $this->db->where('id', $id);
        }
        $result = $this->db->delete(db_prefix() . 'product_master');
        log_activity('Product Deleted[ ID: '.$id.', '.$product->product_name.', Staff id '.get_staff_user_id().' ]');

        $this->db->where('product_id', $id);
        $product_variations = $this->db->get(db_prefix() . 'product_variations')->result_array();
        foreach ($product_variations as $product_variation) {
            $this->db->where('id', $product_variation['id']);
            $this->db->delete(db_prefix() . 'product_variations');
            log_activity('Product Variation Details Deleted [ ID: ' . $product_variation['id'] . ' ]');
        }

        return $result;
    }

    /**
     * Get products by category ID
     *
     * @param integer $category_id Category ID
     * @return array Products in the specified category
     */
    public function get_by_category($category_id)
    {
        // Make sure category_id is set and numeric
        if (!$category_id || !is_numeric($category_id)) {
            return [];
        }
        
        $this->db->join('product_categories', db_prefix() . 'product_categories.p_category_id='.db_prefix() . 'product_master.product_category_id', 'LEFT');
        $this->db->where('product_category_id', $category_id);
        $products = $this->db->get(db_prefix() . 'product_master')->result_array();
        
        // If no products found, return empty array
        if (empty($products)) {
            return [];
        }
        
        foreach ($products as $product_index => $product) {
            if (isset($product['is_variation']) && $product['is_variation'] == 1) {
                $this->db->select(db_prefix() . 'product_variations.*, ' . db_prefix() . 'variations.name as variation_name, ' . db_prefix() . 'variation_values.value as variation_value');
                $this->db->join('variations', db_prefix() . 'variations.id=' . db_prefix() . 'product_variations.variation_id', 'LEFT');
                $this->db->join('variation_values', db_prefix() . 'variation_values.id=' . db_prefix() . 'product_variations.variation_value_id', 'LEFT');
                $this->db->where('product_id', $product['id']);
                $this->db->order_by('variation_id');
                $products[$product_index]['variations'] = $this->db->get(db_prefix() . 'product_variations')->result();
            }
        }

        return $products;
    }
}
