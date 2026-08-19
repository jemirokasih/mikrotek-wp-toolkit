<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Changelog {

    public static function get_all() {
        return self::entries();
    }

    public static function entries() {
        return [
            '4.0.1' => [
                'Fixed' => [
                    'Perbaikan fitur Default Featured Image agar terpanggil sempurna di widget Elementor Posts, Loop Grid, Archive Posts, Portfolio, dan Dynamic Tags.',
                    'Penambahan fallback filter WordPress Core & Elementor: `post_thumbnail_id`, `get_post_metadata`, `post_thumbnail_url`, `wp_get_attachment_image_src`, serta `elementor/image_size/get_attachment_image_html`.',
                ],
            ],
            '4.0.0' => [
                'Rebranding' => [
                    'Resmi diluncurkan sebagai 100% Standalone Plugin Mikrotek WP Toolkit.',
                ],
                'Added' => [
                    'Sistem arsitektur modular v4 baru untuk performa dan kemudahan pemeliharaan.',
                    'Overhaul UI/UX Admin Menus dengan chip preset 1-klik interaktif dan panduan visual lengkap.',
                    'Enterprise MySQL Custom Table Audit Trail Engine (wp_mikrotek_audit_logs) dengan status kesehatan sistem, persetujuan aktivasi, opsi deaktivasi (Keep/Truncate/Drop), dan auto-prune retention.',
                    'Standalone Redirection Manager (301 Permanent, 302/307 Temporary) dengan hit counter.',
                    'Database URL Migration Tool (Search & Replace) dengan dukungan Safe Serialized Handler dan Regular Expression (Regex).',
                    'Lightweight Broken Link Checker (on-demand scan tanpa beban background server).',
                    'Custom Login URL Changer (/masuk, /rahasia) dan blokir direct access /wp-login.php & /wp-admin ke 404 Not Found.',
                    'Username Enumeration Protection (Obscure Login Errors & REST API /wp/v2/users blocking).',
                    'Kumpulan Utility Shortcodes ([mikrotek_year], [wpt_year], dll) beserta dashboard dokumentasi & live preview.',
                    'Maintenance Mode / Coming Soon dengan kustomisasi logo, pesan, warna background, dan status HTTP 503/200.',
                    'Sub-menu About Info yang menampilkan versi terpasang, ringkasan sistem server, dan riwayat changelog.',
                ],
                'Refactored' => [
                    'Pembersihan total seluruh legacy stub files, backward compatibility class_alias, dan konstanta MZI White Label Pro.',
                ],
                'Security' => [
                    'Peningkatan keamanan input validation, sanitization, nonce verification, dan output escaping di seluruh modul.',
                ],
            ],
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
            ],
        ];
    }
}
