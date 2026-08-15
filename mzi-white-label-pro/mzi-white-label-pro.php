<?php
/**
 * Plugin Name: MZI White Label Pro
 * Description: Professional White Label Branding Plugin for WordPress.
 * Version: 4.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: PT Mikrotek Zemiro Indonesia
 * Author URI: https://mzi.co.id
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mzi-white-label-pro
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MZI_WLP_VERSION', '4.0.0');
define('MZI_WLP_FILE', __FILE__);
define('MZI_WLP_PATH', plugin_dir_path(__FILE__));
define('MZI_WLP_URL', plugin_dir_url(__FILE__));

require_once MZI_WLP_PATH . 'includes/class-settings.php';
require_once MZI_WLP_PATH . 'includes/class-plugin.php';

add_action('plugins_loaded', ['MZI_White_Label_Pro_Plugin', 'init']);
