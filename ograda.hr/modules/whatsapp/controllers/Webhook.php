<?php

defined('BASEPATH') || exit('No direct script access allowed');

use WpOrg\Requests\Requests as WhatsappRequests;

class Webhook extends ClientsController
{
    public $is_first_time = false;
    private $interaction_menu_state_table = 'interaction_menu_state'; // Database table for menu state tracking

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['whatsapp_interaction_model', 'bots_model']);
        $this->load->library('WhatsappLibrary');
        $this->WhatsappLibrary = new WhatsappLibrary();
    }

    public function getdata()
    {
        if ($this->isVerificationRequest()) {
            $this->handleVerificationRequest();
        } else {
            $this->processWebhookData();
        }
    }

    private function isVerificationRequest(): bool
    {
        return isset($_GET['hub_mode'], $_GET['hub_challenge'], $_GET['hub_verify_token']);
    }

    private function handleVerificationRequest()
    {
        if ($_GET['hub_verify_token'] === get_option('whatsapp_webhook_token')) {
            echo $_GET['hub_challenge'];
            http_response_code(200);
        } else {
            http_response_code(403);
        }
        exit();
    }

    private function processWebhookData()
    {
        $feedData = file_get_contents('php://input');
        if (!$feedData) {
            http_response_code(400); // Bad Request
            exit();
        }

        $payload = json_decode($feedData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400); // Bad Request
            exit();
        }

        $this->forwardWebhookData($payload);
        $this->handleData($payload);

        collect($payload['entry'] ?? [])
            ->pluck('changes')
            ->flatten(1)
            ->each(function ($change) {
                if (!empty($change['field']) && method_exists($this, $change['field'])) {
                    $this->{$change['field']}($change['value']);
                }
            });

        http_response_code(200); // OK
        exit();
    }

    private function forwardWebhookData(array $payload)
    {
        if (get_option('enable_webhooks') !== '1') {
            return;
        }

        $webhookUrl = get_option('webhooks_url');
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            log_message('error', 'Invalid webhook URL');
            return;
        }

        $maxRetries = 3;
        $timeout = 10; // seconds

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = WhatsappRequests::request(
                    $webhookUrl,
                    [], // No custom headers
                    json_encode($payload), // Encode payload as JSON
                    'POST',
                    ['timeout' => $timeout] // Set a timeout for the request
                );

                update_option('whatsapp_webhook_code', $response->status_code);
                update_option('whatsapp_webhook_data', htmlentities($response->body));

                if ($response->status_code >= 200 && $response->status_code < 300) {
                    break; // Exit loop if successful
                }
            } catch (Exception $e) {
                update_option('whatsapp_webhook_code', 'EXCEPTION');
                update_option('whatsapp_webhook_data', $e->getMessage());
                log_message('error', 'Webhook forwarding failed: ' . $e->getMessage());
            }

            sleep(2); // Wait before retrying
        }
    }

    private function handleData($payload)
    {
        if (empty($payload['entry']) || !is_array($payload['entry'])) {
            $this->sendErrorResponse('Invalid payload structure', $payload);
            return;
        }

        $entry = array_shift($payload['entry']);
        if (empty($entry['changes']) || !is_array($entry['changes'])) {
            $this->sendErrorResponse('Invalid changes structure', $payload);
            return;
        }

        $changes = array_shift($entry['changes']);
        $value = $changes['value'] ?? null;

        if (empty($value)) {
            $this->sendErrorResponse('Invalid value structure', $payload);
            return;
        }

        if (isset($value['messages']) && is_array($value['messages'])) {
            $this->processMessages($value);
        } elseif (isset($value['statuses']) && is_array($value['statuses'])) {
            $this->processStatuses($value);
        } else {
            $this->sendErrorResponse('Invalid payload structure', $payload);
        }
    }

    private function processStatuses($value)
    {
        $statusEntry = array_shift($value['statuses']);
        $id = $statusEntry['id'];
        $status = $statusEntry['status'];

        $this->whatsapp_interaction_model->update_message_status($id, $status);
    }

    private function processMessages($value)
    {
        $messageEntry = array_shift($value['messages']);
        $contact = array_shift($value['contacts']);
        $metadata = $value['metadata'];

        if (empty($messageEntry) || empty($contact) || empty($metadata)) {
            $this->sendErrorResponse('Missing required message or contact data', $value);
            return;
        }

        $interaction_id = $this->saveInteractionAndMessage($messageEntry, $contact, $metadata);
        $this->processBots($interaction_id, $messageEntry['text']['body'] ?? $messageEntry['interactive']['button_reply']['id'] ?? "", $messageEntry, $contact, $metadata);

        if ($this->isAIReplyEnabled() && $messageEntry['type'] === "text") {
            $this->aiMessageReply($interaction_id);
        }

        http_response_code(200);
    }

    private function saveInteractionAndMessage(array $messageEntry, array $contact, array $metadata): int
    {
        $receiverId = $messageEntry['from'];
        $timestamp = $this->formatTimestamp($messageEntry['timestamp']);

        $interaction = $this->whatsapp_interaction_model->get_interaction(null, [
            'receiver_id' => $receiverId,
            'wa_no' => $metadata['display_phone_number'],
            'wa_no_id' => $metadata['phone_number_id'],
        ]);

        $this->is_first_time = !$interaction;

        $unreadCount = $interaction ? $interaction['unread'] + 1 : 1;

        $messageData = $this->extractMessage($messageEntry['type'], $messageEntry);

        $interactionData = [
            'receiver_id' => $receiverId,
            'name' => $contact['profile']['name'],
            'wa_no' => $metadata['display_phone_number'],
            'wa_no_id' => $metadata['phone_number_id'],
            'unread' => $unreadCount,
            'last_message' => $messageData['message'] ?? $messageEntry['type'],
            'time_sent' => $timestamp,
            'last_msg_time' => $timestamp,
        ];
        $interactionId = $this->whatsapp_interaction_model->insert_interaction($interactionData);

        $messageInsertData = [
            'interaction_id' => $interactionId,
            'sender_id' => $receiverId,
            'message_id' => $messageEntry['id'],
            'ref_message_id' => $messageEntry['context']['id'] ?? $messageEntry['reaction']['message_id'] ?? null,
            'message' => $messageData['message']??$messageEntry['type'],
            'type' => $messageEntry['type'],
            'staff_id' => get_staff_user_id() ?? null,
            'url' => $messageData['url'] ?? null,
            'status' => 'delivered',
            'nature' => 'received',
            'time_sent' => $timestamp,
        ];

        $this->whatsapp_interaction_model->insert_interaction_message($messageInsertData);

        return $interactionId;
    }

    private function formatTimestamp($timestamp): string
    {
        return date("Y-m-d H:i:s", $timestamp);
    }

private function extractMessage($type, $messageEntry)
{
    $data = [
        'message' => '',
        'url' => null,
    ];

    switch ($type) {
        case 'text':
            $data['message'] = $messageEntry['text']['body'] ?? 'No text found';
            break;

        case 'image':
        case 'video':
        case 'document':
        case 'audio':
        case 'sticker':
            $data = $this->extractMediaMessage($type, $messageEntry);
            break;

        case 'location':
            $location_name = $messageEntry['location']['name'] ?? 'Unknown location';
            $data['message'] = 'Location received: ' . $location_name;
            break;

        case 'contacts':
            $contact_name = $messageEntry['contacts'][0]['name']['formatted_name'] ?? 'Unknown contact';
            $data['message'] = 'Contact received: ' . $contact_name;
            break;

        case 'interactive':
            if (isset($messageEntry['interactive']['button_reply'])) {
                $data['message'] = 'Button clicked: ' . $messageEntry['interactive']['button_reply']['title'];
            } elseif (isset($messageEntry['interactive']['list_reply'])) {
                $data['message'] = 'List option selected: ' . $messageEntry['interactive']['list_reply']['title'];
            } else {
                $data['message'] = 'Unknown interactive response';
            }
            break;

        case 'reaction':
            $data['ref_message_id'] = $messageEntry['reaction']['message_id'] ?? '';
            $emoji = $messageEntry['reaction']['emoji'] ?? '';
            $data['message'] = json_decode('"' . $emoji . '"', false, 512, JSON_UNESCAPED_UNICODE);
            break;
        case 'request_welcome':  
            $data['message'] = 'Automated Request Welcome Message is triggered.Set automated message on bot 3 for it.';
            break;

        default:
            $data['message'] = 'Unknown message type';
            break;
    }

    return $data;
}


    private function extractMediaMessage($type, $messageEntry)
    {
        $data = [
            'message' => ucfirst($type) . ' received',
            'url' => $messageEntry[$type]['link'] ?? null,
        ];

        if (isset($messageEntry[$type]['id'])) {
            $media_id = $messageEntry[$type]['id'];
            $caption = $messageEntry[$type]['caption'] ?? null;
            $access_token = get_option('whatsapp_access_token');
            $attachment = $this->WhatsappLibrary->retrieveUrl($media_id, $access_token);
            $data['message'] .= $caption ? " - $caption" : '';
            $data['url'] = $attachment ?: $data['url'];
        }

        return $data;
    }

    private function aiMessageReply(int $interaction_id)
    {
        $client = OpenAI::client(get_option('whatsapp_openai_token'));

        $interaction_history = $this->whatsapp_interaction_model->get_interaction_history($interaction_id);

        if (empty($interaction_history)) {
            log_message('error', "No interaction history found for ID: $interaction_id");
            return 'No interaction history available.';
        }

        $result = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => $interaction_history,
        ]);

        $ai_reply_content = $result->choices[0]->message->content ?? $result;

        $interaction = $this->db->where('id', $interaction_id)->get(db_prefix() . 'whatsapp_interactions')->row();

        $response_data = $this->WhatsappLibrary->send_message($interaction->wa_no, [
            [
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $ai_reply_content,
                ],
            ],
        ]);

        $message_id = $response_data['messages'][0]['id'] ?? null;
        if (is_null($message_id)) {
            log_message('error', "Failed to send message via WhatsApp for interaction ID: $interaction_id");
        }

        $this->whatsapp_interaction_model->insert_interaction_message([
            'interaction_id' => $interaction_id,
            'sender_id' => $interaction->wa_no_id,
            'message_id' => $message_id,
            'message' => $ai_reply_content,
            'type' => 'text',
            'staff_id' => get_staff_user_id() ?? null,
            'status' => 'sent',
            'time_sent' => date("Y-m-d H:i:s"),
        ]);
    }

    private function isAIReplyEnabled(): bool
    {
        return get_option('whatsapp_openai_token') && get_option('whatsapp_openai_status') === "enable";
    }

    private function processBots($interaction_id, $trigger_msg, $messageEntry, $contact, $metadata)
    {
        $bots = whatsapp_bots($interaction_id);

        if (empty($bots)) {
            return;
        }

        $logBatch = [];

        foreach ($bots as $bot) {
            $this->processBot($bot, $interaction_id, $trigger_msg, $messageEntry, $contact, $metadata, $logBatch);
        }

        if (!empty($logBatch)) {
            $this->addWhatsbotLog($logBatch);
        }
    }

    private function processBot($bot, $interaction_id, $trigger_msg, $messageEntry, $contact, $metadata, &$logBatch)
    {
        $log_data = [
            'phone_number_id' => $metadata['phone_number_id'],
            'category' => whatsapp_get_bot_type($bot['bot_type'])['label'],
            'category_id' => $bot['id'],
            'rel_type' => $contact->rel_type ?? 'individual',
            'rel_id' => $contact->rel_id ?? $contact->id ?? "-",
            'response_code' => null,
            'raw_data' => null,
            'recorded_at' => date('Y-m-d H:i:s'),
        ];
         log_message('error', 'This Message Received: ' . print_r($messageEntry,true));
  
        // Default session timeout to 86400 seconds (24 hours) if not set
        $session_timeout = 86400;
    
        // Check if the bot's reply type is 7 (session-based bot)
        if ($bot['reply_type'] == 7) {
            // Convert $trigger_msg (hours) to seconds
            $session_timeout = (int)$trigger_msg * 3600; // 1 hour = 3600 seconds
        }
    
        // Retrieve the interaction record
        $interaction = $this->whatsapp_interaction_model->get_interaction($interaction_id);
    
        if ($interaction) {
            // Check if it's the first message in a new session based on the interaction record
            $is_first_message_in_session = $this->isSessionExpired($interaction['time_sent'], $session_timeout);
    
            // Check if the message type is "request_welcome" (first-time message from the client)
            $is_first_time = isset($messageEntry['type']) && $messageEntry['type'] === 'request_welcome';
    
            if ($this->shouldTriggerBot($bot, $trigger_msg, $is_first_message_in_session, $is_first_time)) {
                $log_data = $this->prepareBotLogData($bot, $metadata);
                $message_data = $this->prepareMessageData($bot, $messageEntry['from']);
                $response = $this->WhatsappLibrary->send_message($metadata['phone_number_id'], $messageEntry['from'], $message_data, $log_data);
    
                $this->saveInteractionMessage($bot, $response, $messageEntry['from'], $message_data, $metadata, $interaction_id, $response);
                $logBatch[] = $response['log_data'];
            }
        }
    }




    private function isSessionExpired($last_interaction_time, $session_timeout): bool
    {
        // If no previous interaction, consider it the first message in the session
        if (!$last_interaction_time) {
            return true;
        }
    
        $current_time = time();
        $time_since_last_interaction = $current_time - strtotime($last_interaction_time);
    
        // If the time since the last interaction exceeds the session timeout, it is the first message in the session
        return $time_since_last_interaction > $session_timeout;
    }



private function shouldTriggerBot(array $bot, string $trigger_msg, bool $is_first_message_in_session, bool $is_first_time): bool
{
    $trigger_type = $bot['reply_type'] ?? null;
    $trigger_msg = strtolower(trim($trigger_msg));
    log_message('error', 'Evaluating bot trigger with message: ' . $trigger_msg);

    $conditions = $this->extractConditions($bot['trigger'] ?? '');

    foreach ($conditions as $condition) {
        $condition = strtolower(trim($condition));

        switch ($trigger_type) {
            case 1: // Exact Match
                if ($this->isExactMatch($trigger_msg, $condition)) {
                    log_message('error', 'Bot triggered on exact match: ' . $condition);
                    return true;
                }
                break;

            case 2: // Message Contains Word
                if ($this->containsWord($trigger_msg, $condition)) {
                    log_message('error', 'Bot triggered on word containment: ' . $condition);
                    return true;
                }
                break;

            case 3: // Client Sends First Message
                if ($is_first_time) {
                    log_message('error', 'Bot triggered on client’s first message.');
                    return true;
                }
                break;

            case 4: // Keyword Match
                if ($this->isKeywordMatch($trigger_msg, $condition)) {
                    log_message('error', 'Bot triggered on keyword match: ' . $condition);
                    return true;
                }
                break;

            case 5: // Within Office Time Range
                if ($this->isWithinOfficeTimings($condition)) {
                    log_message('error', 'Bot triggered within office timings: ' . $condition);
                    return true;
                }
                break;

            case 6: // Out of Office Time Range
                if ($this->isOutsideOfficeTimings($condition)) {
                    log_message('error', 'Bot triggered outside office timings: ' . $condition);
                    return true;
                }
                break;

            case 7: // First Message in New Session
                if ($is_first_message_in_session) {
                    log_message('error', 'Bot triggered on first message in session.');
                    return true;
                }
                break;

            default:
                log_message('error', 'Unsupported trigger type: ' . $trigger_type);
                break;
        }
    }

    log_message('error', 'No trigger conditions met.');
    return false;
}



    private function isExactMatch(string $trigger_msg, string $condition): bool
    {
        return strtolower($trigger_msg) === strtolower($condition);
    }

    private function containsWord(string $message, string $word): bool
    {
        return strpos($message, $word) !== false;
    }

    private function isKeywordMatch(string $message, string $keywords): bool
    {
        $keywordsArray = $this->extractKeywords($keywords);
        foreach ($keywordsArray as $keyword) {
            if (preg_match($this->getPatternForTriggerType(4, $keyword), $message)) {
                return true;
            }
        }
        return false;
    }

    private function isWithinOfficeTimings(string $time_range): bool
    {
        list($start_time, $end_time) = explode('-', $time_range);
        $current_time = date('H:i');

        $withinRange = $current_time >= $start_time && $current_time <= $end_time;
        log_message('error', 'Current time ' . $current_time . ($withinRange ? ' is' : ' is not') . ' within the office timings ' . $time_range);
        return $withinRange;
    }

    private function extractConditions(string $trigger): array
    {
        return array_map('trim', explode('|', strtolower($trigger)));
    }

    private function getPatternForTriggerType($trigger_type, $keyword)
    {
        return $trigger_type === 4 ? '/\b' . preg_quote($keyword, '/') . '\b/i' : '/' . preg_quote($keyword, '/') . '/i';
    }

    private function extractKeywords(string $trigger): array
    {
        return array_map('trim', explode(',', $trigger));
    }

    private function isOutsideOfficeTimings(string $time_range): bool
    {
        list($start_time, $end_time) = explode('-', $time_range);
        $current_time = date('H:i');

        $outsideRange = $current_time < $start_time || $current_time > $end_time;

        log_message('error', 'Current time ' . $current_time . ($outsideRange ? ' is' : ' is not') . ' outside the office timings ' . $time_range);

        return $outsideRange;
    }

    private function prepareMessageData($bot, $contact_number)
    {
        $message_data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $contact_number,
        ];

        switch ($bot['bot_type']) {
            case 1: // Message Bot
                return $this->prepareTextMessage($bot, $message_data);
            case 7: // Interactive Buttons Bot
            case 9: // Quick Reply Bot
                return $this->prepareInteractiveMessage($bot, $message_data);
            case 2: // Template Bot
                return $this->prepareTemplateMessage($bot, $message_data);
            case 3: // Menu Bot
                return $this->prepareMenuMessage($bot, $message_data);
            case 4: // Flow Bot
            case 8: // List Bot
                return $this->prepareListMessage($bot, $message_data);
            case 5: // Media Bot
                return $this->prepareMediaMessage($bot, $message_data);
            case 6: // Location Bot
                return $this->prepareLocationMessage($bot, $message_data);
            case 10: // Sticker Bot
                return $this->prepareStickerMessage($bot, $message_data);
            case 11: // Contact Bot
                return $this->prepareContactMessage($bot, $message_data);
            case 12: // Poll Bot
                return $this->preparePollMessage($bot, $message_data);
            default:
                log_message('error', 'Unsupported bot type: ' . $bot['bot_type']);
                return null;
        }
    }
    private function prepareTextMessage($bot, $message_data)
    {
        $message = htmlspecialchars(trim($bot['reply_text']), ENT_QUOTES, 'UTF-8');

        $message_data['type'] = 'text';
        $message_data['text'] = [
            'body' => $message,
            'preview_url' => true,
        ];

        if (!empty($bot['bot_header'])) {
            $message_data['header'] = [
                'type' => 'text',
                'text' => htmlspecialchars(trim($bot['bot_header']), ENT_QUOTES, 'UTF-8'),
            ];
        }

        if (!empty($bot['bot_footer'])) {
            $message_data['footer'] = [
                'text' => htmlspecialchars(trim($bot['bot_footer']), ENT_QUOTES, 'UTF-8'),
            ];
        }

        return $message_data;
    }

    private function prepareInteractiveMessage($bot, $message_data)
    {
        $message_data['type'] = 'interactive';
        $message_data['interactive'] = [
            'type' => 'button',
            'body' => [
                'text' => $bot['reply_text'],
            ],
            'action' => [
                'buttons' => $this->prepareButtons($bot),
            ],
        ];

        if (!empty($bot['bot_header'])) {
            $message_data['interactive']['header'] = [
                'type' => 'text',
                'text' => $bot['bot_header'],
            ];
        }

        if (!empty($bot['bot_footer'])) {
            $message_data['interactive']['footer'] = [
                'text' => $bot['bot_footer'],
            ];
        }

        return $message_data;
    }

    private function prepareTemplateMessage($bot, $message_data)
    {
        $message_data = $this->WhatsappLibrary->prepare_template_message_data($message_data, $bot);
        return $message_data;
    }
    private function prepareMenuMessage($bot, $contact_number)
    {
        $message_data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $contact_number,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'header' => [
                    'type' => 'text',
                    'text' => !empty($bot['bot_header']) ? $bot['bot_header'] : 'Menu Header',
                ],
                'body' => [
                    'text' => !empty($bot['reply_text']) ? $bot['reply_text'] : 'Choose an option',
                ],
                'footer' => [
                    'text' => !empty($bot['bot_footer']) ? $bot['bot_footer'] : 'Footer text',
                ],
                'action' => [
                    'button' => !empty($bot['button_name']) ? $bot['button_name'] : 'Select',
                    'sections' => $this->prepareMenuSections(json_decode($bot['menu_items'], true)),
                ],
            ],
        ];

        return $message_data;
    }

    private function prepareMenuSections($menu_items)
    {
        $sections = [];
        $grouped_items = [];

        foreach ($menu_items as $item) {
            $parent_id = $item['menu_item_parent_id'] ?? '0';
            $grouped_items[$parent_id][] = $item;
        }

        return $this->generateMenuSection($grouped_items, '0');
    }

    private function generateMenuSection($grouped_items, $parent_id)
    {
        $sections = [];

        if (isset($grouped_items[$parent_id])) {
            $rows = [];
            foreach ($grouped_items[$parent_id] as $item) {
                $rows[] = [
                    'id' => $item['menu_item_id'],
                    'title' => substr($item['menu_item'], 0, 24),
                    'description' => $item['message'] ?? '',
                ];

                if (isset($grouped_items[$item['menu_item_id']])) {
                    $sub_section = $this->generateMenuSection($grouped_items, $item['menu_item_id']);
                    $sections = array_merge($sections, $sub_section);
                }
            }

            if (!empty($rows)) {
                $sections[] = [
                    'title' => 'Menu',
                    'rows' => $rows,
                ];
            }
        }

        return $sections;
    }

    // Save the user's menu interaction state.
    private function saveMenuState($interaction_id, $menu_path)
    {
        $data = [
            'interaction_id' => $interaction_id,
            'menu_path' => $menu_path,
            'bot_id' => $this->getCurrentBotId(),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    
        // Insert or update the interaction_menu_state table
        $existing_state = $this->db->get_where($this->interaction_menu_state_table, ['interaction_id' => $interaction_id])->row();
        if ($existing_state) {
            $this->db->where('interaction_id', $interaction_id)->update($this->interaction_menu_state_table, $data);
        } else {
            $this->db->insert($this->interaction_menu_state_table, $data);
        }
    }

    // Get the user's current menu state to determine the next action.
    private function getMenuState($interaction_id)
    {
        $menu_state = $this->db->get_where($this->interaction_menu_state_table, ['interaction_id' => $interaction_id])->row();
        return $menu_state ? $menu_state->menu_path : null;
    }

    // Update menu navigation based on user input
    private function updateMenuState($interaction_id, $selected_option)
    {
        $current_menu_path = $this->getMenuState($interaction_id);
        $new_menu_path = $this->determineNextMenuPath($current_menu_path, $selected_option);
        $this->saveMenuState($interaction_id, $new_menu_path);
    }

    private function determineNextMenuPath($current_menu_path, $selected_option)
    {
        // Logic to decide the next menu path based on the current path and the selected option
        if ($selected_option === 'back') {
            // Go back to the previous menu
            return $this->getPreviousMenuPath($current_menu_path);
        } elseif ($selected_option === 'main') {
            // Go back to the main menu
            return 'main_menu';
        } else {
            // Move forward based on the selected option
            return $current_menu_path . '/' . $selected_option;
        }
    }

    private function getPreviousMenuPath($menu_path)
    {
        $path_segments = explode('/', $menu_path);
        array_pop($path_segments); // Remove the last segment to go back
        return implode('/', $path_segments);
    }
    
  
    private function prepareListMessage($bot, $message_data)
    {
        $message_data['type'] = 'interactive';
        $message_data['interactive'] = [
            'type' => 'list',
            'header' => [
                'type' => 'text',
                'text' => $bot['bot_header'] ?? 'Menu Header',
            ],
            'body' => [
                'text' => $bot['reply_text'] ?? 'Choose an option',
            ],
            'footer' => [
                'text' => $bot['bot_footer'] ?? 'Footer text',
            ],
            'action' => [
                'button' => $bot['button_name'] ?? 'Choose',
                'sections' => $this->prepareSections($bot),
            ],
        ];
        return $message_data;
    }

    private function prepareMediaMessage($bot, $message_data)
    {
        $message_data['type'] = $bot['media_type'];
        $message_data[$bot['media_type']] = [
            'link' =>  $this->bots_model->get_asset_url($bot['filename']),
            'caption' => $bot['reply_text'] ?? '',
        ];
        return $message_data;
    }

    private function prepareLocationMessage($bot, $message_data)
    {
        $message_data['type'] = 'location';
        $message_data['location'] = [
            'latitude' => $bot['latitude'],
            'longitude' => $bot['longitude'],
            'name' => $bot['location_name'] ?? '',
            'address' => $bot['location_address'] ?? '',
        ];
        return $message_data;
    }

    private function prepareStickerMessage($bot, $message_data)
    {
        $message_data['type'] = 'sticker';
        $message_data['sticker'] = [
            'link' => $bot['sticker_link'],
        ];
        return $message_data;
    }

    private function prepareContactMessage($bot, $message_data)
    {
        $message_data['type'] = 'contacts';
        $message_data['contacts'] = [
            [
                'name' => [
                    'formatted_name' => $bot['contact_name'],
                    'first_name' => $bot['contact_first_name'],
                    'last_name' => $bot['contact_last_name'],
                ],
                'phones' => [
                    [
                        'phone' => $bot['contact_number'],
                    ],
                ],
                'emails' => [
                    [
                        'email' => $bot['contact_email'],
                    ],
                ],
            ],
        ];
        return $message_data;
    }

    private function preparePollMessage($bot, $message_data)
    {
        $message_data['type'] = 'interactive';
        $message_data['interactive'] = [
            'type' => 'button',
            'body' => [
                'text' => $bot['poll_question'] ?? 'Poll question here',
            ],
            'action' => [
                'buttons' => $this->preparePollButtons($bot),
            ],
        ];
        return $message_data;
    }

    private function preparePollButtons($bot)
    {
        $buttons = [];

        for ($i = 1; $i <= 3; $i++) {
            if (!empty($bot["poll_option{$i}"])) {
                $buttons[] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => 'poll_option_' . $i, // Assign a unique ID to each button
                        'title' => $bot["poll_option{$i}"], // Use poll options as titles
                    ],
                ];
            }
        }

        return $buttons;
    }

    private function prepareBotLogData($bot, $metadata)
    {
        return [
            'phone_number_id' => $metadata['phone_number_id'],
            'category' => whatsapp_get_bot_type($bot['bot_type'])['label'],
            'category_id' => $bot['bot_type'] ?? "-",
            'rel_type' => 'individual',
            'rel_id' => $bot['id'] ?? "-",
            'recorded_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function saveInteractionMessage($bot, $response, $contact_number, $message_data, $metadata, $interaction_id)
    {
        $formatted_message = '';
        $message_type = $message_data['type'] ?? 'text';
        $url = null;

        switch ($message_type) {
            case 'interactive':
                $header_data = $message_data['interactive']['header']['text'] ?? '';
                $body = $message_data['interactive']['body']['text'] ?? '';
                $footer = $message_data['interactive']['footer']['text'] ?? '';
                $formatted_message = "$header_data<p>" . nl2br(whatsappDecodeWhatsAppSigns($body)) . "</p><span class='text-muted tw-text-xs'>" . nl2br(whatsappDecodeWhatsAppSigns($footer)) . "</span>";
                break;

            case 'template':
                $header_data = $this->format_header_data($bot);
                $body = whatsappParseText($bot['rel_type'], 'body', $bot, 'data');
                $footer = whatsappParseText($bot['rel_type'], 'footer', $bot, 'data');
                $buttonHtml = $this->whatsapp_interaction_model->format_button_data($bot);

                $body = is_array($body) ? implode("\n", $body) : $body;
                $footer = is_array($footer) ? implode("\n", $footer) : $footer;

                $formatted_message = "$header_data<p>" . nl2br(htmlspecialchars($body)) . "</p><span class='text-muted tw-text-xs'>" . nl2br(htmlspecialchars($footer)) . "</span>$buttonHtml";
                break;

            case 'text':
                $body = $message_data['text']['body'] ?? '';
                $formatted_message = nl2br(whatsappDecodeWhatsAppSigns($body));
                break;

            case 'image':
            case 'video':
            case 'document':
            case 'audio':
                $caption = $message_data[$message_type]['caption'] ?? '';
                $url = $message_data[$message_type]['link'] ?? '';
                $formatted_message = nl2br(whatsappDecodeWhatsAppSigns($caption));
                break;

            case 'location':
                $location_name = $message_data['location']['name'] ?? '';
                $address = $message_data['location']['address'] ?? '';
                $formatted_message = "Location: $location_name\nAddress: $address";
                break;

            case 'contacts':
                $contact_name = $message_data['contacts'][0]['name']['formatted_name'] ?? 'Contact';
                $formatted_message = "Contact: $contact_name";
                break;

            case 'sticker':
                $url = $message_data['sticker']['link'] ?? '';
                $formatted_message = "Sticker received";
                break;

            default:
                $formatted_message = "Unknown message type received";
                break;
        }

        $messageData = [
            'interaction_id' => $interaction_id,
            'sender_id' => $metadata['display_phone_number'] ?? '',
            'url' => $url,
            'message' => $formatted_message,
            'status' => 'sent',
            'time_sent' => date('Y-m-d H:i:s'),
            'message_id' => $response['id'] ?? null,
            'staff_id' => $metadata['staff_id'] ?? 0,
            'type' => $message_type,
            'nature' => 'sent',
        ];

        $this->db->insert(db_prefix() . 'whatsapp_interaction_messages', $messageData);

        if ($this->db->affected_rows() <= 0) {
            throw new Exception('Failed to save the interaction message.');
        }

        return true;
    }

    private function format_header_data($data)
    {
        $headerArray = whatsappParseText($data['rel_type'], 'header', $data, 'data');
        $header = implode(' ', $headerArray);

        switch ($data['header_data_format']) {
            case 'IMAGE':
                return '<a href="' . base_url(get_upload_path_by_type('bots') . '/' . $data['filename']) . '" data-lightbox="image-group"><img src="' . base_url(get_upload_path_by_type('bots') . '/' . $data['filename']) . '" class="img-responsive img-rounded" style="width: 300px"></img></a>';
            case 'TEXT':
                return "<span class='tw-mb-3 bold'>" . nl2br(whatsappDecodeWhatsAppSigns($header)) . "</span>";
            default:
                return "<span class='tw-mb-3 bold'>" . nl2br(whatsappDecodeWhatsAppSigns($header)) . "</span>";
        }
    }

    private function formatTemplateComponents($components)
    {
        $formatted_components = '';

        foreach ($components as $component) {
            if ($component['type'] === 'header') {
                $formatted_components .= "<b>Header:</b> " . nl2br(whatsappDecodeWhatsAppSigns($component['parameters'][0]['text'] ?? '')) . "<br>";
            } elseif ($component['type'] === 'body') {
                $formatted_components .= "<b>Body:</b> " . nl2br(whatsappDecodeWhatsAppSigns($component['parameters'][0]['text'] ?? '')) . "<br>";
            } elseif ($component['type'] === 'button') {
                foreach ($component['sub_type'] === 'quick_reply' ? $component['parameters'] : [] as $button) {
                    $formatted_components .= "<b>Button:</b> " . $button['reply']['title'] . "<br>";
                }
            }
        }

        return $formatted_components;
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
            $this->db->insert_batch(db_prefix() . 'whatsapp_activity_log', $logBatch);
        }
    }

    private function sendErrorResponse($message, $data = [])
    {
        log_message('error', $message . ': ' . json_encode($data));
        http_response_code(400); // Bad Request
        echo json_encode(['error' => $message]);
        exit();
    }

    public function mark_interaction_as_read()
    {
        $interaction_id = $this->input->post('interaction_id', true) ?? '';

        if (empty($interaction_id)) {
            echo json_encode(['error' => 'Invalid interaction ID']);
            return;
        }

        $success = $this->whatsapp_interaction_model->update_message_status($interaction_id, 'read');

        echo json_encode(['success' => $success ? true : 'Failed to mark interaction as read']);
    }

    public function template_category_update($changed_data)
    {
        $this->db->update(db_prefix() . 'whatsapp_templates', ['category' => $changed_data['new_category']], ['template_id' => $changed_data['message_template_id']]);

        $message = "Your WhatsApp template {$changed_data['message_template_name']} category changed from {$changed_data['previous_category']} to {$changed_data['new_category']} for {$changed_data['message_template_language']} language.";

        log_activity($message);

        $this->notifyStaffAboutTemplateCategoryChange($changed_data);
    }

    private function notifyStaffAboutTemplateCategoryChange($changed_data)
    {
        $staff = $this->db->select('staffid, email')
            ->from(db_prefix() . 'staff')
            ->where('active', 1)
            ->get()->result_array();

        if (!empty($staff)) {
            foreach ($staff as $staff_member) {
                send_mail_template('whatsapp_template_category_changed', $staff_member['email'], $changed_data);
            }
        }
    }

    public function send_message()
    {
        $id = $this->input->post('id', true) ?? '';
        $existing_interaction = $this->db->where('id', $id)->get(db_prefix() . 'whatsapp_interactions')->row_array();
        if (!$existing_interaction) {
            echo json_encode(['success' => false, 'error' => 'Interaction not found']);
            return;
        }

        $to = $this->input->post('to', true) ?? '';
        $message = strip_tags($this->input->post('message', true) ?? '');

        $imageAttachment = $_FILES['image'] ?? null;
        $videoAttachment = $_FILES['video'] ?? null;
        $documentAttachment = $_FILES['document'] ?? null;
        $audioAttachment = $_FILES['audio'] ?? null;
        $reaction_emoji = $this->input->post('reaction_emoji', true) ?? '';
        $ref_message_id = $this->input->post('ref_message_id', true) ?? '';
        $carousel_data = $this->input->post('carousel_data', true) ?? null;
        $list_data = $this->input->post('list_data', true) ?? null;

        $message_data = [];
        if (!empty($reaction_emoji) && !empty($ref_message_id)) {
            $message_data = [
                'type' => 'reaction',
                'reaction' => [
                    'emoji' => $reaction_emoji,
                    'message_id' => $ref_message_id,
                ],
            ];
        } elseif (!empty($message)) {
            $message_data = [
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $message,
                ],
            ];
        } elseif (!empty($audioAttachment)) {
            $audio_url = $this->WhatsappLibrary->handle_attachment_upload($audioAttachment);
            $message_data = [
                'type' => 'audio',
                'audio' => [
                    'link' => WHATSAPP_MODULE_UPLOAD_URL . $audio_url,
                ],
            ];
        } elseif (!empty($imageAttachment)) {
            $image_url = $this->WhatsappLibrary->handle_attachment_upload($imageAttachment);
            $message_data = [
                'type' => 'image',
                'image' => [
                    'link' => WHATSAPP_MODULE_UPLOAD_URL . $image_url,
                ],
            ];
        } elseif (!empty($videoAttachment)) {
            $video_url = $this->WhatsappLibrary->handle_attachment_upload($videoAttachment);
            $message_data = [
                'type' => 'video',
                'video' => [
                    'link' => WHATSAPP_MODULE_UPLOAD_URL . $video_url,
                ],
            ];
        } elseif (!empty($documentAttachment)) {
            $document_url = $this->WhatsappLibrary->handle_attachment_upload($documentAttachment);
            $message_data = [
                'type' => 'document',
                'document' => [
                    'link' => WHATSAPP_MODULE_UPLOAD_URL . $document_url,
                ],
            ];
        } elseif (!empty($carousel_data)) {
            $message_data = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'carousel',
                    'header' => $carousel_data['header'],
                    'body' => $carousel_data['body'],
                    'footer' => $carousel_data['footer'],
                    'action' => [
                        'button' => $carousel_data['button'],
                        'sections' => $carousel_data['sections'],
                    ],
                ],
            ];
        } elseif (!empty($list_data)) {
            $message_data = [
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'header' => $list_data['header'],
                    'body' => $list_data['body'],
                    'footer' => $list_data['footer'],
                    'action' => [
                        'button' => $list_data['button'],
                        'sections' => $list_data['sections'],
                    ],
                ],
            ];
        }
        if (!empty($ref_message_id)) {
            $message_data['context'] = ['message_id' => $ref_message_id];
        }
        if (empty($message_data)) {
            echo json_encode(['success' => false, 'error' => 'No valid message data found']);
            return;
        }

        $res = $this->WhatsappLibrary->send_message($existing_interaction['wa_no_id'], $to, $message_data);

        $interaction_id = $this->whatsapp_interaction_model->insert_interaction([
            'receiver_id' => $to,
            'last_message' => isset($message_data['text']['body']) ? $message_data['text']['body'] : $message_data['type'],
            'wa_no' => $existing_interaction['wa_no'],
            'wa_no_id' => $existing_interaction['wa_no_id'],
            'time_sent' => date("Y-m-d H:i:s"),
        ]);

        $this->whatsapp_interaction_model->insert_interaction_message([
            'interaction_id' => $interaction_id,
            'sender_id' => $existing_interaction['wa_no'],
            'message' => $message,
            'message_id' => $res['id'] ?? null,
            'type' => $message_data['type'] ?? '',
            'staff_id' => get_staff_user_id() ?? null,
            'url' => isset($message_data[$message_data['type']]['link']) ? basename($message_data[$message_data['type']]['link']) : null,
            'status' => 'sent',
            'nature' => 'sent',
            'time_sent' => date("Y-m-d H:i:s"),
            'ref_message_id' => $ref_message_id,
        ]);

        echo json_encode(['success' => true]);
    }


}
