<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WhatsApp Official Cloud API Chat & Marketing module for Perfex CRM
Description: Schedule programmatic marketing actions and  interact with your clients in realtime, through admin area of Perfex CRM.
Version: 1.4.0
Requires at least: 2.3.*
Module URI: https://codecanyon.net/item/whatsapp-cloud-api-interaction-module-for-perfex-crm/52004114
*/

define('WHATSAPP_MODULE_NAME', 'whatsapp');
define('WHATSAPP_MODULE', 'whatsapp');

include(__DIR__ . '/vendor/autoload.php');

$CI = &get_instance();

// Register language files
register_language_files(WHATSAPP_MODULE_NAME, [WHATSAPP_MODULE_NAME]);

$viewuri = $_SERVER['REQUEST_URI'];
$CI->load->library(WHATSAPP_MODULE_NAME . '/WhatsappLibrary');
get_instance()->load->helper(WHATSAPP_MODULE_NAME . '/' . WHATSAPP_MODULE_NAME);

// Define constants for upload folders
define('WHATSAPP_MODULE_UPLOAD_FOLDER', 'uploads/' . WHATSAPP_MODULE_NAME);
define('WHATSAPP_MODULE_UPLOAD_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/' . WHATSAPP_MODULE_UPLOAD_FOLDER . '/');
modules\whatsapp\core\Apiinit::the_da_vinci_code(WHATSAPP_MODULE_NAME);
modules\whatsapp\core\Apiinit::ease_of_mind(WHATSAPP_MODULE_NAME);
// Create upload directories if they don't exist
// Get the absolute path for the upload folder
$uploadFolderPath = FCPATH . WHATSAPP_MODULE_UPLOAD_FOLDER;

// Create necessary directories
create_directory_if_not_exists($uploadFolderPath);
create_directory_if_not_exists($uploadFolderPath . '/bot_files/');
create_directory_if_not_exists($uploadFolderPath . '/campaign/');
create_directory_if_not_exists($uploadFolderPath . '/template/');
create_directory_if_not_exists($uploadFolderPath . '/numbers/');

// Function to create a directory if it does not exist
function create_directory_if_not_exists($path)
{
    if (!is_dir($path)) {
        if (!mkdir($path, 0755, true)) {
            // Log the error for debugging
            log_message('error', 'Failed to create directory: ' . $path);
            die('Failed to create directory: ' . $path);
        } else {
            // Create an index.html file to prevent directory listing
            $fp = fopen($path . '/index.html', 'w');
            if ($fp) {
                fclose($fp);
            } else {
                log_message('error', 'Failed to create index.html in directory: ' . $path);
                die('Failed to create index.html in directory: ' . $path);
            }
        }
    }
}


// Initialize the WhatsApp gateway for sending messages
hooks()->add_filter('sms_gateways', 'whatsapp_gateway_sms_gateways');

function whatsapp_gateway_sms_gateways($gateways)
{
    $gateways[] = 'whatsapp/sms_whatsapp_gateway';
    return $gateways;
}

// Function to handle module activation
register_activation_hook(WHATSAPP_MODULE_NAME, 'whatsapp_activation_hook');

function whatsapp_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__.'/install.php';}



// Initialize permissions
hooks()->add_filter('staff_permissions', function ($permissions) {
    $viewGlobalName      = _l('permission_view');

    $allPermissionsArray = [
        'view'   => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    $permissions['whatsapp_numbers'] = [
        'name'         => _l('whatsapp_numbers'),
        'capabilities' => [
            'view'          => $viewGlobalName,
            'load_template' => _l('whatsapp_numbers'),
        ],
    ];
    $permissions['whatsapp_bot'] = [
        'name'         => _l('whatsapp_bot'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['whatsapp_template'] = [
        'name'         => _l('template'),
        'capabilities' => [
            'view'          => $viewGlobalName,
            'load_template' => _l('load_template'),
        ],
    ];

    $permissions['whatsapp_campaign'] = [
        'name'         => _l('campaigns'),
        'capabilities' => array_merge($allPermissionsArray, [
            'show' => _l('show_campaign'),
        ]),
    ];

    $permissions['whatsapp_chat'] = [
        'name'         => _l('chat'),
        'capabilities' => [
            'view' => $viewGlobalName,
        ],
    ];

    $permissions['whatsapp_log_activity'] = [
        'name'         => _l('log_activity'),
        'capabilities' => [
            'view'      => $viewGlobalName,
            'clear_log' => _l('clear_log'),
        ],
    ];

    $permissions['whatsapp_settings'] = [
        'name'         => _l('whatsapp_settings'),
        'capabilities' => [
            'view' => _l('view'),
        ],
    ];
    // Adding quick replies permission
    $permissions['quickreplies'] = [
        'name'         => _l('quick_replies'),
        'capabilities' => $allPermissionsArray,
    ];
    return $permissions;
});



// Menu Items
hooks()->add_action('admin_init', function () {
    $CI = get_instance();

    // Check if user has permission to view any WhatsApp module section
    $permissions = [
        'whatsapp_bots',
        'whatsapp_template',
        'whatsapp_campaign',
        'whatsapp_chat',
        'whatsapp_log_activity',
        'whatsapp_settings',
        'whatsapp_numbers'
    ];

    $hasAccess = false;
    foreach ($permissions as $permission) {
        if (staff_can('view', $permission)) {
            $hasAccess = true;
            break;
        }
    }
        $unreadMessages = whatsapp_unreadmessages();
        $whatsappName = _l('WhatsApp');
        
        if ($unreadMessages > 0) {
            $whatsappName .= ' (' . $unreadMessages . ')';
        }
    // Add main WhatsApp menu item if the user has access to any WhatsApp section
    if ($hasAccess) {
        $CI->app_menu->add_sidebar_menu_item('whatsapp', [
            'slug'     => 'whatsapp',
            'name'     => $whatsappName,
            'icon'     => 'fa-brands fa-whatsapp',
            'href'     => '#',
            'position' => 1,
        ]);

    }

    // Add children menu items based on specific permissions
    $menuItems = [
        'whatsapp_chat' => [
            [
                'slug'     => 'whatsapp_chat_integration',
                'name'     => _l('chat'),
                'icon'     => 'fa-solid fa-comment-dots',
                'href'     => admin_url(WHATSAPP_MODULE . '/interaction'),
                'position' => 1,
            ],
        ],
        'whatsapp_numbers' => [
            [
                'slug'     => 'whatsapp_numbers',
                'name'     => _l('whatsapp_numbers'),
                'icon'     => 'fa-solid fa-address-book',
                'href'     => admin_url(WHATSAPP_MODULE . '/numbers'),
                'position' => 2,
            ],
            [
                'slug'     => 'whatsapp_bots',
                'name'     => _l('bots') . ' <span class="badge badge-warning">Beta</span>',
                'icon'     => 'fa-solid fa-comments',
                'href'     => admin_url(WHATSAPP_MODULE . '/bots'),
                'position' => 3,
            ],
        ],
        'whatsapp_template' => [
            [
                'slug'     => 'whatsapp_templates',
                'name'     => _l('templates'),
                'icon'     => 'fa-solid fa-folder-open',
                'href'     => admin_url(WHATSAPP_MODULE . '/templates'),
                'position' => 5,
            ],
        ],
        'whatsapp_campaign' => [
            [
                'slug'     => 'campaigns',
                'name'     => _l('campaigns'),
                'icon'     => 'fa-solid fa-bullhorn',
                'href'     => admin_url(WHATSAPP_MODULE . '/campaigns'),
                'position' => 6,
            ],
        ],
        'quickreplies' => [
            [
                'slug'     => 'quickreplies',
                'name'     => _l('quick_replies'),
                'icon'     => 'fa-solid fa-reply',
                'href'     => admin_url(WHATSAPP_MODULE . '/QuickReplies'),
                'position' => 7,
            ],
        ],
        'whatsapp_log_activity' => [
            [
                'slug'     => 'whatsapp_activity_log',
                'name'     => _l('activity_log'),
                'icon'     => 'fa-solid fa-clipboard-list',
                'href'     => admin_url(WHATSAPP_MODULE . '/activity_log'),
                'position' => 8,
            ],
        ],
        'whatsapp_docs' => [
            [
                'slug'     => 'whatsapp_documentation',
                'name'     => _l('documentation'),
                'icon'     => 'fa-solid fa-book', // Changed icon to 'fa-book'
                'href'     => admin_url(WHATSAPP_MODULE . '/documentation'),
                'position' => 8,
                'target'   => '_blank', // Opens in a new tab
            ],
        ],


        'whatsapp_settings' => [
            [
                'slug'     => 'whatsapp_settings',
                'name'     => _l('settings'),
                'icon'     => 'fa-solid fa-sliders-h',
                'href'     => admin_url('settings?group=whatsapp_setting'),
                'position' => 9,
            ],
        ],
    ];

    foreach ($menuItems as $permission => $items) {
        if (staff_can('view', $permission)) {
            foreach ($items as $item) {
                $CI->app_menu->add_sidebar_children_item('whatsapp', $item);
            }
        }
    }

    // Add settings tab if the user has permission to view settings
    if (staff_can('view', 'whatsapp_settings')) {
        $CI->app_tabs->add_settings_tab('whatsapp_setting', [
            'name'     => _l('settings_group_whatsapp_interaction'),
            'view'     => 'whatsapp/admin/settings',
            'position' => 8,
            'icon'     => 'fa-solid fa-tools',
        ]);
    }
});



// Head components
function whatsapp_add_head_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (strpos($viewuri, 'admin/whatsapp/interaction') !== false) {
        echo '<link href="' . base_url('modules/whatsapp/assets/css/twailwind.css') . '" rel="stylesheet" type="text/css" />';
        echo '<link href="' . base_url('modules/whatsapp/assets/css/fa.css') . '" rel="stylesheet" type="text/css" />';
    }
}
hooks()->add_action('app_admin_footer', function () {
    $CI = &get_instance();
       $viewuri = $_SERVER['REQUEST_URI'];
 if (get_instance()->app_modules->is_active(WHATSAPP_MODULE)) {
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>
                var merge_fields = ' . json_encode($merge_fields) . '
            </script>';
        echo '<script src="' . module_dir_url(WHATSAPP_MODULE, 'assets/js/underscore-min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url(WHATSAPP_MODULE, 'assets/js/tribute.min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url(WHATSAPP_MODULE, 'assets/js/whatsapp.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url(WHATSAPP_MODULE, 'assets/js/prism.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
    }
});
// Hooks
hooks()->add_action('app_admin_head', 'whatsapp_add_head_components');
/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */



// delete campaign lead when lead deleted
hooks()->add_action('after_lead_deleted', function ($id) {
    get_instance()->db->delete(db_prefix() . 'whatsapp_campaign_data', ['rel_id' => $id, 'rel_type' => 'leads']);
});

// delete campaign contacts when contact deleted
hooks()->add_action('contact_deleted', function ($id, $result) {
    get_instance()->db->delete(db_prefix() . 'whatsapp_campaign_data', ['rel_id' => $id, 'rel_type' => 'contacts']);
}, 0, 2);


// add new created contact in campaign that is select all contacts
hooks()->add_action('contact_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'whatsapp_campaigns', ['select_all' => '1', 'rel_type' => 'contacts'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = get_whatsapp_template($campaign['template_id']);
            get_instance()->db->insert(db_prefix() . 'whatsapp_campaign_data', [
                'campaign_id'       => $campaign['id'],
                'rel_id'            => $id,
                'rel_type'          => 'contacts',
                'header_message'    => $template['header_data_text'],
                'body_message'      => $template['body_data'],
                'footer_message'    => $template['footer_data'],
                'status'            => 1,
            ]);
        }
    }
});

hooks()->add_action('after_cron_run', 'send_campaign');
function send_campaign()
{
        get_instance()->load->model(WHATSAPP_MODULE . '/whatsapp_interaction_model');
        get_instance()->whatsapp_interaction_model->send_campaign();
    
}

// add widgets
hooks()->add_filter('get_dashboard_widgets', function ($widgets) {
    $new_widgets = [];
    $new_widgets[] = [
        'path'      => WHATSAPP_MODULE . '/widgets/whatsapp-widget',
        'container' => 'top-12',
    ];

    return array_merge($new_widgets, $widgets);
});

if (!is_dir(WHATSAPP_MODULE_UPLOAD_FOLDER)) {
    if (!mkdir(WHATSAPP_MODULE_UPLOAD_FOLDER, 0755, true)) {
        exit('Failed to create directory: ' . WHATSAPP_MODULE_UPLOAD_FOLDER);
    }
    $fp = fopen(WHATSAPP_MODULE_UPLOAD_FOLDER . '/index.html', 'w');
    fclose($fp);
}

hooks()->add_filter('get_upload_path_by_type', 'add_whatsapp_files_upload_path', 0, 2);
function add_whatsapp_files_upload_path($path, $type)
{
    switch ($type) {
        case 'bot_files':
            $path = WHATSAPP_MODULE_UPLOAD_FOLDER . '/bot_files/';
            break;
        case 'campaign':
            $path = WHATSAPP_MODULE_UPLOAD_FOLDER . '/campaign/';
            break;
        case 'template':
            $path = WHATSAPP_MODULE_UPLOAD_FOLDER . '/template/';
            break;
         case 'whatsapp_numbers':
            $path = WHATSAPP_MODULE_UPLOAD_FOLDER . '/numbers/';
            break;
        default:
            $path = $path;
            break;
    }
    return $path;
}
hooks()->add_action('app_init', WHATSAPP_MODULE_NAME . '_actLib');
function whatsapp_actLib()
{
    $CI = &get_instance();
    $CI->load->library(WHATSAPP_MODULE_NAME . '/Whatsapp_aeiou');
    $envato_res = $CI->whatsapp_aeiou->validatePurchase(WHATSAPP_MODULE_NAME);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', WHATSAPP_MODULE_NAME . '_sidecheck');
function whatsapp_sidecheck($module_name)
{
    if (WHATSAPP_MODULE_NAME == $module_name['system_name']) {
        modules\whatsapp\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', WHATSAPP_MODULE_NAME . '_deregister');
function whatsapp_deregister($module_name)
{
    if (WHATSAPP_MODULE_NAME == $module_name['system_name']) {
        delete_option(WHATSAPP_MODULE_NAME . '_verification_id');
        delete_option(WHATSAPP_MODULE_NAME . '_last_verification');
        delete_option(WHATSAPP_MODULE_NAME . '_product_token');
        delete_option(WHATSAPP_MODULE_NAME . '_heartbeat');
    }
}
