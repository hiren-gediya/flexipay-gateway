jQuery(document).ready(function ($) {
    let frame;

    // Handle the "Select Image" button click in WooCommerce settings
    $(document).on('click', '.flexipay-upload-button', function (e) {
        e.preventDefault();
        let $button = $(this);
        let $wrapper = $button.closest('.flexipay-media-wrapper');
        let $preview = $wrapper.find('.flexipay-image-preview');
        let $removeBtn = $wrapper.find('.flexipay-remove-button');
        let $input = $button.closest('td').find('input.flexipay-image-url');

        if (frame) {
            frame.off('select'); // Avoid multiple listeners
        }

        frame = wp.media({
            title: 'Select QR Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function () {
            let attachment = frame.state().get('selection').first().toJSON();
            
            // Update the hidden input
            $input.val(attachment.url).trigger('change');
            
            // Update the preview
            $preview.html('<img src="' + attachment.url + '" style="max-width: 150px; display: block; margin-bottom: 10px;">');
            
            // Show the remove button
            $removeBtn.show();
        });

        frame.open();
    });

    // Handle the "Remove" button click
    $(document).on('click', '.flexipay-remove-button', function (e) {
        e.preventDefault();
        let $button = $(this);
        let $wrapper = $button.closest('.flexipay-media-wrapper');
        let $preview = $wrapper.find('.flexipay-image-preview');
        let $input = $button.closest('td').find('input.flexipay-image-url');

        // Clear input and preview
        $input.val('').trigger('change');
        $preview.html('<p class="description">No image selected.</p>');
        
        // Hide the remove button
        $button.hide();
    });
});