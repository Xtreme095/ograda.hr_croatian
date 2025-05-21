<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: WhatsApp Cloud API Chat Module
Description: The first real-time interaction module for Perfex CRM, to interact with your clients through Admin area.
Version: 1.2.6
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

get_instance()->load->helper(WHATSAPP_MODULE_NAME . '/' . WHATSAPP_MODULE_NAME);

// Define constants for upload folders
define('WHATSAPP_MODULE_UPLOAD_FOLDER', 'uploads/' . WHATSAPP_MODULE_NAME);
define('WHATSAPP_MODULE_UPLOAD_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/' . WHATSAPP_MODULE_UPLOAD_FOLDER . '/');
modules\whatsapp\core\Apiinit::the_da_vinci_code(WHATSAPP_MODULE_NAME);
modules\whatsapp\core\Apiinit::ease_of_mind(WHATSAPP_MODULE_NAME);
// Create upload directories if they don't exist
if (!is_dir(WHATSAPP_MODULE_UPLOAD_FOLDER)) {
    if (!mkdir(WHATSAPP_MODULE_UPLOAD_FOLDER, 0755, true)) {
        die('Failed to create directory: ' . WHATSAPP_MODULE_UPLOAD_FOLDER);
    }
    $fp = fopen(WHATSAPP_MODULE_UPLOAD_FOLDER . '/index.html', 'w');
    fclose($fp);
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
    require_once __DIR__ . '/install.php';
}

// Register module activation hook
register_activation_hook(__FILE__, 'whatsapp_activate');

// Initialize permissions
hooks()->add_filter('staff_permissions', function ($permissions) {
    $viewGlobalName      = _l('permission_view');

    $allPermissionsArray = [
        'view'   => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $permissions['whatsapp_message_bot'] = [
        'name'         => _l('message_bot'),
        'capabilities' => $allPermissionsArray,
    ];

    $permissions['whatsapp_template_bot'] = [
        'name'         => _l('template_bot'),
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

    return $permissions;
});



// Menu Items
hooks()->add_action('admin_init', function () {
    if (staff_can('view', 'whatsapp_message_bot') || staff_can('view', 'whatsapp_template_bot') || staff_can('view', 'whatsapp_template') || staff_can('view', 'whatsapp_campaign') || staff_can('view', 'whatsapp_chat') || staff_can('view', 'whatsapp_log_activity') || staff_can('view', 'whatsapp_settings')) {
        get_instance()->app_menu->add_sidebar_menu_item('whatsapp', [
                'slug'     => 'whatsapp',
                'name'     => _l('whatsapp'),
                'icon'     => 'fa-brands fa-whatsapp',
                'href'     => '#',
                'position' => 1,
        ]);
    }

    if (staff_can('view', 'whatsapp_message_bot')) {
        get_instance()->app_menu->add_sidebar_children_item(WHATSAPP_MODULE, [
                    'slug'     => 'whatsapp_message_bot',
                    'name'     => _l('message_bot'),
                    'icon'     => 'fa-solid fa-envelope',
                    'href'     => admin_url(WHATSAPP_MODULE . '/bots'),
                    'position' => 2,
        ]);
    }

    if (staff_can('view', 'whatsapp_template_bot')) {
        get_instance()->app_menu->add_sidebar_children_item(WHATSAPP_MODULE, [
                    'slug'     => 'whatsapp_template_bot',
                    'name'     => _l('template_bot'),
                    'icon'     => 'fa-solid fa-file-alt',
                    'href'     => admin_url(WHATSAPP_MODULE . '/bots?group=template'),
                    'position' => 3,
        ]);
    }

    if (staff_can('view', 'whatsapp_template')) {
        get_instance()->app_menu->add_sidebar_children_item(WHATSAPP_MODULE, [
                    'slug'     => 'whatsapp_templates',
                    'name'     => _l('templates'),
                    'icon'     => 'fa-solid fa-scroll',
                    'href'     => admin_url(WHATSAPP_MODULE . '/templates'),
                    'position' => 4,
        ]);
    }

    if (staff_can('view', 'whatsapp_campaign')) {
        get_instance()->app_menu->add_sidebar_children_item(WHATSAPP_MODULE, [
                    'slug'     => 'campaigns',
                    'name'     => _l('campaigns'),
                    'icon'     => 'fa-solid fa-bullhorn',
                    'href'     => admin_url(WHATSAPP_MODULE . '/campaigns'),
                    'position' => 5,
        ]);
    }

    if (staff_can('view', 'whatsapp_chat')) {
        get_instance()->app_menu->add_sidebar_children_item(WHATSAPP_MODULE, [
                    'slug'     => 'whatsapp_chat_integration',
                    'name'     => _l('chat'),
                    'icon'     => 'fa-regular fa-comments',
                    'href'     => admin_url(WHATSAPP_MODULE . '/interaction'),
                    'position' => 6,
        ]);
    }

    if (staff_can('view', 'whatsapp_log_activity')) {
        get_instance()->app_menu->add_sidebar_children_item('whatsapp', [
                    'slug'     => 'whatsapp_activity_log',
                    'name'     => _l('activity_log'),
                    'icon'     => 'fa-solid fa-chart-line',
                    'href'     => admin_url(WHATSAPP_MODULE . '/activity_log'),
                    'position' => 7,
        ]);
    }

    if (staff_can('view', 'whatsapp_settings')) {
        get_instance()->app_menu->add_sidebar_children_item('whatsapp', [
                    'slug'     => 'whatsapp_settings',
                    'name'     => _l('settings'),
                    'icon'     => 'fa-solid fa-cogs',
                    'href'     => admin_url('settings?group=whatsapp_interaction'),
                    'position' => 8,
        ]);
    }

    if (staff_can('view', 'whatsapp_settings')) {
        get_instance()->app_tabs->add_settings_tab('whatsapp', [
            'name'     => _l('settings_group_whatsapp_interaction'),
            'view'     => 'whatsapp/admin/settings',
            'position' => 8,
            'icon'     => 'fa fa-user-cog',
        ]);
    }
});



// Head components
function whatsapp_add_head_components()
{
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (strpos($viewuri, 'admin/whatsapp/interaction') !== false) {
        echo '<link href="' . base_url('modules/whatsapp/assets/interaction.css') . '" rel="stylesheet" type="text/css" />';
        echo '<link href="' . base_url('modules/whatsapp/assets/twailwind.css') . '" rel="stylesheet" type="text/css" />';
        echo '<link href="' . base_url('modules/whatsapp/assets/fa.css') . '" rel="stylesheet" type="text/css" />';
    }
}
hooks()->add_action('app_admin_footer', function () {
    $CI = &get_instance();
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

// add new created lead in campaign that is selected all leads
hooks()->add_action('lead_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'whatsapp_campaigns', ['select_all' => '1', 'rel_type' => 'leads'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = whatsapp_get_whatsapp_template($campaign['template_id']);
            get_instance()->db->insert(db_prefix() . 'whatsapp_campaign_data', [
                'campaign_id'       => $campaign['id'],
                'rel_id'            => $id,
                'rel_type'          => 'leads',
                'header_message'    => $template['header_data_text'],
                'body_message'      => $template['body_data'],
                'footer_message'    => $template['footer_data'],
                'status'            => 1,
            ]);
        }
    }
});

// delete campaign lead when lead deleted
hooks()->add_action('after_lead_deleted', function ($id) {
    get_instance()->db->delete(db_prefix() . 'whatsapp_campaign_data', ['rel_id' => $id, 'rel_type' => 'leads']);
});

// delete campaign contacts when contact deleted
hooks()->add_action('contact_deleted', function ($id, $result) {
    get_instance()->db->delete(db_prefix() . 'whatsapp_campaign_data', ['rel_id' => $id, 'rel_type' => 'contacts']);
}, 0, 2);

hooks()->add_filter('before_settings_updated', function ($data) {
    $data['settings']['whatsapp_auto_lead_settings'] = $data['settings']['whatsapp_auto_lead_settings'] ?? '0';
    $data['settings']['enable_webhooks'] = $data['settings']['enable_webhooks'] ?? '0';

    return $data;
});


// add new created contact in campaign that is select all contacts
hooks()->add_action('contact_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'whatsapp_campaigns', ['select_all' => '1', 'rel_type' => 'contacts'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = whatsapp_get_whatsapp_template($campaign['template_id']);
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
    $scheduledData = get_instance()->db
        ->select(db_prefix() . 'whatsapp_campaigns.*, ' . db_prefix() . 'whatsapp_templates.*, ' . db_prefix() . 'whatsapp_campaign_data.*')
        ->join(db_prefix() . 'whatsapp_campaigns', db_prefix() . 'whatsapp_campaigns.id = ' . db_prefix() . 'whatsapp_campaign_data.campaign_id', 'left')
        ->join(db_prefix() . 'whatsapp_templates', db_prefix() . 'whatsapp_campaigns.template_id = ' . db_prefix() . 'whatsapp_templates.id', 'left')
        ->where(db_prefix() . 'whatsapp_campaigns.scheduled_send_time <= NOW()')
        ->where(db_prefix() . 'whatsapp_campaigns.pause_campaign', 0)
        ->where(db_prefix() . 'whatsapp_campaign_data.status', 1)
        ->where(db_prefix() . 'whatsapp_campaigns.is_bot', 0)
        ->get(db_prefix() . 'whatsapp_campaign_data')->result_array();

    if (!empty($scheduledData)) {
        get_instance()->load->model(WHATSAPP_MODULE . '/WHATSAPP_model');
        get_instance()->WHATSAPP_model->send_campaign($scheduledData);
    }
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
function add_WHATSAPP_files_upload_path($path, $type)
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
