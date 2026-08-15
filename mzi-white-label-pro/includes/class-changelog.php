<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Changelog {

    public static function get_all() {
        return self::entries();
    }

    public static function entries() {
        return [
            '3.0.2' => [
                'Fixed' => [
                    'Fixed WordPress.org Plugin Check findings.',
                    'Removed duplicate Plugin URI from the plugin header.',
                    'Updated WordPress.org contributor username and tested-up-to metadata.',
                    'Improved escaping and request handling compliance.',
                ],
            ],
            '3.0.1' => [
                'Added' => [
                    'Persistent changelog registry so previous release notes stay visible.',
                    'Short helper descriptions under every settings option.',
                    'Dashboard widget hide options for Yoast SEO, Elementor, and Yoast SEO / Wincher widgets.',
                    'Client-mode option to hide update notifications from non-administrator users.',
                ],
                'Changed' => [
                    'Changelog tab now renders versioned entries instead of one overwritten text block.',
                ],
            ],
            '3.0.0' => [
                'Added' => [
                    'Modular plugin architecture.',
                    'Admin menu manager for client mode.',
                    'Dashboard widget manager.',
                    'Login page customizer.',
                    'Compatibility guard for Wordfence.',
                    'Separate admin, login, and dark admin assets.',
                ],
                'Changed' => [
                    'Main plugin file is now a lightweight bootstrap.',
                    'Settings UI now uses a field registry.',
                    'Existing mzi_white_label_settings option is preserved for upgrade compatibility.',
                ],
                'Compatibility' => [
                    'Wordfence admin pages, translated text, protected menu slugs, and heartbeat behavior are protected by default.',
                ],
            ],
        ];
    }
}
