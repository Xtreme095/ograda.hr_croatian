<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Codes and Configurations
 * 
 * This file stores all API codes and configurations for the application.
 * Do not commit sensitive information to version control.
 */

// N8N Chat Webhook URL
$n8n_chat_webhook_url = "https://n8n.srv830016.hstgr.cloud/webhook/5f1c0c82-0ff9-40c7-9e2e-b1a96ffe24cd/chat";

// N8N Chat Widget Customization Options
$n8n_chat_options = [
    'language' => 'hr', // Croatian language
    'initialMessages' => [
        'Pozdrav! 👋',
        'Kako vam mogu pomoći danas?'
    ],
    'i18n' => [
        'hr' => [
            'title' => 'Pozdrav! 👋',
            'subtitle' => 'Započnite razgovor. Tu smo da vam pomognemo 24/7.',
            'footer' => 'ograda.hr',
            'getStarted' => 'Nova Konverzacija',
            'inputPlaceholder' => 'Upišite vaše pitanje...',
        ]
    ],
    'showWelcomeScreen' => false, // Disable the welcome screen that requires a button click to start chat
    'pollingConfig' => [
        'enabled' => false // Disable automatic polling to prevent frequent executions
    ],
    'chatInputKey' => 'chatInput', // Specifies the key used for the message sent to n8n
    'chatSessionKey' => 'sessionId', // Specifies the key used for the session ID
    'sessionPollingEnabled' => false, // Disable session polling to prevent loadPreviousSession requests
    'loadPreviousSession' => false // Explicitly disable loading previous sessions
]; 