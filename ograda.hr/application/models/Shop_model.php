<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shop_model extends App_Model
{
    

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  string
     * @return array
     * Get email template by type
     */
    public function getAllProducts($like = false)
    {

        if($like) {
            $likeArray = explode(' ', $like);
            foreach ($likeArray as $r) {
                $this->db->like('description', $r);
            }
        }

        return $this->db->get(db_prefix() . 'items')->result();
    }

    public function getProductAttribute($productName) {

        $this->db->select("Name, attribute1_values, attribute2_values"); 
        $this->db->from("tblproducts");
        $this->db->where("Name", $productName);
        
        $query = $this->db->get();
        return $query->row(); 
    }

    // public function getProductPrice($productName) {
    //     $this->db->select('regular_price');
    //     $this->db->from('tblproducts');
    //     $this->db->where('Name', $productName);
    //     $query = $this->db->get();

    //     return $query->row(); 
    // }

    public function getProductPrice($baseName,$material, $height)
    {
        $this->db->select('Name, regular_price');
        $this->db->from('tblproducts');

        $fullName = $baseName . ' - ' . $material . ', ' . $height;
        $this->db->where('Name', $fullName);
    
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getPriceByHeight($baseName, $height)
    {
        $this->db->select('Name, regular_price');
        $this->db->from('tblproducts');
       
        $fullName = $baseName . ' - ' . $height;
        $this->db->where('Name', $fullName);

        $query = $this->db->get();
        return $query->result_array();
    }

    public function getPricesByBaseName($baseName) {
        $this->db->select('Name, regular_price'); 
        $this->db->from('tblproducts'); 
        $this->db->like('Name', $baseName . ' -', 'after');
        $query = $this->db->get();
    
        return $query->result_array(); 
    }
    

}