<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Templates Controller
 *
 * Handles operations related to WhatsApp templates.
 */
class Templates extends AdminController
{
    /**
     * Constructor
     *
     * Initializes the controller and checks module activation status.
     */
    public function __construct()
    {
        parent::__construct();

        // Check if the whatsapp module is inactive; deny access if so
        if ($this->app_modules->is_inactive('whatsapp')) {
            access_denied();
        }

        $this->load->model('whatsapp_interaction_model'); // Load the WhatsApp interaction model
    }

    /**
     * Index method
     *
     * Loads the main view for WhatsApp templates management.
     */
    public function index()
    {
        // Check if user has permission to view WhatsApp templates
        if (!staff_can('view', 'whatsapp_template')) {
            access_denied();
        }

        $viewData['title'] = _l('templates'); // Set view title

        $this->load->view('templates', $viewData); // Load templates view
    }

    /**
     * Get Table Data method
     *
     * Retrieves data for the templates table via AJAX.
     *
     * @return bool Returns false if the request is not an AJAX request.
     */
    public function get_table_data()
    {
        if (!$this->input->is_ajax_request()) {
            return false;
        }

        $this->app->get_table_data(module_views_path(WHATSAPP_MODULE, 'tables/templates')); // Get table data
    }

    /**
     * Load Templates method
     *
     * Loads WhatsApp templates asynchronously.
     *
     * @return bool Returns false if the request is not an AJAX request or if the user lacks permission.
     */
    public function load_templates()
    {
        if (!$this->input->is_ajax_request() && !staff_can('load_template', 'whatsapp_template')) {
            return false;
        }

        $response = $this->whatsapp_interaction_model->load_templates(); // Call model method to load templates

        if (false == $response['success']) {
            // If loading templates fails, return error response
            echo json_encode([
                'success' => $response['success'],
                'type'    => $response['type'],
                'message' => $response['message'],
            ]);
            exit();
        }

        // If templates are loaded successfully, return success response
        echo json_encode([
            'success' => true,
            'type'    => 'success',
            'message' => _l('template_data_loaded'),
        ]);
    }

    /**
     * Create method
     *
     * Displays the form for creating a new WhatsApp template.
     */
    public function create()
    {
        // Check if user has permission to create a WhatsApp template
        if (!staff_can('create', 'whatsapp_template')) {
            access_denied();
        }

        $viewData['title'] = _l('create_template'); // Set view title

        $this->load->view('template_form', $viewData); // Load the create template view
    }

    /**
     * Save method
     *
     * Handles the saving of a new WhatsApp template.
     */
    public function save()
    {
        // Check if user has permission to save a WhatsApp template
        if (!staff_can('create', 'whatsapp_template')) {
            access_denied();
        }

        $data = $this->input->post(); // Get POST data

        $response = $this->whatsapp_interaction_model->save_template($data); // Call model method to save the template

        if ($response['success']) {
            // If saving is successful, redirect with success message
            set_alert('success', _l('template_saved_successfully'));
            redirect(admin_url('templates'));
        } else {
            // If saving fails, redirect with error message
            set_alert('danger', $response['message']);
            redirect(admin_url('templates/create'));
        }
    }

    /**
     * Edit method
     *
     * Displays the form for editing an existing WhatsApp template.
     *
     * @param int $id The ID of the template to edit.
     */
    public function edit($id)
    {
        // Check if user has permission to edit a WhatsApp template
        if (!staff_can('edit', 'whatsapp_template')) {
            access_denied();
        }

        $template = $this->whatsapp_interaction_model->get_template($id); // Get the template by ID

        if (!$template) {
            // If template does not exist, show 404 page
            show_404();
        }

        $viewData['template'] = $template;
        $viewData['title'] = _l('edit_template'); // Set view title

        $this->load->view('template_form', $viewData); // Load the edit template view
    }

    /**
     * Update method
     *
     * Handles the updating of an existing WhatsApp template.
     *
     * @param int $id The ID of the template to update.
     */
    public function update($id)
    {
        // Check if user has permission to update a WhatsApp template
        if (!staff_can('edit', 'whatsapp_template')) {
            access_denied();
        }

        $data = $this->input->post(); // Get POST data

        $response = $this->whatsapp_interaction_model->update_template($id, $data); // Call model method to update the template

        if ($response['success']) {
            // If updating is successful, redirect with success message
            set_alert('success', _l('template_updated_successfully'));
            redirect(admin_url('templates'));
        } else {
            // If updating fails, redirect with error message
            set_alert('danger', $response['message']);
            redirect(admin_url('templates/edit/' . $id));
        }
    }
}
