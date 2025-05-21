<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Product_categories_model - Model for product categories
 */
class Product_categories_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get categories
     * 
     * @param integer $id Optional category ID
     * @return mixed Object if ID provided, array of all categories otherwise
     */
    public function get($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('p_category_id', $id);
            $category = $this->db->get(db_prefix().'product_categories')->row_array();
            
            // Rename field to be consistent
            if ($category) {
                $category['id'] = $category['p_category_id'];
                $category['name'] = $category['p_category_name'];
                $category['description'] = $category['p_category_description'];
            }

            return $category;
        }
        
        $categories = $this->db->get(db_prefix().'product_categories')->result_array();
        
        // Process each category to ensure consistent field names
        foreach ($categories as &$category) {
            $category['id'] = $category['p_category_id'];
            $category['name'] = $category['p_category_name'];
            $category['description'] = $category['p_category_description'];
            // Add image field if it exists in the table, otherwise set to null
            $category['image'] = null;
        }

        return $categories;
    }

    /**
     * Add new category
     * 
     * @param array $data Category data
     * @return integer|boolean ID of inserted category or false
     */
    public function add($data)
    {
        $this->db->insert(db_prefix().'product_categories', [
            'p_category_name' => $data['name'] ?? '',
            'p_category_description' => $data['description'] ?? ''
        ]);
        
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('Product Category Added [ID:'.$insert_id.']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Edit category
     * 
     * @param array $data Category data
     * @return boolean Success or failure
     */
    public function edit($data)
    {
        $this->db->where('p_category_id', $data['id']);
        $update_data = [
            'p_category_name' => $data['name'] ?? '',
            'p_category_description' => $data['description'] ?? ''
        ];
        
        $res = $this->db->update(db_prefix().'product_categories', $update_data);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Category Updated [ID:'.$data['id'].']');
        }

        return $res;
    }

    /**
     * Delete category
     * 
     * @param integer $id Category ID
     * @return boolean Success or failure
     */
    public function delete($id)
    {
        $this->db->where('p_category_id', $id);
        $this->db->delete(db_prefix().'product_categories');
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Product Category Deleted [ID:'.$id.']');
            return true;
        }

        return false;
    }
} 