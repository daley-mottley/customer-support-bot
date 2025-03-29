jQuery(document).ready(function($) {
    var mediaUploader;

    // Handle avatar image upload
    $('#vacw-avatar-upload-button').click(function(e) {
        e.preventDefault();

        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Avatar Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false // Set to true to allow multiple files to be selected
        });

        // When a file is selected, grab the URL and set it as the hidden input's value and preview
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#vacw-avatar-url').val(attachment.url);
            $('#vacw-avatar-preview').attr('src', attachment.url).show(); // Update image preview
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Handle Gemini API Key visibility toggle
    $('#toggle_gemini_api_key_visibility').click(function(e) { // Changed selector ID
        e.preventDefault();
        var apiKeyInput = $('#vacw_gemini_api_key'); // Changed selector ID
        var icon = $(this).find('.dashicons');

        // Toggle the input field type between password and text
        if (apiKeyInput.attr('type') === 'password') {
            apiKeyInput.attr('type', 'text');  // Show API key
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');  // Change icon to hidden
        } else {
            apiKeyInput.attr('type', 'password');  // Hide API key
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');  // Change icon back to visible
        }
    });

    // Initialize WordPress color picker
    $('.vacw-color-field').wpColorPicker();

});
