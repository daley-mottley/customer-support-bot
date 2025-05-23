<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="chatbot" class="main-card collapsed" role="dialog" aria-label="Customer Support Chatbot">
  <!-- Button to toggle the chatbot open and closed -->
  <button id="chatbot_toggle" aria-controls="chatbot" aria-expanded="false" aria-label="Open Chatbot">
    <!-- Icon when chatbot is closed -->
    <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="currentColor">
      <path d="M0 0h24v24H0V0z" fill="none"/>
      <path d="M15 4v7H5.17l-.59.59-.58.58V4h11m1-2H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1V3c0-.55-.45-1-1-1zm5 4h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1z"/>
    </svg>
    <!-- Icon when chatbot is open -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="display:none">
      <path d="M0 0h24v24H0V0z" fill="none"/>
      <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
    </svg>
  </button>
  <!-- Chatbot header -->
  <div class="main-title">
    <div>
        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '/images/logo-white.png'); ?>" alt="Logo" />
    </div>
    <!-- Display the assistant's name from the settings -->
    <span><?php echo esc_html(get_option('vacw_assistant_name', 'Customer Support Assistant')); ?></span>
  </div>
  <!-- Chat area where messages are displayed -->
  <div class="chat-area" id="message-box" role="log" aria-live="polite"></div>
  <div class="line"></div>
  <!-- Input area for the user -->
  <div class="input-div">
    <label for="message" class="screen-reader-text">
      <?php _e('Type your message', 'customer-support-bot'); ?>
    </label>
    <input class="input-message" name="message" type="text" id="message"
           placeholder="<?php _e('Type your message ...', 'customer-support-bot'); ?>" />
    <button class="input-send" aria-label="<?php _e('Send Message', 'customer-support-bot'); ?>">
      <!-- Send icon -->
      <svg style="width:24px;height:24px">
        <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z" />
      </svg>
    </button>
  </div>
</div>
