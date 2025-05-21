<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Whatsapp_interaction_model extends App_Model
{
    protected $whatsappLibrary;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->set_charset_utf8mb4();
        $this->load->library('whatsappLibrary');
        $this->whatsappLibrary = new whatsappLibrary();
    }

    private function set_charset_utf8mb4()
    {

        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET character_set_connection=utf8mb4");
        $this->db->query("SET character_set_results=utf8mb4");
        $this->db->query("SET character_set_client=utf8mb4");
    }
public function load_templates($accessToken = '', $accountId = '')
{
    $templates = $this->whatsappLibrary->loadTemplatesFromWhatsApp($accessToken, $accountId);

    if (!$templates['status']) {
        return [
            'success' => false,
            'type'    => 'danger',
            'message' => $templates['message'],
        ];
    }

    $data = $templates['data'];
    $insertData = [];

    foreach ($data as $templateData) {
        $components = array_column($templateData->components, null, 'type');

        $templateId = $templateData->id;

        $record = [
            'template_id'         => $templateId,
            'template_name'       => $templateData->name,
            'language'            => $templateData->language,
            'status'              => $templateData->status,
            'category'            => $templateData->category,
            'header_data_format'  => $components['HEADER']->format ?? '',
            'header_data_text'    => $components['HEADER']->text ?? null,
            'header_params_count' => preg_match_all('/{{(.*?)}}/i', $components['HEADER']->text ?? '', $matches),
            'body_data'           => $components['BODY']->text ?? null,
            'body_params_count'   => preg_match_all('/{{(.*?)}}/i', $components['BODY']->text ?? '', $matches),
            'footer_data'         => $components['FOOTER']->text ?? null,
            'footer_params_count' => preg_match_all('/{{(.*?)}}/i', $components['FOOTER']->text ?? '', $matches),
            'buttons_data'        => json_encode($components['BUTTONS'] ?? []),
        ];

        // Check if the template already exists in the database
        $existingTemplate = $this->db->get_where(db_prefix() . 'whatsapp_templates', ['template_id' => $templateId])->row();

        if ($existingTemplate) {
            // Update the existing template
            $this->db->where('template_id', $templateId);
            $this->db->update(db_prefix() . 'whatsapp_templates', $record);
        } else {
            // Insert new template
            $insertData[] = $record;
        }
    }

    // Insert new templates that don't exist in the database
    if (!empty($insertData)) {
        $this->db->insert_batch(db_prefix() . 'whatsapp_templates', $insertData);
    }

    return ['success' => true];
}

    public function save_template($data)
    {
        // Prepare the components for the template
        $headerComponent = $this->whatsappLibrary->getHeaderTextComponent($data);
        $bodyComponent = $this->whatsappLibrary->getBodyTextComponent($data);
        $footerComponent = $this->whatsappLibrary->getFooterTextComponent($data);
        $buttonsComponent = $this->whatsappLibrary->getButtonsComponent($data);

        // Send the template to WhatsApp via the API
        $response = $this->whatsappLibrary->createTemplate($data, $headerComponent, $bodyComponent, $footerComponent, $buttonsComponent, $this->whatsappLibrary->getAccountID(), $this->whatsappLibrary->getToken());

        if ($response != 200) {
            return ['success' => false, 'message' => _l('template_creation_failed')];
        }

        // Save template data in the local database
        $this->db->insert(db_prefix() . 'whatsapp_templates', [
            'template_id' => uniqid(),
            'template_name' => $data['template_name'],
            'category' => $data['category'],
            'status' => 'APPROVED', // Assuming newly created templates are approved
            'language' => $data['language'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => _l('template_saved_successfully')];
    }

    /**
     * Get Template
     *
     * Retrieves a template by its ID.
     *
     * @param int $id The template ID.
     * @return object|bool The template object if found, or false if not.
     */
    public function get_template($id)
    {
        $this->db->where('template_id', $id);
        $template = $this->db->get(db_prefix() . 'whatsapp_templates')->row();

        return $template ?: false;
    }

    /**
     * Update Template
     *
     * Updates an existing template in the local database and via the WhatsApp API.
     *
     * @param int $id The ID of the template to update.
     * @param array $data The new template data.
     * @return array Response containing success status and message.
     */
    public function update_template($id, $data)
    {
        // Retrieve the existing template
        $existingTemplate = $this->get_template($id);

        if (!$existingTemplate) {
            return ['success' => false, 'message' => _l('template_not_found')];
        }

        // Prepare the components for the template
        $headerComponent = $this->whatsappLibrary->getHeaderTextComponent($data);
        $bodyComponent = $this->whatsappLibrary->getBodyTextComponent($data);
        $footerComponent = $this->whatsappLibrary->getFooterTextComponent($data);
        $buttonsComponent = $this->whatsappLibrary->getButtonsComponent($data);

        // Update the template via the API
        $response = $this->whatsappLibrary->editTemplate($headerComponent, $bodyComponent, $footerComponent, $buttonsComponent, $existingTemplate->template_id, $this->whatsappLibrary->getToken());

        if (isset($response['error'])) {
            return ['success' => false, 'message' => _l('template_update_failed')];
        }

        // Update template data in the local database
        $this->db->where('template_id', $id);
        $this->db->update(db_prefix() . 'whatsapp_templates', [
            'template_name' => $data['template_name'],
            'category' => $data['category'],
            'status' => $data['status'],
            'language' => $data['language'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'message' => _l('template_updated_successfully')];
    }

    /**
     * Delete Template
     *
     * Deletes a template from the local database and optionally via the WhatsApp API.
     *
     * @param int $id The ID of the template to delete.
     * @return array Response containing success status and message.
     */
    public function delete_template($id)
    {
        // Retrieve the existing template
        $existingTemplate = $this->get_template($id);

        if (!$existingTemplate) {
            return ['success' => false, 'message' => _l('template_not_found')];
        }

        // Optionally delete the template via the WhatsApp API
        // Implement API delete if supported by WhatsApp API in future.

        // Delete template from local database
        $this->db->where('template_id', $id);
        $this->db->delete(db_prefix() . 'whatsapp_templates');

        return ['success' => true, 'message' => _l('template_deleted_successfully')];
    }
    public function send_campaign()
    {
        $logBatch = [];
        // Fetch scheduled campaign data
        $scheduledData = $this->fetch_scheduled_campaign_data();

        if (!empty($scheduledData)) {
            foreach ($scheduledData as $data) {
                $phone_number = $this->get_phone_number($data['rel_type'], $data['rel_id'], $data);
                // Prepare the template message data
                $message_data = $this->whatsappLibrary->prepare_template_message_data($phone_number, $data);
                $logdata = $this->prepare_log_data($data, $message_data);
                // Send the template message using the WhatsApp library
                $response = $this->whatsappLibrary->send_message(whatsapp_default_phone_number()['phone_number_id'], $phone_number, $message_data, $logdata);

                // Log data for the WhatsApp activity log
                $logBatch[] = $response['log_data'];

                // Prepare and collect chat messages
                $chatMessage = $this->prepare_chat_message($response, $data);

                // Update campaign data based on the response
                $this->update_campaign_data($data['id'], $response);
            }

            // Insert collected logs and chat messages into the database
            $this->addWhatsbotLog($logBatch);
        } else {
            log_message('error', 'No scheduled campaigns found.');
        }
    }
    private function get_phone_number($relType, $relId, $data)
    {
        $this->db->where('id', $relId);
        $rel_data = $this->db->get(db_prefix() . $relType)->row();
        $name = ($relType === 'contacts') ? $rel_data->firstname . ' ' . $rel_data->lastname : $rel_data->name;
        $phone_number = $rel_data->phonenumber;

        return $phone_number;
    }

    private function whatsappGetInteractionId($data, $relType, $id, $name, $phonenumber)
    {
        $interaction = $this->db->get_where(db_prefix() . 'whatsapp_interactions', [
            'type' => $relType,
            'type_id' => $id,
            'wa_no' => whatsapp_default_phone_number()['phone_number'],
            'wa_no_id' => whatsapp_default_phone_number()['phone_number_id'],
            'receiver_id' => $phonenumber
        ])->row();

        if (!empty($interaction)) {
            return $interaction->id;
        }

        $message = $this->generateBotMessage($data);

        $interactionData = [
            'receiver_id' => $phonenumber,
            'last_message' => $message,
            'last_msg_time' => date('Y-m-d H:i:s'),
            'wa_no' => whatsapp_default_phone_number()['phone_number'],
            'wa_no_id' => whatsapp_default_phone_number()['phone_number_id'],
            'time_sent' => date('Y-m-d H:i:s'),
            'type' => $relType,
            'type_id' => $id,
        ];

        return $this->insert_interaction($interactionData);
    }
    /**
     * Fetch scheduled campaign data
     */
    private function fetch_scheduled_campaign_data()
    {
        $campaigns_table = db_prefix() . 'whatsapp_campaigns';
        $templates_table = db_prefix() . 'whatsapp_templates';
        $campaign_data_table = db_prefix() . 'whatsapp_campaign_data';
    
        return $this->db->select([
                    "$campaigns_table.*",
                    "$templates_table.*",
                    "$campaign_data_table.*"
                ])
                ->from($campaign_data_table)
                ->join($campaigns_table, "$campaigns_table.id = $campaign_data_table.campaign_id", 'left')
                ->join($templates_table, "$templates_table.id = $campaigns_table.template_id", 'left')
                ->where([
                    "$campaigns_table.pause_campaign" => 0,
                    "$campaign_data_table.status" => 1
                ])
                ->group_start()
                    ->where("$campaigns_table.send_now", 1)
                    ->or_where("$campaigns_table.scheduled_send_time <=", date('Y-m-d H:i:s'))
                ->group_end()
                ->get()
                ->result_array();
    }


    private function prepare_chat_message($responseData, $data)
    {
        // Accessing the response_data
        $response_data = $responseData['response_data'];

      // Extract the first contact and message using array_shift
        $first_contact = array_shift($response_data['contacts']);
        $first_message = array_shift($response_data['messages']);

        // Check if the contact and message data were extracted successfully
        if ($first_contact && $first_message) {
            $phone_number = $first_contact['wa_id'];
            $message_id = $first_message['id'];
            $message_status = $first_message['message_status'];
            // Prepare the header, body, and footer
            $header_data = $this->format_header_data($data);
            $body = whatsappParseText($data['rel_type'], 'body', $data);
            $footer = whatsappParseText($data['rel_type'], 'footer', $data);
            $buttonHtml = $this->format_button_data($data);

            // Handle interaction creation
            $interaction_id = $this->whatsapp_interaction_model->insert_interaction([
                'receiver_id' => $phone_number,  // Use wa_id from the response data
                'last_message' => "Campaign Message",
                'wa_no' => whatsapp_default_phone_number()['phone_number'],
                'wa_no_id' => whatsapp_default_phone_number()['phone_number_id'],
                'time_sent' => date("Y-m-d H:i:s"),
            ]);

            // Handle interaction message creation
            $this->whatsapp_interaction_model->insert_interaction_message([
                'interaction_id' => $interaction_id,
                'sender_id' => whatsapp_default_phone_number()['phone_number'],
                'message' => "$header_data<p>" . nl2br(whatsappDecodeWhatsAppSigns($body)) . "</p><span class='text-muted tw-text-xs'>" . nl2br(whatsappDecodeWhatsAppSigns($footer ?? '')) . "</span>$buttonHtml",
                'message_id' => $message_id,
                'type' => "template",  // Use message_status
                'staff_id' => get_staff_user_id() ?? 0,
                'url' => null, // If you have URL logic, include it here
                'status' => $message_status,
                'nature' => 'sent',
                'time_sent' => date("Y-m-d H:i:s"),
                'ref_message_id' => $responseData['ref_message_id'] ?? null,
            ]);
        } else {
            // Handle the error if contact or message data is missing
            throw new Exception("Contacts or messages data is missing.");
        }
    }
        public function format_header_data($data)
    {
        $header = whatsappParseText($data['rel_type'], 'header', $data);

        switch ($data['header_data_format']) {
            case 'IMAGE':
                return '<a href="' . base_url(get_upload_path_by_type('campaign') . '/' . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('campaign') . '/' . $data['filename']) . '" class="img-responsive img-rounded" style="width: 300px"></img></a>';
            case 'TEXT':
            default:
                return "<span class='tw-mb-3 bold'>" . nl2br(whatsappDecodeWhatsAppSigns($header ?? '')) . "</span>";
        }
    }
public function format_button_data($data)
{
    // Retrieve the buttons data from the input $data array
    $buttons = $data['buttons_data'] ?? null;

    // Initialize a variable to store the formatted HTML
    $formatted_html = '';

    // Check if buttons is not an array, try to decode JSON
    if (!is_array($buttons)) {
        $buttons = json_decode($buttons, true);
    }

    // Check if buttons is now an array before processing
    if (is_array($buttons)) {
        // Loop through each button and format it as an HTML button
        foreach ($buttons as $button) {
            if (isset($button['type']) && isset($button['text'])) {
                $type = strtoupper($button['type']); // Normalize type to uppercase
                $text = htmlspecialchars($button['text']);
                
                switch ($type) {
                    case 'URL':
                        if (isset($button['url'])) {
                            $url = htmlspecialchars($button['url']);
                            $formatted_html .= '<a href="' . $url . '" class="btn btn-primary" target="_blank">'
                                . $text . '</a> ';
                        }
                        break;

                    case 'REPLY':
                        if (isset($button['id'])) {
                            $id = htmlspecialchars($button['id']);
                            $formatted_html .= '<button type="button" class="btn btn-secondary" onclick="handleReply(\'' . $id . '\')">'
                                . $text . '</button> ';
                        }
                        break;

                    default:
                        // Log an error or handle unknown button types
                        log_message('error', 'Unknown button type: ' . json_encode($button));
                        break;
                }
            }
        }
    } else {
        // Log an error if buttons_data is neither an array nor valid JSON
        log_message('error', 'Invalid buttons_data format: ' . json_encode($buttons));
    }

    // Return the formatted HTML buttons
    return $formatted_html;
}

    private function prepare_log_data($data, $message_data)
    {
        return [
            'phone_number_id' => whatsapp_default_phone_number()['phone_number_id'],
            'category' => 'Marketing Compain',
            'category_id' => $data['id'],
            'rel_type' => $data['rel_type'],
            'rel_id' => $data['rel_id'],
            'recorded_at' => date('Y-m-d H:i:s'),
            'category_params' => json_encode($message_data),
        ];
    }

    
    private function generateBotMessage($data)
    {
        if (!empty($data['reply_type'])) {
            $message_data = whatsappParseMessageText($data);
            return $message_data['reply_text'];
        }

        if (!empty($data['bot_type'])) {
            return whatsappParseText($data['rel_type'], 'header', $data) . ' ' .
                whatsappParseText($data['rel_type'], 'body', $data) . ' ' .
                whatsappParseText($data['rel_type'], 'footer', $data);
        }

        return '';
    }

    public function insert_interaction($data)
    {
        $existing_interaction = $this->db->where('receiver_id', $data['receiver_id'])->where('wa_no', $data['wa_no'])->where('wa_no_id', $data['wa_no_id'])->get(db_prefix() . 'whatsapp_interactions')->row();

        if ($existing_interaction) {
            // Existing interaction found with matching 'receiver_id' and 'wa_no'
            $this->db->where('id', $existing_interaction->id)->update(db_prefix() . 'whatsapp_interactions', $data);

            return $existing_interaction->id;
        }
        // No existing interaction found with matching 'receiver_id' and 'wa_no'
        $this->db->insert(db_prefix() . 'whatsapp_interactions', $data);

        return $this->db->insert_id();
    }

    public function get_numbers()
    {
        // Assuming you have a table named `whatsapp_numbers` where phone numbers are stored
        $query = $this->db->get(db_prefix() . 'whatsapp_numbers');

        // Return the result as an array of objects or arrays
        return $query->result_array(); // or $query->result();
    }
        public function chat_received_messages_mark_as_read($id)
    {
        // Retrieve the interaction from the database
        $interaction = $this->db->where('id', $id)->get(db_prefix() . 'whatsapp_interactions')->row();

        if (!$interaction) {
            // If no interaction is found, return false
            log_message('error', 'Interaction not found for ID: ' . $id);
            return false;
        }

        // Retrieve all delivered messages associated with this interaction that were received by the user
        $messages = $this->db->where([
            'interaction_id' => $id,
            'status' => 'delivered',
            'nature' => 'received'
        ])->get(db_prefix() . 'whatsapp_interaction_messages')->result();

        if (empty($messages)) {
            // If there are no delivered messages, there's nothing to mark as read
            log_message('info', 'No delivered messages to mark as read for interaction ID: ' . $id);
            return false;
        }

        // Mark each message as read
        foreach ($messages as $message) {
            // Update the status of each message to 'read'
            $this->db->where('id', $message->id)
                ->update(db_prefix() . 'whatsapp_interaction_messages', ['status' => 'read']);

            // Optionally, log the message ID that was marked as read
            log_message('info', 'Message marked as read: ' . $message->id);
        }

        // Set the unread count to 0 for the interaction
        $this->db->where('id', $id)->update(db_prefix() . 'whatsapp_interactions', ['unread' => 0]);

        log_message('info', 'Unread count updated to 0 for interaction ID: ' . $id);

        return true;
    }


    public function insert_interaction_message($data)
    {
        if (isset($data['message_id'])) {
            $this->db->where('message_id', $data['message_id']);
            if ($this->db->get(db_prefix() . 'whatsapp_interaction_messages')->num_rows() > 0) {
                return false; // Message ID already exists
            }
        }

        $this->db->insert(db_prefix() . 'whatsapp_interaction_messages', $data);
        return $this->db->insert_id();
    }

    public function getContactData($contactNumber, $name)
    {
        $contact = $this->db->get_where(db_prefix() . 'contacts', ['phonenumber' => $contactNumber])->row();
        if ($contact) {
            $contact->rel_type = 'contacts';
            $contact->name = $contact->firstname . ' ' . $contact->lastname;
            return $contact;
        }

        $lead = $this->db->get_where(db_prefix() . 'leads', ['phonenumber' => $contactNumber])->row();
        if ($lead) {
            $lead->rel_type = 'leads';
            return $lead;
        }

        return false;
    }

    public function update_message_status($interaction_id, $status)
    {
        $this->db->where('message_id', $interaction_id)
            ->update(db_prefix() . 'whatsapp_interaction_messages', ['status' => $status]);
        $this->db->where('whatsapp_id', $interaction_id)
            ->update(db_prefix() . 'whatsapp_campaign_data', ['message_status' => $status]);
    }

    private function addWhatsbotLog($logBatch)
    {
        if (!empty($logBatch)) {
            // Insert the log data in batch
            $this->db->insert_batch(db_prefix() . 'whatsapp_activity_log', $logBatch);
        }
    }

    // Define the method to get log details
    public function getWhatsappLogDetails($interaction_id)
    {
        // Fetch interaction details based on interaction_id
        $this->db->where('id', $interaction_id);
        $query = $this->db->get(db_prefix() . 'whatsapp_activity_log');

        // Check if any result is returned
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    }
    /**
     * Get interaction by ID
     */
    public function get_interaction($id)
    {
        return $this->db->get_where(db_prefix() . 'whatsapp_interactions', ['id' => $id])->row_array();
    }
    public function get_interaction_history($interaction_id)
    {
        // Fetch the interaction details
        $interaction = $this->get_interaction($interaction_id);

        if ($interaction) {
            // Fetch the messages associated with this interaction
            $messages = $this->get_interaction_messages($interaction_id);

            // Filter only text messages
            $textMessages = array_filter($messages, function ($message) {
                return $message['type'] === 'text';
            });

            // Assign filtered text messages back to interaction
            $interaction['messages'] = $textMessages;

            // Fetch staff name for each message if available
            foreach ($interaction['messages'] as &$message) {
                if (!empty($message['staff_id'])) {
                    $message['staff_name'] = get_staff_full_name($message['staff_id']);
                } else {
                    $message['staff_name'] = null;
                }
            }
        }

        // Format messages for OpenAI API
        $formattedMessages = [];
        foreach ($interaction['messages'] as $chatMessage) {
            $formattedMessages[] = [
                'role' => $interaction['wa_no'] === $chatMessage['sender_id'] ? 'assistant' : 'user', // Assuming 'wa_no' is the user identifier
                'content' => $chatMessage['message'] // Assuming 'message_content' contains the message text
            ];
        }

        return $formattedMessages;
    }

    private function update_campaign_data($campaign_id, $response)
    {
            $first_message = array_shift($response['messages']);
            $message_status = $response['message_status'];

        $update_data = [
            'status' => ($response['success'] == true) ? 2 : 3,
            'whatsapp_id' => $response['id'],'message_status' => $message_status,
            'response_message' => $response['log_data']['response_data'] ?? '',
        ];
        $this->db->update(db_prefix() . 'whatsapp_campaign_data', $update_data, ['id' => $campaign_id]);
    }
public function get_interactions($filters = [])
{
    // Start building the query
    $this->db->select('whatsapp_interactions.*, leads_status.name as lead_status_name, leads.assigned as assigned_staff_id')
             ->from(db_prefix() . 'whatsapp_interactions')
             ->join(db_prefix() . 'leads', db_prefix() . 'leads.id = ' . db_prefix() . 'whatsapp_interactions.type_id', 'left')
             ->join(db_prefix() . 'leads_status', db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status', 'left');

    // Apply filters to the query
    $this->apply_filters($filters);

    // Order by the last message time in descending order
    $this->db->order_by('whatsapp_interactions.time_sent', 'DESC');

    // Fetch interactions
    $interactions = $this->db->get()->result_array();

    // Process each interaction
    $this->process_interactions($interactions);

    return $interactions;
}

private function apply_filters($filters)
{
    if (!empty($filters['wa_no_id']) && $filters['wa_no_id'] !== '*') {
        $this->db->where('wa_no_id', $filters['wa_no_id']);
    }

    if (!empty($filters['interaction_type']) && $filters['interaction_type'] !== '*') {
        $this->db->where('type', $filters['interaction_type']);
    }

    if (!empty($filters['status_id']) && $filters['status_id'] !== '*') {
        $this->db->where('leads.status', $filters['status_id']);
    }

    if (!empty($filters['assigned_staff_id']) && $filters['assigned_staff_id'] !== '*') {
        $this->db->where('leads.assigned', $filters['assigned_staff_id']);
    }

    if (isset($filters['status']) && $filters['status'] === 'unread') {
        $this->db->where('unread >', 0);
    }

    // Filter for active and expired interactions
    if (isset($filters['status'])) {
        $now = new DateTime();
        $activeTimeThreshold = $now->sub(new DateInterval('PT24H'))->format('Y-m-d H:i:s');

        if ($filters['status'] === 'active') {
            $this->db->where('last_msg_time >', $activeTimeThreshold);
        } elseif ($filters['status'] === 'expired') {
            $this->db->where('last_msg_time <=', $activeTimeThreshold);
        }
    }
}

private function process_interactions(&$interactions)
{
    foreach ($interactions as &$interaction) {
        $interaction['messages'] = $this->get_interaction_messages($interaction['id']);
        $this->map_interaction($interaction);

        // Include lead status and assigned staff name
        if ($interaction['type'] === 'lead') {
            $interaction['lead_status_name'] = $interaction['lead_status_name'] ?? 'Unknown';
            $interaction['assigned_staff_name'] = get_staff_full_name($interaction['assigned_staff_id']);
        }

        $interaction['status'] = $this->determine_status($interaction['last_msg_time']);

        if (isset($interaction['last_message'])) {
            $interaction['last_message'] = $this->truncate_message($interaction['last_message']);
        }

        $this->process_messages($interaction['messages']);
    }
}

private function determine_status($lastMsgTime)
{
    if (!empty($lastMsgTime)) {
        $lastMsgDateTime = new DateTime($lastMsgTime);  // Convert last message time to DateTime object
        $now = new DateTime();  // Get the current time
        $interval = $now->diff($lastMsgDateTime);  // Calculate the time difference
        
        // Calculate the total number of hours between the last message time and now
        $totalHours = ($interval->days * 24) + $interval->h;  // Total hours including the difference in days

        // If the total hours are less than 24, it's active; otherwise, it's expired
        return ($totalHours < 24) ? 'active' : 'expired';
    }

    return 'expired';  // Default status if no last message time exists
}



private function truncate_message($message, $length = 30)
{
    return mb_strimwidth($message, 0, $length, '...');
}

private function process_messages(&$messages)
{
    foreach ($messages as &$message) {
        $message['staff_name'] = !empty($message['staff_id']) ? get_staff_full_name($message['staff_id']) : null;
        $message['asset_url'] = $this->get_asset_url($message['url']);
    }
}

private function get_interaction_messages($interaction_id)
{
    return $this->db->where('interaction_id', $interaction_id)
                    ->order_by('time_sent', 'ASC')
                    ->get(db_prefix() . 'whatsapp_interaction_messages')
                    ->result_array();
}

private function map_interaction($interaction)
{
    if (is_null($interaction['type']) || is_null($interaction['type_id'])) {
        $this->auto_map_interaction($interaction);
    }
}

private function auto_map_interaction($interaction)
{
    $entity = $this->map_interaction_entity($interaction, 'clients', 'userid', 'customer')
              ?? $this->map_interaction_entity($interaction, 'contacts', 'id', 'contact')
              ?? $this->map_interaction_entity($interaction, 'staff', 'staffid', 'staff');

    if (!$entity && get_option('whatsapp_auto_lead_settings') === 'enable') {
        $lead_id = $this->create_lead_for_interaction($interaction);
        $entity = $lead_id;
        $type = 'lead';
    }

    if (isset($type) && isset($entity)) {
        $data = [
            'type'       => $type,
            'type_id'    => $entity,
            'wa_no'      => $interaction['wa_no'] ?? whatsapp_default_phone_number()['phone_number'],
            'receiver_id'=> $interaction['receiver_id'],
        ];

        $this->db->where('id', $interaction['id'])->update(db_prefix() . 'whatsapp_interactions', $data);
    }
}

private function create_lead_for_interaction($interaction)
{
    $lead_data = [
        'phonenumber' => $interaction['receiver_id'],
        'name'        => $interaction['name'],
        'status'      => get_option('whatsapp_lead_status'),
        'source'      => get_option('whatsapp_lead_source'),
        'assigned'    => get_option('whatsapp_lead_assigned'),
        'dateadded'   => date('Y-m-d H:i:s'),
    ];

    get_instance()->load->model('leads_model');
    return get_instance()->leads_model->add($lead_data);
}

private function map_interaction_entity($interaction, $table, $column, $type)
{
    $entity = $this->db->where('phonenumber', $interaction['receiver_id'])
                       ->get(db_prefix() . $table)
                       ->row();

    return $entity ? $entity->$column : null;
}

private function get_asset_url($url)
{
    if ($url && strpos($url, '/') === false) {
        return WHATSAPP_MODULE_UPLOAD_URL . $url;
    }
    return $url ?? null;
}
// Get the current menu state by interaction ID
public function get_menu_state($interaction_id)
{
    return $this->db->select('menu_path, bot_id')
        ->from(db_prefix() . 'interaction_menu_state')
        ->where('interaction_id', $interaction_id)
        ->get()
        ->row_array();
}

// Create a new menu state record with bot_id
public function create_menu_state($interaction_id, $menu_path, $bot_id)
{
    $this->db->insert(db_prefix() . 'interaction_menu_state', [
        'interaction_id' => $interaction_id,
        'menu_path' => $menu_path,
        'bot_id' => $bot_id,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

// Update an existing menu state record with bot_id
public function update_menu_state($interaction_id, $menu_path, $bot_id)
{
    $this->db->where('interaction_id', $interaction_id)
        ->update(db_prefix() . 'interaction_menu_state', [
            'menu_path' => $menu_path,
            'bot_id' => $bot_id,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
}

// Clear the menu state for an interaction (called when the user exits the menu)
public function clear_menu_state($interaction_id)
{
    $this->db->where('interaction_id', $interaction_id)
        ->delete(db_prefix() . 'interaction_menu_state');
}

}
