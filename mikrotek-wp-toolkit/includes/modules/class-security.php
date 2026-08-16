<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Security {

    public function __construct() {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');

        add_filter('the_generator', '__return_empty_string');
        add_filter('emoji_svg_url', '__return_false');
        add_filter('login_errors', [$this, 'generic_login_errors']);
        add_action('admin_menu', [$this, 'client_mode_restriction'], 999);
        add_action('admin_init', [$this, 'hide_update_notices']);
        add_action('admin_head', [$this, 'hide_update_notice_styles']);

        add_action('init', [$this, 'handle_custom_login_url'], 1);
        add_filter('site_url', [$this, 'filter_login_url'], 10, 3);
        add_filter('wp_login_url', [$this, 'filter_login_url_single'], 10, 2);
    }

    public function handle_custom_login_url() {
        $slug = trim(Mikrotek_WP_Toolkit_Settings::get('custom_login_slug'));
        if (empty($slug)) {
            return;
        }

        $slug = sanitize_title($slug);
        $request_uri = isset($_SERVER['REQUEST_URI']) ? rawurldecode(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $req_path = untrailingslashit(wp_parse_url($request_uri, PHP_URL_PATH));

        $custom_path = untrailingslashit(wp_parse_url(home_url('/' . $slug), PHP_URL_PATH));
        $wp_login_path = untrailingslashit(wp_parse_url(site_url('wp-login.php'), PHP_URL_PATH));

        // 1. MATCH: User requested custom login slug (e.g. /masuk or /rahasia)
        if ($req_path === $custom_path) {
            remove_action('template_redirect', 'redirect_canonical');
            add_action('wp_loaded', function() {
                global $pagenow, $user_login, $error, $user_identity, $interim_login, $action;
                $pagenow       = 'wp-login.php';
                $user_login    = '';
                $error         = '';
                $user_identity = '';
                $interim_login = false;
                $action        = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : 'login';

                $GLOBALS['pagenow']       = 'wp-login.php';
                $GLOBALS['user_login']    = '';
                $GLOBALS['error']         = '';
                $GLOBALS['user_identity'] = '';
                $GLOBALS['interim_login'] = false;

                require_once ABSPATH . 'wp-login.php';
                die;
            });
            return;
        }

        // 2. BLOCK: Direct /wp-login.php or /wp-admin access for non-logged in users when custom slug is set
        if (!is_user_logged_in()) {
            if ($req_path === $wp_login_path || false !== strpos($request_uri, 'wp-login.php')) {
                if (isset($_GET['action']) && in_array($_GET['action'], ['logout', 'postpass', 'lostpassword', 'resetpass', 'register'], true)) {
                    return;
                }

                $this->render_404_page();
            }

            if (is_admin() && !wp_doing_ajax()) {
                $this->render_404_page();
            }
        }
    }

    private function render_404_page() {
        global $wp_query;
        if ($wp_query) {
            $wp_query->set_404();
        }
        status_header(404);
        nocache_headers();

        $template = get_404_template();
        if ($template && file_exists($template)) {
            include $template;
        } else {
            wp_die(__('Halaman tidak ditemukan.', 'mikrotek-wp-toolkit'), '404 Not Found', ['response' => 404]);
        }
        exit;
    }

    public function filter_login_url($url, $path, $scheme) {
        if ('login' !== $scheme && 'login_post' !== $scheme) {
            return $url;
        }

        $slug = trim(Mikrotek_WP_Toolkit_Settings::get('custom_login_slug'));
        if (empty($slug)) {
            return $url;
        }

        $slug = sanitize_title($slug);

        return home_url('/' . $slug);
    }

    public function filter_login_url_single($login_url, $redirect) {
        $slug = trim(Mikrotek_WP_Toolkit_Settings::get('custom_login_slug'));
        if (empty($slug)) {
            return $login_url;
        }

        $slug = sanitize_title($slug);
        $url = home_url('/' . $slug);

        if (!empty($redirect)) {
            $url = add_query_arg('redirect_to', urlencode($redirect), $url);
        }

        return $url;
    }

    public function generic_login_errors($error) {
        if (Mikrotek_WP_Toolkit_Settings::enabled('hide_login_errors')) {
            return __('<strong>ERROR</strong>: Invalid username, email address, or password.', 'mikrotek-wp-toolkit');
        }

        return $error;
    }

    public function client_mode_restriction() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('client_mode') || current_user_can('administrator')) {
            return;
        }

        remove_menu_page('options-general.php');
        remove_menu_page('plugins.php');
        remove_menu_page('tools.php');
    }

    public function hide_update_notices() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('hide_update_notices') || current_user_can('administrator')) {
            return;
        }

        remove_action('admin_notices', 'update_nag', 3);
        remove_action('network_admin_notices', 'update_nag', 3);
        add_filter('pre_site_transient_update_core', '__return_null');
        add_filter('pre_site_transient_update_plugins', '__return_null');
        add_filter('pre_site_transient_update_themes', '__return_null');
    }

    public function hide_update_notice_styles() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('hide_update_notices') || current_user_can('administrator')) {
            return;
        }

        echo '<style>.update-nag, .plugin-update-tr, .theme-update-message, #wp-admin-bar-updates { display: none !important; }</style>';
    }
}


