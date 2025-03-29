<?php
/*
Plugin Name: Customer Support Bot
Description: A WordPress plugin that provides an AI-powered customer support chatbot with appointment scheduling capabilities.
Version: 0.3.2
Author: Admin
Text Domain: customer-support-bot
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load text domain for translations
function vacw_load_textdomain() {
    load_plugin_textdomain('customer-support-bot', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'vacw_load_textdomain');

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-scripts.php';
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-widget.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/prompt-builder.php';
require_once plugin_dir_path(__FILE__) . 'includes/function-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/frontend-custom-css.php';
