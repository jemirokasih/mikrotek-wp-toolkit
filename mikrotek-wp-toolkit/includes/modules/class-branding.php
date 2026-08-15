<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Branding {

    public function __construct() {
        add_filter('admin_title', [$this, 'custom_admin_title'], 10, 2);
        add_filter('login_title', [$this, 'custom_login_title'], 10, 2);
        add_filter('login_headerurl', [$this, 'custom_login_headerurl']);
        add_filter('login_headertext', [$this, 'custom_login_headertext']);
        add_action('login_enqueue_scripts', [$this, 'enqueue_login_styles']);

        add_action('admin_bar_menu', [$this, 'custom_admin_bar_logo'], 11);
        add_filter('admin_footer_text', [$this, 'custom_admin_footer_text']);
        add_filter('update_footer', [$this, 'custom_admin_footer_version'], 11);

        add_action('wp_head', [$this, 'inject_favicon']);
        add_action('admin_head', [$this, 'inject_favicon']);
        add_action('login_head', [$this, 'inject_favicon']);
    }

    public function custom_admin_title($admin_title, $title) {
        $cms_name = Mikrotek_WP_Toolkit_Settings::get('cms_name');
        if (empty($cms_name)) {
            return $admin_title;
        }

        return str_replace('WordPress', esc_html($cms_name), $admin_title);
    }

    public function custom_login_title($login_title, $title) {
        $custom_title = Mikrotek_WP_Toolkit_Settings::get('login_title');
        if (!empty($custom_title)) {
            return esc_html($custom_title) . ' &lsaquo; ' . get_bloginfo('name');
        }

        $cms_name = Mikrotek_WP_Toolkit_Settings::get('cms_name');
        if (empty($cms_name)) {
            return $login_title;
        }

        return str_replace('WordPress', esc_html($cms_name), $login_title);
    }

    public function custom_login_headerurl() {
        return home_url('/');
    }

    public function custom_login_headertext() {
        $cms_name = Mikrotek_WP_Toolkit_Settings::get('cms_name');

        return !empty($cms_name) ? esc_html($cms_name) : get_bloginfo('name');
    }

    public function enqueue_login_styles() {
        wp_enqueue_style(
            'mikrotek-wpt-login-css',
            MIKROTEK_WPT_URL . 'assets/css/login.css',
            [],
            MIKROTEK_WPT_VERSION
        );

        $logo       = Mikrotek_WP_Toolkit_Settings::get('login_logo');
        $width      = Mikrotek_WP_Toolkit_Settings::get('login_logo_width');
        $height     = Mikrotek_WP_Toolkit_Settings::get('login_logo_height');
        $bg_image   = Mikrotek_WP_Toolkit_Settings::get('login_background');
        $bg_color   = Mikrotek_WP_Toolkit_Settings::get('login_background_color');
        $btn_color  = Mikrotek_WP_Toolkit_Settings::get('login_button_color');
        $custom_css = Mikrotek_WP_Toolkit_Settings::get('login_custom_css');
        $layout     = Mikrotek_WP_Toolkit_Settings::get('login_layout', 'normal');
        $position   = Mikrotek_WP_Toolkit_Settings::get('login_form_position', 'center');

        $css = '';

        if (!empty($logo)) {
            $css .= 'body.login h1 a { background-image: url("' . esc_url($logo) . '") !important; background-size: contain !important; width: 100% !important; height: 80px !important; }';
        }

        if (!empty($width)) {
            $css .= 'body.login h1 a { width: ' . absint($width) . 'px !important; }';
        }

        if (!empty($height)) {
            $css .= 'body.login h1 a { height: ' . absint($height) . 'px !important; }';
        }

        if (!empty($bg_color)) {
            $css .= 'body.login { background-color: ' . esc_attr($bg_color) . ' !important; }';
        }

        if (!empty($bg_image)) {
            $css .= 'body.login { background-image: url("' . esc_url($bg_image) . '") !important; background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important; }';
        }

        if (!empty($btn_color)) {
            $css .= 'body.login #wp-submit { background-color: ' . esc_attr($btn_color) . ' !important; border-color: ' . esc_attr($btn_color) . ' !important; text-shadow: none !important; box-shadow: none !important; }';
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_login_back_to_site')) {
            $css .= '#backtoblog { display: none !important; }';
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_login_language_switcher')) {
            $css .= '.language-switcher { display: none !important; }';
        }

        if ('sidepanel_left' === $layout) {
            $css .= 'body.login { display: flex; min-height: 100vh; flex-direction: row; }';
            $css .= 'body.login #login { margin: 0; width: 450px; max-width: 100%; min-height: 100vh; background: #ffffff; padding: 40px 30px; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 0 20px rgba(0,0,0,0.1); }';
        } elseif ('sidepanel_right' === $layout) {
            $css .= 'body.login { display: flex; min-height: 100vh; flex-direction: row-reverse; }';
            $css .= 'body.login #login { margin: 0; width: 450px; max-width: 100%; min-height: 100vh; background: #ffffff; padding: 40px 30px; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 0 20px rgba(0,0,0,0.1); }';
        }

        if (!empty($custom_css)) {
            $css .= "\n" . $custom_css;
        }

        if (!empty($css)) {
            wp_add_inline_style('mikrotek-wpt-login-css', $css);
        }
    }

    public function custom_admin_bar_logo($wp_admin_bar) {
        $logo = Mikrotek_WP_Toolkit_Settings::get('admin_logo');
        if (empty($logo)) {
            return;
        }

        $wp_admin_bar->remove_node('wp-logo');

        $wp_admin_bar->add_node([
            'id'    => 'mikrotek-wpt-custom-logo',
            'title' => '<img src="' . esc_url($logo) . '" style="max-height:20px;vertical-align:middle;margin-right:5px;" alt="Logo">',
            'href'  => admin_url(),
            'meta'  => [
                'title' => get_bloginfo('name'),
            ],
        ]);
    }

    public function custom_admin_footer_text($text) {
        $footer_text = Mikrotek_WP_Toolkit_Settings::get('footer_text');
        if (!empty($footer_text)) {
            return esc_html($footer_text);
        }

        $cms_name = Mikrotek_WP_Toolkit_Settings::get('cms_name');
        if (!empty($cms_name)) {
            return sprintf(__('Terima kasih telah menggunakan %s.', 'mikrotek-wp-toolkit'), esc_html($cms_name));
        }

        return $text;
    }

    public function custom_admin_footer_version($version) {
        $cms_name = Mikrotek_WP_Toolkit_Settings::get('cms_name');
        if (!empty($cms_name)) {
            return '';
        }

        return $version;
    }

    public function inject_favicon() {
        $favicon = Mikrotek_WP_Toolkit_Settings::get('favicon');
        if (!empty($favicon)) {
            echo '<link rel="icon" href="' . esc_url($favicon) . '">';
        }
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Branding')) {
    class_alias('Mikrotek_WP_Toolkit_Branding', 'MZI_White_Label_Pro_Branding');
}
