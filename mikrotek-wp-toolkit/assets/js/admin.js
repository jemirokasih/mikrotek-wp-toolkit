(function($) {
    'use strict';

    $(function() {
        // Initialize WordPress Color Picker
        if ($.fn.wpColorPicker) {
            $('.mikrotek-wpt-color-field, .mzi-wlp-color-field').wpColorPicker();
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
        $(document).on('click', '.mikrotek-upload-button, .mzi-upload-button', function(e) {
            e.preventDefault();

            var target = $(this).data('target');
            var mediaUploader = wp.media({
                title: typeof mikrotekMedia !== 'undefined' ? mikrotekMedia.title : 'Select Image',
                button: {
                    text: typeof mikrotekMedia !== 'undefined' ? mikrotekMedia.buttonText : 'Use this image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();

                $('#' + target).val(attachment.url).trigger('change');
                $('#' + target + '_preview').attr('src', attachment.url);
                $('#' + target + '_preview_wrap').fadeIn(200);
                $('.mikrotek-remove-button[data-target="' + target + '"], .mzi-remove-button[data-target="' + target + '"]').show();
            });

            mediaUploader.open();
        });

        // Remove Image Button
        $(document).on('click', '.mikrotek-remove-button, .mzi-remove-button', function(e) {
            e.preventDefault();

            var target = $(this).data('target');

            $('#' + target).val('').trigger('change');
            $('#' + target + '_preview').attr('src', '');
            $('#' + target + '_preview_wrap').fadeOut(200);
            $(this).hide();
        });
    });
})(jQuery);
