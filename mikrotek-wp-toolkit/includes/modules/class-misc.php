<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Misc {

    private static $cached_default_image_url = null;
    private static $cached_default_attachment_id = null;
    private static $in_meta_filter = false;

    public function __construct() {
        add_action('admin_init', [$this, 'init_misc_features']);
        add_action('admin_head', [$this, 'inject_misc_styles']);
        add_action('admin_footer', [$this, 'inject_custom_js']);
        add_action('admin_head', [$this, 'remove_help_tabs']);
        add_filter('screen_options_show_screen', [$this, 'toggle_screen_options']);

        // Default Featured Image hooks for WordPress Core & Elementor Widgets
        add_filter('has_post_thumbnail', [$this, 'default_has_post_thumbnail'], 10, 3);
        add_filter('post_thumbnail_id', [$this, 'default_post_thumbnail_id'], 10, 2);
        add_filter('post_thumbnail_html', [$this, 'default_post_thumbnail_html'], 10, 5);
        add_filter('post_thumbnail_url', [$this, 'default_post_thumbnail_url'], 10, 3);
        add_filter('wp_get_attachment_image_src', [$this, 'default_attachment_image_src'], 10, 4);
        add_filter('get_post_metadata', [$this, 'default_get_post_metadata'], 10, 4);

        // Elementor Specific Image Fallback Filter
        add_filter('elementor/image_size/get_attachment_image_html', [$this, 'default_elementor_image_html'], 10, 4);
    }

    private function get_default_image_url() {
        if (self::$cached_default_image_url !== null) {
            return self::$cached_default_image_url;
        }

        $default_image = Mikrotek_WP_Toolkit_Settings::get('default_featured_image');
        self::$cached_default_image_url = !empty($default_image) ? (string) $default_image : '';
        return self::$cached_default_image_url;
    }

    private function get_default_attachment_id() {
        if (self::$cached_default_attachment_id !== null) {
            return self::$cached_default_attachment_id;
        }

        $default_url = $this->get_default_image_url();
        if (empty($default_url)) {
            self::$cached_default_attachment_id = 0;
            return 0;
        }

        if (is_numeric($default_url)) {
            self::$cached_default_attachment_id = (int) $default_url;
            return self::$cached_default_attachment_id;
        }

        $attachment_id = attachment_url_to_postid($default_url);
        if ($attachment_id) {
            self::$cached_default_attachment_id = (int) $attachment_id;
            return self::$cached_default_attachment_id;
        }

        // Fallback ID for external image URLs to allow truthy check in get_post_thumbnail_id()
        self::$cached_default_attachment_id = -99999;
        return self::$cached_default_attachment_id;
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

    public function default_has_post_thumbnail($has_thumbnail, $post = null, $thumbnail_id = null) {
        if ($has_thumbnail) {
            return true;
        }

        $default_url = $this->get_default_image_url();

        return !empty($default_url);
    }

    public function default_post_thumbnail_id($thumbnail_id, $post = null) {
        if (!empty($thumbnail_id)) {
            return $thumbnail_id;
        }

        $default_id = $this->get_default_attachment_id();

        return $default_id ? $default_id : $thumbnail_id;
    }

    public function default_get_post_metadata($value, $object_id, $meta_key, $single) {
        if ($meta_key !== '_thumbnail_id' || self::$in_meta_filter || !empty($value)) {
            return $value;
        }

        self::$in_meta_filter = true;
        $has_meta = metadata_exists('post', $object_id, '_thumbnail_id');
        self::$in_meta_filter = false;

        if (!$has_meta) {
            $default_id = $this->get_default_attachment_id();
            if ($default_id) {
                return $single ? $default_id : [$default_id];
            }
        }

        return $value;
    }

    public function default_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size = 'post-thumbnail', $attr = '') {
        if (!empty($html)) {
            return $html;
        }

        $default_url = $this->get_default_image_url();
        if (empty($default_url)) {
            return $html;
        }

        $attachment_id = $this->get_default_attachment_id();

        if ($attachment_id > 0) {
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

        return '<img src="' . esc_url($default_url) . '" class="attachment-default wp-post-image mikrotek-default-featured-image"' . $attr_str . ' alt="">';
    }

    public function default_post_thumbnail_url($url, $post = null, $size = 'post-thumbnail') {
        if (!empty($url)) {
            return $url;
        }

        $attachment_id = $this->get_default_attachment_id();
        if ($attachment_id > 0) {
            $src = wp_get_attachment_image_src($attachment_id, $size);
            if (!empty($src[0])) {
                return $src[0];
            }
        }

        return $this->get_default_image_url();
    }

    public function default_attachment_image_src($image, $attachment_id, $size = 'thumbnail', $icon = false) {
        if (!empty($image)) {
            return $image;
        }

        $default_id = $this->get_default_attachment_id();
        $default_url = $this->get_default_image_url();

        if (!empty($default_url) && ($attachment_id === $default_id || $attachment_id === -99999)) {
            return [$default_url, 1200, 800, false];
        }

        return $image;
    }

    public function default_elementor_image_html($html, $settings = [], $image_key = 'image', $post = null) {
        if (!empty($html)) {
            return $html;
        }

        $default_url = $this->get_default_image_url();
        if (empty($default_url)) {
            return $html;
        }

        $size = !empty($settings[$image_key . '_size']) ? $settings[$image_key . '_size'] : 'full';
        $attachment_id = $this->get_default_attachment_id();

        if ($attachment_id > 0) {
            $default_html = wp_get_attachment_image($attachment_id, $size);
            if (!empty($default_html)) {
                return $default_html;
            }
        }

        return '<img src="' . esc_url($default_url) . '" class="attachment-default wp-post-image mikrotek-default-featured-image" alt="">';
    }
}


