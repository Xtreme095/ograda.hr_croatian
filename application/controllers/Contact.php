<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('ContactModel'); 
        $this->load->library('form_validation'); 
    }

    public function save() {
        
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[3]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[tblcontact.email]');
        $this->form_validation->set_rules('message', 'Message', 'required|min_length[4]');

        if ($this->form_validation->run()) {
            
            $data = [
                'name'      => $this->input->post('name', TRUE),
                'email'     => $this->input->post('email', TRUE),
                'message'   => $this->input->post('message', TRUE),
                'created_at' => date('Y-m-d H:i:s')
            ];

            
            $this->ContactModel->insert($data);

            
            $this->session->set_flashdata('success', 'Vaša poruka je uspešno poslata!');
        } else {
            
            $this->session->set_flashdata('error', 'Nevažeći unos ili imejl već postoji');
        }

        
        redirect(base_url('contact'));
    }
}
