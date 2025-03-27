<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Send a request to the OpenAI API and handle errors.
 *
 * @param array  $payload The request payload.
 * @param string $api_key The OpenAI API key.
 * @return array The API response or an error array.
 */
function vacw_send_openai_request($payload, $api_key) {
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ],
        'body' => wp_json_encode($payload),
        'timeout' => 60,
    ]);

    if (is_wp_error($response)) {
        vacw_log('Error communicating with OpenAI API: ' . $response->get_error_message(), 'error');
        return ['error' => 'Unable to connect to the service. Please try again later.'];
    }

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    if (isset($result['error'])) {
        $error_type = $result['error']['type'];
        $error_message = $result['error']['message'];
        vacw_log('OpenAI API Error: ' . $error_type . ' - ' . $error_message, 'error');

        $api_error_map = [
            'authentication_error' => 'Authentication failed. Please check your API key.',
            'rate_limit_error' => 'Too many requests. Please try again later.',
            'invalid_request_error' => 'Invalid request. Please try rephrasing your message.',
            'server_error' => 'Server error. Please try again later.',
        ];

        $user_message = $api_error_map[$error_type] ?? 'An unexpected error occurred. Please try again later.';
        return ['error' => $user_message];
    }

    return $result;
}

/**
 * Handles AJAX requests to get bot responses from OpenAI.
 */
function vacw_get_bot_response() {
    check_ajax_referer('vacw_nonce_action', 'security');

    $user_message = sanitize_text_field(wp_unslash($_POST['message'] ?? ''));
    if (empty($user_message)) {
        wp_send_json_error('The user message is empty.', 400);
    }

    $api_key = get_option('vacw_openai_api_key');
    if (empty($api_key)) {
        wp_send_json_error('API key is not set.', 500);
    }

    $prompt = vacw_build_prompt($user_message);
    if (!$prompt || !isset($prompt['system']) || !isset($prompt['user'])) {
        wp_send_json_error('Error building the prompt.', 500);
    }

    $payload = [
        'model' => 'gpt-4o-mini', 
        'messages' => [
            ['role' => 'system', 'content' => $prompt['system']],
            ['role' => 'user', 'content' => $prompt['user']],
        ],
        'temperature' => 0.7,
        'max_tokens' => 1500,
        'top_p' => 1.0,
        'frequency_penalty' => 0.0,
        'presence_penalty' => 0.0,
    ];

    $result = vacw_send_openai_request($payload, $api_key);
    if (isset($result['error'])) {
        wp_send_json_error($result['error'], 500);
    }

    $message = $result['choices'][0]['message'];

    if (isset($message['function_call'])) {
        $function_name = sanitize_text_field($message['function_call']['name']);
        $arguments_json = $message['function_call']['arguments'];

        $arguments = json_decode($arguments_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            vacw_log('Error decoding function call arguments: ' . json_last_error_msg(), 'error');
            wp_send_json_error('Error processing request.', 500);
        }

        $function_response = vacw_handle_function_call($function_name, $arguments);

        $payload['messages'][] = [
            'role' => 'function',
            'name' => $function_name,
            'content' => wp_json_encode($function_response),
        ];

        $second_result = vacw_send_openai_request($payload, $api_key);
        if (isset($second_result['error'])) {
            wp_send_json_error($second_result['error'], 500);
        }

        $reply = sanitize_text_field($second_result['choices'][0]['message']['content']);
        wp_send_json_success(['content' => $reply]);
    } else {
        $reply = sanitize_text_field($message['content']);
        wp_send_json_success(['content' => $reply]);
    }

    wp_die();
}
add_action('wp_ajax_vacw_get_bot_response', 'vacw_get_bot_response');
add_action('wp_ajax_nopriv_vacw_get_bot_response', 'vacw_get_bot_response');
