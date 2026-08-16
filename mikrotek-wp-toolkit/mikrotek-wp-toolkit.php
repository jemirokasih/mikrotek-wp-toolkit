<?php
/**
 * Plugin Name: Mikrotek WP Toolkit
 * Description: Professional White Label Branding, Security, Audit Trail, Redirection & Utility Toolkit for WordPress.
 * Version: 4.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: PT Mikrotek Zemiro Indonesia
 * Author URI: https://mzi.co.id
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mikrotek-wp-toolkit
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MIKROTEK_WPT_VERSION', '4.0.0');
define('MIKROTEK_WPT_FILE', __FILE__);
define('MIKROTEK_WPT_PATH', plugin_dir_path(__FILE__));
define('MIKROTEK_WPT_URL', plugin_dir_url(__FILE__));

require_once MIKROTEK_WPT_PATH . 'includes/class-settings.php';
require_once MIKROTEK_WPT_PATH . 'includes/class-plugin.php';

add_action('plugins_loaded', ['Mikrotek_WP_Toolkit_Plugin', 'init']);
