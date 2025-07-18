<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        show_404();
    }

    public function activate()
    {
        echo json_encode(['status' => true, 'original_url' => $this->input->post('original_url')]);
    }

    public function upgrade_database()
    {
        echo json_encode(['status' => true, 'original_url' => $this->input->post('original_url')]);
    }
}
