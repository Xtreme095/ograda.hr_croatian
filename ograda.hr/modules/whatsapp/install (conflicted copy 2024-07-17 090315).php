<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Get CodeIgniter instance
$CI = &get_instance();

// Define table names with prefix
$interaction_table = db_prefix() . 'whatsapp_interactions';
$interaction_messages_table = db_prefix() . 'whatsapp_interaction_messages';
if(!get_option('whatsapp_verify_token'))add_option('whatsapp_verify_token', '123456');

// Create table for WhatsApp official interactions if it doesn't exist
$create_interaction_table_query = "
    CREATE TABLE IF NOT EXISTS `$interaction_table` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `wa_no` VARCHAR(20) NULL ,
        `wa_no_id` VARCHAR(20) NULL,
        `receiver_id` VARCHAR(20) NOT NULL,
        `last_message` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        `last_msg_time` DATETIME NULL,
        `time_sent` DATETIME NOT NULL,
        `type` VARCHAR(500) NULL,
        `type_id` VARCHAR(500) NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$CI->db->query($create_interaction_table_query);

// Create table for interaction messages if it doesn't exist
$create_interaction_messages_table_query = "
    CREATE TABLE IF NOT EXISTS `$interaction_messages_table` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `interaction_id` INT(11) UNSIGNED NOT NULL,
        `sender_id` VARCHAR(20) NOT NULL,
        `url` VARCHAR(255) NULL,
        `message` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        `status` VARCHAR(20) NULL,
        `time_sent` DATETIME NOT NULL,
        `message_id` VARCHAR(500) NULL,
        `staff_id` VARCHAR(500) NULL,
        `type` VARCHAR(20) NULL,
        `ref_message_id` VARCHAR(500) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`interaction_id`) REFERENCES `$interaction_table`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$CI->db->query($create_interaction_messages_table_query);


if (!$CI->db->table_exists(db_prefix().'whatsapp_bot')) {
    $CI->db->query(
        'CREATE TABLE `'.db_prefix().'whatsapp_bot` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `reply_text` text NOT NULL,
            `reply_type` int NOT NULL,
            `trigger` varchar(255) NOT NULL,
            `bot_header` varchar(65) DEFAULT NULL,
            `bot_footer` varchar(65) DEFAULT NULL,
            `button1` varchar(25) DEFAULT NULL,
            `button1_id` varchar(258) DEFAULT NULL,
            `button2` varchar(25) DEFAULT NULL,
            `button2_id` varchar(258) DEFAULT NULL,
            `button3` varchar(25) DEFAULT NULL,
            `button3_id` varchar(258) DEFAULT NULL,
            `button_name` varchar(25) DEFAULT NULL,
            `button_url` varchar(255) DEFAULT NULL,
            `filename` text DEFAULT NULL,
            `addedfrom` int NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_bot_active` tinyint(1) NOT NULL DEFAULT "1",
            `sending_count` int DEFAULT "0",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';'
    );
}

if (!table_exists('whatsapp_templates')) {
    $CI->db->query(
        'CREATE TABLE `'.db_prefix().'whatsapp_templates` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `template_id` BIGINT UNSIGNED NOT NULL COMMENT "id from api" ,
            `template_name` VARCHAR(255) NOT NULL ,
            `language` VARCHAR(50) NOT NULL ,
            `status` VARCHAR(50) NOT NULL ,
            `category` VARCHAR(100) NOT NULL ,
            `header_data_format` VARCHAR(10) NOT NULL ,
            `header_data_text` TEXT ,
            `header_params_count` INT NOT NULL ,
            `body_data` TEXT NOT NULL ,
            `body_params_count` INT NOT NULL ,
            `footer_data` TEXT,
            `footer_params_count` INT NOT NULL ,
            `buttons_data` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`id`),
            UNIQUE KEY `template_id` (`template_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';'
    );
}

if (!table_exists('whatsapp_campaigns')) {
    $CI->db->query(
        'CREATE TABLE `'.db_prefix().'whatsapp_campaigns` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `template_id` int DEFAULT NULL,
            `scheduled_send_time` timestamp NULL DEFAULT NULL,
            `send_now` tinyint NOT NULL DEFAULT "0",
            `header_params` text,
            `body_params` text,
            `footer_params` text,
            `filename` text DEFAULT NULL,
            `pause_campaign` tinyint(1) NOT NULL DEFAULT "0",
            `select_all` tinyint(1) NOT NULL DEFAULT "0",
            `trigger` varchar(191) DEFAULT NULL,
            `bot_type` int NOT NULL DEFAULT 0,
            `is_bot_active` int NOT NULL DEFAULT 1,
            `is_bot` int NOT NULL DEFAULT 0,
            `is_sent` tinyint(1) NOT NULL DEFAULT "0",
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
             `sending_count` int DEFAULT "0",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';'
    );
}

if (!table_exists('whatsapp_campaign_data')) {
    $CI->db->query(
        'CREATE TABLE `'.db_prefix().'whatsapp_campaign_data` (
            `id` int NOT NULL AUTO_INCREMENT,
            `campaign_id` int NOT NULL,
            `rel_id` int DEFAULT NULL,
            `rel_type` varchar(50) NOT NULL,
            `header_message` text DEFAULT NULL,
            `body_message` text DEFAULT NULL,
            `footer_message` text DEFAULT NULL,
            `status` int DEFAULT NULL,
            `response_message` TEXT NULL DEFAULT NULL,
            `whatsapp_id` TEXT NULL DEFAULT NULL,
            `message_status` varchar(25) NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';'
    );
}

if (!$CI->db->table_exists(db_prefix().'whatsapp_activity_log')) {
    $CI->db->query(
        'CREATE TABLE `'.db_prefix().'whatsapp_activity_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `phone_number_id` varchar(255) NULL DEFAULT NULL,
            `access_token` TEXT NULL DEFAULT NULL,
            `business_account_id` varchar(255) NULL DEFAULT NULL,
            `response_code` varchar(4) NOT NULL,
            `response_data` text NOT NULL,
            `category` varchar(50) NOT NULL,
            `category_id` int(11) NOT NULL,
            `rel_type` varchar(50) NOT NULL,
            `rel_id` int(11) NOT NULL,
            `category_params` longtext NOT NULL,
            `raw_data` TEXT NOT NULL,
            `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';'
    );
}
