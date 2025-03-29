<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sends a request to the Google Gemini API and handles responses/errors.
 *
 * @param array  $payload The request payload structured for the Gemini API.
 * @param string $api_key The Gemini API key.
 * @return array The decoded JSON response from the API or an array with an 'error' key.
 */
function vacw_send_gemini_request($payload, $api_key) {
    // Gemini API endpoint (using v1beta for function calling - check for stable releases)
    // Make sure the model supports function calling (e.g., gemini-1.5-pro-latest)
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent';
    $url_with_key = add_query_arg('key', $api_key, $api_url);

    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json', // Good practice to specify accept header
        ],
        'body'        => wp_json_encode($payload),
        'method'      => 'POST',
        'data_format' => 'body',
        'timeout'     => 60, // Set a reasonable timeout (in seconds)
    ];

    // Log the request details (consider logging payload conditionally based on WP_DEBUG)
    if (function_exists('vacw_log')) {
        vacw_log('Sending request to Gemini: ' . $url_with_key);
        // Avoid logging full payload in production if it contains sensitive info
        if (defined('WP_DEBUG') && WP_DEBUG) {
             vacw_log('Gemini Payload: ' . wp_json_encode($payload, JSON_PRETTY_PRINT));
        }
    }

    $response = wp_remote_post($url_with_key, $args);

    // --- Handle WP HTTP API Errors ---
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        if (function_exists('vacw_log')) {
            vacw_log('WP Error communicating with Gemini API: ' . $error_message, 'error');
        }
        // Provide a generic user-facing error
        return ['error' => __('Unable to connect to the AI service due to a network issue. Please try again later.', 'customer-support-bot')];
    }

    // --- Handle API Response Codes and Body ---
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    // Log the raw response for debugging
    if (function_exists('vacw_log')) {
         vacw_log("Gemini Response Code: " . $response_code);
         if (defined('WP_DEBUG') && WP_DEBUG) {
            vacw_log('Gemini Raw Response Body: ' . $response_body);
         }
    }


    // --- Handle API Errors (HTTP Status >= 400 or 'error' in JSON) ---
    if ($response_code >= 400 || isset($result['error'])) {
        $error_message = isset($result['error']['message']) ? $result['error']['message'] : __('Unknown API error occurred.', 'customer-support-bot');
        $error_status = isset($result['error']['status']) ? $result['error']['status'] : 'UNKNOWN_STATUS';

        if (function_exists('vacw_log')) {
            vacw_log("Gemini API Error (HTTP $response_code | Status: $error_status): $error_message", 'error');
        }

        // Map common Gemini errors to user-friendly messages
        $user_error = __('An error occurred while processing your request with the AI service. Please try again.', 'customer-support-bot');
        if (strpos(strtolower($error_message), 'api key not valid') !== false || $error_status === 'UNAUTHENTICATED' || $response_code == 401 || $response_code == 403) {
            $user_error = __('The configured AI API key is invalid or lacks permissions. Please check the plugin settings.', 'customer-support-bot');
        } elseif ($error_status === 'RESOURCE_EXHAUSTED' || $response_code == 429) {
            $user_error = __('The request limit for the AI service has been reached. Please try again later.', 'customer-support-bot');
        } elseif ($error_status === 'INVALID_ARGUMENT' || $response_code == 400) {
             $user_error = __('There was an issue with the request format sent to the AI service.', 'customer-support-bot');
        }

        // Check specifically for prompt feedback block reasons
        if (isset($result['promptFeedback']['blockReason'])) {
             $block_reason = $result['promptFeedback']['blockReason'];
             if (function_exists('vacw_log')) {
                vacw_log("Gemini content blocked. Reason: " . $block_reason, 'warn');
             }
             $user_error = __('The request was blocked due to content restrictions. Please rephrase your message.', 'customer-support-bot');
        }

        return ['error' => $user_error];
    }

    // --- Handle Empty or Unexpected Successful Responses ---
    if (empty($result['candidates'][0]['content']['parts'])) {
         if (function_exists('vacw_log')) {
            vacw_log('Gemini response missing candidates or parts. Possible safety block or empty response.', 'warn');
         }
         // Check finish reason for safety blocks
        if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'SAFETY') {
             return ['error' => __('Your message could not be processed due to safety settings enforced by the AI service. Please rephrase.', 'customer-support-bot')];
        }
        return ['error' => __('The AI service returned an empty or unexpected response. Please try again.', 'customer-support-bot')];
    }

    // If we reached here, the response seems valid (though content might still be missing text/functionCall)
    return $result;
}

/**
 * Handles the AJAX request from the frontend chatbot.
 */
function vacw_get_bot_response() {
    // Verify nonce for security
    check_ajax_referer('vacw_nonce_action', 'security');

    // Sanitize user input
    $user_message = sanitize_text_field(wp_unslash($_POST['message'] ?? ''));

    // --- Input Validation ---
    if (empty($user_message)) {
        wp_send_json_error(__('User message cannot be empty.', 'customer-support-bot'), 400);
        wp_die();
    }

    // --- API Key Check ---
    $api_key = get_option('vacw_gemini_api_key');
    if (empty($api_key)) {
        wp_send_json_error(__('The Gemini API key is not configured in the plugin settings. The chatbot cannot function.', 'customer-support-bot'), 500);
        wp_die();
    }

    // --- Prepare Prompt and Tools ---
    // Note: Conversation history management is NOT implemented here.
    // For a multi-turn conversation, history needs to be passed from the frontend
    // and incorporated into the `$gemini_contents` array.
    $prompt_data = vacw_build_prompt_context($user_message);
    if (!$prompt_data || !isset($prompt_data['user_message_with_context']) || !isset($prompt_data['functions'])) {
        wp_send_json_error(__('Internal error: Could not build the prompt context.', 'customer-support-bot'), 500);
        wp_die();
    }

    $gemini_tools = [['functionDeclarations' => $prompt_data['functions']]];
    $gemini_contents = [
        // Turn 1: User message (with system context prepended)
        [
            'role' => 'user',
            'parts' => [['text' => $prompt_data['user_message_with_context']]]
        ]
        // Future: Add 'model' and 'user' turns from history here
    ];

    // --- Initial API Request ---
    $payload = [
        'contents' => $gemini_contents,
        'tools' => $gemini_tools,
        // Optional: Add generation config if needed
        // 'generationConfig' => [ 'temperature' => 0.7, 'maxOutputTokens' => 1500 ]
    ];

    $result = vacw_send_gemini_request($payload, $api_key);

    // Handle errors from the API call
    if (isset($result['error'])) {
        wp_send_json_error($result['error'], 500); // Send specific error message from send_gemini_request
        wp_die();
    }

    // --- Process API Response ---
    // Validate the structure before accessing deeply nested keys
     if (!isset($result['candidates'][0]['content']['parts'][0])) {
         if (function_exists('vacw_log')) {
            vacw_log('Invalid response structure from Gemini: Missing first part.', 'error');
         }
         wp_send_json_error(__('Received an invalid response structure from the AI service.', 'customer-support-bot'), 500);
         wp_die();
     }
    $response_part = $result['candidates'][0]['content']['parts'][0];
    $model_response_content_for_history = $result['candidates'][0]['content']; // Store the whole content part for history


    // --- Check for Function Call ---
    if (isset($response_part['functionCall'])) {
        $function_call = $response_part['functionCall'];
        $function_name = sanitize_text_field($function_call['name']);
        // Gemini uses 'args' for arguments
        $arguments = isset($function_call['args']) ? $function_call['args'] : [];

        // Basic validation for arguments
        if (!is_array($arguments)) {
             if (function_exists('vacw_log')) {
                vacw_log('Invalid function arguments received from Gemini: Not an array. Args: ' . print_r($arguments, true), 'error');
             }
             wp_send_json_error(__('Error processing function arguments from AI.', 'customer-support-bot'), 500);
             wp_die();
        }

         if (function_exists('vacw_log')) {
            vacw_log("Gemini requested function call: '{$function_name}' with args: " . json_encode($arguments));
         }

        // --- Execute Local Function Handler ---
        $function_response_data = vacw_handle_function_call($function_name, $arguments);

         if (function_exists('vacw_log')) {
            vacw_log("Local function '{$function_name}' response data: " . json_encode($function_response_data));
         }

        // --- Prepare Second API Request (with Function Response) ---
        // Append the model's request and the function's response to the conversation history
        $gemini_contents[] = [
            'role' => 'model',
            'parts' => $model_response_content_for_history['parts'] // Use the stored model response parts
        ];
        $gemini_contents[] = [
            'role' => 'function', // Special role for function results
            'parts' => [[
                'functionResponse' => [
                    'name' => $function_name,
                    // Gemini expects the 'response' field to contain the result data.
                    // Ensure it's an object, even if the original data was a simple string or array.
                    'response' => ['content' => $function_response_data]
                ]
            ]]
        ];

        $second_payload = [
            'contents' => $gemini_contents, // Send updated history
            'tools' => $gemini_tools,       // Resend tools declaration
            // 'generationConfig' => [...]
        ];

        $second_result = vacw_send_gemini_request($second_payload, $api_key);

        // Handle errors from the second API call
        if (isset($second_result['error'])) {
            wp_send_json_error($second_result['error'], 500);
            wp_die();
        }

        // Extract final text reply from the second response
        if (isset($second_result['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = wp_kses_post(trim($second_result['candidates'][0]['content']['parts'][0]['text'])); // Sanitize and trim
             if (empty($reply)) {
                 // Handle cases where the AI might return empty text after function call
                 if (function_exists('vacw_log')) {
                     vacw_log('Gemini returned empty text after function call execution.', 'warn');
                 }
                 wp_send_json_success(['content' => __('Okay, I have processed that.', 'customer-support-bot')]); // Provide a fallback message
             } else {
                 wp_send_json_success(['content' => $reply]);
             }
        } else {
             // Log if the expected text part is missing after function call
             if (function_exists('vacw_log')) {
                 vacw_log('Gemini did not return text part after function call. Response: ' . json_encode($second_result), 'error');
             }
             wp_send_json_error(__('AI service did not provide a text response after function execution.', 'customer-support-bot'), 500);
        }

    // --- Check for Direct Text Response ---
    } elseif (isset($response_part['text'])) {
        $reply = wp_kses_post(trim($response_part['text'])); // Sanitize and trim output
         if (empty($reply)) {
             // Handle cases where the AI might return empty text directly
             if (function_exists('vacw_log')) {
                 vacw_log('Gemini returned empty text in initial response.', 'warn');
             }
             wp_send_json_success(['content' => __('I received your message, but I do not have a specific response for that.', 'customer-support-bot')]); // Provide a fallback message
         } else {
             wp_send_json_success(['content' => $reply]);
         }
    } else {
        // --- Handle Unexpected Response Format ---
         if (function_exists('vacw_log')) {
            vacw_log('Unexpected response format from Gemini: No text or functionCall found in first part. Response: ' . json_encode($result), 'error');
         }
        wp_send_json_error(__('Unexpected response format from the AI service.', 'customer-support-bot'), 500);
    }

    // Ensure the script exits cleanly after sending JSON response
    wp_die();
}
// Hook the AJAX handler for logged-in users
add_action('wp_ajax_vacw_get_bot_response', 'vacw_get_bot_response');
// Hook the AJAX handler for non-logged-in users
add_action('wp_ajax_nopriv_vacw_get_bot_response', 'vacw_get_bot_response');
