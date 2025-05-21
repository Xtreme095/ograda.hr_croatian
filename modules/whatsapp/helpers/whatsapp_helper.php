<?php

defined('BASEPATH') || exit('No direct script access allowed');
if (!function_exists('whatsapp_get_bot_type')) {
    function whatsapp_get_bot_type($id = '')
    {
        $bot_types = [
            // Basic Text Bots
            [
                'id'          => 1,
                'label'       => _l('message_bot'),
                'description' => _l('message_bot_description'),
            ],
            // Template Bots
            [
                'id'          => 2,
                'label'       => _l('template_bot'),
                'description' => _l('template_bot_description'),
            ],
            // Menu Bots (Interactive Menu)
            [
                'id'          => 3,
                'label'       => _l('menu_bot'),
                'description' => _l('menu_bot_description'),
            ],
            // Flow Bots (Interactive Menu)
            [
                'id'          => 4,
                'label'       => _l('flow_bot'),
                'description' => _l('flow_bot_description'),
            ],
            // Media Bots (e.g., Image, Video, Document, Audio)
            [
                'id'          => 5,
                'label'       => _l('media_bot'),
                'description' => _l('media_bot_description'),
            ],
            // Location Bots
            [
                'id'          => 6,
                'label'       => _l('location_bot'),
                'description' => _l('location_bot_description'),
            ],
            // Interactive Buttons Bots
            [
                'id'          => 7,
                'label'       => _l('interactive_buttons_bot'),
                'description' => _l('interactive_buttons_bot_description'),
            ],
            // List Bots (Interactive List)
            [
                'id'          => 8,
                'label'       => _l('list_bot'),
                'description' => _l('list_bot_description'),
            ],
            // Quick Reply Bots (Interactive Quick Replies)
            [
                'id'          => 9,
                'label'       => _l('quick_reply_bot'),
                'description' => _l('quick_reply_bot_description'),
            ],
            // Sticker Bots
            [
                'id'          => 10,
                'label'       => _l('sticker_bot'),
                'description' => _l('sticker_bot_description'),
            ],
            // Contact Bots
            [
                'id'          => 11,
                'label'       => _l('contact_bot'),
                'description' => _l('contact_bot_description'),
            ],
        ];

        if (!empty($id)) {
            $key = array_search($id, array_column($bot_types, 'id'));
            return $bot_types[$key] ?? null; // Return null if ID is not found
        }

        return $bot_types;
    }
}
if (!function_exists('whatsapp_get_reply_type')) {
    function whatsapp_get_reply_type($id = '')
    {
        // Define the available reply types with examples
        $reply_types = [
            // Basic Triggers
            [
                'id'    => 1,
                'label' => _l('on_exact_match'),
                'example' => _l('on_exact_match_description'),
            ],
            [
                'id'    => 2,
                'label' => _l('when_message_contains'),
                'example' => _l('when_message_contains_description'),
            ],
            [
                'id'    => 3,
                'label' => _l('when_client_send_the_first_message'),
                'example' => _l('when_client_send_the_first_message_description'),
            ],
            [
                'id'    => 4,
                'label' => _l('on_keyword_match'),
                'example' => _l('on_keyword_match_description'),
            ],
            [
                'id'    => 5,
                'label' => _l('within_office_time_range'),
                'example' => _l('within_office_time_range_description'),
            ],
            [
                'id'    => 6,
                'label' => _l('outof_office_time_range'),
                'example' => _l('outof_office_time_range_description'),
            ],
            [
                'id'    => 7,
                'label' => _l('first_message_in_session'),
                'example' => _l('first_message_in_session_description'),
            ],
        ];

        // If an ID is provided, return the specific reply type with its example
        if (!empty($id)) {
            $key = array_search($id, array_column($reply_types, 'id'));

            // Return the matching reply type if found, otherwise return null
            return $reply_types[$key] ?? null;
        }

        // Return all reply types if no ID is provided
        return $reply_types;
    }
}




/**
 * Render Menu Tree
 *
 * Recursively generates the HTML structure for menu items.
 *
 * @param array $menuTree The array of menu items.
 * @param string $parentIndex The parent index (used for nested menu items).
 * @return string
 */
function render_menu_tree($menuTree = [], $parentIndex = '')
{
    $html = '';

    foreach ($menuTree as $index => $menuItem) {
        $currentIndex = $parentIndex ? $parentIndex . '-' . $index : $index;

        $html .= '<div class="menu-item row mb-2" data-index="' . htmlspecialchars($currentIndex, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<div class="col-md-5">';
        $html .= '<input type="text" name="menu_items[' . htmlspecialchars($currentIndex, ENT_QUOTES, 'UTF-8') . '][text]" class="form-control" placeholder="Enter menu text" value="' . htmlspecialchars($menuItem['text'], ENT_QUOTES, 'UTF-8') . '" />';
        $html .= '</div>';
        $html .= '<div class="col-md-5">';
        $html .= '<input type="text" name="menu_items[' . htmlspecialchars($currentIndex, ENT_QUOTES, 'UTF-8') . '][action]" class="form-control" placeholder="Enter menu action" value="' . htmlspecialchars($menuItem['action'], ENT_QUOTES, 'UTF-8') . '" />';
        $html .= '</div>';
        $html .= '<div class="col-md-2 text-right">';
        $html .= '<button type="button" class="btn btn-danger btn-remove-menu-item"><i class="fa fa-trash"></i></button>';
        $html .= '</div>';
        $html .= '</div>';

        // Recursively render any child menu items
        if (isset($menuItem['children']) && is_array($menuItem['children'])) {
            $html .= '<div class="ml-4">';
            $html .= render_menu_tree($menuItem['children'], $currentIndex);
            $html .= '</div>';
        }
    }

    return $html;
}
/**
 * Render Flow Tree
 *
 * Recursively generates the HTML structure for flow steps.
 *
 * @param array $flowTree The array of flow steps.
 * @param string $parentIndex The parent index (used for nested flow steps).
 * @return string
 */
function render_flow_tree($flowTree = [], $parentIndex = '')
{
    $html = '';

    foreach ($flowTree as $index => $step) {
        $currentIndex = $parentIndex ? $parentIndex . '-' . $index : $index;

        $html .= '<div class="flow-step row mb-2" data-index="' . htmlspecialchars($currentIndex, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<div class="col-md-5">';
        $html .= '<input type="text" name="flow_steps[' . htmlspecialchars($currentIndex, ENT_QUOTES, 'UTF-8') . '][text]" class="form-control" placeholder="Enter step text" value="' . htmlspecialchars($step['text'], ENT_QUOTES, 'UTF-8') . '" />';
        $html .= '</div>';
        $html .= '<div class="col-md-5">';
        $html .= '<input type="text" name="flow_steps[' . htmlspecialchars($currentIndex, ENT_QUOTES, 'UTF-8') . '][action]" class="form-control" placeholder="Enter step action" value="' . htmlspecialchars($step['action'], ENT_QUOTES, 'UTF-8') . '" />';
        $html .= '</div>';
        $html .= '<div class="col-md-2 text-right">';
        $html .= '<button type="button" class="btn btn-danger btn-remove-flow-step"><i class="fa fa-trash"></i></button>';
        $html .= '</div>';
        $html .= '</div>';

        // Recursively render any child steps
        if (isset($step['children']) && is_array($step['children'])) {
            $html .= '<div class="ml-4">';
            $html .= render_flow_tree($step['children'], $currentIndex);
            $html .= '</div>';
        }
    }

    return $html;
}




/**
 * Get WhatsApp template based on ID
 *
 * @param string $id
 * @return array
 */
if (!function_exists('get_whatsapp_template')) {
    function get_whatsapp_template($id = '')
    {
        if (is_numeric($id)) {
            return get_instance()->db->order_by('language', 'asc')->get_where(db_prefix().'whatsapp_templates', ['id' => $id, 'status' => 'APPROVED'])->row_array();
        }

        return get_instance()->db->order_by('language', 'asc')->get_where(db_prefix().'whatsapp_templates', ['status' => 'APPROVED'])->result_array();
    }
}

/**
 * Get campaign data based on campaign ID
 *
 * @param string $campaign_id
 * @return array
 */
if (!function_exists('whatsapp_get_campaign_data')) {
    function whatsapp_get_campaign_data($campaign_id = '')
    {
        return get_instance()->db->get_where(db_prefix().'whatsapp_campaign_data', ['campaign_id' => $campaign_id])->result_array();
    }
}

/**
 * Check if a string is a valid JSON
 *
 * @param string $string
 * @return bool
 */
if (!function_exists('wbIsJson')) {
    function wbIsJson($string)
    {
        return ((is_string($string) &&
            (is_object(json_decode($string)) ||
                is_array(json_decode($string))))) ? true : false;
    }
}

/**
 * Get the relation types
 *
 * @return array
 */
if (!function_exists('whatsapp_get_rel_type')) {
    function whatsapp_get_rel_type()
    {
        return [
            [
                'key'  => 'leads',
                'name' => _l('leads'),
            ],
            [
                'key'  => 'contacts',
                'name' => _l('contacts'),
            ],
        ];
    }
}

/**
 * Parse text with merge fields
 *
 * @param string $rel_type
 * @param string $type
 * @param array $data
 * @param string $return_type
 * @return string|array
 */
if (!function_exists('whatsappParseText')) {
    function whatsappParseText($rel_type, $type, $data, $return_type = 'text')
    {
        $rel_type = ($rel_type === 'contacts') ? 'client' : $rel_type;
        $CI = get_instance();
        $CI->load->library('merge_fields/app_merge_fields');

        // Retrieve and merge fields
        $merge_fields = $CI->app_merge_fields->format_feature(
            $rel_type . '_merge_fields',
            $data['userid'] ?? $data['rel_id'] ?? $data['type_id'],
            $data['rel_id'] ?? $data['type_id']
        );
        $other_merge_fields = $CI->app_merge_fields->format_feature('other_merge_fields');
        $merge_fields = array_merge($other_merge_fields, $merge_fields);

        // Initialize parsed data array
        $parse_data = [];
        $params = $data["{$type}_params"] ?? '[]';

        if (wbIsJson($params)) {
            $parsed_text = json_decode($params, true);
            $parsed_text = array_map(function ($body) use ($merge_fields) {
                $body['value'] = preg_replace('/@{(.*?)}/', '{$1}', $body['value']);
                foreach ($merge_fields as $key => $val) {
                    $body['value'] = str_replace($key, $val ?: ' ', $body['value']);
                }
                return preg_replace('/\s+/', ' ', trim($body['value']));
            }, $parsed_text);
        } else {
            $parsed_text[1] = preg_replace('/\s+/', ' ', trim($params));
        }

        // Prepare the final data
        for ($i = 1; $i <= ($data["{$type}_params_count"] ?? 0); ++$i) {
            $replacement_text = !empty($parsed_text[$i]) ? $parsed_text[$i] : ' ';
            if ($return_type === 'text' && !empty($data["{$type}_message"])) {
                $data["{$type}_message"] = str_replace("{{{$i}}}", $replacement_text, $data["{$type}_message"]);
            }
            if ($return_type === 'text' && !empty($data["{$type}_data"])) {
                $data["{$type}_data"] = str_replace("{{{$i}}}", $replacement_text, $data["{$type}_data"]);
            }
            $parse_data[] = $replacement_text;
        }

        // Return based on return_type
        if ($return_type === 'text') {
            return $data["{$type}_message"] ?? '';
        } elseif ($return_type === 'data') {
            return $data["{$type}_data"] ?? [];
        } else {
            return $parse_data;
        }
    }
}




/**
 * Parse message text with merge fields
 *
 * @param array $data
 * @return array
 */
if (!function_exists('whatsappParseMessageText')) {
    function whatsappParseMessageText($data)
    {
        $rel_type = $data['rel_type'];
        $rel_type = ('contacts' == $rel_type) ? 'client' : $rel_type;
        get_instance()->load->library('merge_fields/app_merge_fields');
        $merge_fields = get_instance()->app_merge_fields->format_feature(
            $rel_type . '_merge_fields',
            $data['userid'] ?? $data['rel_id']??$data['type_id'],
            $data['rel_id']??$data['type_id']
        );
        $other_merge_fields = get_instance()->app_merge_fields->format_feature('other_merge_fields');
        $merge_fields       = array_merge($other_merge_fields, $merge_fields);

        $data['reply_text'] = preg_replace('/@{(.*?)}/', '{$1}', $data['reply_text']);
        foreach ($merge_fields as $key => $val) {
            $data['reply_text'] =
                false !== stripos($data['reply_text'], $key)
                ? str_replace($key, !empty($val) ? $val : ' ', $data['reply_text'])
                : str_replace($key, '', $data['reply_text']);
        }

        return $data;
    }
}

/**
 * Get the campaign status based on status ID
 *
 * @param string $status_id
 * @return array
 */
if (!function_exists('whatsapp_campaign_status')) {
    function whatsapp_campaign_status($status_id = '')
    {
        $statusid              = ['0', '1', '2'];
        $status['label']       = ['Failed', 'Pending', 'Success'];
        $status['label_class'] = ['label-danger', 'label-warning', 'label-success'];
        if (in_array($status_id, $statusid)) {
            $index = array_search($status_id, $statusid);
            if (false !== $index && isset($status['label'][$index])) {
                $status['label'] = $status['label'][$index];
            }
            if (false !== $index && isset($status['label_class'][$index])) {
                $status['label_class'] = $status['label_class'][$index];
            }
        } else {
            $status['label']       = _l('draft');
            $status['label_class'] = 'label-default';
        }

        return $status;
    }
}

/**
 * Get all staff members
 *
 * @return array
 */
if (!function_exists('whatsapp_get_all_staff')) {
    function whatsapp_get_all_staff()
    {
        return get_instance()->db->get(db_prefix().'staff')->result_array();
    }
}

/**
 * Get staff members allowed to view message templates
 *
 * @return array
 */
if (!function_exists('wbGetStaffMembersAllowedToViewMessageTemplates')) {
    function wbGetStaffMembersAllowedToViewMessageTemplates()
    {
        get_instance()->db->join(db_prefix().'staff_permissions', db_prefix().'staff_permissions.staff_id = '.db_prefix().'staff.staffid', 'LEFT');
        get_instance()->db->where([db_prefix().'staff_permissions.capability' => 'view', db_prefix().'staff_permissions.feature' => 'whatsapp_template']);
        get_instance()->db->or_where([db_prefix().'staff.admin' => '1']);

        return get_instance()->db->get(db_prefix().'staff')->result_array();
    }
}

/**
 * Get the interaction ID based on data, relation type, ID, name, and phone number
 *
 * @param array $data
 * @param string $relType
 * @param string $id
 * @param string $name
 * @param string $phonenumber
 * @return int
 */


/**
 * Decode WhatsApp signs to HTML tags
 *
 * @param string $text
 * @return string
 */
if (!function_exists('whatsappDecodeWhatsAppSigns')) {
    function whatsappDecodeWhatsAppSigns($text)
    {
        $patterns = [
            '/\*(.*?)\*/',       // Bold
            '/_(.*?)_/',         // Italic
            '/~(.*?)~/',         // Strikethrough
            '/```(.*?)```/',      // Monospace
        ];
        $replacements = [
            '<strong>$1</strong>',
            '<em>$1</em>',
            '<del>$1</del>',
            '<code>$1</code>',
        ];

        return preg_replace($patterns, $replacements, $text);
    }
}

if (!function_exists('whatsapp_handle_upload')) {
    function whatsapp_handle_upload($bot_id)
    {
        // Check if a file is uploaded with the 'bot_file' key
        if (isset($_FILES['bot_file']['name']) && !empty($_FILES['bot_file']['name'])) {
            // Get the upload path for bot files
            $path = get_upload_path_by_type('bot_files');

            // Ensure the temporary file path exists
            $tmpFilePath = $_FILES['bot_file']['tmp_name'];
            if (!empty($tmpFilePath) && is_uploaded_file($tmpFilePath)) {

                // Ensure the upload path exists or create it
                _maybe_create_upload_path($path);

                // Generate a random filename to prevent overwriting
                $filename = generate_random_filename($_FILES['bot_file']['name']);

                // Check if the file extension is allowed
                if (_upload_extension_allowed($filename)) {

                    // Define the new file path
                    $newFilePath = $path . $filename;

                    // Move the uploaded file to the new location
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                        // Update the database with the new filename
                        get_instance()->db->update(db_prefix() . 'whatsapp_bot', ['filename' => $filename], ['id' => $bot_id]);

                        // Return the filename on success
                        return $filename;
                    } else {
                        log_message('error', 'Failed to move uploaded file to destination: ' . $newFilePath);
                    }
                } else {
                    log_message('error', 'File extension not allowed for file: ' . $filename);
                }
            } else {
                log_message('error', 'Temporary file path is invalid or file not uploaded.');
            }
        } else {
            log_message('error', 'No file was uploaded or the file name is empty.');
        }
        // Return false if upload failed
        return false;
    }

    /**
     * Generate a random filename
     *
     * @param string $originalName The original file name
     * @return string The new random file name
     */
    function generate_random_filename($originalName)
    {
        // Extract the file extension
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Generate a unique filename
        $uniqueId = bin2hex(random_bytes(8)); // Generates a random unique ID
        return $uniqueId . '.' . $extension;
    }
}


if (!function_exists('whatsapp_handle_campaign_upload')) {
    function whatsapp_handle_campaign_upload($id, $type)
    {
        if (isset($_FILES['image']['name'])) {
            $path        = get_upload_path_by_type($type);
            $tmpFilePath = $_FILES['image']['tmp_name'];
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                _maybe_create_upload_path($path);
                $filename = unique_filename($path, $_FILES['image']['name']);
                if (_upload_extension_allowed($filename)) {
                    $newFilePath = $path . $filename;
                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        get_instance()->db->update(db_prefix().'whatsapp_campaigns', ['filename' => $filename], ['id' => $id]);
                        return $filename;
                    }
                }
            }
        }
        return false;
    }
}

if (!function_exists('whatsapp_get_allowed_extension')) {
    function whatsapp_get_allowed_extension()
    {
        return [
            'image' => [
                'extension' => '.jpeg, .png',
                'size'      => 5
            ],
            'video' => [
                'extension' => '.mp4, .3gp',
                'size'      => 16,
            ],
            'audio' => [
                'extension' => '.aac, .amr, .mp3, .m4a, .ogg',
                'size'      => 16,
            ],
            'document' => [
                'extension' => '.pdf, .doc, .docx, .txt, .xls, .xlsx, .ppt, .pptx',
                'size'      => 100,
            ],
        ];
    }
}
if (!function_exists('truncate_message')) {
    function truncate_message($message, $limit = 25) {
    // Remove HTML tags
    $message = strip_tags($message);
    // Check if the message length exceeds the limit
    if (strlen($message) > $limit) {
        // Truncate the message and add ellipsis
        $message = substr($message, 0, $limit) . '...';
    }
    return $message;
}

}
if (!function_exists('whatsapp_get_media_types')) {
    function whatsapp_get_media_types()
    {
        return [
            [
                'id'    => 'image',
                'label' => _l('Image'),
            ],
            [
                'id'    => 'video',
                'label' => _l('Video'),
            ],
            [
                'id'    => 'document',
                'label' => _l('Document'),
            ],
            [
                'id'    => 'audio',
                'label' => _l('Audio'),
            ],
        ];
    }
}
if (!function_exists('whatsapp_default_phone_number')) {
    /**
     * Fetches the details of the default phone number from the database.
     *
     * @return array|null Default phone number details or null if not found.
     */
    function whatsapp_default_phone_number()
    {
        $CI =& get_instance(); // Get CI instance

        // Load database library
        $CI->load->database();

        // Query to get the default phone number details
        $CI->db->select('*');
        $CI->db->from(db_prefix() . 'whatsapp_numbers');
        $CI->db->where('is_default', 1);
        $query = $CI->db->get();

        // Return the result
        return $query->row_array(); // Return as associative array
    }
}
if (!function_exists('whatsapp_bots')) {
    /**
     * Fetches the details of all bots linked to a specific interaction ID.
     *
     * @param int $interaction_id The ID of the interaction to fetch bots for.
     * @return array|null An array of bot details or null if not found.
     */
    function whatsapp_bots($interaction_id)
    {
        $CI =& get_instance(); // Get CI instance

        // Start building the query
        $CI->db->select('wb.id as bot_id, wb.*, wt.*, wi.*');
        $CI->db->from(db_prefix() . 'whatsapp_bot as wb');

        // Join with whatsapp_templates table
        $CI->db->join(db_prefix() . 'whatsapp_templates as wt', 'wb.template_id = wt.id', 'left');

        // Join with whatsapp_interaction table using interaction_id
        $CI->db->join(db_prefix() . 'whatsapp_interactions as wi', 'wi.id = ' . $CI->db->escape($interaction_id), 'left');

        // Ensure bot is active
        $CI->db->where('wb.is_bot_active', 1);

        // Filter by the specific interaction
        $CI->db->where('wi.id', $interaction_id);

        // Execute the query and return the result
        $result = $CI->db->get()->result_array();

        // Return the result
        return $result;
    }
}


if (!function_exists('whatsapp_unreadmessages')) {
    /**
     * Fetches the count of unread WhatsApp messages from the database.
     *
     * @return int The total number of unread messages.
     */
    function whatsapp_unreadmessages()
    {
        $CI =& get_instance(); // Get CI instance

        // Load database library
        $CI->load->database();

        // Query to get the count of unread messages
        $CI->db->select('SUM(unread) as unread_count');
        $CI->db->from(db_prefix() . 'whatsapp_interactions');
        $CI->db->where('unread >', 0);
        $query = $CI->db->get();

        // Fetch the result and return the unread count
        $result = $query->row_array();

        return isset($result['unread_count']) ? (int)$result['unread_count'] : 0;
    }
}

if (!function_exists('whatsapp_handle_header_upload')) {
    /**
     * Handle file upload in header_params
     *
     * This function processes the `header_params` array, checks for files, uploads them,
     * and replaces the file path in the array.
     *
     * @param array $header_params The header_params array with potential files.
     * @param string $bot_id The ID of the bot (used to associate the uploaded files).
     * @return array The updated header_params array with file paths.
     */
    function whatsapp_handle_header_upload($header_params, $bot_id)
    {
        // Path to upload the files
        $upload_path = get_upload_path_by_type('bot_files');

        // Ensure the upload path exists
        _maybe_create_upload_path($upload_path);

        // Loop through each header param
        foreach ($header_params as &$param) {
            // Check if the param is a file type and the file is uploaded
            if (isset($param['type']) && $param['type'] == 'file' && isset($_FILES[$param['name']])) {
                $file = $_FILES[$param['name']];

                // Validate the temporary file path
                if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                    // Generate a random filename to avoid conflicts
                    $filename = generate_random_filename($file['name']);

                    // Check if the file extension is allowed
                    if (_upload_extension_allowed($filename)) {
                        $new_file_path = $upload_path . $filename;

                        // Move the file to the final destination
                        if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
                            // Update the param with the new file path
                            $param['value'] = $new_file_path;

                            // Optionally, update the database with the full header_params array
                            get_instance()->db->update(db_prefix() . 'whatsapp_bot', ['header_params' => json_encode($header_params)], ['id' => $bot_id]);
                        } else {
                            log_message('error', 'Failed to move uploaded file to destination: ' . $new_file_path);
                        }
                    } else {
                        log_message('error', 'File extension not allowed for file: ' . $filename);
                    }
                }
            }
        }

        // Return the updated header_params array
        return $header_params;
    }
}
if (!function_exists('get_template_maper')) {
    function get_template_maper($templateId, $type = 'campaign')  // Default type is 'campaign'
    {
        // Fetch the template details
        $template = get_whatsapp_template($templateId);

        $header_data = $template['header_data_text'] ?? '';
        $body_data   = $template['body_data'] ?? '';
        $footer_data = $template['footer_data'] ?? '';
        $button_data = !empty($template['buttons_data']) ? json_decode($template['buttons_data']) : [];

        $campaign_or_bot = null;
        $header_params = $body_params = $footer_params = null;

        if (!empty($templateId)) {
            // Depending on the type, fetch the relevant data
            if ($type === 'campaign') {
                $campaign_or_bot = get_instance()->campaigns_model->get($templateId);
                $header_params = json_decode($campaign_or_bot['header_params'] ?? '');
                $body_params = json_decode($campaign_or_bot['body_params'] ?? '');
                $footer_params = json_decode($campaign_or_bot['footer_params'] ?? '');
            } elseif ($type === 'bot') {
                $campaign_or_bot = get_instance()->bots_model->get($templateId);  // Assuming you have a model for bots
                $header_params = json_decode($campaign_or_bot['header_params'] ?? '');
                $body_params = json_decode($campaign_or_bot['body_params'] ?? '');
                $footer_params = json_decode($campaign_or_bot['footer_params'] ?? '');
            }
        }

        // Prepare the data array for the view
        $data = [
            'template'       => $template,
            'header_params'  => $header_params,
            'body_params'    => $body_params,
            'footer_params'  => $footer_params,
            $type            => $campaign_or_bot,  // This will be 'campaign' or 'bot'
        ];

        // Always use the 'variables' view
        $view = get_instance()->load->view('whatsapp/variables', $data, true);

        // Return the response as an array
        return [
            'view'         => $view,
            'header_data'  => $header_data,
            'body_data'    => $body_data,
            'footer_data'  => $footer_data,
            'button_data'  => $button_data
        ];
    }
}
