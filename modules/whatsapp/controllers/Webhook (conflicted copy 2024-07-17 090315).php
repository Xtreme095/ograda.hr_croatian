<?php

defined('BASEPATH') || exit('No direct script access allowed');

use WpOrg\Requests\Requests as WhatsappRequests;

/**
 * Class Whatsapp_webhook
 *
 * Handles incoming webhooks from WhatsApp and processes them accordingly.
 */
class Webhook extends ClientsController
{
    

    public $is_first_time = false;

    /**
     * Constructor for Webhook class.
     * Loads necessary models for processing webhooks.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['whatsapp_interaction_model', 'bots_model']);
        $this->load->library('whatsappLibrary');
        $this->whatsappLibrary = new whatsappLibrary(); // Correct instantiation
    }

    /**
     * Index method.
     *
     * Handles incoming webhook requests from WhatsApp.
     * Verifies webhook setup if a verification token matches.
     * Processes incoming webhook data for messages and statuses.
     */
    public function getdata()
    {
        if (isset($_GET['hub_mode']) && isset($_GET['hub_challenge']) && isset($_GET['hub_verify_token'])) {
            // Handle verification requests from WhatsApp
            if ($_GET['hub_verify_token'] == get_option('whatsapp_verify_token')) {
                echo $_GET['hub_challenge'];
            }
        } else {
            // Handle incoming webhook events from WhatsApp
            $feedData = file_get_contents('php://input');
            if (!empty($feedData)) {
                $payload = json_decode($feedData, true);
                $this->handledata($payload);
                collect($payload['entry'])
                    ->pluck('changes')
                    ->flatten(1)
                    ->each(function ($change) {
                        $this->{$change['field']}($change['value']);
                    });
                // Forward webhook data if enabled
                if ('1' == get_option('enable_webhooks') && filter_var(get_option('webhooks_url'), \FILTER_VALIDATE_URL)) {
                    try {
                        $request = WhatsappRequests::request(
                            get_option('webhooks_url'),
                            [],
                            $payload,
                            'GET'
                        );
                        $response_code = $request->status_code;
                        $response_data = htmlentities($request->body);
                    } catch (Exception $e) {
                        $response_code = 'EXCEPTION';
                        $response_data = $e->getMessage();
                    }
                    update_option("whatsapp_webhook_code", $response_code);
                    update_option("whatsapp_webhook_data", $response_data);
                    // Log webhook response
                }
            }
        }
    }

    /**
     * Messages method.
     *
     * Processes incoming WhatsApp messages.
     * Updates message statuses and interacts with contacts.
     *
     * @param object $changed_data Data object containing changed data from WhatsApp webhook.
     */
    public function messages($changed_data)
    {
        // Handle incoming messages from WhatsApp
   

        if (!empty($changed_data['messages'])) {
            $message = reset($changed_data['messages']);
            $trigger_msg = (!empty($message['interactive'])) ? $message['interactive']['button_reply']['id'] : $message['text']['body'];
            $contact = reset($changed_data['contacts']);
            $metadata = $changed_data['metadata'];
            try {
                $contact_number = $message['from'];
                $contact_data = $this->whatsapp_interaction_model->getContactData($contact_number, $contact['profile']['name']);

                // Fetch template and message bots based on interaction
                $template_bots = $this->bots_model->getTemplateBotsbyRelType($contact_data->rel_type ?? '', $trigger_msg);
                $message_bots = $this->bots_model->getMessageBotsbyRelType($contact_data->rel_type ?? '', $trigger_msg);

                $add_messages = function ($item) {
                    $item['header_message'] = $item['header_data_text'];
                    $item['body_message'] = $item['body_data'];
                    $item['footer_message'] = $item['footer_data'];
                    return $item;
                };

                // Map template bots
                $template_bots = array_map($add_messages, $template_bots);
                $chatMessage = [];

                // Iterate over template bots
                foreach ($template_bots as $template) {
                    $template['rel_id'] = $contact_data->id;
                    if (!empty($contact_data->userid)) {
                        $template['userid'] = $contact_data->userid;
                    }

                    // Send template on exact match, contains, or first time
                    if ((1 == $template['bot_type'] && strtolower($template['trigger']) == strtolower($trigger_msg)) || 2 == $template['bot_type'] || (3 == $template['bot_type'] && $this->is_first_time)) {
                        $response = $this->whatsappLibrary($contact_number, $template, 'template_bot', $metadata['phone_number_id']);
                        $logBatch[] = $response['log_data'];
                        if ($response['status']) {
                            $interactionId = whatsappGetInteractionId($template, $template['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                            $chatMessage[] = $this->store_bot_messages($template, $interactionId, $contact_data, 'template_bot', $response);
                        }
                    }
                }

                // Iterate over message bots
                foreach ($message_bots as $message) {
                    $message['rel_id'] = $contact_data->id;
                    if ((1 == $message['reply_type'] && strtolower($message['trigger']) == strtolower($trigger_msg)) || 2 == $message['reply_type'] || (3 == $message['reply_type'] && $this->is_first_time)) {
                        $response = $this->sendMessage($contact_number, $message, $metadata['phone_number_id']);
                        if ($response['status']) {
                            $interactionId = whatsappGetInteractionId($message, $message['rel_type'], $contact_data->id, $contact_data->name, $contact_number, $changed_data['metadata']['display_phone_number']);
                            $chatMessage[] = $this->store_bot_messages($message, $interactionId, $contact_data, '', $response);
                        }
                    }
                }

                // Add chat messages to database
                $this->whatsapp_interaction_model->addChatMessage($chatMessage);
                // Add template bot logs
                $this->whatsapp_interaction_model->addWhatsbotLog($logBatch ?? []);
            } catch (\Throwable $th) {
                file_put_contents(FCPATH . '/errors.json', json_encode([$th->getMessage()]));
            }
        }
    }

    /**
     * Getdata method.
     *
     * Processes incoming data payload from WhatsApp webhook.
     *
     * @param array $payload Data payload received from WhatsApp webhook.
     */

    public function handledata($payload)
    {
        // Extract entry and changes
        $entry   = array_shift($payload['entry']);
        $changes = array_shift($entry['changes']);
        $value   = $changes['value'];

        // Check if payload contains messages
        if (isset($value['messages'])) {
            $messageEntry = array_shift($value['messages']);
            $contact      = array_shift($value['contacts']);
            $name         = $contact['profile']['name'];
            $from         = $messageEntry['from'];
            $metadata     = $value['metadata'];
            $wa_no        = $metadata['display_phone_number'];
            $wa_no_id     = $metadata['phone_number_id'];
            $messageType  = $messageEntry['type'];
            $message_id   = $messageEntry['id'];

            $this->is_first_time = !(bool) total_rows(db_prefix() . 'whatsapp_interactions', ['receiver_id' => $from]);

            // Extract message content based on type
            switch ($messageType) {
                case 'text':
                    $message = $messageEntry['text']['body'];
                    break;
                case 'interactive':
                    $message = $messageEntry['interactive']['button_reply']['title'];
                    break;
                case 'button':
                    $message = $messageEntry['button']['text'];
                    break;
                case 'reaction':
                    $emoji        = $messageEntry['reaction']['emoji'];
                    $decodedEmoji = json_decode('"' . $emoji . '"', false, 512, \JSON_UNESCAPED_UNICODE);
                    $message      = $decodedEmoji;
                    break;
                case 'image':
                case 'audio':
                case 'document':
                case 'video':
                    $media_id     = $messageEntry[$messageType]['id'];
                    $caption      = $messageEntry[$messageType]['caption'] ?? null;
                    $access_token = get_option('whatsapp_access_token');
                    $attachment   = $this->whatsappLibrary->retrieveUrl($media_id, $access_token);
                    break;
                default:
                    $message = ''; // Default to empty string
                    break;
            }

            // Save message to database
            $interaction_id = $this->whatsapp_interaction_model->insert_interaction([
                'receiver_id'   => $from,
                'wa_no'         => $wa_no,
                'wa_no_id'      => $wa_no_id,
                'name'          => $name,
                'last_message'  => $message ?? $messageType,
                'time_sent'     => date('Y-m-d H:i:s'),
                'last_msg_time' => date('Y-m-d H:i:s'),
            ]);

            $interaction = $this->whatsapp_interaction_model->get_interaction($interaction_id);
            $this->whatsapp_interaction_model->map_interaction($interaction);

            // Insert interaction message data into the 'whatsapp_official_interaction_messages' table
            $this->whatsapp_interaction_model->insert_interaction_message([
                'interaction_id' => $interaction_id,
                'sender_id'      => $from,
                'message_id'     => $message_id,
                'message'        => $message ?? $caption ?? '-',
                'type'           => $messageType,
                'staff_id'       => get_staff_user_id() ?? null,
                'url'            => $attachment ?? null,
                'status'         => 'sent',
                'time_sent'      => date('Y-m-d H:i:s'),
            ]);
            if (get_option('whatsapp_openai_token') && get_option('whatsapp_openai_status') == "enable" && $messageType =="text") {
                $this->ai_message_reply($interaction_id);
            }
            // Respond with success message
            http_response_code(200);
        } elseif (isset($value['statuses'])) {
            $statusEntry = array_shift($value['statuses']);
            $id          = $statusEntry['id'];
            $status      = $statusEntry['status'];
            $this->whatsapp_interaction_model->update_message_status($id, $status);
        } else {
            $this->output
                ->set_status_header(400)
                ->set_output('Invalid payload structure');
        }
    }
    private function ai_message_reply($interaction_id) {
        $client = OpenAI::client(get_option('whatsapp_openai_token'));
                $result = $client->chat()->create([
                    'model' => 'gpt-4',
                    'messages' => $this->whatsapp_interaction_model->get_interaction_history($interaction_id),
                ]);
                
        // Retrieve interaction details from database
        $interaction = $this->db->where('id', $interaction_id)->get(db_prefix() . 'whatsapp_interactions')->row();
        
        // Prepare variables for message sending
        $name = $interaction->name;
        $wa_no_id = $interaction->wa_no_id;
        $wa_no = $interaction->wa_no;
    
        // Send AI-generated message using WhatsApp library
        $this->whatsappLibrary->send_message($wa_no, [
            [
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $result->choices[0]->message->content
                ]
            ]
        ]);
    
        // Log the AI-generated message in the database
        $interaction_message_id = $this->whatsapp_interaction_model->insert_interaction_message([
            'interaction_id' => $interaction_id,
            'sender_id' => $wa_no_id,
            'message_id' => $result->choices[0]->message->content,
            'message' => $result->choices[0]->message->content,
            'type' => 'text',
            'staff_id' => get_staff_user_id() ?? null,
            'status' => 'sent',
            'time_sent' => date("Y-m-d H:i:s")
        ]);
    
    }
    
    public function send_message()
{
    // Retrieve POST data
    $id                   = $this->input->post('id', true) ?? '';
    $existing_interaction = $this->db->where('id', $id)->get(db_prefix() . 'whatsapp_interactions')->result_array();
    $to                   = $this->input->post('to', true) ?? '';
    $message              = strip_tags($this->input->post('message', true) ?? '');
    $imageAttachment      = $_FILES['image'] ?? null;
    $videoAttachment      = $_FILES['video'] ?? null;
    $documentAttachment   = $_FILES['document'] ?? null;
    $audioAttachment      = $_FILES['audio'] ?? null;

    // Initialize message data
    $message_data = [];

    // Check if there is only text message or only attachment
    if (!empty($message)) {
        // Send only text message
        $message_data = [
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message
            ]
        ];
    } elseif (!empty($audioAttachment)) {
        // Handle audio attachment
        $audio_url = $this->whatsappLibrary->handle_attachment_upload($audioAttachment);
        $message_data = [
            'type' => 'audio',
            'audio' => [
                'link' => WHATSAPP_MODULE_UPLOAD_URL . $audio_url
            ]
        ];
    } elseif (!empty($imageAttachment)) {
        // Handle image attachment
        $image_url = $this->whatsappLibrary->handle_attachment_upload($imageAttachment);
        $message_data = [
            'type' => 'image',
            'image' => [
                'link' => WHATSAPP_MODULE_UPLOAD_URL . $image_url
            ]
        ];
    } elseif (!empty($videoAttachment)) {
        // Handle video attachment
        $video_url = $this->whatsappLibrary->handle_attachment_upload($videoAttachment);
        $message_data = [
            'type' => 'video',
            'video' => [
                'link' => WHATSAPP_MODULE_UPLOAD_URL . $video_url
            ]
        ];
    } elseif (!empty($documentAttachment)) {
        // Handle document attachment
        $document_url = $this->whatsappLibrary->handle_attachment_upload($documentAttachment);
        $message_data = [
            'type' => 'document',
            'document' => [
                'link' => WHATSAPP_MODULE_UPLOAD_URL . $document_url
            ]
        ];
    } else {
        // No valid message data found
        echo json_encode(['success' => false, 'error' => 'No valid message data found']);
        return;
    }

    // Send message via WhatsApp API
    $response_data = $this->whatsappLibrary->send_message($existing_interaction[0]['wa_no_id'], $to, $message_data);


        // Check if the response data contains the message ID
        $messageId = null;
        if (isset($response_data['messages'][0]['id'])) {
            // Message sent successfully, store the message ID
            $messageId = $response_data['messages'][0]['id'];
        }

        // Insert message into the database
        $interaction_id = $this->whatsapp_interaction_model->insert_interaction([
            'receiver_id'  => $to,
            'last_message' => isset($message_data['text']['body']) ? $message_data['text']['body'] : '',
            'wa_no'        => $existing_interaction[0]['wa_no'],
            'wa_no_id'     => $existing_interaction[0]['wa_no_id'],
            'time_sent'    => date('Y-m-d H:i:s'),
        ]);

        $url = null;
        if (isset($message_data[$message_data['type']]['url'])) {
            $url = basename($message_data[$message_data['type']]['url']);
        }

       // Insert message into the database
        $interaction_id = $this->whatsapp_interaction_model->insert_interaction([
            'receiver_id' => $to,
            'last_message' => $message ?? ($message_data['type'] ?? ''), // Ensure fallback in case message_data is not set
            'wa_no' => $existing_interaction[0]['wa_no'],
            'wa_no_id' => $existing_interaction[0]['wa_no_id'],
            'time_sent' => date("Y-m-d H:i:s")
        ]);
    
        // Insert interaction message into the database
        $this->whatsapp_interaction_model->insert_interaction_message([
            'interaction_id' => $interaction_id,
            'sender_id' => $existing_interaction[0]['wa_no'], // Accessing object property directly
            'message' => $message,
            'message_id' => $res['id'] ?? null, // Ensure to get the message ID from the response
            'type' => $message_data['type'] ?? '', // Ensure fallback in case message_data['type'] is not set
            'staff_id' => get_staff_user_id() ?? null,
            'url' => isset($message_data[$message_data['type']]['link']) ? basename($message_data[$message_data['type']]['link']) : null, // Check if URL exists before accessing
            'status' => 'sent',
            'time_sent' => date("Y-m-d H:i:s")
        ]);


        // Return success response
        echo json_encode(['success' => true]);
    
}

    

    public function mark_interaction_as_read()
    {
        // Retrieve POST data
        $interaction_id = $_POST['interaction_id'] ?? '';

        // Validate input
        if (empty($interaction_id)) {
            echo json_encode(['error' => 'Invalid interaction ID']);

            return;
        }

        // Call the model function to mark the interaction as read
        $success = $this->whatsapp_interaction_model->update_message_status($interaction_id, 'read');

        // Check if the interaction was successfully marked as read
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to mark interaction as read']);
        }
    }

    public function template_category_update($changed_data)
    {
        $this->db->update(db_prefix() . 'whatsapp_templates', ['category' => $changed_data['new_category']], ['template_id' => $changed_data['message_template_id']]);
        $message = "Your whatsapp template {$changed_data['message_template_name']} category changed from {$changed_data['previous_category']} to {$changed_data['new_category']} for {$changed_data['message_template_language']} language";
        log_activity($message);

        $notifiedUsers = [];
        foreach (wbGetStaffMembersAllowedToViewMessageTemplates() as $staff) {
            if (
                add_notification([
                    'description' => 'message_template_category_has_been_changed',
                    'additional_data' => serialize(['template_name' => $changed_data['message_template_name'], 'from_to' => _l('from') . ' ' . $changed_data['previous_category'] . ' ' . _l('to') . ' ' . $changed_data['new_category']]),
                    'touserid' => $staff['staffid'],
                    'fromuserid' => 1,
                    'link' => 'whatsapp_api',
                ])
            ) {
                $notifiedUsers[] = $staff['staffid'];
            }
        }

        pusher_trigger_notification($notifiedUsers);
    }

    public function store_bot_messages($data, $interactionId, $rel_data, $type, $response)
    {
        $data['sending_count'] = (int)$data['sending_count'] + 1;
        if ('template_bot' == $type && !empty($response['status'])) {
            $header = wbParseText($data['rel_type'], 'header', $data);
            $body   = wbParseText($data['rel_type'], 'body', $data);
            $footer = wbParseText($data['rel_type'], 'footer', $data);

            $buttonHtml = '';
            if (!empty(json_decode($data['buttons_data']))) {
                $buttons = json_decode($data['buttons_data']);
                $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
                foreach ($buttons->buttons as $key => $value) {
                    $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $value->text . '</button>';
                }
                $buttonHtml .= '</div>';
            }

            $header_data = '';
            if ($data['header_data_format'] == 'IMAGE' && is_image(get_upload_path_by_type('template') . $data['filename'])) {
                $header_data = '<a href="' . base_url(get_upload_path_by_type('template') . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('template') . $data['filename']) . '" class="img-responsive img-rounded" style="object-fit: cover;"></img></a>';
            } elseif ($data['header_data_format'] == 'TEXT' || $data['header_data_format'] == '') {
                $header_data = "<span class='tw-mb-3 bold'>" . nl2br(whatsappDecodeWhatsAppSigns($header ?? '')) . "</span>";
            }

            $this->bots_model->update_sending_count(db_prefix() . 'whatsapp_campaigns', $data['sending_count'], ['id' => $data['campaign_table_id']]);

            // Prepare the data for chat message
            return [
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
        $data = wbParseMessageText($data);
        $header = $data['bot_header'];
        $body   = $data['reply_text'];
        $footer = $data['bot_footer'];

        $header_image = '';

        $buttonHtml = "<div class='tw-flex tw-gap-2 tw-w-full padding-5 tw-flex-col mtop5'>";
        $option = false;
        if (!empty($data['button1'])) {
            $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $data['button1'] . '</button>';
            $option = true;
        }
        if (!empty($data['button2'])) {
            $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $data['button2'] . '</button>';
            $option = true;
        }
        if (!empty($data['button3'])) {
            $buttonHtml .= '<button class="btn btn-default tw-w-full">' . $data['button3'] . '</button>';
            $option = true;
        }
        if (!$option && !empty($data['button_name']) && !empty($data['button_url']) && filter_var($data['button_url'], \FILTER_VALIDATE_URL)) {
            $buttonHtml .= '<a href="' . $data['button_url'] . '" class="btn btn-default tw-w-full mtop10"><i class="mright5 fa-solid fa-share-from-square"></i>' . $data['button_name'] . '</a> <br>';
            $option = true;
        }
        if (!$option && is_image(get_upload_path_by_type('bot_files') . $data['filename'])) {
            $header_image = '<a href="' . base_url(get_upload_path_by_type('bot_files') . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('bot_files') . $data['filename']) . '" class="img-responsive img-rounded" style="width: 300px"></img></a>';
        }
        $buttonHtml .= '</div>';

        $this->bots_model->update_sending_count(db_prefix() . 'whatsapp_bot', $data['sending_count'], ['id' => $data['id']]);

        return [
            'interaction_id' => $interactionId,
            'sender_id'      => $this->whatsappLibrary->getDefaultPhoneNumber(),
            'url'            => null,
            'message'        => $header_image . "
                            <span class='tw-mb-3 bold'>" . nl2br(whatsappDecodeWhatsAppSigns($header ?? '')) . "</span>
                            <p>" . nl2br(whatsappDecodeWhatsAppSigns($body)) . "</p>
                            <span class='text-muted tw-text-xs'>" . nl2br(whatsappDecodeWhatsAppSigns($footer ?? '')) . "</span> $buttonHtml ",
            'status'     => 'sent',
            'time_sent'  => date('Y-m-d H:i:s'),
            'message_id' => $response['data']->messages[0]->id,
            'staff_id'   => 0,
            'type'       => 'text',
        ];
    }
}
