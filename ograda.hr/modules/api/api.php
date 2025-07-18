<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: API
Module URI: https://codecanyon.net/item/rest-api-for-perfex-crm/25278359
Description: Rest API module for Perfex CRM
Version: 2.0.7
Author: Themesic Interactive
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

require_once __DIR__.'/vendor/autoload.php';

define('API_MODULE_NAME', 'api');
define('API_MODULE_UPDATED', '0');
hooks()->add_action('admin_init', 'api_init_menu_items');


// modules\api\core\Apiinit::the_da_vinci_code(API_MODULE_NAME);

/**
* Load the module helper
*/
$CI = & get_instance();
$CI->load->helper(API_MODULE_NAME . '/api');

/**
* Register activation module hook
*/
register_activation_hook(API_MODULE_NAME, 'api_activation_hook');

function api_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(API_MODULE_NAME, [API_MODULE_NAME]);

	
/**
 * Init api module menu items in setup in admin_init hook
 * @return null
 */
function api_init_menu_items()
{
    /**
    * If the logged in user is administrator, add custom menu in Setup
    */
    if (is_admin()) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_menu_item('api-options', [
            'collapse' => true,
            'name'     => _l('api'),
            'position' => 40,
            'icon'     => 'fa fa-cogs',
        ]);
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-register-options',
            'name'     => _l('api_management'),
            'href'     => admin_url('api/api_management'),
            'position' => 5,
        ]);
        
        $CI->app_menu->add_sidebar_children_item('api-options', [
            'slug'     => 'api-guide-options',
            'name'     => _l('api_guide'),
            'href'     => 'https://perfexcrm.themesic.com/apiguide/',
            'position' => 10,
        ]);
    }
}

//hooks()->add_action('app_init', API_MODULE_NAME.'_actLib');
function api_actLib()
{
    $CI = &get_instance();
    $CI->load->library(API_MODULE_NAME.'/api_aeiou');
    $envato_res = $CI->api_aeiou->validatePurchase(API_MODULE_NAME);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

//hooks()->add_action('pre_activate_module', API_MODULE_NAME.'_sidecheck');
function api_sidecheck($module_name)
{
    if (API_MODULE_NAME == $module_name['system_name']) {
        modules\api\core\Apiinit::activate($module_name);
    }
}

//hooks()->add_action('pre_deactivate_module', API_MODULE_NAME.'_deregister');
function api_deregister($module_name)
{
    if (API_MODULE_NAME == $module_name['system_name']) {
        delete_option(API_MODULE_NAME.'_verification_id');
        delete_option(API_MODULE_NAME.'_last_verification');
        delete_option(API_MODULE_NAME.'_product_token');
        delete_option(API_MODULE_NAME.'_heartbeat');
    }
}