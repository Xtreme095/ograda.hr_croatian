<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ContactModel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function insert($data) {
        return $this->db->insert('tblcontact', $data);
    }
}
