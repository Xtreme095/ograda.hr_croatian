<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Get CodeIgniter instance
$CI = &get_instance();

// Define table names with prefix
$interaction_table = db_prefix() . 'whatsapp_interactions';
$interaction_messages_table = db_prefix() . 'whatsapp_interaction_messages';
$whatsapp_bot_table = db_prefix() . 'whatsapp_bot';
$whatsapp_templates_table = db_prefix() . 'whatsapp_templates';
$whatsapp_campaigns_table = db_prefix() . 'whatsapp_campaigns';
$whatsapp_campaign_data_table = db_prefix() . 'whatsapp_campaign_data';
$whatsapp_activity_log_table = db_prefix() . 'whatsapp_activity_log';
$whatsapp_numbers_table = db_prefix() . 'whatsapp_numbers';
$quick_replies_table = db_prefix() . 'quick_replies';
$whatsapp_automations = db_prefix() . 'whatsapp_automations';
$interaction_menu_state_table = db_prefix() . 'interaction_menu_state';

$options = [
    'whatsapp_webhook_token' => '123456',
    'whatsapp_blueticks_status' => 'enable',
    'whatsapp_openai_status' => 'enable',
    'whatsapp_auto_lead_settings' => '1',
];

foreach ($options as $key => $value) {
    if (!get_option($key)) {
        add_option($key, $value);
    }
}

// Create or alter tables as needed

// Create or update whatsapp_interactions table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$interaction_table` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `wa_no` VARCHAR(20) NULL,
        `wa_no_id` VARCHAR(20) NULL,
        `receiver_id` VARCHAR(20) NOT NULL,
        `last_message` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `last_msg_time` DATETIME NULL,
        `time_sent` DATETIME NOT NULL,
        `type` VARCHAR(500) NULL,
        `type_id` VARCHAR(500) NULL,
        `unread` INT DEFAULT 0 NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Create or update whatsapp_interaction_messages table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$interaction_messages_table` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `interaction_id` INT(11) UNSIGNED NOT NULL,
        `sender_id` VARCHAR(20) NOT NULL,
        `url` VARCHAR(255) NULL,
        `message` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `status` VARCHAR(20) NULL,
        `time_sent` DATETIME NOT NULL,
        `message_id` VARCHAR(500) NULL,
        `staff_id` VARCHAR(500) NULL,
        `type` VARCHAR(20) NULL,
        `ref_message_id` VARCHAR(500) NULL,
        `nature` VARCHAR(50) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`interaction_id`) REFERENCES `$interaction_table`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");



// Create or update whatsapp_templates table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$whatsapp_templates_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `template_id` BIGINT UNSIGNED NOT NULL,
        `template_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `language` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `status` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `category` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `header_data_format` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `header_data_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `header_params_count` INT NOT NULL,
        `body_data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `body_params_count` INT NOT NULL,
        `footer_data` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `footer_params_count` INT NOT NULL,
        `buttons_data` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `template_id` (`template_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
");
// Create or update whatsapp_bot table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$whatsapp_bot_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `rel_type` VARCHAR(50) NOT NULL,
        `reply_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `reply_type` INT NOT NULL,
        `trigger` VARCHAR(255) NOT NULL,
        `bot_header` VARCHAR(65) DEFAULT NULL,
        `bot_footer` VARCHAR(65) DEFAULT NULL,
        `button1` VARCHAR(25) DEFAULT NULL,
        `button1_id` VARCHAR(258) DEFAULT NULL,
        `button2` VARCHAR(25) DEFAULT NULL,
        `button2_id` VARCHAR(258) DEFAULT NULL,
        `button3` VARCHAR(25) DEFAULT NULL,
        `button3_id` VARCHAR(258) DEFAULT NULL,
        `button_name` VARCHAR(25) DEFAULT NULL,
        `button_url` VARCHAR(255) DEFAULT NULL,
        `filename` TEXT DEFAULT NULL,
        `addedfrom` INT NOT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `is_bot_active` TINYINT(1) NOT NULL DEFAULT 1,
        `sending_count` INT DEFAULT 0,
        `flow_data` TEXT NULL,
        `bot_type` VARCHAR(50) NULL,
        `bot_list` TEXT NULL,
        `template_id` VARCHAR(50) NULL,
        `body_params` TEXT NULL,
        `header_params` TEXT NULL,
        `footer_params` TEXT NULL,
        `latitude` VARCHAR(50) NULL,
        `longitude` VARCHAR(50) NULL,
        `location_name` VARCHAR(255) NULL,
        `location_address` VARCHAR(255) NULL,
        `poll_question` VARCHAR(255) NULL,
        `poll_option1` VARCHAR(255) NULL,
        `poll_option2` VARCHAR(255) NULL,
        `poll_option3` VARCHAR(255) NULL,
        `media_type` VARCHAR(255) NULL,
        `contact_name` VARCHAR(255) NULL,
        `contact_first_name` VARCHAR(255) NULL,
        `contact_last_name` VARCHAR(255) NULL,
        `contact_number` VARCHAR(255) NULL,
        `contact_email` VARCHAR(255) NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
// Create or update whatsapp_campaigns table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$whatsapp_campaigns_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `rel_type` VARCHAR(50) NOT NULL,
        `template_id` INT DEFAULT NULL,
        `scheduled_send_time` TIMESTAMP NULL DEFAULT NULL,
        `send_now` TINYINT NOT NULL DEFAULT 0,
        `header_params` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `body_params` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `footer_params` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `filename` TEXT DEFAULT NULL,
        `pause_campaign` TINYINT(1) NOT NULL DEFAULT 0,
        `select_all` TINYINT(1) NOT NULL DEFAULT 0,
        `trigger` VARCHAR(191) DEFAULT NULL,
        `bot_type` INT NOT NULL DEFAULT 0,
        `is_bot_active` INT NOT NULL DEFAULT 1,
        `is_bot` INT NOT NULL DEFAULT 0,
        `is_sent` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `sending_count` INT DEFAULT 0,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
");

// Create or update whatsapp_campaign_data table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$whatsapp_campaign_data_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `campaign_id` INT NOT NULL,
        `rel_id` INT DEFAULT NULL,
        `rel_type` VARCHAR(50) NOT NULL,
        `header_message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `body_message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `footer_message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `status` INT DEFAULT NULL,
        `response_message` TEXT DEFAULT NULL,
        `whatsapp_id` TEXT DEFAULT NULL,
        `message_status` VARCHAR(25) DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
");

// Create or update whatsapp_activity_log table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$whatsapp_activity_log_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `phone_number_id` VARCHAR(255) DEFAULT NULL,
        `access_token` TEXT DEFAULT NULL,
        `business_account_id` VARCHAR(255) DEFAULT NULL,
        `response_code` VARCHAR(4) NOT NULL,
        `response_data` TEXT NOT NULL,
        `category` VARCHAR(50) NOT NULL,
        `category_id` INT(11) NOT NULL,
        `rel_type` VARCHAR(50) NOT NULL,
        `rel_id` INT(11) NOT NULL,
        `category_params` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `raw_data` TEXT NOT NULL,
        `recorded_at` DATETIME NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
");

// Create or update whatsapp_numbers table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$whatsapp_numbers_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `phone_number_id` VARCHAR(50) NOT NULL,
        `phone_number` VARCHAR(50) NOT NULL,
        `profile_picture_url` TEXT DEFAULT NULL,
        `about` TEXT DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `vertical` VARCHAR(255) DEFAULT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `websites` TEXT DEFAULT NULL,
        `is_default` TINYINT(1) NOT NULL DEFAULT 0,
        `profile_picture` TEXT DEFAULT NULL,
        `verified_name` TEXT DEFAULT NULL,
        `code_verification_status` VARCHAR(50) DEFAULT NULL,
        `display_phone_number` VARCHAR(50) DEFAULT NULL,
        `quality_rating` VARCHAR(50) DEFAULT NULL,
        `platform_type` VARCHAR(50) DEFAULT NULL,
        `throughput_level` VARCHAR(50) DEFAULT NULL,
        `external_id` VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `phone_number_id` (`phone_number_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
");

// Create or update quick_replies table
$CI->db->query("
    CREATE TABLE IF NOT EXISTS `$quick_replies_table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `message` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
");


// Clean up old records with null wa_no_id
$CI->db->query("DELETE FROM `$interaction_table` WHERE `wa_no_id` IS NULL");

// Columns to be added with their respective SQL definitions
$columnsToAdd = [
    'profile_picture' => 'TEXT DEFAULT NULL',
    'verified_name' => 'VARCHAR(255) DEFAULT NULL',
    'code_verification_status' => 'VARCHAR(50) DEFAULT NULL',
    'display_phone_number' => 'VARCHAR(50) DEFAULT NULL',
    'quality_rating' => 'VARCHAR(50) DEFAULT NULL',
    'platform_type' => 'VARCHAR(50) DEFAULT NULL',
    'throughput_level' => 'VARCHAR(50) DEFAULT NULL',
    'external_id' => 'VARCHAR(50) DEFAULT NULL' // 'ID' column renamed to 'external_id' to avoid conflict with primary key
];

// Loop through each column and check if it exists, if not, add it
foreach ($columnsToAdd as $columnName => $columnDefinition) {
    if (!$CI->db->field_exists($columnName, $whatsapp_numbers_table)) {
        $CI->db->query("ALTER TABLE `$whatsapp_numbers_table` ADD COLUMN `$columnName` $columnDefinition;");
    }
}

// Define the columns to be added, if they don't exist
$columns_to_add = [
    'bot_header' => 'VARCHAR(65) DEFAULT NULL',
    'bot_footer' => 'VARCHAR(65) DEFAULT NULL',
    'button1' => 'VARCHAR(25) DEFAULT NULL',
    'button1_id' => 'VARCHAR(258) DEFAULT NULL',
    'button2' => 'VARCHAR(25) DEFAULT NULL',
    'button2_id' => 'VARCHAR(258) DEFAULT NULL',
    'button3' => 'VARCHAR(25) DEFAULT NULL',
    'button3_id' => 'VARCHAR(258) DEFAULT NULL',
    'button_name' => 'VARCHAR(25) DEFAULT NULL',
    'button_url' => 'VARCHAR(255) DEFAULT NULL',
    'filename' => 'TEXT DEFAULT NULL',
    'latitude' => 'VARCHAR(50) DEFAULT NULL',
    'longitude' => 'VARCHAR(50) DEFAULT NULL',
    'location_name' => 'VARCHAR(255) DEFAULT NULL',
    'location_address' => 'VARCHAR(255) DEFAULT NULL',
    'bot_list' => 'TEXT DEFAULT NULL',
    'poll_question' => 'VARCHAR(255) DEFAULT NULL',
    'poll_option1' => 'VARCHAR(255) DEFAULT NULL',
    'poll_option2' => 'VARCHAR(255) DEFAULT NULL',
    'poll_option3' => 'VARCHAR(255) DEFAULT NULL',
    'is_bot_active' => 'TINYINT(1) DEFAULT 1 NOT NULL',
    'sending_count' => 'INT DEFAULT 0',
    'media_type' => 'VARCHAR(255) DEFAULT NULL',
    'contact_name' => 'VARCHAR(255) DEFAULT NULL',
    'contact_first_name' => 'VARCHAR(255) DEFAULT NULL',
    'contact_last_name' => 'VARCHAR(255) DEFAULT NULL',
    'contact_number' => 'VARCHAR(255) DEFAULT NULL',
    'contact_email' => 'VARCHAR(255) DEFAULT NULL'
];

// Loop through each column and add it if it doesn't exist
foreach ($columns_to_add as $column_name => $column_definition) {
    if (!$CI->db->field_exists($column_name, $whatsapp_bot_table)) {
        $CI->db->query("ALTER TABLE `$whatsapp_bot_table` ADD COLUMN `$column_name` $column_definition;");
    }
}

// Check if the 'verified_name' column does not exist in the 'whatsapp_numbers' table
if (!$CI->db->field_exists('verified_name', $whatsapp_numbers_table)) {
    $CI->db->query("ALTER TABLE `$whatsapp_numbers_table` ADD COLUMN `verified_name` TEXT DEFAULT NULL;");
}

// Add 'unread' column to `whatsapp_interactions` if it doesn't exist
if (!$CI->db->field_exists('unread', $interaction_table)) {
    $CI->db->query("ALTER TABLE `$interaction_table` ADD COLUMN `unread` VARCHAR(11) DEFAULT '0' NULL;");
}
$CI->db->query("ALTER TABLE `$whatsapp_templates_table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$fields = [
    'template_name' => 'VARCHAR(255) NOT NULL',
    'language' => 'VARCHAR(50) NOT NULL',
    'status' => 'VARCHAR(50) NOT NULL',
    'category' => 'VARCHAR(100) NOT NULL',
    'header_data_format' => 'VARCHAR(10) NOT NULL',
    'header_data_text' => 'TEXT DEFAULT NULL',
    'body_data' => 'TEXT NOT NULL',
    'footer_data' => 'TEXT DEFAULT NULL'
];

foreach ($fields as $field_name => $field_definition) {
    $CI->db->query("ALTER TABLE `$whatsapp_templates_table` MODIFY COLUMN `$field_name` $field_definition;");
}
// Check if the 'nature' column does not exist in the 'interaction_messages_table' table
if (!$CI->db->field_exists('nature', $interaction_messages_table)) {
    $CI->db->query("ALTER TABLE `$interaction_messages_table` ADD COLUMN `nature` VARCHAR(50) NULL;");
}
// Check if the 'menu_items' column does not exist in the 'whatsapp_bot_table' table
if (!$CI->db->field_exists('menu_items', $whatsapp_bot_table)) {
$CI->db->query("ALTER TABLE `$whatsapp_bot_table` ADD COLUMN `menu_items` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;");
}
// Create or update interaction_menu_state table
$CI->db->query("
CREATE TABLE IF NOT EXISTS `$interaction_menu_state_table` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `menu_path` TEXT NULL,
    `interaction_id` INT(11) UNSIGNED NULL,
    `bot_id` INT(11) UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
// Check if the 'wa_no_id' column does not exist in the 'whatsapp_numbers' table
if (!$CI->db->field_exists('wa_no_id', $interaction_table)) {
    $CI->db->query("ALTER TABLE `$interaction_table` ADD COLUMN wa_no_id` VARCHAR(20) NULL;");
    }

?>
