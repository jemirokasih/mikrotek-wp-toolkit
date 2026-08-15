<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Compatibility {

    public function __construct() {
        add_filter('gettext', [$this, 'preserve_protected_text'], 1, 3);
    }

    public static function is_protected_enabled() {
        return Mikrotek_WP_Toolkit_Settings::enabled('protect_wordfence', true);
    }

    public static function is_wordfence_context() {
        if (!self::is_protected_enabled()) {
            return false;
        }

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if (false !== strpos($page, 'Wordfence')) {
            return true;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

        return false !== strpos($request_uri, 'wordfence');
    }

    public static function is_protected_menu_slug($slug) {
        if (!self::is_protected_enabled()) {
            return false;
        }

        return false !== strpos((string) $slug, 'Wordfence');
    }

    public function preserve_protected_text($translated_text, $text, $domain) {
        if ('wordfence' === $domain && self::is_protected_enabled()) {
            return $text;
        }

        return $translated_text;
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Compatibility')) {
    class_alias('Mikrotek_WP_Toolkit_Compatibility', 'MZI_White_Label_Pro_Compatibility');
}
