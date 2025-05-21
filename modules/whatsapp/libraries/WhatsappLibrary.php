<?php

use GuzzleHttp\Client as GuzzleClient;
use WpOrg\Requests\Requests as Whatsapp;

class WhatsappLibrary
{
    public static $facebookAPI = 'https://graph.facebook.com/v20.0/';
    protected $clientHandler;
    protected $client;
    public static $extensionMap = [
        'image/jpeg'                                                                => 'jpg',
        'image/png'                                                                 => 'png',
        'audio/mp3'                                                                 => 'mp3',
        'video/mp4'                                                                 => 'mp4',
        'audio/aac'                                                                 => 'aac',
        'audio/amr'                                                                 => 'amr',
        'audio/ogg'                                                                 => 'ogg',
        'audio/mp4'                                                                 => 'mp4',
        'text/plain'                                                                => 'txt',
        'application/pdf'                                                           => 'pdf',
        'application/vnd.ms-powerpoint'                                             => 'ppt',
        'application/msword'                                                        => 'doc',
        'application/vnd.ms-excel'                                                  => 'xls',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
        'video/3gp'                                                                 => '3gp',
        'image/webp'                                                                => 'webp',
    ];
    public function __construct()
    {
        $this->client = new GuzzleClient();
        $this->clientHandler = new GuzzleHttp\Client();
    }
    private function getToken()
    {
        return get_option('whatsapp_access_token');
    }
    /**
     * Get the business account ID for the WhatsApp Cloud API
     *
     * @return string Business account ID
     */
    private function getAccountID()
    {
        return get_option('whatsapp_business_account_id');
    }

    /**
     * Get the default phone number for the WhatsApp Cloud API
     *
     * @return string Default phone number
     */
    public function getDefaultPhoneNumber()
    {
        return whatsapp_default_phone_number()['phone_number'];
    }
    public function getDefaultPhoneNumberID()
    {
        return whatsapp_default_phone_number()['phone_number_id'];
    }

    public function getPhoneNumbers()
    {
        $accessToken = get_option('whatsapp_access_token');
        $accountId   = get_option('whatsapp_business_account_id');

        $request = Whatsapp::get(
            self::$facebookAPI . $accountId . '/phone_numbers?access_token=' . $accessToken
        );
        $response = json_decode($request->body);
        if (property_exists($response, 'error')) {
            return ['status' => false, 'message' => $response->error->message];
        }

        return ['status' => true, 'data' => $response->data];
    }
    public function getProfile($id)
    {
        $accessToken = get_option('whatsapp_access_token');
        $phoneId = $id;

        $url = self::$facebookAPI . $phoneId . '/whatsapp_business_profile?fields=profile_picture_url,about,address,vertical,email,websites&access_token=' . $accessToken;

        try {
            $response = $this->clientHandler->request('GET', $url);
            $responseData = json_decode($response->getBody(), true);

            log_message('info', 'Profile Response Data: ' . json_encode($responseData));

            if (isset($responseData['error'])) {
                return ['status' => false, 'message' => $responseData['error']['message']];
            }

            return ['status' => true, 'data' => $responseData];
        } catch (Exception $e) {
            log_message('error', 'Error fetching profile: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function readmessage($fromNumber, $messageId)
    {
        $accessToken = get_option('whatsapp_access_token');

        if (empty($accessToken)) {
            log_message('error', 'Access token is not set');
            return ['error' => 'Access token is not set'];
        }

        $apiUrl = 'https://graph.facebook.com/v20.0/' . $fromNumber . '/messages';

        $data = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            curl_close($ch);
            log_message('error', 'Failed to mark message as read: ' . $error_message);
            return ['error' => 'Failed to mark message as read: ' . $error_message];
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            log_message('error', 'Failed to mark message as read. Response: ' . $response);
            return ['error' => 'Failed to mark message as read', 'details' => json_decode($response, true)];
        }

        log_message('info', 'Message marked as read successfully. Response: ' . $response);
        return ['success' => true, 'response' => json_decode($response, true)];
    }



    public function updateProfile(array $profileData, $phoneNumberId, $accessToken)
    {
        $apiVersion = 'v20.0';
        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/whatsapp_business_profile";

        $data = [
            "messaging_product" => $profileData['messaging_product'],
            "about" => $profileData['about'],
            "address" => $profileData['address'],
            "vertical" => $profileData['vertical'],
            "email" => $profileData['email'],
            "websites" => $profileData['websites'],
        ];

        $timeout = 10; // Set the desired timeout value in seconds

        try {
            $response = $this->clientHandler->request('POST', $url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => $timeout,
            ]);

            $responseData = json_decode($response->getBody(), true);
            log_message('info', 'POST Request URL: ' . $url);
            log_message('info', 'POST Request Data: ' . json_encode($data));
            log_message('info', 'POST Request Headers: ' . json_encode([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ]));
            log_message('info', 'POST Response Data: ' . json_encode($responseData));

            return $responseData;
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = (string) $response->getBody();

            log_message('error', 'Client error: ' . $e->getMessage());
            log_message('error', 'Response body: ' . $responseBodyAsString);

            return json_decode($responseBodyAsString, true);
        } catch (Exception $e) {
            log_message('error', 'Error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
    public function uploadProfilePicture($filePath, $accessToken)
    {
        $apiVersion = 'v20.0';
        $fileContent = file_get_contents($filePath);

        // Step 1: Upload the image
        $uploadUrl = "https://graph.facebook.com/{$apiVersion}/me/photos";
        $response = $this->client->request('POST', $uploadUrl, [
            'multipart' => [
                [
                    'name'     => 'source',
                    'contents' => $fileContent,
                    'filename' => basename($filePath)
                ],
                [
                    'name'     => 'access_token',
                    'contents' => $accessToken
                ]
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);
        $photoId = $responseData['id'] ?? null;

        log_message('info', 'Uploaded Photo ID: ' . $photoId);

        return $photoId;
    }

    public function createTemplate($templateRawData, $header, $body, $footer, $buttons, $accountId, $accessToken)
    {

        $client = new \GuzzleHttp\Client();
        $url = "https://graph.facebook.com/v20.0/{$accountId}/message_templates";
        $templateName = $templateRawData['template_name'];
        if (!strpos($templateName, '_')) {
            $convertedName = str_replace(' ', '_', $templateName);
        } else {
            $convertedName = $templateName;
        }
        $templateData = [
            "name" => $convertedName,
            "category" => $templateRawData['category'],
            "allow_category_change" => true,
            "language" => $templateRawData['language'],
            "components" => [
                $body,
            ],
        ];

        if (!empty($header)) {
            $templateData["components"][] = $header;
        }

        if (!empty($footer)) {
            $templateData["components"][] = $footer;
        }

        if (!empty($buttons)) {
            $templateData["components"][] = $buttons;
        }


        //dd($templateData);

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $templateData,
        ]);

        $responseData = json_decode($response->getBody(), true);
        $status_code = $response->getStatusCode();


        return $status_code;
    }


    public function editTemplate($header, $body, $footer, $buttons, $templateId, $accessToken)
    {
        $client = new \GuzzleHttp\Client();
        $url = "https://graph.facebook.com/v20.0/{$templateId}";
        $templateData = [$header, $body, $footer, $buttons];
        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $templateData,
        ]);

        $responseData = json_decode($response->getBody(), true);
        return $responseData;
    }

    public function loadTemplatesFromWhatsApp()
    {
        // Retrieve necessary configuration options
        $accessToken = get_option('whatsapp_access_token');
        $accountId   = get_option('whatsapp_business_account_id');

        // Construct the API URL
        $url = self::$facebookAPI . $accountId . '/?fields=id,name,message_templates,phone_numbers&access_token=' . $accessToken;

        try {
            // Make the GET request using Whatsapp helper class
            $request  = Whatsapp::get($url);
            $response = json_decode($request->body);

            // Check for errors in the response
            if (property_exists($response, 'error')) {
                return [
                    'status'  => false,
                    'message' => $response->error->message,
                ];
            }

            // Ensure message_templates data is available
            if (isset($response->message_templates->data)) {
                // Filter out templates whose names start with 'sample_'
                $filteredTemplates = array_filter($response->message_templates->data, function ($template) {
                    return strpos($template->name, 'sample_') !== 0;
                });

                return [
                    'status' => true,
                    'data'   => $filteredTemplates,
                ];
            } else {
                return [
                    'status'  => false,
                    'message' => 'No message templates data found in the response.',
                ];
            }
        } catch (\Exception $e) {
            // Handle any exceptions during the request process
            return [
                'status'  => false,
                'message' => 'An error occurred while fetching templates: ' . $e->getMessage(),
            ];
        }
    }


    public function prepare_template_message_data($interaction, $data)
    {
        // Base structure for the message
        $message_data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $interaction,
            'type' => 'template',
            'template' => [
                'name' => $data['template_name'],
                'language' => [
                    'code' => $data['language'] ?? 'en_US',
                    'policy' => 'deterministic',
                ],
                'components' => [],
            ],
        ];

        // Add header component if header data format is valid
        if (isset($data['header_data_format']) && isset($data['filename'])) {
            $header_type = strtolower($data['header_data_format']); // Determine the header type
            $valid_header_types = ['image', 'document', 'video'];

            if (in_array($header_type, $valid_header_types)) {

                $message_data['template']['components'][] = [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => $header_type, // Use the appropriate header type
                            $header_type => [
                                'link' => WHATSAPP_MODULE_UPLOAD_URL . '/compain/' . $data['filename'] 
                            ]
                        ]
                    ]
                ];
            }
        }

        // Add body component with parameters if body data is provided
        if (isset($data['body_data'])) {
            $body_params = json_decode($data['body_params'], true); // Decode JSON body_params
            $body_parameters = [];

            if (is_array($body_params)) {
                foreach ($body_params as $key => $param) {
                    $body_parameters[] = [
                        'type' => 'text',
                        'text' => $param['value']
                    ];
                }
            }

            $message_data['template']['components'][] = [
                'type' => 'body',
                'parameters' => $body_parameters // Include parameters here
            ];
        }

        // Add button components if buttons data is provided
        if (isset($data['buttons_data']) && is_array($data['buttons_data'])) {
            foreach ($data['buttons_data'] as $button) {
                $formatted_button = [
                    'type' => 'button',
                    'sub_type' => $button['sub_type'] ?? '', // Ensure you use the correct button type
                    'parameters' => $button['parameters'] ?? []
                ];

                $message_data['template']['components'][] = $formatted_button;
            }
        }
        return $message_data;
    }


    public function retrieveUrl($media_id, $accessToken)
    {
        $uploadFolder = WHATSAPP_MODULE_UPLOAD_FOLDER;

        $client   = new \GuzzleHttp\Client();
        $url      = self::$facebookAPI . $media_id;
        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        if (200 === $response->getStatusCode()) {
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['url'])) {
                $media     = $responseData['url'];
                $mediaData = $client->get($media, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                ]);
                if (200 === $mediaData->getStatusCode()) {
                    $imageContent = $mediaData->getBody();
                    $contentType  = $mediaData->getHeader('Content-Type')[0];

                    $extensionMap = self::$extensionMap;
                    $extension   = $extensionMap[$contentType] ?? 'unknown';
                    $filename    = 'media_' . uniqid() . '.' . $extension;
                    $storagePath = $uploadFolder . '/' . $filename;

                    $CI = &get_instance();
                    $CI->load->helper('file');
                    write_file($storagePath, $imageContent);

                    return $filename;
                }
            }
        }

        return null;
    }

    /**
     * Handle attachment upload and save the file
     *
     * @param array $attachment Attachment file information
     * @return string|bool Filename of the saved attachment or false on failure
     */
    public function handle_attachment_upload($attachment)
    {
        $uploadFolder = WHATSAPP_MODULE_UPLOAD_FOLDER;

        $contentType  = $attachment['type'];
        $extensionMap = self::$extensionMap;
        $extension = $extensionMap[$contentType] ?? 'unknown';

        $filename = uniqid('attachment_') . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;

        $destination = $uploadFolder . '/' . $filename;

        if (move_uploaded_file($attachment['tmp_name'], $destination)) {
            return $filename;
        }
        return false;
    }

    function send_message($from_phone_number_id, $to, $message_data, $log_data = null)
    {
        $access_token = get_option('whatsapp_access_token');

        // Validate recipient and message data
        if (empty($to) || empty($message_data) || empty($from_phone_number_id) || empty($access_token)) {
            log_message('error', 'Invalid recipient, message data, phone number ID, or access token');
            return ['error' => 'Invalid recipient, message data, phone number ID, or access token', 'log_data' => $log_data];
        }

        // Define API endpoint
        $api_url = 'https://graph.facebook.com/v20.0/' . $from_phone_number_id . '/messages';


        // Check if the type key exists in message data
        if (!isset($message_data['type'])) {
            log_message('error', 'Message type is not set');
            return ['error' => 'Message type is not set', 'log_data' => $log_data];
        }

        // Prepare request data
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => $message_data['type']
        ];

        // Add specific message data based on message type
        switch ($message_data['type']) {
            case 'text':
                $data['text'] = $message_data['text'];
                break;
            case 'reaction':
                $data['reaction'] = $message_data['reaction'];
                break;
            case 'audio':
                $data['audio'] = $message_data['audio'];
                break;
            case 'image':
                $data['image'] = $message_data['image'];
                break;
            case 'video':
                $data['video'] = $message_data['video'];
                break;
            case 'document':
                $data['document'] = $message_data['document'];
                break;
            case 'location':
                $data['location'] = $message_data['location'];
                break;
            case 'contacts':
                $data['contacts'] = $message_data['contacts'];
                break;
            case 'interactive':
                $data['interactive'] = $message_data['interactive'];
                break;
            case 'template':
                $data['template'] = [
                    'name' => $message_data['template']['name'],
                    'language' => [
                        'code' => $message_data['template']['language']['code'],
                        'policy' => $message_data['template']['language']['policy'] ?? 'deterministic',
                    ],
                    'components' => $message_data['template']['components'] ?? []
                ];
                break;
            default:
                log_message('error', 'Invalid message type');
                return ['error' => 'Invalid message type', 'log_data' => $log_data];
        }

        // Add context if it exists in message data
        if (isset($message_data['context'])) {
            $data['context'] = $message_data['context'];
        }

        // Log the data being sent
        log_message('error', 'Request data: ' . json_encode($data));
        $log_data['category_params'] = json_encode($data);
        $log_data['phone_number_id'] = $from_phone_number_id;
        $log_data['business_account_id'] = get_option('whatsapp_business_account_id');
        $log_data['access_token'] = $access_token;

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ]);

        // Execute cURL request
        $response = curl_exec($ch);

        // Log raw response
        log_message('error', 'Raw response: ' . $response);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $error_message = curl_error($ch);
            log_message('error', 'Failed to send message: ' . $error_message);
            curl_close($ch);
            $log_data['response_data'] = json_encode(['error' => $error_message]);
            return ['error' => 'Failed to send message: ' . $error_message, 'log_data' => $log_data];
        }

        // Get response code
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $log_data['response_code'] = $response_code;

        // Close cURL session
        curl_close($ch);

        // Process the response
        $response_data = json_decode($response, true);


        // Add response and raw data to log
        $log_data['response_data'] = json_encode($response_data);
        $log_data['raw_data'] = json_encode($data);

        // Check if the response data contains the message ID
        if (isset($response_data['messages'][0]['id'])) {
            // Message sent successfully
            $messageId = $response_data['messages'][0]['id'];
            return ['success' => true, 'id' => $messageId, 'log_data' => $log_data, 'response_data' => $response_data];
        } else {
            // Failed to send message
            log_message('error', 'Failed to send message. Response: ' . json_encode($response_data));
            return ['success' => false, 'error' => 'Failed to send message', 'details' => $response_data, 'log_data' => $log_data, 'response_data' => $response_data];
        }
    }
}
