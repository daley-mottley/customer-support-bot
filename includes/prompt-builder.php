<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds the initial context and function definitions for the Gemini API request.
 *
 * @param string $user_message The latest message from the user.
 * @return array An array containing the 'user_message_with_context' and 'functions' definition.
 */
function vacw_build_prompt_context($user_message) {
    // Define the assistant's role and capabilities (System Prompt Context)
    // This context will be prepended to the first user message.
    $assistant_name = esc_html(get_option('vacw_assistant_name', 'Customer Support Assistant'));
    $system_prompt_context = <<<CONTEXT
You are a helpful customer support assistant named {$assistant_name}.
Your primary functions are to answer general inquiries and assist users with scheduling appointments by checking availability and booking them.
You have access to the following tools (functions). Use them only when appropriate based on the user's request.
- Use 'check_availability' when a user asks if a specific date/time is free.
- Use 'book_appointment' only after confirming the full details (date, time, name, email) with the user.
Do not guess information like dates, times, names, or emails if the user hasn't provided them; ask politely for clarification.
Always respond in a friendly, professional, and concise manner. Do not mention the internal function names to the user.
CONTEXT;

    // Define available functions in Gemini API format (Function Declarations)
    $functions = [
        [
            'name'        => 'check_availability',
            'description' => 'Check if the requested appointment date and time slot is available.',
            'parameters'  => [
                'type'       => 'OBJECT', // Gemini uses uppercase OBJECT
                'properties' => [
                    'date' => [
                        'type'        => 'STRING',
                        'description' => 'The requested date for the appointment in YYYY-MM-DD format.',
                    ],
                    'time' => [
                        'type'        => 'STRING',
                        'description' => 'The requested time for the appointment in HH:MM format (24-hour clock preferred, e.g., 14:30 for 2:30 PM).',
                    ],
                ],
                'required' => ['date', 'time'],
            ],
        ],
        [
            'name'        => 'book_appointment',
            'description' => 'Book an appointment for the user after confirming all necessary details.',
            'parameters'  => [
                'type'       => 'OBJECT',
                'properties' => [
                    'date' => [
                        'type'        => 'STRING',
                        'description' => 'The confirmed date for the appointment in YYYY-MM-DD format.',
                    ],
                    'time' => [
                        'type'        => 'STRING',
                        'description' => 'The confirmed time for the appointment in HH:MM format (24-hour clock preferred).',
                    ],
                    'name' => [
                        'type'        => 'STRING',
                        'description' => 'The full name of the person booking the appointment.',
                    ],
                    'email' => [
                        'type'        => 'STRING',
                        'description' => 'The email address of the person booking the appointment.',
                    ],
                ],
                'required' => ['date', 'time', 'name', 'email'],
            ],
        ],
    ];

    // For the first turn, combine system context with the user message.
    // For subsequent turns (if history is implemented), only the user_message would be needed here.
    $user_message_with_context = $system_prompt_context . "\n\nUser Query: " . $user_message;

    return [
        'user_message_with_context' => $user_message_with_context,
        'functions' => $functions, // Function definitions for Gemini tools
    ];
}
