<?php
// Exit if uninstall.php is not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Option names used by the plugin
$options_to_delete = [
    'vacw_avatar_url',
    'vacw_assistant_name',
    'vacw_gemini_api_key', // Use the new Gemini API key option name
    'vacw_bot_greeting',
    'vacw_theme_color',
    'vacw_text_color',
    'vacw_check_availability_webhook',
    'vacw_book_appointment_webhook',
    // Add any other options specific to this plugin if created later
];

// Loop through the options and delete them
foreach ($options_to_delete as $option_name) {
    delete_option($option_name);
}

