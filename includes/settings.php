<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1 class="mb-4"><?php _e('Customer Support Bot Settings', 'customer-support-bot'); ?></h1>

    <form method="post" action="options.php">
        <?php
        // Output security fields for the settings group
        settings_fields('vacw_settings_group');
        // Output setting sections and fields (we aren't using sections here, but it's good practice)
        do_settings_sections('vacw_settings_group');
        ?>

        <!-- Avatar Upload Section -->
        <div class="mb-3 form-group">
            <label for="vacw_avatar_url" class="form-label">
                <?php _e('Avatar Image', 'customer-support-bot'); ?>
            </label>
            <div class="d-flex align-items-center">
                <!-- Display the current avatar or a default placeholder -->
                <img id="vacw-avatar-preview"
                     src="<?php echo esc_url(get_option('vacw_avatar_url', plugins_url('../assets/default-avatar.png', __FILE__))); ?>"
                     class="rounded-circle me-3"
                     style="width: 80px; height: 80px; object-fit: cover;"
                     alt="<?php esc_attr_e('Current Avatar Preview', 'customer-support-bot'); ?>"/>
                <!-- Button to trigger the media uploader -->
                <button type="button" id="vacw-avatar-upload-button" class="btn btn-secondary">
                    <?php _e('Upload/Change Image', 'customer-support-bot'); ?>
                </button>
            </div>
            <!-- Hidden input to store the selected image URL -->
            <input type="hidden" id="vacw-avatar-url" name="vacw_avatar_url"
                   value="<?php echo esc_attr(get_option('vacw_avatar_url')); ?>" />
            <p class="form-text text-muted">
                <?php _e('Upload an avatar image for the chatbot.', 'customer-support-bot'); ?>
            </p>
        </div>

        <!-- Assistant Name Section -->
        <div class="mb-3 form-group">
            <label for="vacw_assistant_name" class="form-label">
                <?php _e('Assistant Name', 'customer-support-bot'); ?>
            </label>
            <input type="text" name="vacw_assistant_name" id="vacw_assistant_name" class="form-control"
                   value="<?php echo esc_attr(get_option('vacw_assistant_name', 'Customer Support Assistant')); ?>" />
             <p class="form-text text-muted">
                <?php _e('The name displayed in the chat widget header.', 'customer-support-bot'); ?>
            </p>
        </div>

        <!-- Gemini API Key Section -->
        <div class="mb-3 form-group">
            <label for="vacw_gemini_api_key" class="form-label">
                <?php _e('Gemini API Key', 'customer-support-bot'); ?> <span class="text-danger">*</span>
            </label>
             <div class="input-group">
                <input type="password" name="vacw_gemini_api_key" id="vacw_gemini_api_key" class="form-control"
                       value="<?php echo esc_attr(get_option('vacw_gemini_api_key')); ?>" required />
                 <button class="btn btn-outline-secondary" type="button" id="toggle_gemini_api_key_visibility" title="<?php esc_attr_e('Toggle visibility', 'customer-support-bot'); ?>">
                    <span class="dashicons dashicons-visibility"></span>
                 </button>
             </div>
            <p class="form-text text-muted">
                <?php _e('Enter your Google AI Gemini API key. Required for the chatbot to function. Get one from Google AI Studio.', 'customer-support-bot'); ?>
            </p>
        </div>


        <!-- Bot Greeting Section -->
        <div class="mb-3 form-group">
             <label for="vacw_bot_greeting" class="form-label">
                <?php _e('Bot Greeting Message', 'customer-support-bot'); ?>
             </label>
             <input type="text" name="vacw_bot_greeting" id="vacw_bot_greeting" class="form-control"
                    value="<?php echo esc_attr(get_option('vacw_bot_greeting', __('Hi! How can I assist you today?', 'customer-support-bot'))); ?>" />
             <p class="form-text text-muted">
                <?php _e('The first message the bot displays when opened.', 'customer-support-bot'); ?>
             </p>
        </div>

        <!-- Primary Theme Color Picker Section -->
        <div class="mb-3 form-group">
             <label for="vacw_theme_color" class="form-label">
                <?php _e('Primary Theme Color', 'customer-support-bot'); ?>
             </label>
             <input type="text" name="vacw_theme_color" id="vacw_theme_color" class="vacw-color-field"
                    value="<?php echo esc_attr(get_option('vacw_theme_color', '#dbe200')); ?>" data-default-color="#dbe200" />
             <p class="form-text text-muted">
                <?php _e('Choose the primary background color for the chat widget elements.', 'customer-support-bot'); ?>
             </p>
        </div>

        <!-- Text Color Picker Section -->
        <div class="mb-3 form-group">
             <label for="vacw_text_color" class="form-label">
                <?php _e('Text Color', 'customer-support-bot'); ?>
             </label>
             <input type="text" name="vacw_text_color" id="vacw_text_color" class="vacw-color-field"
                    value="<?php echo esc_attr(get_option('vacw_text_color', '#000000')); ?>" data-default-color="#000000" />
             <p class="form-text text-muted">
                <?php _e('Choose the text color used within the chat widget.', 'customer-support-bot'); ?>
             </p>
        </div>

         <!-- Check Availability Webhook Section -->
        <div class="mb-3 form-group">
            <label for="vacw_check_availability_webhook" class="form-label">
                <?php _e('Check Availability Webhook URL', 'customer-support-bot'); ?>
            </label>
            <input type="url" name="vacw_check_availability_webhook" id="vacw_check_availability_webhook" class="form-control"
                   value="<?php echo esc_attr(get_option('vacw_check_availability_webhook')); ?>" placeholder="https://hook.make.com/..." />
            <p class="form-text text-muted">
                <?php _e('Enter the webhook URL for checking appointment availability (e.g., from Make.com/Zapier). Required for availability checks.', 'customer-support-bot'); ?>
            </p>
        </div>

        <!-- Book Appointment Webhook Section -->
        <div class="mb-3 form-group">
            <label for="vacw_book_appointment_webhook" class="form-label">
                <?php _e('Book Appointment Webhook URL', 'customer-support-bot'); ?>
            </label>
            <input type="url" name="vacw_book_appointment_webhook" id="vacw_book_appointment_webhook" class="form-control"
                   value="<?php echo esc_attr(get_option('vacw_book_appointment_webhook')); ?>" placeholder="https://hook.make.com/..." />
            <p class="form-text text-muted">
                <?php _e('Enter the webhook URL for booking appointments (e.g., from Make.com/Zapier). Required for booking appointments.', 'customer-support-bot'); ?>
            </p>
        </div>

        <!-- Save Button -->
        <?php submit_button(__('Save Settings', 'customer-support-bot'), 'btn btn-primary'); ?>
    </form>
</div>
