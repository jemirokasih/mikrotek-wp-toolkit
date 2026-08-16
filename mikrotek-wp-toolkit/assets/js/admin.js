(function($) {
    'use strict';

    // Global Preset Helper Function
    window.mikrotekAppendPreset = function(targetId, text) {
        var el = document.getElementById(targetId);
        if (!el) return;
        var current = el.value.trim();
        if (current.length > 0) {
            var lines = current.split("\n");
            if (lines.indexOf(text) !== -1) {
                alert("Preset ini sudah ada dalam daftar.");
                return;
            }
            el.value = current + "\n" + text;
        } else {
            el.value = text;
        }
        el.dispatchEvent(new Event("change"));
    };

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

        // Single-instance WordPress Media Uploader handler
        $(document).off('click.mikrotekMedia').on('click.mikrotekMedia', '.mikrotek-upload-button, .mzi-upload-button', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var targetId = $btn.attr('data-target') || $btn.data('target');

            if (!targetId || typeof wp === 'undefined' || !wp.media) {
                return false;
            }

            var frame = wp.media({
                title: 'Upload or Select Image',
                button: {
                    text: 'Use This Image'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                if (attachment && attachment.url) {
                    $('#' + targetId).val(attachment.url).trigger('change');
                    $('#' + targetId + '_preview').attr('src', attachment.url);
                    $('#' + targetId + '_preview_wrap').css('display', 'block').show();
                    $('[data-target="' + targetId + '"]').filter('.mikrotek-remove-button, .mzi-remove-button').css('display', 'inline-block').show();
                }
            });

            frame.open();
            return false;
        });

        // Single-instance Remove Image handler
        $(document).off('click.mikrotekRemove').on('click.mikrotekRemove', '.mikrotek-remove-button, .mzi-remove-button', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            var targetId = $btn.attr('data-target') || $btn.data('target');

            if (targetId) {
                $('#' + targetId).val('').trigger('change');
                $('#' + targetId + '_preview').attr('src', '');
                $('#' + targetId + '_preview_wrap').hide();
                $btn.hide();
            }
            return false;
        });
    });
})(jQuery);
