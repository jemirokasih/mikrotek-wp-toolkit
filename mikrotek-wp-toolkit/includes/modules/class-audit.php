<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Audit {

    const OPTION_LOGS = 'mikrotek_wp_toolkit_audit_logs';
    const OLD_OPTION_LOGS = 'mzi_white_label_audit_logs';

    public function __construct() {
        add_action('admin_init', [$this, 'handle_export_csv']);
        add_action('wp_login', [$this, 'on_user_login'], 10, 2);
        add_action('wp_login_failed', [$this, 'on_login_failed']);
        add_action('wp_logout', [$this, 'on_user_logout']);
        add_action('save_post', [$this, 'on_save_post'], 10, 3);
        add_action('delete_post', [$this, 'on_delete_post']);
        add_action('activated_plugin', [$this, 'on_plugin_activated']);
        add_action('deactivated_plugin', [$this, 'on_plugin_deactivated']);
        add_action('deleted_plugin', [$this, 'on_plugin_deleted'], 10, 2);
        add_action('switch_theme', [$this, 'on_theme_switched']);
        add_action('deleted_theme', [$this, 'on_theme_deleted'], 10, 2);
        add_action('upgrader_process_complete', [$this, 'on_upgrader_complete'], 10, 2);
    }

    public static function is_enabled() {
        return Mikrotek_WP_Toolkit_Settings::enabled('enable_audit_trail');
    }

    public static function get_logs() {
        $logs = get_option(self::OPTION_LOGS, null);
        if (null === $logs || false === $logs) {
            $old_logs = get_option(self::OLD_OPTION_LOGS, []);
            if (!empty($old_logs) && is_array($old_logs)) {
                update_option(self::OPTION_LOGS, $old_logs);
                return $old_logs;
            }
            $logs = [];
        }
        return is_array($logs) ? $logs : [];
    }

    public static function log_event($event, $details = '', $username = null) {
        if (!self::is_enabled()) {
            return;
        }

        if (null !== $username) {
            $user_label = $username;
            $user_role  = 'none';
        } else {
            $user = wp_get_current_user();
            $user_label = ($user && $user->exists()) ? $user->user_login : 'Guest / System';
            $user_role  = ($user && !empty($user->roles)) ? implode(', ', $user->roles) : 'none';
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'Unknown IP';

        $logs = self::get_logs();

        $new_entry = [
            'id'        => 'log_' . uniqid(),
            'timestamp' => current_time('mysql'),
            'user'      => $user_label,
            'role'      => $user_role,
            'event'     => $event,
            'details'   => $details,
            'ip'        => $ip,
        ];

        array_unshift($logs, $new_entry);

        if (count($logs) > 500) {
            $logs = array_slice($logs, 0, 500);
        }

        update_option(self::OPTION_LOGS, $logs);
    }

    public function handle_export_csv() {
        if (!isset($_GET['page']) || !in_array($_GET['page'], ['mikrotek-wp-toolkit-audit', 'mzi-white-label-audit'], true)) {
            return;
        }

        if (!isset($_GET['action']) || 'export_csv' !== $_GET['action']) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mikrotek-wp-toolkit'));
        }

        check_admin_referer('mikrotek_wpt_audit_export_action', 'mikrotek_wpt_nonce');

        $logs = self::get_logs();

        $search_query = isset($_GET['s']) ? trim(sanitize_text_field(wp_unslash($_GET['s']))) : '';
        if (!empty($search_query) && !empty($logs)) {
            $logs = array_values(array_filter($logs, function($log) use ($search_query) {
                $q = strtolower($search_query);
                return (
                    strpos(strtolower(isset($log['user']) ? $log['user'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['role']) ? $log['role'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['event']) ? $log['event'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['details']) ? $log['details'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['ip']) ? $log['ip'] : ''), $q) !== false
                );
            }));
        }

        $filename = 'audit-trail-logs-' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['Timestamp', 'User', 'Role', 'Event', 'Details', 'IP Address']);

        foreach ($logs as $log) {
            fputcsv($output, [
                isset($log['timestamp']) ? $log['timestamp'] : '',
                isset($log['user']) ? $log['user'] : '',
                isset($log['role']) ? $log['role'] : '',
                isset($log['event']) ? $log['event'] : '',
                isset($log['details']) ? $log['details'] : '',
                isset($log['ip']) ? $log['ip'] : '',
            ]);
        }

        fclose($output);
        exit;
    }

    public function on_user_login($user_login, $user) {
        self::log_event('User Login Success', 'Pengguna berhasil masuk ke dashboard WordPress.', $user_login);
    }

    public function on_login_failed($username) {
        self::log_event('User Login Failed', 'Percobaan login gagal untuk username: ' . sanitize_text_field($username), $username);
    }

    public function on_user_logout() {
        self::log_event('User Logout', 'Pengguna keluar dari akun.');
    }

    public function on_save_post($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (in_array($post->post_type, ['revision', 'nav_menu_item'], true)) {
            return;
        }

        $action_label = $update ? 'Update Konten' : 'Buat Konten Baru';
        self::log_event($action_label, sprintf('%s (ID: %d, Type: %s)', $post->post_title, $post_id, $post->post_type));
    }

    public function on_delete_post($post_id) {
        $post = get_post($post_id);
        if ($post && !in_array($post->post_type, ['revision', 'nav_menu_item'], true)) {
            self::log_event('Hapus Konten', sprintf('%s (ID: %d, Type: %s)', $post->post_title, $post_id, $post->post_type));
        }
    }

    public function on_plugin_activated($plugin) {
        self::log_event('Aktivasi Plugin', 'Plugin diaktifkan: ' . sanitize_text_field($plugin));
    }

    public function on_plugin_deactivated($plugin) {
        self::log_event('Deaktivasi Plugin', 'Plugin dinonaktifkan: ' . sanitize_text_field($plugin));
    }

    public function on_plugin_deleted($plugin_file, $deleted) {
        if ($deleted) {
            self::log_event('Hapus Plugin', 'Plugin dihapus: ' . sanitize_text_field($plugin_file));
        }
    }

    public function on_theme_switched($theme_name) {
        self::log_event('Ganti Tema', 'Tema aktif diubah ke: ' . sanitize_text_field($theme_name));
    }

    public function on_theme_deleted($stylesheet, $deleted) {
        if ($deleted) {
            self::log_event('Hapus Tema', 'Tema dihapus: ' . sanitize_text_field($stylesheet));
        }
    }

    public function on_upgrader_complete($upgrader_object, $options) {
        if (!is_array($options) || empty($options['action']) || empty($options['type'])) {
            return;
        }

        $action = sanitize_key($options['action']);
        $type   = sanitize_key($options['type']);

        if ('install' === $action) {
            $label = ('plugin' === $type) ? 'Install Plugin' : ('theme' === $type ? 'Install Tema' : 'Install Core');
            self::log_event($label, sprintf('Instalasi baru untuk %s.', $type));
        } elseif ('update' === $action) {
            $label = ('plugin' === $type) ? 'Update Plugin' : ('theme' === $type ? 'Update Tema' : 'Update Core');
            self::log_event($label, sprintf('Pembaruan (update) untuk %s.', $type));
        }
    }

    public static function render_audit_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mikrotek-wp-toolkit'));
        }

        $message = '';
        $message_type = 'updated';

        // Handle Toggle Enable/Disable Audit Trail
        if (isset($_POST['mikrotek_wpt_action']) && 'toggle_audit_trail' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_audit_toggle_action', 'mikrotek_wpt_nonce');

            $options = Mikrotek_WP_Toolkit_Settings::all();
            $enable_val = isset($_POST['enable_audit_trail']) && '1' === $_POST['enable_audit_trail'] ? '1' : '';
            $options['enable_audit_trail'] = $enable_val;
            $options['__fields'] = 'enable_audit_trail';

            update_option(Mikrotek_WP_Toolkit_Settings::option_name(), $options);

            $status_text = !empty($enable_val) ? 'Diaktifkan' : 'Dinonaktifkan';
            $message = 'Audit Trail Log Recording berhasil ' . $status_text . '.';
        }

        // Handle Clear Audit Logs
        if (isset($_POST['mikrotek_wpt_action']) && 'clear_audit_logs' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_audit_clear_action', 'mikrotek_wpt_nonce');

            update_option(self::OPTION_LOGS, []);
            $message = 'Seluruh catatan Audit Trail berhasil dibersihkan.';
        }

        $is_enabled = self::is_enabled();
        $logs = self::get_logs();

        $search_query = isset($_GET['s']) ? trim(sanitize_text_field(wp_unslash($_GET['s']))) : '';
        $orderby      = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'timestamp';
        $order        = isset($_GET['order']) && 'asc' === strtolower($_GET['order']) ? 'asc' : 'desc';

        // Search Filter
        if (!empty($search_query) && !empty($logs)) {
            $logs = array_values(array_filter($logs, function($log) use ($search_query) {
                $q = strtolower($search_query);
                return (
                    strpos(strtolower(isset($log['user']) ? $log['user'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['role']) ? $log['role'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['event']) ? $log['event'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['details']) ? $log['details'] : ''), $q) !== false ||
                    strpos(strtolower(isset($log['ip']) ? $log['ip'] : ''), $q) !== false
                );
            }));
        }

        // Sort Logs
        if (!empty($logs)) {
            usort($logs, function($a, $b) use ($orderby, $order) {
                $valA = isset($a[$orderby]) ? strtolower($a[$orderby]) : '';
                $valB = isset($b[$orderby]) ? strtolower($b[$orderby]) : '';

                if ($valA === $valB) {
                    return 0;
                }

                if ('asc' === $order) {
                    return ($valA < $valB) ? -1 : 1;
                }

                return ($valA > $valB) ? -1 : 1;
            });
        }

        $build_sort_url = function($col) use ($orderby, $order, $search_query) {
            $new_order = ($orderby === $col && 'asc' === $order) ? 'desc' : 'asc';
            $url = admin_url('admin.php?page=mikrotek-wp-toolkit-audit&orderby=' . $col . '&order=' . $new_order);
            if (!empty($search_query)) {
                $url = add_query_arg('s', urlencode($search_query), $url);
            }
            return $url;
        };

        $get_sort_icon = function($col) use ($orderby, $order) {
            if ($orderby !== $col) {
                return '<span class="dashicons dashicons-sort" style="font-size:12px;width:12px;height:12px;color:#cbd5e1;"></span>';
            }
            return 'asc' === $order ? '▲' : '▼';
        };

        $export_args = [
            'page'   => 'mikrotek-wp-toolkit-audit',
            'action' => 'export_csv',
        ];
        if (!empty($search_query)) {
            $export_args['s'] = $search_query;
        }

        $export_url = wp_nonce_url(
            add_query_arg($export_args, admin_url('admin.php')),
            'mikrotek_wpt_audit_export_action',
            'mikrotek_wpt_nonce'
        );

        ?>
        <div class="wrap mikrotek-wpt-wrap">
            <h1>Mikrotek Audit Trail</h1>
            <p class="description">
                Pantau seluruh aktivitas penting pengguna (login, logout, edit konten, aktivasi/deaktivasi/hapus plugin, instalasi tema, dll) untuk keamanan situs.
            </p>

            <?php if (!empty($message)) : ?>
                <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible" style="margin-top: 15px;">
                    <p><strong><?php echo esc_html($message); ?></strong></p>
                </div>
            <?php endif; ?>

            <!-- Header Card: Status & Toggle -->
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;">
                    <div>
                        <h2 style="margin:0 0 5px 0;">Status Perekaman Audit Trail</h2>
                        <p class="description" style="margin:0;">
                            Sebelum aktivitas mulai direkam ke dalam log, fitur Audit Trail harus dicentang / diaktifkan terlebih dahulu.
                        </p>
                    </div>
                    <div>
                        <form method="post" action="">
                            <?php wp_nonce_field('mikrotek_wpt_audit_toggle_action', 'mikrotek_wpt_nonce'); ?>
                            <input type="hidden" name="mikrotek_wpt_action" value="toggle_audit_trail">

                            <?php if ($is_enabled) : ?>
                                <input type="hidden" name="enable_audit_trail" value="0">
                                <span class="badge" style="background:#dcfce7;color:#15803d;padding:6px 12px;border-radius:6px;font-weight:700;font-size:14px;margin-right:10px;">
                                    ● ACTIVE / RECORDING
                                </span>
                                <button type="submit" class="button button-secondary">Nonaktifkan Audit Trail</button>
                            <?php else : ?>
                                <input type="hidden" name="enable_audit_trail" value="1">
                                <span class="badge" style="background:#fee2e2;color:#b91c1c;padding:6px 12px;border-radius:6px;font-weight:700;font-size:14px;margin-right:10px;">
                                    ○ DISABLED / NOT RECORDING
                                </span>
                                <button type="submit" class="button button-primary">Aktifkan Audit Trail Sekarang</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
                    <h2 style="margin:0;">Catatan Aktivitas Log (<?php echo count($logs); ?>)</h2>

                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <form method="get" action="" style="margin:0;">
                            <input type="hidden" name="page" value="mikrotek-wp-toolkit-audit">
                            <?php if (!empty($orderby)) : ?>
                                <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
                                <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>">
                            <?php endif; ?>
                            <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Cari user, event, IP..." class="regular-text">
                            <button type="submit" class="button">Cari</button>
                            <?php if (!empty($search_query)) : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=mikrotek-wp-toolkit-audit')); ?>" class="button">Reset</a>
                            <?php endif; ?>
                        </form>

                        <?php if (!empty($logs) || !empty($search_query)) : ?>
                            <a href="<?php echo esc_url($export_url); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:3px;"></span> Export CSV
                            </a>

                            <form method="post" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh log audit trail?');" style="margin:0;">
                                <?php wp_nonce_field('mikrotek_wpt_audit_clear_action', 'mikrotek_wpt_nonce'); ?>
                                <input type="hidden" name="mikrotek_wpt_action" value="clear_audit_logs">
                                <button type="submit" class="button button-link-delete">Bersihkan Log</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$is_enabled && empty($logs)) : ?>
                    <div class="notice notice-warning" style="margin:0;">
                        <p>Audit Trail saat ini <strong>Nonaktif</strong>. Klik tombol <strong>Aktifkan Audit Trail Sekarang</strong> di atas untuk mulai merekam aktivitas.</p>
                    </div>
                <?php elseif (empty($logs)) : ?>
                    <p class="description">Tidak ada log aktivitas yang cocok dengan pencarian.</p>
                <?php else : ?>
                    <table class="widefat striped" style="margin-top:10px;">
                        <thead>
                            <tr>
                                <th>
                                    <a href="<?php echo esc_url($build_sort_url('timestamp')); ?>">
                                        Waktu <?php echo $get_sort_icon('timestamp'); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo esc_url($build_sort_url('user')); ?>">
                                        User <?php echo $get_sort_icon('user'); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo esc_url($build_sort_url('role')); ?>">
                                        Role <?php echo $get_sort_icon('role'); ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo esc_url($build_sort_url('event')); ?>">
                                        Aktivitas (Event) <?php echo $get_sort_icon('event'); ?>
                                    </a>
                                </th>
                                <th>Rincian (Details)</th>
                                <th>
                                    <a href="<?php echo esc_url($build_sort_url('ip')); ?>">
                                        IP Address <?php echo $get_sort_icon('ip'); ?>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($log['timestamp']); ?></code></td>
                                    <td><strong><?php echo esc_html($log['user']); ?></strong></td>
                                    <td><span class="description" style="font-size:11px;"><?php echo esc_html($log['role']); ?></span></td>
                                    <td>
                                        <span class="badge" style="background:#e0e7ff;color:#3730a3;padding:3px 8px;border-radius:4px;font-weight:600;font-size:12px;">
                                            <?php echo esc_html($log['event']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($log['details']); ?></td>
                                    <td><code><?php echo esc_html($log['ip']); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Audit')) {
    class_alias('Mikrotek_WP_Toolkit_Audit', 'MZI_White_Label_Pro_Audit');
}
