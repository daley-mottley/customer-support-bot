<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sends a request to the Google Gemini API and handles responses/errors.
 *
 * Returns a user-friendly error message within the 'error' key if something goes wrong.
 *
 * @param array  $payload The request payload structured for the Gemini API.
 * @param string $api_key The Gemini API key.
 * @return array The decoded JSON response from the API or an array with an 'error' key containing a user-friendly message.
 */
function vacw_send_gemini_request($payload, $api_key) {
    // Gemini API endpoint (using v1beta for function calling - check for stable releases)
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent';
    $url_with_key = add_query_arg('key', $api_key, $api_url);

    $args = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ],
        'body'        => wp_json_encode($payload),
        'method'      => 'POST',
        'data_format' => 'body',
        'timeout'     => 60, // Reasonable timeout
    ];

    // Log the request details (conditionally log payload)
    if (function_exists('vacw_log')) {
        vacw_log('Sending request to Gemini: ' . $url_with_key);
        // Avoid logging full payload in production unless WP_DEBUG is explicitly true
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
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
        return ['error' => __('Unable to connect to the AI service due to a network issue. Please try again later or contact the site administrator.', 'customer-support-bot')];
    }

    // --- Handle API Response Codes and Body ---
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    // Log the raw response for debugging
    if (function_exists('vacw_log')) {
         vacw_log("Gemini Response Code: " . $response_code);
         // Conditionally log full body
          if (defined('WP_DEBUG') && WP_DEBUG === true) {
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
            $user_error = __('Chatbot Error: The configured AI API key is invalid or lacks permissions. Please contact the site administrator.', 'customer-support-bot');
        } elseif ($error_status === 'RESOURCE_EXHAUSTED' || $response_code == 429) {
            $user_error = __('The request limit for the AI service has been reached. Please try again later.', 'customer-support-bot');
        } elseif ($error_status === 'INVALID_ARGUMENT' || $response_code == 400) {
             $user_error = __('There was an issue with the request format sent to the AI service. Please check the logs or contact the administrator.', 'customer-support-bot');
        }

        // Check specifically for prompt feedback block reasons
        if (isset($result['promptFeedback']['blockReason'])) {
             $block_reason = $result['promptFeedback']['blockReason'];
             if (function_exists('vacw_log')) {
                vacw_log("Gemini content blocked. Reason: " . $block_reason, 'warn');
             }
             $user_error = __('The request was blocked due to content restrictions set by the AI service. Please rephrase your message.', 'customer-support-bot');
        }

        return ['error' => $user_error];
    }

    // --- Handle Empty or Unexpected Successful Responses ---
    // Check if candidates array exists and has at least one element
    if (!isset($result['candidates'][0])) {
        if (function_exists('vacw_log')) {
           vacw_log('Gemini response missing candidates array or is empty.', 'warn');
        }
        // Check for top-level prompt feedback if candidates are missing
        if (isset($result['promptFeedback']['blockReason'])) {
             $block_reason = $result['promptFeedback']['blockReason'];
             if (function_exists('vacw_log')) {
                 vacw_log("Gemini content blocked (detected in promptFeedback). Reason: " . $block_reason, 'warn');
             }
             return ['error' => __('The request was blocked due to content restrictions set by the AI service. Please rephrase your message.', 'customer-support-bot')];
        }
        return ['error' => __('The AI service returned an unexpected empty response structure.', 'customer-support-bot')];
    }

    // Check if parts array exists and has at least one element within the first candidate
    if (empty($result['candidates'][0]['content']['parts'])) {
         if (function_exists('vacw_log')) {
            vacw_log('Gemini response missing parts array in the first candidate.', 'warn');
         }
         // Check finish reason for safety blocks if parts are missing
        if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'SAFETY') {
             return ['error' => __('Your message could not be processed due to safety settings enforced by the AI service. Please rephrase.', 'customer-support-bot')];
        }
        return ['error' => __('The AI service returned an empty or unexpected response content. Please try again.', 'customer-support-bot')];
    }

    // If we reached here, the response structure seems minimally valid
    return $result;
}

/**
 * Handles the AJAX request from the frontend chatbot.
 */
function vacw_get_bot_response() {
    // Verify nonce for security
    if (!check_ajax_referer('vacw_nonce_action', 'security', false)) { // Set die=false
        if (function_exists('vacw_log')) {
            vacw_log('Nonce verification failed.', 'error');
        }
        wp_send_json_error(__('Security check failed. Please refresh the page and try again.', 'customer-support-bot'), 403); // Use 403 Forbidden
        wp_die();
    }

    // Sanitize user input
    $user_message = sanitize_text_field(wp_unslash($_POST['message'] ?? ''));

    // --- Input Validation ---
    if (empty($user_message)) {
         if (function_exists('vacw_log')) {
            vacw_log('Empty user message received.', 'warn');
         }
        wp_send_json_error(__('User message cannot be empty.', 'customer-support-bot'), 400); // Use 400 Bad Request
        wp_die();
    }

    // --- API Key Check ---
    $api_key = get_option('vacw_gemini_api_key');
    if (empty($api_key)) {
        if (function_exists('vacw_log')) {
            vacw_log('Gemini API Key is missing from settings.', 'error');
        }
        // Send a more informative error back to the frontend
        wp_send_json_error(__('Chatbot Error: The AI service API key is missing. Please contact the site administrator.', 'customer-support-bot'), 500); // Use 500 Internal Server Error
        wp_die();
    }

    // --- Prepare Prompt and Tools ---
    // Note: Conversation history management is NOT implemented here.
    try {
        $prompt_data = vacw_build_prompt_context($user_message);
        if (!$prompt_data || !isset($prompt_data['user_message_with_context']) || !isset($prompt_data['functions'])) {
            throw new Exception('Failed to build prompt context structure.');
        }
    } catch (Exception $e) {
        if (function_exists('vacw_log')) {
            vacw_log('Error building prompt context: ' . $e->getMessage(), 'error');
        }
        wp_send_json_error(__('Internal Error: Could not prepare the request for the AI service.', 'customer-support-bot'), 500);
        wp_die();
    }

    $gemini_tools = [['functionDeclarations' => $prompt_data['functions']]];
    // Initialize conversation history structure (replace with actual history if implemented)
    $gemini_contents = [
        // Turn 1: User message (with system context prepended)
        [
            'role' => 'user',
            'parts' => [['text' => $prompt_data['user_message_with_context']]]
        ]
        // Future: Add 'model' and 'user' turns from history here
    ];

    // Wrap the entire API interaction flow in a try-catch
    try {
        // --- Initial API Request ---
        $payload = [
            'contents' => $gemini_contents,
            'tools' => $gemini_tools,
            // Optional: Add generation config if needed
            // 'generationConfig' => [ 'temperature' => 0.7, 'maxOutputTokens' => 1500 ]
        ];

        $result = vacw_send_gemini_request($payload, $api_key);

        // --- Handle errors returned specifically by vacw_send_gemini_request ---
        if (isset($result['error'])) {
            // The error message here should already be user-friendly
             if (function_exists('vacw_log')) {
                vacw_log('Error received from vacw_send_gemini_request (initial): ' . $result['error'], 'warn');
             }
            // Pass the specific error message and code (defaulting to 500 if not set)
            $error_code = isset($result['code']) ? intval($result['code']) : 500;
            wp_send_json_error($result['error'], $error_code);
            wp_die();
        }

        // --- Process API Response (Success Path) ---
        // Structure checked within vacw_send_gemini_request, safe to access here
        $response_part = $result['candidates'][0]['content']['parts'][0];
        // Store the whole content part from the model for potential history inclusion
        $model_response_content_for_history = $result['candidates'][0]['content'];


        // --- Check for Function Call ---
        if (isset($response_part['functionCall'])) {
            $function_call = $response_part['functionCall'];
            $function_name = sanitize_text_field($function_call['name']);
            // Gemini uses 'args' for arguments
            $arguments = isset($function_call['args']) ? $function_call['args'] : [];

            // Validate arguments
            if (!is_array($arguments)) {
                throw new Exception('Invalid function arguments received from AI: Not an array.');
            }

             if (function_exists('vacw_log')) {
                vacw_log("Gemini requested function call: '{$function_name}' with args: " . json_encode($arguments));
             }

            // --- Execute Local Function Handler ---
            // Assuming vacw_handle_function_call exists and returns data
            // Consider adding try-catch around this if it can throw exceptions
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
                if (function_exists('vacw_log')) {
                    vacw_log('Error received from vacw_send_gemini_request (second): ' . $second_result['error'], 'warn');
                }
                 $error_code = isset($second_result['code']) ? intval($second_result['code']) : 500;
                 wp_send_json_error($second_result['error'], $error_code);
                 wp_die();
            }

            // Extract final text reply from the second response
            // Structure already checked in vacw_send_gemini_request
            if (isset($second_result['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = wp_kses_post(trim($second_result['candidates'][0]['content']['parts'][0]['text']));
                if (empty($reply)) {
                     if (function_exists('vacw_log')) {
                        vacw_log('Gemini returned empty text after function call execution.', 'warn');
                     }
                     // Provide a generic confirmation if AI gives no text after function success
                     wp_send_json_success(['content' => __('Okay, I have processed that request.', 'customer-support-bot')]);
                 } else {
                     wp_send_json_success(['content' => $reply]);
                 }
            } else {
                 // This case means the second request succeeded but didn't yield text, which is unexpected
                 throw new Exception('AI service did not provide a text response after function execution.');
            }

        // --- Check for Direct Text Response ---
        } elseif (isset($response_part['text'])) {
            $reply = wp_kses_post(trim($response_part['text']));
             if (empty($reply)) {
                 if (function_exists('vacw_log')) {
                    vacw_log('Gemini returned empty text in initial response.', 'warn');
                 }
                 // Provide a generic acknowledgement if AI gives no text directly
                 wp_send_json_success(['content' => __('I received your message, but I do not have a specific text response for that.', 'customer-support-bot')]);
             } else {
                 wp_send_json_success(['content' => $reply]);
             }
        } else {
            // Handle unexpected but non-error response format (e.g., only function DECLARATION, no call/text)
            throw new Exception('Unexpected response format from AI: No text or function call found in the primary response part.');
        }

    } catch (Exception $e) {
        // Catch any other unexpected exceptions during API interaction or processing
        if (function_exists('vacw_log')) {
            vacw_log('Unhandled exception in vacw_get_bot_response: ' . $e->getMessage(), 'error');
        }
        // Send a generic error, as the specific cause might be sensitive or internal
        wp_send_json_error(__('Sorry, an unexpected internal error occurred while processing your request. Please try again later or contact support.', 'customer-support-bot'), 500);
        wp_die();
    }

    // Fallback die() in case wp_send_json_* doesn't exit (it should)
    wp_die();
}

// Hook the AJAX handler for logged-in users
add_action('wp_ajax_vacw_get_bot_response', 'vacw_get_bot_response');
// Hook the AJAX handler for non-logged-in users
add_action('wp_ajax_nopriv_vacw_get_bot_response', 'vacw_get_bot_response');
