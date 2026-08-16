<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Maintenance {

    public function __construct() {
        add_action('template_redirect', [$this, 'handle_maintenance_mode']);
    }

    public function handle_maintenance_mode() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('enable_maintenance_mode')) {
            return;
        }

        // Allow Administrators to bypass Maintenance Mode
        if (current_user_can('manage_options')) {
            return;
        }

        // Allow login page access
        if (in_array($GLOBALS['pagenow'], ['wp-login.php'], true)) {
            return;
        }

        $mode     = Mikrotek_WP_Toolkit_Settings::get('maintenance_mode_type', '503');
        $title    = Mikrotek_WP_Toolkit_Settings::get('maintenance_title', 'Under Maintenance');
        $headline = Mikrotek_WP_Toolkit_Settings::get('maintenance_headline', 'Kami Akan Segera Kembali!');
        $message  = Mikrotek_WP_Toolkit_Settings::get('maintenance_message', 'Situs web kami saat ini sedang dalam pemeliharaan rutin. Silakan kembali beberapa saat lagi.');

        $logo = Mikrotek_WP_Toolkit_Settings::get('maintenance_logo');
        if (empty($logo)) {
            $logo = Mikrotek_WP_Toolkit_Settings::get('login_logo');
        }
        if (empty($logo)) {
            $logo = Mikrotek_WP_Toolkit_Settings::get('admin_logo');
        }

        $bg_color = Mikrotek_WP_Toolkit_Settings::get('maintenance_bg_color', '#0f172a');

        if ('503' === $mode) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 3600');
        }

        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($title); ?> - <?php echo esc_html(get_bloginfo('name')); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    background-color: <?php echo esc_attr($bg_color); ?>;
                    color: #f8fafc;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    padding: 20px;
                    text-align: center;
                }
                .mikrotek-maintenance-card {
                    background: rgba(30, 41, 59, 0.85);
                    backdrop-filter: blur(12px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 16px;
                    padding: 40px 30px;
                    max-width: 540px;
                    width: 100%;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                }
                .mikrotek-maintenance-logo {
                    max-width: 220px;
                    max-height: 110px;
                    width: auto;
                    height: auto;
                    margin-bottom: 24px;
                    object-fit: contain;
                }
                h1 {
                    font-size: 26px;
                    font-weight: 700;
                    margin-bottom: 12px;
                    color: #ffffff;
                }
                p {
                    font-size: 15px;
                    line-height: 1.6;
                    color: #cbd5e1;
                    margin-bottom: 24px;
                }
                .badge {
                    display: inline-block;
                    background: rgba(79, 70, 229, 0.2);
                    color: #818cf8;
                    border: 1px solid rgba(129, 140, 248, 0.3);
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 16px;
                }
            </style>
        </head>
        <body>
            <div class="mikrotek-maintenance-card">
                <?php if (!empty($logo)) : ?>
                    <img src="<?php echo esc_url($logo); ?>" alt="Logo" class="mikrotek-maintenance-logo">
                <?php endif; ?>
                <div>
                    <span class="badge"><?php echo esc_html($title); ?></span>
                </div>
                <h1><?php echo esc_html($headline); ?></h1>
                <p><?php echo esc_html($message); ?></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}


