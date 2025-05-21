<?php

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Controller for WhatsApp integration functionalities.
 */
class Whatsapp extends AdminController
{
    /**
     * Constructor for Whatsapp controller.
     * Loads necessary models and libraries.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['whatsapp_interaction_model','QuickReply_model','leads_model','staff_model']);
        $this->load->library('whatsappLibrary');
        $this->whatsappLibrary = new whatsappLibrary(); // Correct instantiation
    }

    /**
     * Default entry point. Redirects to connect account page.
     */
    public function index()
    {
        if (!staff_can('view', 'whatsapp_chat')) {
            access_denied();
        }

        $data['title'] = _l('chat');
        $this->load->view('admin/interaction', $data);
    }

    /**
     * Displays the chat interface if the user has the necessary view permissions.
     */
    public function interaction()
    {
        if (!staff_can('view', 'whatsapp_chat')) {
            access_denied();
        }

        $data['title'] = _l('chat');
        $data['numbers'] = $this->whatsapp_interaction_model->get_numbers();
        $data['quick_replies'] = $this->QuickReply_model->get_all_replies();
        $data['staffs'] =$this->staff_model->get('', ['active' => 1]);
        $data['statuses'] =$this->leads_model->get_status();
        $this->load->view('admin/interaction', $data);
    }
    
    public function interactions()
    {
        // Collect filters from GET parameters
        $filters = [
            'wa_no_id' => $this->input->get('wa_no_id'),
            'interaction_type' => $this->input->get('interaction_type'),
            'status_id' => $this->input->get('status_id'),
            'assigned_staff_id' => $this->input->get('assigned_staff_id'),
            'status' => $this->input->get('status')
        ];
    
        // Get filtered interactions based on filters
        $data['interactions'] = $this->whatsapp_interaction_model->get_interactions($filters);
    
        // Return data as JSON
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Displays the chat interface if the user has the necessary view permissions.
     */
    public function bot_flows()
    {
        if (!staff_can('view', 'whatsapp_chat')) {
            access_denied();
        }

        $data['title'] = _l('chat');
        $data['numbers'] = $this->whatsapp_interaction_model->get_numbers();
        $this->load->view('admin/bot_flows', $data);
    }
    /**
     * Fetches and displays phone numbers and associated profile data.
     * Logs the fetched data and saves profile images.
     */
public function numbers()
{
    if (!staff_can('view', 'whatsapp_chat')) {
        access_denied();
    }

    $this->load->database(); // Load the database library

    $data['title'] = _l('numbers');
    $apiNumbers = $this->whatsappLibrary->getPhoneNumbers();

    log_message('error', 'Phone Numbers Data: ' . json_encode($apiNumbers));

    // Define the directory to save profile images
    $profileImageDir = FCPATH . 'uploads/whatsapp/profiles/';
    if (!is_dir($profileImageDir)) {
        mkdir($profileImageDir, 0755, true);
    }

    // Fetch and update profile data for each number
    if ($apiNumbers['status'] && !empty($apiNumbers['data'])) {
        foreach ($apiNumbers['data'] as $number) {
            log_message('error', 'Phone Number Data: ' . json_encode($number));

            $phoneNumber = preg_replace('/[^\d]/', '', $number->display_phone_number); // Remove non-digit characters
            // Optional: Add logic to format phone number if needed, e.g., adding country code

            $updateData = [
                'phone_number' => $phoneNumber,
                'display_phone_number' => $phoneNumber,
                'verified_name' => $number->verified_name ?? '',
                'code_verification_status' => $number->code_verification_status ?? '',
                'quality_rating' => $number->quality_rating ?? '',
                'platform_type' => $number->platform_type ?? '',
                'throughput_level' => $number->throughput->level ?? '',
                'external_id' => $number->id ?? '', // External ID
                'phone_number_id' => $number->id, // Added this to update or insert correctly
            ];

            // Check if the record exists
            $this->db->where('phone_number_id', $number->id);
            $existingRecord = $this->db->get(db_prefix() . 'whatsapp_numbers')->row();

            if ($existingRecord) {
                // Update the existing record
                $this->db->where('phone_number_id', $number->id);
                $this->db->update(db_prefix() . 'whatsapp_numbers', $updateData);
            } else {
                // Insert a new record
                $this->db->insert(db_prefix() . 'whatsapp_numbers', $updateData);
            }
        }
    }

    // Retrieve the updated data from the database
    $data['numbers'] = $this->db->get(db_prefix() . 'whatsapp_numbers')->result_array();

    // Check if any phone number is set as default
    $this->db->where('is_default', 1);
    $defaultNumber = $this->db->get(db_prefix() . 'whatsapp_numbers')->row();

    if (!$defaultNumber) {
        // No default number found, mark the first number as default
        $firstNumber = $this->db->order_by('phone_number_id', 'ASC')->limit(1)->get(db_prefix() . 'whatsapp_numbers')->row();

        if ($firstNumber) {
            $this->db->where('phone_number_id', $firstNumber->phone_number_id);
            $this->db->update(db_prefix() . 'whatsapp_numbers', ['is_default' => 1]);

            // Update options if needed
            update_option('whatsapp_default_phone_number_id', $firstNumber->phone_number_id);
            update_option('whatsapp_default_phone_number', $firstNumber->phone_number);
        }
    }

    // Load the view with the updated data
    $this->load->view('admin/numbers', $data);
}

/**
 * Sets the default phone number for WhatsApp.
 * Validates the request, updates the default phone number, and returns a JSON response.
 */
public function set_default_phone_number()
{
    if (!$this->input->get()) {
        show_404();
    }

    // Retrieve and sanitize input
    $phone_number_id = $this->input->get('phone_number_id', true);
    $phone_number = $this->input->get('phone_number', true);

    // Validate inputs
    if (empty($phone_number_id) || empty($phone_number)) {
        set_alert('danger', _l('Invalid input data'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    // Begin transaction
    $this->db->trans_start();

    try {
        // Mark all phone numbers as non-default
        $this->db->update(db_prefix() . 'whatsapp_numbers', ['is_default' => 0]);

        // Check if the provided phone number exists
        $this->db->where('phone_number_id', $phone_number_id);
        $existingRecord = $this->db->get(db_prefix() . 'whatsapp_numbers')->row();

        if ($existingRecord) {
            // Mark the selected phone number as default
            $this->db->where('phone_number_id', $phone_number_id);
            $this->db->update(db_prefix() . 'whatsapp_numbers', ['is_default' => 1]);

            // Update options
            update_option('whatsapp_default_phone_number_id', $phone_number_id);
            update_option('whatsapp_default_phone_number', $phone_number);
        } else {
            // Auto-mark the first available phone number as default
            $firstNumber = $this->db->order_by('phone_number_id', 'ASC')->limit(1)->get(db_prefix() . 'whatsapp_numbers')->row();

            if ($firstNumber) {
                $phone_number_id = $firstNumber->phone_number_id;
                $phone_number = $firstNumber->phone_number;

                $this->db->where('phone_number_id', $firstNumber->phone_number_id);
                $this->db->update(db_prefix() . 'whatsapp_numbers', ['is_default' => 1]);

                // Update options with the first number
                update_option('whatsapp_default_phone_number_id', $phone_number_id);
                update_option('whatsapp_default_phone_number', $phone_number);
            } else {
                // No phone numbers available
                set_alert('danger', _l('No phone numbers available to set as default'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

        // Complete transaction
        $this->db->trans_complete();

        // Check transaction status
        if ($this->db->trans_status() === FALSE) {
            // Transaction failed
            $this->db->trans_rollback();
            set_alert('danger', _l('Failed to update default phone number'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            // Transaction successful
            $this->db->trans_commit();
            set_alert('success', _l('Default phone number updated'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $this->db->trans_rollback();
        log_message('error', 'Error setting default phone number: ' . $e->getMessage());
        set_alert('danger', _l('An error occurred while updating the default phone number'));
        redirect($_SERVER['HTTP_REFERER']);
    }
}

      /*** Updates the profile data for a given phone number.
     * Uploads a new profile picture if provided and updates the profile data.
     */
    public function update_profile()
    {
        if ($this->input->post()) {
            $profileData = $this->input->post();
            $accessToken = get_option('whatsapp_access_token');
            $phoneNumberId = $profileData['phone_number_id'];

            

            $response = $this->whatsappLibrary->updateProfile($profileData, $phoneNumberId, $accessToken);
            
            redirect(admin_url('whatsapp/numbers'));
        }
    }

    /**
     * Fetches and sends interaction data as a JSON response.
     * Retrieves interaction data from the model and outputs it as JSON.
     */


    /**
     * Loads the activity log view if the user has view permissions.
     * Displays the activity log related to WhatsApp interactions.
     */
    public function activity_log()
    {
        if (!staff_can('view', 'whatsapp_log_activity')) {
            access_denied('activity_log');
        }
        $data['title'] = _l('activity_log');
        $this->load->view('activity_log/whatsapp_activity_log', $data);
    }

    /**
     * Handles AJAX request for activity log table data.
     * Fetches and displays the activity log table via AJAX.
     */
    public function activity_log_table()
    {
        if (!$this->input->is_ajax_request()) {
            ajax_access_denied();
        }

        $this->app->get_table_data(module_views_path(WHATSAPP_MODULE, 'tables/activity_log_table'));
    }

    /**
     * Loads the view for log details based on a specific log ID.
     * Retrieves detailed information about a specific log entry.
     */
    public function view_log_details($id = '')
    {
        $data['title'] = _l('activity_log');
        $data['log_data'] = $this->whatsapp_interaction_model->getWhatsappLogDetails($id);

        $this->load->view('activity_log/view_log_details', $data);
    }

    /**
     * Marks a chat interaction as read and returns the response as JSON.
     * Updates the status of a chat interaction to 'read'.
     */
    public function chat_mark_as_read()
    {
        $id = $this->input->post('interaction_id');
        $response = $this->whatsapp_interaction_model->chat_received_messages_mark_as_read($id);
    }

    /**
     * Clears the activity log if the user has the necessary permissions.
     * Truncates the activity log table and sets an alert message.
     */
    public function clear_log()
    {
        if (staff_can('clear_log', 'whatsapp_log_activity')) {
            $this->db->truncate(db_prefix() . 'whatsapp_activity_log');
            set_alert('danger', _l('log_cleared_successfully'));
        }
        redirect(admin_url('whatsapp/activity_log'));
    }

    /**
     * Deletes a specific log entry based on the provided ID.
     * Redirects to the activity log page with an appropriate alert message.
     *
     * @param int $id The ID of the log entry to delete.
     */
    public function delete_log($id)
    {
        if (staff_can('clear_log', 'whatsapp_log_activity')) {
            $delete = $this->whatsapp_interaction_model->delete_log($id);
            set_alert('danger', $delete ? _l('deleted', _l('log')) : _l('something_went_wrong'));
        }
        redirect(admin_url('whatsapp/activity_log'));
    }
    
    public function documentation()
    {
        $data['title'] = _l('documentation');
        $this->load->view('documentation', $data);
    }

}
