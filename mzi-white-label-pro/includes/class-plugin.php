<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Plugin {

    private static $instance = null;

    public static function init() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->register_modules();
    }

    private function load_dependencies() {
        require_once MZI_WLP_PATH . 'includes/class-changelog.php';
        require_once MZI_WLP_PATH . 'includes/class-admin.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-compatibility.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-branding.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-admin-menu-manager.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-dashboard.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-security.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-email.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-advanced.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-admin-theme.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-misc.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-tools.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-redirects.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-audit.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-about.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-shortcodes.php';
        require_once MZI_WLP_PATH . 'includes/modules/class-maintenance.php';
    }

    private function register_modules() {
        new MZI_White_Label_Pro_Admin();
        new MZI_White_Label_Pro_Compatibility();
        new MZI_White_Label_Pro_Branding();
        new MZI_White_Label_Pro_Admin_Menu_Manager();
        new MZI_White_Label_Pro_Dashboard();
        new MZI_White_Label_Pro_Security();
        new MZI_White_Label_Pro_Email();
        new MZI_White_Label_Pro_Advanced();
        new MZI_White_Label_Pro_Admin_Theme();
        new MZI_White_Label_Pro_Misc();
        new MZI_White_Label_Pro_Redirects();
        new MZI_White_Label_Pro_Audit();
        new MZI_White_Label_Pro_About();
        new MZI_White_Label_Pro_Shortcodes();
        new MZI_White_Label_Pro_Maintenance();
    }
}
