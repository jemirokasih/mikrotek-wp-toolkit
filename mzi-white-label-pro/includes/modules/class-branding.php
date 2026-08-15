<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Branding {

    public function __construct() {
        add_action('login_enqueue_scripts', [$this, 'enqueue_login_styles']);
        add_action('login_head', [$this, 'custom_admin_favicon']);
        add_action('admin_head', [$this, 'custom_admin_favicon']);
        add_action('wp_before_admin_bar_render', [$this, 'replace_admin_bar_logo']);

        add_filter('login_headerurl', [$this, 'login_logo_url']);
        add_filter('login_headertext', [$this, 'login_logo_title']);
        add_filter('login_display_language_dropdown', [$this, 'login_language_dropdown']);
        add_filter('login_body_class', [$this, 'add_login_body_classes']);
        add_filter('admin_footer_text', [$this, 'custom_admin_footer']);
        add_filter('update_footer', '__return_empty_string', 11);
        add_filter('gettext', [$this, 'replace_wordpress_text'], 20, 3);
        add_filter('admin_title', [$this, 'custom_admin_title'], 10, 2);
    }

    public function enqueue_login_styles() {
        $login_logo = MZI_White_Label_Pro_Settings::get('login_logo');
        $login_background = MZI_White_Label_Pro_Settings::get('login_background');
        $inline_css = $this->login_inline_css($login_logo, $login_background);

        if ('' === $inline_css) {
            return;
        }

        wp_enqueue_style(
            'mzi-wlp-login',
            MZI_WLP_URL . 'assets/css/login.css',
            [],
            MZI_WLP_VERSION
        );

        wp_add_inline_style('mzi-wlp-login', $inline_css);
    }

    private function login_inline_css($login_logo, $login_background) {
        $css = '';

        if (!empty($login_logo)) {
            $css .= 'body.login h1 a{background-image:url("' . esc_url($login_logo) . '") !important;}';
        }

        if (!empty($login_background)) {
            $css .= 'body.login{background-image:url("' . esc_url($login_background) . '");}';
        }

        $logo_width = absint(MZI_White_Label_Pro_Settings::get('login_logo_width'));
        $logo_height = absint(MZI_White_Label_Pro_Settings::get('login_logo_height'));
        $background_color = MZI_White_Label_Pro_Settings::get('login_background_color');
        $button_color = MZI_White_Label_Pro_Settings::get('login_button_color');
        $layout = MZI_White_Label_Pro_Settings::get('login_layout', 'normal');
        $form_position = MZI_White_Label_Pro_Settings::get('login_form_position', 'center');

        if ($logo_width > 0) {
            $css .= 'body.login h1 a{width:' . $logo_width . 'px !important;}';
        }

        if ($logo_height > 0) {
            $css .= 'body.login h1 a{height:' . $logo_height . 'px !important;}';
        }

        if (!empty($background_color)) {
            $css .= 'body.login{background-color:' . sanitize_hex_color($background_color) . ';}';
        }

        if (!empty($button_color)) {
            $button_color = sanitize_hex_color($button_color);
            $css .= '.login .button-primary{background:' . $button_color . ';border-color:' . $button_color . ';}';
        }

        if ('sidepanel_left' === $layout) {
            $css .= 'body.login{display:flex;flex-direction:row-reverse;justify-content:space-between;min-height:100vh;padding:0;margin:0;align-items:stretch;}';
            $css .= 'body.login #login{width:420px;max-width:100%;min-height:100vh;margin:0 !important;padding:45px 35px !important;background:#ffffff;box-shadow:4px 0 25px rgba(0,0,0,0.15);display:flex;flex-direction:column;justify-content:center;box-sizing:border-box;}';
        } elseif ('sidepanel_right' === $layout) {
            $css .= 'body.login{display:flex;flex-direction:row;justify-content:space-between;min-height:100vh;padding:0;margin:0;align-items:stretch;}';
            $css .= 'body.login #login{width:420px;max-width:100%;min-height:100vh;margin:0 !important;padding:45px 35px !important;background:#ffffff;box-shadow:-4px 0 25px rgba(0,0,0,0.15);display:flex;flex-direction:column;justify-content:center;box-sizing:border-box;}';
        } else {
            if ('left' === $form_position) {
                $css .= '#login{margin-left:8%;margin-right:auto;}';
            } elseif ('right' === $form_position) {
                $css .= '#login{margin-left:auto;margin-right:8%;}';
            }
        }

        if (MZI_White_Label_Pro_Settings::enabled('hide_login_back_to_site')) {
            $css .= '.login #backtoblog{display:none;}';
        }

        $custom_css = MZI_White_Label_Pro_Settings::get('login_custom_css');

        if ('' !== $custom_css) {
            $css .= wp_strip_all_tags($custom_css);
        }

        return $css;
    }

    public function login_language_dropdown($display) {
        if (MZI_White_Label_Pro_Settings::enabled('hide_login_language_switcher')) {
            return false;
        }

        return $display;
    }

    public function add_login_body_classes($classes) {
        $layout = MZI_White_Label_Pro_Settings::get('login_layout', 'normal');
        if (in_array($layout, ['sidepanel_left', 'sidepanel_right'], true)) {
            $classes[] = 'login-sidepanel';
            $classes[] = 'login-' . str_replace('_', '-', $layout);
        }

        return $classes;
    }

    public function login_logo_url() {
        return home_url();
    }

    public function login_logo_title() {
        return MZI_White_Label_Pro_Settings::get('login_title', get_bloginfo('name'));
    }

    public function replace_admin_bar_logo() {
        $admin_logo = MZI_White_Label_Pro_Settings::get('admin_logo');

        if (empty($admin_logo)) {
            return;
        }

        global $wp_admin_bar;

        $wp_admin_bar->remove_menu('wp-logo');
        $wp_admin_bar->add_menu([
            'id'    => 'mzi-logo',
            'title' => '<img src="' . esc_url($admin_logo) . '" style="height:20px;margin-top:6px;">',
            'href'  => home_url(),
        ]);
    }

    public function custom_admin_favicon() {
        $favicon = MZI_White_Label_Pro_Settings::get('favicon');

        if (empty($favicon)) {
            return;
        }

        echo '<link rel="icon" href="' . esc_url($favicon) . '">';
    }

    public function custom_admin_footer() {
        return MZI_White_Label_Pro_Settings::get('footer_text', '');
    }

    public function custom_admin_title($admin_title, $title) {
        $cms_name = MZI_White_Label_Pro_Settings::get('cms_name', 'CMS');

        return $title . ' ‹ ' . $cms_name;
    }

    public function replace_wordpress_text($translated_text, $text, $domain) {
        if (MZI_White_Label_Pro_Compatibility::is_wordfence_context($domain)) {
            return $translated_text;
        }

        $cms_name = MZI_White_Label_Pro_Settings::get('cms_name', 'CMS');

        $replace = [
            'WordPress' => $cms_name,
            'Powered by WordPress' => $cms_name,
            'Thank you for creating with WordPress.' => '',
            'Howdy' => 'Halo',
        ];

        if (isset($replace[$translated_text])) {
            return $replace[$translated_text];
        }

        return $translated_text;
    }
}
