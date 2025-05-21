<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Bots Controller
 * 
 * Handles the functionality related to bots management.
 */
class Bots extends AdminController
{
    /**
     * Constructor
     * 
     * Loads necessary models.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['bots_model', 'campaigns_model']);
    }

    /**
     * Index method
     * 
     * Loads the main management page for bots.
     */
    public function index()
    {
        $data['title'] = _l('bots');
        $this->load->view('bots/manage', $data);
    }
    /**
     * Table method
     *
     * Loads the data for the specified table.
     *
     * @param string $table The table name.
     */
    public function table($table)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSAPP_MODULE, 'tables/' . $table));
    }
    /**
     * Form method
     * 
     * Loads the view for creating or editing a bot.
     * 
     * @param string $id The ID of the bot (optional).
     */
    public function form($id = '')
    {
        // Determine if the user is creating or editing a bot
        $permission = (empty($id)) ? 'create' : 'edit';

        // Load necessary data for the form
        $data['bot_types'] = whatsapp_get_bot_type();
        $data['templates'] = get_whatsapp_template();

        // Load the bot data if editing
        if (!empty($id)) {
            $data['bot'] = $this->bots_model->getMessageBot($id);

            // Decode JSON data only if it is not null or empty
            $data['bot']['header_params'] = !empty($data['bot']['header_params']) ? json_decode($data['bot']['header_params'], true) : [];
            $data['bot']['body_params'] = !empty($data['bot']['body_params']) ? json_decode($data['bot']['body_params'], true) : [];
            $data['bot']['footer_params'] = !empty($data['bot']['footer_params']) ? json_decode($data['bot']['footer_params'], true) : [];
        } else {
            $data['bot'] = null; // Initialize bot as null if creating a new bot
        }
        $data['title'] = isset($data['bot']) ? _l('edit_bot') : _l('create_bot');

        // Load the form view
        $this->load->view('bots/form_bot', $data);
    }


    public function get_template_map()
    {
        if ($this->input->is_ajax_request()) {
            // Load the helper if not already loaded
            $this->load->helper('whatsapp');
    
            // Call the helper function
            $templateId = $this->input->post('template_id');
            $type = $this->input->post('type') ?? 'bot';  // Default to 'campaign'
    
            $result = get_template_maper($templateId, $type);
    
            echo json_encode($result);
        }
    }



    /**
     * Save Bots method
     * 
     * Saves bot data from POST request.
     */
public function saveBot()
{
    if ($this->input->post()) {
        // Determine permission based on whether the bot is being created or edited
        $permission = empty($this->input->post('id')) ? 'create' : 'edit';

        // Check if the staff has the appropriate permission
        if (!staff_can($permission, 'whatsapp_message_bot')) {
            access_denied();
        }

        // Process the menu_structure field (if it's an array) by encoding it to JSON
        $postData = $this->input->post();
        if (isset($postData['menu_items']) && is_array($postData['menu_items'])) {
            $postData['menu_items'] = json_encode($postData['menu_items']);
        }
      // Process FlowBot data (flow_data)
        if (isset($postData['flow_data']) && is_array($postData['flow_data'])) {
            $postData['flow_data'] = json_encode($postData['flow_data']);
        }
        // Save the bot data using the updated postData
        $res = $this->bots_model->saveBots($postData);

        // Handle file uploads for the bot if the save operation was successful
        if ($res['status']) {
            whatsapp_handle_upload($res['id']);
            set_alert($res['status'], $res['message']);
        } else {
            set_alert('danger', $res['message']);
        }
    }
    redirect(admin_url('whatsapp/bots'));
}



    /**
     * Change Active Status method
     * 
     * Changes the active status of a bot.
     * 
     * @param string $type The type of the bot.
     * @param string $id The ID of the bot.
     * @param string $status The new status of the bot.
     */
    public function change_active_status($type, $id, $status)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        // Change the active status of the bot
        $response = $this->bots_model->change_active_status($type, $id, $status);
        echo json_encode($response);
    }

    /**
     * Delete Bot Files method
     * 
     * Deletes the files associated with a bot.
     * 
     * @param string $id The ID of the bot.
     */
    public function delete_bot_files($id)
    {
        $res = $this->bots_model->delete_bot_files($id);

        if ($res['status']) {
            set_alert('success', $res['message']);
        } else {
            set_alert('danger', $res['message']);
        }

        redirect(admin_url('whatsapp/bots/form/' . $id));
    }

    public function delete($id)
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $response = $this->bots_model->deleteBot($id);
        echo json_encode($response);
    }


}
