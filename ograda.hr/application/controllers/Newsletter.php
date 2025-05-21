<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class Newsletter extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation'); 
        $this->load->model('NewsletterModel');  
    }

    public function subscribe()
    {
        
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[tblnewsletter.email]');

            if ($this->form_validation->run()) {
                $email = $this->input->post('email');
                $this->NewsletterModel->insert(['email' => $email, 'created_at' => date('Y-m-d H:i:s')]);

                $this->session->set_flashdata('modal_message', [
                    'type' => 'success',
                    'text' => 'Uspješno ste se prijavili.!'
                ]);
            } else {
                $this->session->set_flashdata('modal_message', [
                    'type' => 'danger',
                    'text' => 'Nevažeći email ili ste već prijavljeni. Pokušajte ponovno.!'
                ]);
            }

            redirect(base_url());
    
    }
    

   

    
}

