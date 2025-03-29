// --- File: includes/logger.php ---
<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('vacw_log')) {
    function vacw_log($message, $level = 'info') {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $log_message = "[" . date("Y-m-d H:i:s") . "] [" . strtoupper($level) . "] " . (is_string($message) ? $message : print_r($message, true));
            error_log($log_message . "\n", 3, WP_CONTENT_DIR . '/debug.log');
        }
    }
}
