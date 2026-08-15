<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Security {

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
        $slug = trim(MZI_White_Label_Pro_Settings::get('custom_login_slug'));
        if (empty($slug)) {
            return;
        }

        $slug = sanitize_title($slug);
        $request_uri = isset($_SERVER['REQUEST_URI']) ? rawurldecode(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $req_path = untrailingslashit(wp_parse_url($request_uri, PHP_URL_PATH));

        $custom_path = untrailingslashit(wp_parse_url(home_url('/' . $slug), PHP_URL_PATH));
        $wp_login_path = untrailingslashit(wp_parse_url(site_url('wp-login.php'), PHP_URL_PATH));

        // 1. MATCH: User requested custom login slug (e.g. /rahasia or /wordpress/rahasia)
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

        // 2. BLOCK: Direct /wp-login.php or unauthenticated /wp-admin access
        $wp_admin_path = untrailingslashit(wp_parse_url(admin_url(), PHP_URL_PATH));

        if (!is_user_logged_in() && !wp_doing_ajax()) {
            if ($req_path === $wp_admin_path || strpos($req_path, 'wp-admin') !== false || $req_path === $wp_login_path || strpos($req_path, 'wp-login.php') !== false) {
                if (isset($_GET['action']) && in_array($_GET['action'], ['logout', 'postpass', 'lostpassword', 'resetpass', 'register'], true)) {
                    return;
                }

                if (isset($_POST['log']) || isset($_POST['wp-submit'])) {
                    return;
                }

                add_action('wp_loaded', function() {
                    global $wp_query;
                    status_header(404);
                    nocache_headers();
                    if ($wp_query) {
                        $wp_query->set_404();
                    }
                    include get_query_template('404');
                    die;
                });
            }
        }
    }

    public function filter_login_url($url, $path, $scheme) {
        $slug = trim(MZI_White_Label_Pro_Settings::get('custom_login_slug'));
        if (empty($slug)) {
            return $url;
        }

        if ('wp-login.php' === $path || strpos($url, 'wp-login.php') !== false) {
            return home_url('/' . sanitize_title($slug));
        }

        return $url;
    }

    public function filter_login_url_single($login_url, $redirect) {
        $slug = trim(MZI_White_Label_Pro_Settings::get('custom_login_slug'));
        if (empty($slug)) {
            return $login_url;
        }

        $custom_url = home_url('/' . sanitize_title($slug));
        if (!empty($redirect)) {
            $custom_url = add_query_arg('redirect_to', urlencode($redirect), $custom_url);
        }

        return $custom_url;
    }

    public function generic_login_errors($error) {
        if (MZI_White_Label_Pro_Settings::enabled('hide_login_errors')) {
            return __('<strong>ERROR</strong>: Invalid username, email address, or password.', 'mzi-white-label-pro');
        }

        return $error;
    }

    public function client_mode_restriction() {
        if (empty(MZI_White_Label_Pro_Settings::get('client_mode'))) {
            return;
        }

        if (current_user_can('administrator')) {
            return;
        }

        remove_menu_page('plugins.php');
        remove_menu_page('themes.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
    }

    public function hide_update_notices() {
        if (!$this->should_hide_update_notices()) {
            return;
        }

        remove_action('admin_notices', 'update_nag', 3);
        remove_action('network_admin_notices', 'update_nag', 3);
    }

    public function hide_update_notice_styles() {
        if (!$this->should_hide_update_notices()) {
            return;
        }

        ?>
        <style>
            .update-nag,
            .plugin-update-tr,
            .theme-update-message,
            .update-message,
            .notice.update-message,
            .notice.notice-warning.update-message,
            .wp-list-table .update,
            .wp-menu-name .update-plugins {
                display: none !important;
            }
        </style>
        <?php
    }

    private function should_hide_update_notices() {
        if (!MZI_White_Label_Pro_Settings::enabled('client_mode')) {
            return false;
        }

        if (!MZI_White_Label_Pro_Settings::enabled('hide_update_notices')) {
            return false;
        }

        return !current_user_can('administrator');
    }
}
