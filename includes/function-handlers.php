<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle function calls from the assistant.
 *
 * @param string $function_name The name of the function to call.
 * @param array  $arguments     The arguments for the function.
 * @return array The function response or an error array.
 */
function vacw_handle_function_call($function_name, $arguments) {
    switch ($function_name) {
        case 'check_availability':
            $response = wp_remote_post(
                'https://hook.us1.make.com/qqsdn32ixz7xdgfrn1x6ld1ss052xmsu',
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($arguments),
                ]
            );
            if (is_wp_error($response)) {
                vacw_log('Error checking availability: ' . $response->get_error_message(), 'error');
                return ['error' => 'Unable to check availability at this time.'];
            } else {
                return json_decode(wp_remote_retrieve_body($response), true);
            }
            break;

        case 'book_appointment':
            $response = wp_remote_post(
                'https://hook.us1.make.com/no9a7fqgfwp9csvaw7gh2gc5lqroswjd',
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($arguments),
                ]
            );
            if (is_wp_error($response)) {
                vacw_log('Error booking appointment: ' . $response->get_error_message(), 'error');
                return ['error' => 'Unable to book appointment at this time.'];
            } else {
                return json_decode(wp_remote_retrieve_body($response), true);
            }
            break;

        default:
            return ['error' => 'Function not found.'];
    }
}
