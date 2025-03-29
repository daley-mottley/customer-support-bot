<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Display the plugin's settings page in the admin dashboard
function vacw_settings_page() {
    try {
        // Include the settings.php file which contains the form HTML
        include(plugin_dir_path(__FILE__) . 'settings.php');
    } catch (Exception $e) {
        // Use the logging function if available
        if (function_exists('vacw_log')) {
            vacw_log('Error loading settings page: ' . $e->getMessage(), 'error');
        } else {
            error_log('Error loading settings page: ' . $e->getMessage());
        }
        // Display a user-friendly error message
        echo '<div class="error"><p>' . __('Error loading settings page. Please contact the administrator.', 'customer-support-bot') . '</p></div>';
    }
}


// Register the settings page under the "Settings" menu
function vacw_register_settings_page() {
    add_options_page(
        __('Chat Widget Settings', 'customer-support-bot'), // Page Title
        __('Chat Widget', 'customer-support-bot'),         // Menu Title
        'manage_options',                                  // Capability required
        'vacw-settings',                                   // Menu Slug
        'vacw_settings_page'                               // Callback function to display the page
    );
}
add_action('admin_menu', 'vacw_register_settings_page');

// Register plugin settings, including the new Gemini API Key field
function vacw_register_settings() {
    $settings_group = 'vacw_settings_group';

    // Avatar URL field
    register_setting($settings_group, 'vacw_avatar_url', 'sanitize_text_field');

    // Assistant Name field
    register_setting($settings_group, 'vacw_assistant_name', 'sanitize_text_field');

    // Gemini API Key field
    register_setting(
        $settings_group,
        'vacw_gemini_api_key', // New option name for Gemini
        'sanitize_text_field'  // Basic sanitization
    );

    // Bot Greeting field
    register_setting($settings_group, 'vacw_bot_greeting', 'sanitize_text_field');

    // Primary Theme Color field
    register_setting(
        $settings_group,
        'vacw_theme_color',
        array(
            'type'              => 'string',
            'default'           => '#dbe200',        // Default primary color
            'sanitize_callback' => 'sanitize_hex_color' // Sanitize hex color value
        )
    );

    // Text Color field
    register_setting(
        $settings_group,
        'vacw_text_color',
        array(
            'type'              => 'string',
            'default'           => '#000000',        // Default text color
            'sanitize_callback' => 'sanitize_hex_color' // Sanitize hex color value
        )
    );

    // Check Availability Webhook URL
    register_setting(
        $settings_group,
        'vacw_check_availability_webhook',
        'esc_url_raw' // Use esc_url_raw for saving URLs safely
    );

    // Book Appointment Webhook URL
    register_setting(
        $settings_group,
        'vacw_book_appointment_webhook',
        'esc_url_raw' // Use esc_url_raw for saving URLs safely
    );
}
add_action('admin_init', 'vacw_register_settings');
