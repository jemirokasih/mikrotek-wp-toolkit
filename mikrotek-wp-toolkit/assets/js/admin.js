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

            var $btn = $(this);
            var target = $btn.attr('data-target') || $btn.data('target');

            if (typeof wp === 'undefined' || !wp.media) {
                alert('WordPress Media Uploader is loading or unavailable. Please refresh the page.');
                return;
            }

            var mediaUploader = wp.media({
                title: 'Upload or Select Image',
                button: {
                    text: 'Use This Image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                if (attachment && attachment.url) {
                    $('#' + target).val(attachment.url).trigger('change');
                    $('#' + target + '_preview').attr('src', attachment.url);
                    $('#' + target + '_preview_wrap').show();
                    $('[data-target="' + target + '"].mikrotek-remove-button, [data-target="' + target + '"].mzi-remove-button').show();
                }
            });

            mediaUploader.open();
        });

        // Remove Image Button
        $(document).on('click', '.mikrotek-remove-button, .mzi-remove-button', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var target = $btn.attr('data-target') || $btn.data('target');

            $('#' + target).val('').trigger('change');
            $('#' + target + '_preview').attr('src', '');
            $('#' + target + '_preview_wrap').hide();
            $btn.hide();
        });
    });
})(jQuery);
