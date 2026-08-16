<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Audit_Table {

    const TABLE_NAME = 'mikrotek_audit_logs';

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    public static function table_exists() {
        global $wpdb;
        $table_name = self::get_table_name();
        $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
        return $wpdb->get_var($query) === $table_name;
    }

    public static function create_table() {
        global $wpdb;

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            user varchar(100) NOT NULL DEFAULT '',
            role varchar(100) NOT NULL DEFAULT '',
            event varchar(150) NOT NULL DEFAULT '',
            details text NOT NULL,
            ip varchar(45) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY timestamp (timestamp),
            KEY user (user),
            KEY event (event),
            KEY ip (ip)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $created = self::table_exists();

        if ($created) {
            update_option('mikrotek_wpt_audit_table_last_error', '');
            self::migrate_from_options();
            return [
                'success' => true,
                'message' => 'Tabel basis data wp_mikrotek_audit_logs berhasil dibuat & siap digunakan.',
            ];
        }

        $last_error = !empty($wpdb->last_error) ? $wpdb->last_error : 'Gagal mengeksekusi dbDelta query. Periksa hak akses database.';
        update_option('mikrotek_wpt_audit_table_last_error', $last_error);

        return [
            'success' => false,
            'message' => $last_error,
        ];
    }

    public static function get_health_status() {
        global $wpdb;

        $exists = self::table_exists();
        if (!$exists) {
            $last_error = get_option('mikrotek_wpt_audit_table_last_error', '');
            return [
                'exists'          => false,
                'status_code'     => 'NOT_CREATED',
                'status_label'    => 'BELUM DIBUAT',
                'status_color'    => '#dc2626',
                'bg_color'        => '#fee2e2',
                'total_rows'      => 0,
                'size_formatted'  => '0 KB',
                'last_event'      => '-',
                'last_error'      => $last_error,
            ];
        }

        $table_name = self::get_table_name();
        $total_rows = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
        $last_event = $wpdb->get_var("SELECT timestamp FROM {$table_name} ORDER BY id DESC LIMIT 1");

        // Calculate size in KB
        $query_size = $wpdb->prepare(
            "SELECT (data_length + index_length) / 1024 AS size_kb
             FROM information_schema.TABLES
             WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        );
        $size_kb = (float) $wpdb->get_var($query_size);
        $size_formatted = ($size_kb > 1024) ? number_format($size_kb / 1024, 2) . ' MB' : number_format($size_kb, 2) . ' KB';

        return [
            'exists'          => true,
            'status_code'     => 'HEALTHY',
            'status_label'    => 'HEALTHY / AKTIF',
            'status_color'    => '#166534',
            'bg_color'        => '#dcfce7',
            'total_rows'      => $total_rows,
            'size_formatted'  => $size_formatted,
            'last_event'      => !empty($last_event) ? $last_event : 'Belum Ada Log',
            'last_error'      => '',
        ];
    }

    public static function truncate_table() {
        global $wpdb;

        if (!self::table_exists()) {
            return false;
        }

        $table_name = self::get_table_name();
        $wpdb->query("TRUNCATE TABLE {$table_name}");
        return true;
    }

    public static function drop_table() {
        global $wpdb;

        if (!self::table_exists()) {
            return true;
        }

        $table_name = self::get_table_name();
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        delete_option('mikrotek_wpt_audit_table_last_error');
        return !self::table_exists();
    }

    public static function migrate_from_options() {
        global $wpdb;

        if (!self::table_exists()) {
            return;
        }

        $table_name = self::get_table_name();

        // Check if legacy logs exist in wp_options
        $old_logs = get_option('mikrotek_wp_toolkit_audit_logs', []);
        if (empty($old_logs) || !is_array($old_logs)) {
            return;
        }

        foreach (array_reverse($old_logs) as $log) {
            if (empty($log['timestamp']) || empty($log['event'])) {
                continue;
            }

            $wpdb->insert(
                $table_name,
                [
                    'timestamp' => sanitize_text_field($log['timestamp']),
                    'user'      => isset($log['user']) ? sanitize_text_field($log['user']) : 'Guest / System',
                    'role'      => isset($log['role']) ? sanitize_text_field($log['role']) : 'none',
                    'event'     => sanitize_text_field($log['event']),
                    'details'   => isset($log['details']) ? sanitize_text_field($log['details']) : '',
                    'ip'        => isset($log['ip']) ? sanitize_text_field($log['ip']) : 'Unknown IP',
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s']
            );
        }

        // Clear option logs once migrated to prevent duplicate imports
        delete_option('mikrotek_wp_toolkit_audit_logs');
    }

    public static function prune_expired_logs($days) {
        global $wpdb;

        $days = absint($days);
        if ($days <= 0 || !self::table_exists()) {
            return 0;
        }

        $table_name = self::get_table_name();
        $sql = $wpdb->prepare(
            "DELETE FROM {$table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        );

        return (int) $wpdb->query($sql);
    }
}


