<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Settings {

    const OPTION_NAME = 'mzi_white_label_settings';

    public static function option_name() {
        return self::OPTION_NAME;
    }

    public static function all() {
        $settings = get_option(self::OPTION_NAME, []);

        return is_array($settings) ? $settings : [];
    }

    public static function get_all() {
        return self::all();
    }

    public static function get($key, $default = '') {
        $settings = self::all();

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    public static function field_name($key) {
        return self::OPTION_NAME . '[' . $key . ']';
    }

    public static function enabled($key, $default = false) {
        return '1' === self::get($key, $default ? '1' : '');
    }

    public static function sanitize($input) {
        $input = is_array($input) ? $input : [];
        $allowed_fields = self::posted_fields($input);
        $output = self::all();

        $url_fields = [
            'login_logo',
            'admin_logo',
            'favicon',
            'login_background',
            'default_featured_image',
            'maintenance_logo',
        ];

        $email_fields = [
            'mail_email',
        ];

        $checkbox_fields = [
            'client_mode',
            'dark_admin',
            'disable_gutenberg',
            'disable_comments',
            'disable_heartbeat',
            'disable_xmlrpc',
            'disable_rest_api',
            'disable_rest_users',
            'dashboard_widget_enabled',
            'dashboard_hide_selected_widgets',
            'hide_login_back_to_site',
            'hide_login_language_switcher',
            'protect_wordfence',
            'hide_update_notices',
            'hide_login_errors',
            'smtp_auth',
            'hide_plugin_update_notices',
            'hide_theme_update_notices',
            'hide_core_update_notices',
            'disable_screen_options',
            'disable_help_tab',
            'enable_audit_trail',
            'enable_maintenance_mode',
        ];

        foreach ($url_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $output[$field] = isset($input[$field]) ? esc_url_raw($input[$field]) : '';
            }
        }

        foreach ($email_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $output[$field] = isset($input[$field]) ? sanitize_email($input[$field]) : '';
            }
        }

        foreach ($checkbox_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $output[$field] = empty($input[$field]) ? '' : '1';
            }
        }

        $text_fields = [
            'cms_name',
            'footer_text',
            'login_title',
            'custom_login_slug',
            'mail_name',
            'dashboard_widget_title',
            'smtp_host',
            'smtp_username',
            'smtp_password',
            'maintenance_title',
            'maintenance_headline',
        ];

        $textarea_fields = [
            'custom_hidden_menus',
            'menu_renames',
            'dashboard_widget_content',
            'login_custom_css',
            'maintenance_message',
            'custom_admin_css',
            'custom_admin_js',
        ];

        $number_fields = [
            'login_logo_width',
            'login_logo_height',
            'smtp_port',
        ];

        $color_fields = [
            'login_background_color',
            'login_button_color',
            'maintenance_bg_color',
        ];

        $array_fields = [
            'hidden_menus',
            'dashboard_hidden_widgets',
            'client_mode_roles',
        ];

        $select_fields = [
            'login_layout'          => ['normal', 'sidepanel_left', 'sidepanel_right'],
            'login_form_position'   => ['center', 'left', 'right'],
            'mail_method'            => ['default', 'smtp'],
            'smtp_encryption'       => ['none', 'ssl', 'tls'],
            'maintenance_mode_type' => ['503', '200'],
        ];

        foreach ($text_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $output[$field] = isset($input[$field]) ? sanitize_text_field($input[$field]) : '';
            }
        }

        foreach ($textarea_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $output[$field] = isset($input[$field]) ? sanitize_textarea_field($input[$field]) : '';
            }
        }

        foreach ($number_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $output[$field] = isset($input[$field]) ? absint($input[$field]) : '';
            }
        }

        foreach ($color_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $color = isset($input[$field]) ? sanitize_hex_color($input[$field]) : '';
                $output[$field] = $color ? $color : '';
            }
        }

        foreach ($array_fields as $field) {
            if (in_array($field, $allowed_fields, true)) {
                $values = isset($input[$field]) && is_array($input[$field]) ? $input[$field] : [];
                $output[$field] = array_values(array_filter(array_map('sanitize_text_field', $values)));
            }
        }

        foreach ($select_fields as $field => $allowed_values) {
            if (in_array($field, $allowed_fields, true)) {
                $value = isset($input[$field]) ? sanitize_key($input[$field]) : '';
                $output[$field] = in_array($value, $allowed_values, true) ? $value : $allowed_values[0];
            }
        }

        return $output;
    }

    private static function posted_fields($input) {
        if (!isset($input['__fields'])) {
            return array_keys($input);
        }

        if (empty($input['__fields'])) {
            return [];
        }

        $posted_fields = sanitize_text_field($input['__fields']);
        $posted_fields = array_filter(array_map('sanitize_key', explode(',', $posted_fields)));

        return array_values($posted_fields);
    }
}
