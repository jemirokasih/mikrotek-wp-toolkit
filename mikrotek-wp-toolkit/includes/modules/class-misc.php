<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Misc {

    public function __construct() {
        add_action('admin_init', [$this, 'init_misc_features']);
        add_action('admin_head', [$this, 'inject_misc_styles']);
        add_action('admin_footer', [$this, 'inject_custom_js']);
        add_action('admin_head', [$this, 'remove_help_tabs']);
        add_filter('screen_options_show_screen', [$this, 'toggle_screen_options']);

        add_filter('has_post_thumbnail', [$this, 'default_has_post_thumbnail'], 10, 3);
        add_filter('post_thumbnail_html', [$this, 'default_post_thumbnail_html'], 10, 5);
    }

    public function init_misc_features() {
        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_plugin_update_notices')) {
            remove_action('load-plugins.php', 'wp_update_plugins');
            add_filter('pre_site_transient_update_plugins', '__return_null');
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_theme_update_notices')) {
            remove_action('load-themes.php', 'wp_update_themes');
            add_filter('pre_site_transient_update_themes', '__return_null');
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_core_update_notices')) {
            add_filter('pre_site_transient_update_core', '__return_null');
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('network_admin_notices', 'update_nag', 3);
        }
    }

    public function inject_misc_styles() {
        $css = '';

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_plugin_update_notices')) {
            $css .= '.plugin-update-tr, .plugins .update-message, .notice.update-message, span.update-plugins, .plugin-count { display: none !important; }';
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_theme_update_notices')) {
            $css .= '.theme-update-message, .theme-count, span.update-themes { display: none !important; }';
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_core_update_notices')) {
            $css .= '.update-nag, #wp-admin-bar-updates, .update-core-count { display: none !important; }';
        }

        $custom_admin_css = Mikrotek_WP_Toolkit_Settings::get('custom_admin_css');
        if (!empty($custom_admin_css)) {
            $css .= "\n" . $custom_admin_css;
        }

        if (!empty($css)) {
            echo '<style id="mikrotek-wpt-misc-css">' . esc_html($css) . '</style>';
        }
    }

    public function inject_custom_js() {
        $custom_admin_js = Mikrotek_WP_Toolkit_Settings::get('custom_admin_js');
        if (!empty($custom_admin_js)) {
            echo '<script id="mikrotek-wpt-custom-admin-js">' . esc_html($custom_admin_js) . '</script>';
        }
    }

    public function remove_help_tabs() {
        if (Mikrotek_WP_Toolkit_Settings::enabled('disable_help_tab')) {
            $screen = get_current_screen();
            if ($screen && method_exists($screen, 'remove_help_tabs')) {
                $screen->remove_help_tabs();
            }
        }
    }

    public function toggle_screen_options($show) {
        if (Mikrotek_WP_Toolkit_Settings::enabled('disable_screen_options')) {
            return false;
        }

        return $show;
    }

    public function default_has_post_thumbnail($has_thumbnail, $post, $thumbnail_id) {
        if ($has_thumbnail) {
            return true;
        }

        $default_image = Mikrotek_WP_Toolkit_Settings::get('default_featured_image');

        return !empty($default_image);
    }

    public function default_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr) {
        if (!empty($html)) {
            return $html;
        }

        $default_image = Mikrotek_WP_Toolkit_Settings::get('default_featured_image');
        if (empty($default_image)) {
            return $html;
        }

        $attachment_id = attachment_url_to_postid($default_image);

        if ($attachment_id) {
            $default_html = wp_get_attachment_image($attachment_id, $size, false, $attr);
            if (!empty($default_html)) {
                return $default_html;
            }
        }

        $attr_str = '';
        if (is_array($attr)) {
            foreach ($attr as $name => $val) {
                $attr_str .= ' ' . esc_attr($name) . '="' . esc_attr($val) . '"';
            }
        }

        return '<img src="' . esc_url($default_image) . '" class="attachment-default wp-post-image mikrotek-default-featured-image"' . $attr_str . ' alt="">';
    }
}


