<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Plugin {

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
        require_once MIKROTEK_WPT_PATH . 'includes/class-admin.php';
        require_once MIKROTEK_WPT_PATH . 'includes/class-changelog.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-compatibility.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-branding.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-admin-menu-manager.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-dashboard.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-security.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-email.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-advanced.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-admin-theme.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-misc.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-tools.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-redirects.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-audit-table.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-audit.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-about.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-shortcodes.php';
        require_once MIKROTEK_WPT_PATH . 'includes/modules/class-maintenance.php';

        $this->setup_cron_tasks();
    }

    private function setup_cron_tasks() {
        add_action('mikrotek_wpt_audit_daily_prune', function() {
            $retention_days = absint(Mikrotek_WP_Toolkit_Settings::get('audit_log_retention', 30));
            if ($retention_days > 0) {
                Mikrotek_WP_Toolkit_Audit_Table::prune_expired_logs($retention_days);
            }
        });

        if (!wp_next_scheduled('mikrotek_wpt_audit_daily_prune')) {
            wp_schedule_event(time(), 'daily', 'mikrotek_wpt_audit_daily_prune');
        }
    }

    private function register_modules() {
        new Mikrotek_WP_Toolkit_Admin();
        new Mikrotek_WP_Toolkit_Compatibility();
        new Mikrotek_WP_Toolkit_Branding();
        new Mikrotek_WP_Toolkit_Admin_Menu_Manager();
        new Mikrotek_WP_Toolkit_Dashboard();
        new Mikrotek_WP_Toolkit_Security();
        new Mikrotek_WP_Toolkit_Email();
        new Mikrotek_WP_Toolkit_Advanced();
        new Mikrotek_WP_Toolkit_Admin_Theme();
        new Mikrotek_WP_Toolkit_Misc();
        new Mikrotek_WP_Toolkit_Redirects();
        new Mikrotek_WP_Toolkit_Audit();
        new Mikrotek_WP_Toolkit_About();
        new Mikrotek_WP_Toolkit_Shortcodes();
        new Mikrotek_WP_Toolkit_Maintenance();
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Plugin')) {
    class_alias('Mikrotek_WP_Toolkit_Plugin', 'MZI_White_Label_Pro_Plugin');
}
