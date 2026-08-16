<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Advanced {

    public function __construct() {
        add_filter('xmlrpc_enabled', [$this, 'xmlrpc_control']);
        add_filter('use_block_editor_for_post', [$this, 'gutenberg_control']);
        add_action('init', [$this, 'comments_control']);
        add_action('init', [$this, 'heartbeat_control'], 1);

        add_filter('rest_authentication_errors', [$this, 'rest_api_auth_control']);
        add_filter('rest_endpoints', [$this, 'rest_api_endpoints_control']);
    }

    public function xmlrpc_control($enabled) {
        if (!empty(Mikrotek_WP_Toolkit_Settings::get('disable_xmlrpc'))) {
            return false;
        }

        return $enabled;
    }

    public function gutenberg_control($enabled) {
        if (!empty(Mikrotek_WP_Toolkit_Settings::get('disable_gutenberg'))) {
            return false;
        }

        return $enabled;
    }

    public function comments_control() {
        if (empty(Mikrotek_WP_Toolkit_Settings::get('disable_comments'))) {
            return;
        }

        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
    }

    public function heartbeat_control() {
        if (empty(Mikrotek_WP_Toolkit_Settings::get('disable_heartbeat'))) {
            return;
        }

        if (Mikrotek_WP_Toolkit_Compatibility::is_wordfence_context()) {
            return;
        }

        wp_deregister_script('heartbeat');
    }

    public function rest_api_auth_control($result) {
        if (!empty($result)) {
            return $result;
        }

        if (Mikrotek_WP_Toolkit_Settings::enabled('disable_rest_api')) {
            if (!is_user_logged_in()) {
                return new WP_Error(
                    'rest_login_required',
                    __('REST API access is restricted to authenticated users.', 'mikrotek-wp-toolkit'),
                    ['status' => rest_authorization_required_code()]
                );
            }
        }

        return $result;
    }

    public function rest_api_endpoints_control($endpoints) {
        if (Mikrotek_WP_Toolkit_Settings::enabled('disable_rest_users')) {
            if (!is_user_logged_in() || !current_user_can('list_users')) {
                if (isset($endpoints['/wp/v2/users'])) {
                    unset($endpoints['/wp/v2/users']);
                }
                if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
                }
            }
        }

        return $endpoints;
    }
}


