<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Admin_Theme {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_dark_theme']);
    }

    public function enqueue_dark_theme() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('dark_admin')) {
            return;
        }

        wp_enqueue_style(
            'mikrotek-wpt-admin-dark',
            MIKROTEK_WPT_URL . 'assets/css/admin-dark.css',
            [],
            MIKROTEK_WPT_VERSION
        );
    }
}


