(function($) {
    'use strict';

    $(function() {
        // Initialize WordPress Color Picker
        if ($.fn.wpColorPicker) {
            $('.mzi-wlp-color-field').wpColorPicker();
        }

        // Toggle SMTP fields based on mail_method selection
        function toggleSmtpFields() {
            var method = $('select[name*="[mail_method]"]').val();
            var $smtpRows = $('[name*="[smtp_"]').closest('tr');

            if ('smtp' === method) {
                $smtpRows.fadeIn(200);
            } else {
                $smtpRows.hide();
            }
        }

        $('select[name*="[mail_method]"]').on('change', toggleSmtpFields);
        toggleSmtpFields();

        // Media Uploader
        $('.mzi-upload-button').on('click', function(e) {
            e.preventDefault();

            var target = $(this).data('target');
            var mediaUploader = wp.media({
                title: typeof mziWlpMedia !== 'undefined' ? mziWlpMedia.title : 'Select Image',
                button: {
                    text: typeof mziWlpMedia !== 'undefined' ? mziWlpMedia.buttonText : 'Use this image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();

                $('#' + target).val(attachment.url).trigger('change');
                $('#' + target + '_preview').attr('src', attachment.url);
                $('#' + target + '_preview_wrap').fadeIn(200);
                $('.mzi-remove-button[data-target="' + target + '"]').show();
            });

            mediaUploader.open();
        });

        // Remove Image Button
        $('.mzi-remove-button').on('click', function(e) {
            e.preventDefault();

            var target = $(this).data('target');

            $('#' + target).val('').trigger('change');
            $('#' + target + '_preview').attr('src', '');
            $('#' + target + '_preview_wrap').fadeOut(200);
            $(this).hide();
        });
    });
})(jQuery);
