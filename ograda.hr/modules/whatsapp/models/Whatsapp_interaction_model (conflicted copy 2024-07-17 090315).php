<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Whatsapp_interaction_model extends App_Model
{
    

    public function __construct()
    {
        parent::__construct();
        $this->set_charset_utf8mb4();
        $this->load->library('whatsappLibrary');
        $this->whatsappLibrary = new whatsappLibrary(); 
    }

    /**
     * Set character set for the connection and results to utf8mb4
     */
    private function set_charset_utf8mb4() {
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET character_set_connection=utf8mb4");
        $this->db->query("SET character_set_results=utf8mb4");
        $this->db->query("SET character_set_client=utf8mb4");
    }

    public function load_templates($accessToken = '', $accountId = '')
    {
        $templates = $this->whatsappLibrary->loadTemplatesFromWhatsApp($accessToken, $accountId);

        // if there is any error from api then display appropriate message
        if (!$templates['status']) {
            return [
                'success' => false,
                'type'    => 'danger',
                'message' => $templates['message'],
            ];
        }
        $data       = $templates['data'];
        $insertData = [];

        foreach ($data as $key => $templateData) {
            // Adding all as we can change the status from webhook
            $insertData[$key]['template_id']   = $templateData->id;
            $insertData[$key]['template_name'] = $templateData->name;
            $insertData[$key]['language']      = $templateData->language;

            $insertData[$key]['status']   = $templateData->status;
            $insertData[$key]['category'] = $templateData->category;

            $components = array_column($templateData->components, null, 'type');

            $insertData[$key]['header_data_format']  = $components['HEADER']->format ?? '';
            $insertData[$key]['header_data_text']    = $components['HEADER']->text ?? null;
            $insertData[$key]['header_params_count'] = preg_match_all('/{{(.*?)}}/i', $components['HEADER']->text ?? '', $matches);

            $insertData[$key]['body_data']         = $components['BODY']->text ?? null;
            $insertData[$key]['body_params_count'] = preg_match_all('/{{(.*?)}}/i', $components['BODY']->text, $matches);

            $insertData[$key]['footer_data']         = $components['FOOTER']->text ?? null;
            $insertData[$key]['footer_params_count'] = preg_match_all('/{{(.*?)}}/i', $components['FOOTER']->text ?? '', $matches);

            $insertData[$key]['buttons_data'] = json_encode($components['BUTTONS'] ?? []);
        }
        $insertDataId     = array_column($insertData, 'template_id');
        $existingTemplate = $this->db->where_in(array_column($insertData, 'template_id'))->get(db_prefix() . 'whatsapp_templates')->result();

        $existingDataId = array_column($existingTemplate, 'template_id');

        $newTemplateId = array_diff($insertDataId, $existingDataId);
        $newTemplate   = array_filter($insertData, function ($val) use ($newTemplateId) {
            return in_array($val['template_id'], $newTemplateId);
        });

        // No need to update template data in db because you can't edit template in meta dashboard
        if (!empty($newTemplate)) {
            $this->db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
            $this->db->insert_batch(db_prefix() . 'whatsapp_templates', $newTemplate);
        }

        return ['success' => true];
    }

    public function getContactData($contactNumber, $name)
    {
        $contact = $this->db->get_where(db_prefix() . 'contacts', ['phonenumber' => $contactNumber])->row();
        if (!empty($contact)) {
            $contact->rel_type = 'contacts';
            $contact->name = $contact->firstname . ' ' . $contact->lastname;
            return $contact;
        }

        $lead = $this->db->get_where(db_prefix() . 'leads', ['phonenumber' => $contactNumber])->row();
        if (!empty($lead)) {
            $lead->rel_type = 'leads';
            return $lead;
        }

        $leadId = hooks()->apply_filters('ctl_auto_lead_creation', $contactNumber, $name);

        if (!empty($leadId)) {
            $lead           = $this->db->get_where(db_prefix() . 'leads', ['id' => $leadId])->row();
            $lead->rel_type = 'leads';
            return $lead;
        }

        return false;
    }

    public function updateStatus($status_data)
    {
        foreach ($status_data as $status) {
            $stat = is_array($status) ? $status['status'] : $status->status;
            $id = is_array($status) ? $status['id'] : $status->id;
            $this->db->update(db_prefix() . 'whatsapp_campaign_data', ['message_status' => $stat], ['whatsapp_id' => $id]);
        }
    }

    public function send_campaign($scheduled_data)
    {
        $logBatch = $chatMessage = [];

        foreach ($scheduled_data as $data) {
            switch ($data['rel_type']) {
                case 'leads':
                    $this->db->where('id', $data['rel_id']);
                    $rel_data      = $this->db->get(db_prefix() . 'leads')->row();
                    $interactionId = whatsappGetInteractionId($data, 'leads', $rel_data->id, $rel_data->name, $rel_data->phonenumber, $this->whatsappLibrary->getDefaultPhoneNumber());
                    break;

                case 'contacts':
                    $this->db->where('id', $data['rel_id']);
                    $rel_data       = $this->db->get(db_prefix() . 'contacts')->row();
                    $data['userid'] = $rel_data->userid;
                    $interactionId  = whatsappGetInteractionId($data, 'contacts', $data['userid'], $rel_data->firstname . ' ' . $rel_data->lastname, $rel_data->phonenumber, $this->whatsappLibrary->getDefaultPhoneNumber());
                    break;
            }
            $response = $this->whatsappLibrary($rel_data->phonenumber, $data);

            $logBatch[] = $response['log_data'];

            if (!empty($response['status'])) {
                $header = wbParseText($data['rel_type'], 'header', $data);
                $body   = wbParseText($data['rel_type'], 'body', $data);
                $footer = wbParseText($data['rel_type'], 'footer', $data);

                $header_data = '';
                if ($data['header_data_format'] == 'IMAGE') {
                    $header_data = '<a href="' . base_url(get_upload_path_by_type('campaign') . '/' . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('campaign') . '/' . $data['filename']) . '" class="img-responsive img-rounded" style="width: 300px"></img></a>';
                } elseif ($data['header_data_format'] == 'TEXT' || $data['header_data_format'] == '') {
                    $header_data = "<span class='tw-mb-3 bold'>" . nl2br(whatsappDecodeWhatsAppSigns($header ?? '')) . "</span>";
                }

                $buttonHtml = '';
                if (!empty(json_decode($data['buttons_data']))) {
                    $buttons = json_decode($data['buttons_data']);
                    $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
                    foreach ($buttons->buttons as $key => $value) {
                        $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $value->text . '</button>';
                    }
                    $buttonHtml .= '</div>';
                }

                // Prepare the data for chat message
                $chatMessage[] = [
                    'interaction_id' => $interactionId,
                    'sender_id'      => $this->whatsappLibrary->getDefaultPhoneNumber(),
                    'url'            => null,
                    'message'        => "
                            $header_data
                            <p>" . nl2br(whatsappDecodeWhatsAppSigns($body)) . "</p>
                            <span class='text-muted tw-text-xs'>" . nl2br(whatsappDecodeWhatsAppSigns($footer ?? '')) . "</span>
                            $buttonHtml
                        ",
                    'status'     => 'sent',
                    'time_sent'  => date('Y-m-d H:i:s'),
                    'message_id' => $response['data']->messages[0]->id,
                    'staff_id'   => 0,
                    'type'       => 'text',
                ];
            }

            $update_data['status']           = (1 == $response['status']) ? 2 : $response['status'];
            $update_data['whatsapp_id']      = ($response['status']) ? reset($response['data']->messages)->id : null;
            $update_data['response_message'] = $response['message'] ?? '';
            $this->db->update(db_prefix() . 'whatsapp_campaign_data', $update_data, ['id' => $data['id']]);
        }

        // Add activity log
        $this->addWhatsbotLog($logBatch);

        // Add chat message
        $this->addChatMessage($chatMessage);

        return $this->db->update(db_prefix() . 'whatsapp_campaigns', ['is_sent' => 1, 'sending_count' => $data['sending_count'] + 1, 'scheduled_send_time' =>  date('Y-m-d H:i:s')], ['id' => $data['campaign_id']]);
    }

    public function addWhatsbotLog($logData)
    {
        if (!empty($logData)) {
            // Prepare the data for activity log
            $logsData = [
                'phone_number_id'     => get_option('whatsapp_phone_number_id'),
                'access_token'        => get_option('whatsapp_access_token'),
                'business_account_id' => get_option('whatsapp_business_account_id'),
            ];
            $logData = array_map(function ($item) use ($logsData) {
                return array_merge($item, $logsData);
            }, $logData);
            return $this->db->insert_batch(db_prefix() . 'whatsapp_activity_log', $logData);
        }
        return false;
    }

    public function addChatMessage($chatMessage)
    {
        if (!empty($chatMessage)) {
            return $this->db->insert_batch(db_prefix() . 'whatsapp_interaction_messages', $chatMessage);
        }
    }

    public function getWhatsappLogDetails($id)
    {
        return $this->db->get_where(db_prefix() . 'whatsapp_activity_log', ['id' => $id])->row();
    }

    public function delete_log($id)
    {
        return $this->db->delete(db_prefix() . 'whatsapp_activity_log', ['id' => $id]);
    }
    public function chat_mark_as_read($id)
    {
        return $this->db->update(db_prefix() . 'whatsapp_interaction_messages', ['status' => 'read'], ['interaction_id' => $id]);
    }

    public function get_interaction($id)
    {
        return $this->db->get_where(db_prefix() . 'whatsapp_interactions', ['id' => $id])->row_array();
    }
    public function map_interaction($interaction)
{
    // Check if type and type_id are not already set
    if (null === $interaction['type'] || null === $interaction['type_id']) {
        $interaction_id = $interaction['id'];
        $receiver_id    = $interaction['receiver_id'];

        // Check if receiver_id exists in clients, contacts, leads, or staff
        $customer = $this->db->where('phonenumber', $receiver_id)->get(db_prefix().'clients')->row();
        $contact  = $this->db->where('phonenumber', $receiver_id)->get(db_prefix().'contacts')->row();
        $lead     = $this->db->where('phonenumber', $receiver_id)->get(db_prefix().'leads')->row();
        $staff    = $this->db->where('phonenumber', $receiver_id)->get(db_prefix().'staff')->row();

        $entity = null;
        $type   = null;

        // Determine the type and entity ID based on the found record
        if ($customer) {
            $entity = $customer->userid;
            $type   = 'customer';
        } elseif ($contact) {
            $entity = $contact->id;
            $type   = 'contacts';
        } elseif ($staff) {
            $entity = $staff->staffid;
            $type   = 'staff';
        } else {
            // If no matching entity found, assume it's a lead
            $type = 'leads';

            // Prepare lead data
            $lead_data = [
                'phonenumber' => $receiver_id,
                'name'        => $interaction['name'],
                'status'      => get_option('whatsapp_auto_leads_status'),
                'source'      => get_option('whatsapp_auto_leads_source'),
                'assigned'    => get_option('whatsapp_auto_leads_assigned'),
                'dateadded'   => date('Y-m-d H:i:s'),
                'description' => '',
                'address'     => '',
                'email'       => '',
            ];

            // Load leads_model and create a new lead
            get_instance()->load->model('leads_model');
            $lead_id = get_instance()->leads_model->add($lead_data);

            // Set the entity ID based on the created lead
            $entity = $lead_id;
        }

        // Prepare data for interaction update or insert
        $data = [
            'type'        => $type,
            'type_id'     => $entity,
            'wa_no'       => $interaction['wa_no'] ?? get_option('whatsapp_default_phone_number'),
            'receiver_id' => $receiver_id,
        ];

        // Check if the interaction already exists in whatsapp_interactions table
        $existing_interaction = $this->db->where('id', $interaction_id)->get(db_prefix() . 'whatsapp_interactions')->row();

        if ($existing_interaction) {
            // Update existing interaction
            $this->db->where('id', $interaction_id)->update(db_prefix() . 'whatsapp_interactions', $data);
        } else {
            // Insert new interaction
            $data['id'] = $interaction_id;
            $this->db->insert(db_prefix() . 'whatsapp_interactions', $data);
        }
    }

    // Check and update WhatsApp number details if not already set
    if (null === $interaction['wa_no'] || null === $interaction['wa_no_id']) {
        $interaction_id = $interaction['id'];

        // Use default values if 'wa_no' or 'wa_no_id' is null
        $wa_no    = $interaction['wa_no'] ?? get_option('whatsapp_default_phone_number');
        $wa_no_id = $interaction['wa_no_id'] ?? get_option('whatsapp_phone_number_id');

        // Prepare data for update
        $data = [
            'wa_no'    => $wa_no,
            'wa_no_id' => $wa_no_id,
        ];

        // Check if the interaction exists
        $existing_interaction = $this->db->where('id', $interaction_id)->get(db_prefix() . 'whatsapp_interactions')->row();

        if ($existing_interaction) {
            // Update the existing interaction with WhatsApp number details
            $this->db->where('id', $interaction_id)->update(db_prefix() . 'whatsapp_interactions', $data);
        }
    }
}

    public function update_message_status($interaction_id, $status)
    {
        $this->db->where('message_id', $interaction_id)
            ->update(db_prefix().'whatsapp_interaction_messages', ['status' => $status]);
    }
    public function get_last_message_id($interaction_id)
    {
        $this->db->select_max('id')
            ->where('interaction_id', $interaction_id);
        $query  = $this->db->get(db_prefix().'whatsapp_interaction_messages');
        $result = $query->row_array();

        return $result['id'];
    }

    public function insert_interaction_message($data)
    {
        // Assuming 'whatsapp_interaction_messages' is the table name
        $this->db->insert(db_prefix().'whatsapp_interaction_messages', $data);

        // Check if the insert was successful
        if ($this->db->affected_rows() > 0) {
            // Return the ID of the inserted message
            return $this->db->insert_id();
        }
        // Return false if the insert failed
        return false;
    }

    public function get_interaction_messages($interaction_id)
    {
        $this->db->where('interaction_id', $interaction_id)->order_by('time_sent', 'asc');

        return $this->db->get(db_prefix().'whatsapp_interaction_messages')->result_array();
    }
    public function insert_interaction($data)
    {
        $existing_interaction = $this->db->where('receiver_id', $data['receiver_id'])->where('wa_no', $data['wa_no'])->where('wa_no_id', $data['wa_no_id'])->get(db_prefix().'whatsapp_interactions')->row();

        if ($existing_interaction) {
            // Existing interaction found with matching 'receiver_id' and 'wa_no'
            $this->db->where('id', $existing_interaction->id)->update(db_prefix().'whatsapp_interactions', $data);

            return $existing_interaction->id;
        }
        // No existing interaction found with matching 'receiver_id' and 'wa_no'
        $this->db->insert(db_prefix().'whatsapp_interactions', $data);

        return $this->db->insert_id();
    }
    public function get_interactions()
    {
        // Fetch interactions ordered by time_sent in descending order
        $interactions = $this->db->order_by('time_sent', 'DESC')->get(db_prefix().'whatsapp_interactions')->result_array();

        // Fetch messages for each interaction
        foreach ($interactions as &$interaction) {
            $interaction_id = $interaction['id'];
            $messages       = $this->get_interaction_messages($interaction_id);
            if (1 == get_option('whatsapp_auto_lead_settings')) {
                 $this->map_interaction($interaction);
                }
            $interaction['messages'] = $messages;

            // Fetch staff name for each message in the interaction
            foreach ($interaction['messages'] as &$message) {
                if (!empty($message['staff_id'])) {
                    $message['staff_name'] = get_staff_full_name($message['staff_id']);
                } else {
                    $message['staff_name'] = null;
                }

                // Check if URL is already a base name
                if ($message['url'] && false === strpos($message['url'], '/')) {
                    // If URL doesn't contain "/", consider it as a file name
                    // Assuming base URL is available
                    $message['asset_url'] = WHATSAPP_MODULE_UPLOAD_URL.$message['url'];
                } else {
                    // Otherwise, use the URL directly
                    $message['asset_url'] = $message['url'] ?? null;
                }
            }
        }

        return $interactions;
    }
}
