<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Audit {

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
        return Mikrotek_WP_Toolkit_Settings::enabled('enable_audit_trail') && Mikrotek_WP_Toolkit_Audit_Table::table_exists();
    }

    public static function log_event($event, $details = '', $username = null) {
        if (!self::is_enabled()) {
            return;
        }

        global $wpdb;

        if (null !== $username) {
            $user_label = $username;
            $user_role  = 'none';
        } else {
            $user = wp_get_current_user();
            $user_label = ($user && $user->exists()) ? $user->user_login : 'Guest / System';
            $user_role  = ($user && !empty($user->roles)) ? implode(', ', $user->roles) : 'none';
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'Unknown IP';
        $table_name = Mikrotek_WP_Toolkit_Audit_Table::get_table_name();

        $wpdb->insert(
            $table_name,
            [
                'timestamp' => current_time('mysql'),
                'user'      => $user_label,
                'role'      => $user_role,
                'event'     => sanitize_text_field($event),
                'details'   => sanitize_text_field($details),
                'ip'        => $ip,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
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

        if (!Mikrotek_WP_Toolkit_Audit_Table::table_exists()) {
            wp_die(__('Tabel Audit Trail tidak ditemukan di basis data.', 'mikrotek-wp-toolkit'));
        }

        global $wpdb;
        $table_name = Mikrotek_WP_Toolkit_Audit_Table::get_table_name();
        $search_query = isset($_GET['s']) ? trim(sanitize_text_field(wp_unslash($_GET['s']))) : '';

        if (!empty($search_query)) {
            $like = '%' . $wpdb->esc_like($search_query) . '%';
            $sql = $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user LIKE %s OR role LIKE %s OR event LIKE %s OR details LIKE %s OR ip LIKE %s ORDER BY id DESC LIMIT 5000",
                $like, $like, $like, $like, $like
            );
        } else {
            $sql = "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 5000";
        }

        $logs = $wpdb->get_results($sql, ARRAY_A);

        $filename = 'audit-trail-logs-' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Timestamp', 'User', 'Role', 'Event', 'Details', 'IP Address']);

        if (!empty($logs)) {
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['timestamp'],
                    $log['user'],
                    $log['role'],
                    $log['event'],
                    $log['details'],
                    $log['ip'],
                ]);
            }
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

        global $wpdb;
        $message = '';
        $message_type = 'updated';

        // 1. ACTION: Activation Consent (Create Table + Enable Feature)
        if (isset($_POST['mikrotek_wpt_action']) && 'activate_audit_trail' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_audit_activate_action', 'mikrotek_wpt_nonce');

            $result = Mikrotek_WP_Toolkit_Audit_Table::create_table();
            if ($result['success']) {
                $options = Mikrotek_WP_Toolkit_Settings::all();
                $options['enable_audit_trail'] = '1';
                $options['__fields'] = 'enable_audit_trail';
                update_option(Mikrotek_WP_Toolkit_Settings::option_name(), $options);

                $message = 'Audit Trail berhasil diaktifkan. ' . $result['message'];
            } else {
                $message = 'Gagal mengaktifkan Audit Trail: ' . $result['message'];
                $message_type = 'error';
            }
        }

        // 2. ACTION: Deactivation Cleanup (Keep / Truncate / Drop Table)
        if (isset($_POST['mikrotek_wpt_action']) && 'deactivate_audit_trail' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_audit_deactivate_action', 'mikrotek_wpt_nonce');

            $cleanup_option = isset($_POST['deactivate_cleanup_choice']) ? sanitize_key($_POST['deactivate_cleanup_choice']) : 'keep';

            $options = Mikrotek_WP_Toolkit_Settings::all();
            $options['enable_audit_trail'] = '';
            $options['__fields'] = 'enable_audit_trail';
            update_option(Mikrotek_WP_Toolkit_Settings::option_name(), $options);

            if ('truncate' === $cleanup_option) {
                Mikrotek_WP_Toolkit_Audit_Table::truncate_table();
                $message = 'Audit Trail dinonaktifkan & seluruh data log di-truncate (struktur tabel tetap disimpan).';
            } elseif ('drop' === $cleanup_option) {
                Mikrotek_WP_Toolkit_Audit_Table::drop_table();
                $message = 'Audit Trail dinonaktifkan & tabel wp_mikrotek_audit_logs berhasil dihapus total dari database.';
            } else {
                $message = 'Perekaman Audit Trail dinonaktifkan (data log & tabel tetap dipertahankan).';
            }
        }

        // 3. ACTION: Save Retention Policy & Manual Prune
        if (isset($_POST['mikrotek_wpt_action']) && 'save_retention_policy' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_audit_retention_action', 'mikrotek_wpt_nonce');

            $retention_days = isset($_POST['audit_log_retention']) ? absint($_POST['audit_log_retention']) : 30;

            $options = Mikrotek_WP_Toolkit_Settings::all();
            $options['audit_log_retention'] = (string) $retention_days;
            $options['__fields'] = 'audit_log_retention';
            update_option(Mikrotek_WP_Toolkit_Settings::option_name(), $options);

            if (isset($_POST['run_manual_prune']) && $retention_days > 0) {
                $deleted_count = Mikrotek_WP_Toolkit_Audit_Table::prune_expired_logs($retention_days);
                $message = sprintf('Pengaturan retensi disimpan. Berhasil menghapus %d log yang berusia lebih dari %d hari.', $deleted_count, $retention_days);
            } else {
                $message = 'Pengaturan retensi pembersihan log berhasil disimpan.';
            }
        }

        $health_status = Mikrotek_WP_Toolkit_Audit_Table::get_health_status();
        $is_enabled    = Mikrotek_WP_Toolkit_Settings::enabled('enable_audit_trail') && $health_status['exists'];
        $retention_val = Mikrotek_WP_Toolkit_Settings::get('audit_log_retention', '30');

        // Pagination & Sorting Variables
        $table_name   = Mikrotek_WP_Toolkit_Audit_Table::get_table_name();
        $search_query = isset($_GET['s']) ? trim(sanitize_text_field(wp_unslash($_GET['s']))) : '';
        $orderby      = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'id';
        $order        = isset($_GET['order']) && 'asc' === strtolower($_GET['order']) ? 'ASC' : 'DESC';

        $allowed_orderby = ['id', 'timestamp', 'user', 'role', 'event', 'ip'];
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'id';
        }

        $per_page = 20;
        $paged    = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        if ($paged <= 0) {
            $paged = 1;
        }

        $logs = [];
        $total_items = 0;
        $total_pages = 1;

        if ($health_status['exists']) {
            if (!empty($search_query)) {
                $like = '%' . $wpdb->esc_like($search_query) . '%';
                $count_sql = $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE user LIKE %s OR role LIKE %s OR event LIKE %s OR details LIKE %s OR ip LIKE %s",
                    $like, $like, $like, $like, $like
                );
                $total_items = (int) $wpdb->get_var($count_sql);

                $offset = ($paged - 1) * $per_page;
                $sql = $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE user LIKE %s OR role LIKE %s OR event LIKE %s OR details LIKE %s OR ip LIKE %s ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                    $like, $like, $like, $like, $like, $per_page, $offset
                );
            } else {
                $total_items = $health_status['total_rows'];
                $offset = ($paged - 1) * $per_page;
                $sql = $wpdb->prepare(
                    "SELECT * FROM {$table_name} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                    $per_page, $offset
                );
            }

            $logs = $wpdb->get_results($sql, ARRAY_A);
            $total_pages = max(1, ceil($total_items / $per_page));
        }

        $build_sort_url = function($col) use ($orderby, $order, $search_query, $paged) {
            $new_order = ($orderby === $col && 'ASC' === $order) ? 'desc' : 'asc';
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
            return 'ASC' === $order ? '▲' : '▼';
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
            <h1>Mikrotek Enterprise Audit Trail</h1>
            <p class="description">
                Mesin audit trail berbasis tabel MySQL khusus (<code>wp_mikrotek_audit_logs</code>) dengan dukungan metrik kesehatan sistem, eksekusi pencarian performa tinggi, dan pembersihan otomatis (auto-pruning).
            </p>

            <?php if (!empty($message)) : ?>
                <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible" style="margin-top: 15px;">
                    <p><strong><?php echo esc_html($message); ?></strong></p>
                </div>
            <?php endif; ?>

            <!-- STATUS KESEHATAN SISTEM (SYSTEM HEALTH CARD) -->
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top:20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;margin-bottom:15px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span class="badge" style="background:<?php echo esc_attr($health_status['bg_color']); ?>;color:<?php echo esc_attr($health_status['status_color']); ?>;padding:6px 14px;border-radius:6px;font-weight:700;font-size:13px;">
                            ● <?php echo esc_html($health_status['status_label']); ?>
                        </span>
                        <h2 style="margin:0;font-size:18px;">Metrik Kesehatan & Status Database</h2>
                    </div>

                    <?php if ($is_enabled) : ?>
                        <button type="button" class="button button-secondary" onclick="document.getElementById('deactivate_modal_card').style.display = (document.getElementById('deactivate_modal_card').style.display === 'none' ? 'block' : 'none');">
                            Nonaktifkan Audit Trail...
                        </button>
                    <?php endif; ?>
                </div>

                <div style="display:grid;grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));gap:15px;">
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;display:block;">NAMA TABEL MYSQL</span>
                        <code style="font-size:13px;color:#1e293b;font-weight:700;"><?php echo esc_html(Mikrotek_WP_Toolkit_Audit_Table::get_table_name()); ?></code>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;display:block;">TOTAL DATA LOG</span>
                        <strong style="font-size:16px;color:#0f172a;"><?php echo number_format($health_status['total_rows']); ?> Baris</strong>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;display:block;">UKURAN PENYIMPANAN DB</span>
                        <strong style="font-size:16px;color:#4f46e5;"><?php echo esc_html($health_status['size_formatted']); ?></strong>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 16px;border-radius:6px;">
                        <span style="font-size:11px;color:#64748b;font-weight:600;display:block;">AKTIVITAS TERAKHIR</span>
                        <code style="font-size:12px;color:#334155;"><?php echo esc_html($health_status['last_event']); ?></code>
                    </div>
                </div>
            </div>

            <!-- KARTU PEMBERITAHUAN SEBELUM AKTIF / ON-DEMAND CONSENT BANNER -->
            <?php if (!$health_status['exists'] || !$is_enabled) : ?>
                <div class="mikrotek-wpt-card" style="background:#f0f9ff;border:1px solid #bae6fd;border-left:5px solid #0284c7;padding:20px;border-radius:8px;margin-top:20px;">
                    <h2 style="margin:0 0 10px 0;color:#0369a1;font-size:18px;">Aktivasi Mesin Audit Trail Database</h2>
                    <p style="margin:0 0 15px 0;color:#0c4a6e;font-size:14px;line-height:1.6;">
                        Perekaman Audit Trail membutuhkan pembentukan 1 tabel khusus bernama <code>wp_mikrotek_audit_logs</code> di basis data WordPress Anda.
                        Tabel ini dilengkapi indeks MySQL berperforma tinggi agar pencarian ribuan log berjalan sangat cepat tanpa memberatkan situs.
                    </p>

                    <?php if (!empty($health_status['last_error'])) : ?>
                        <div class="notice notice-error" style="margin:0 0 15px 0;padding:10px;">
                            <strong style="color:#b91c1c;">Terjadi Kesalahan Pada Percobaan Sebelumnya:</strong>
                            <p style="margin:4px 0 0 0;font-size:12px;"><code><?php echo esc_html($health_status['last_error']); ?></code></p>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <?php wp_nonce_field('mikrotek_wpt_audit_activate_action', 'mikrotek_wpt_nonce'); ?>
                        <input type="hidden" name="mikrotek_wpt_action" value="activate_audit_trail">

                        <button type="submit" class="button button-primary button-hero" style="background:#0284c7;border-color:#0284c7;">
                            <span class="dashicons dashicons-shield-alt" style="vertical-align:middle;margin-right:5px;"></span>
                            <?php echo !empty($health_status['last_error']) ? 'Coba Lagi / Retry Activation' : 'Setujui & Aktifkan Audit Trail Sekarang'; ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- MODAL KONFIRMASI DEAKTIVASI / DEACTIVATION CLEANUP CHOICE -->
            <div id="deactivate_modal_card" class="mikrotek-wpt-card" style="display:none;background:#fff1f2;border:1px solid #fecdd3;border-left:5px solid #e11d48;padding:20px;border-radius:8px;margin-top:20px;">
                <h3 style="margin:0 0 10px 0;color:#9f1239;">Pilih Aksi Pembersihan Saat Menonaktifkan Audit Trail</h3>
                <p style="margin:0 0 15px 0;color:#881337;font-size:13px;">
                    Silakan pilih bagaimana data log dan tabel basis data ditangani ketika perekaman dinonaktifkan:
                </p>

                <form method="post" action="">
                    <?php wp_nonce_field('mikrotek_wpt_audit_deactivate_action', 'mikrotek_wpt_nonce'); ?>
                    <input type="hidden" name="mikrotek_wpt_action" value="deactivate_audit_trail">

                    <div style="margin-bottom:12px;">
                        <label style="display:block;margin-bottom:8px;font-weight:600;color:#1e293b;">
                            <input type="radio" name="deactivate_cleanup_choice" value="keep" checked>
                            1. Nonaktifkan Perekaman Saja <span class="description">(Rekomendasi: Data log & struktur tabel tetap disimpan aman)</span>
                        </label>
                        <label style="display:block;margin-bottom:8px;font-weight:600;color:#1e293b;">
                            <input type="radio" name="deactivate_cleanup_choice" value="truncate">
                            2. Bersihkan Data Log (TRUNCATE) <span class="description">(Seluruh baris log dihapus, struktur tabel tetap disimpan)</span>
                        </label>
                        <label style="display:block;margin-bottom:8px;font-weight:600;color:#b91c1c;">
                            <input type="radio" name="deactivate_cleanup_choice" value="drop">
                            3. Hapus Total / Uninstall Clean (DROP TABLE) <span class="description">(Hapus seluruh data log & hapus tabel wp_mikrotek_audit_logs dari database)</span>
                        </label>
                    </div>

                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="button button-primary" style="background:#e11d48;border-color:#e11d48;">Eksekusi Deaktivasi</button>
                        <button type="button" class="button button-secondary" onclick="document.getElementById('deactivate_modal_card').style.display='none';">Batal</button>
                    </div>
                </form>
            </div>

            <?php if ($health_status['exists']) : ?>
                <!-- PENGATURAN RETENSI OTO-PEMBERSIHAN (AUTO-PRUNE RETENTION POLICY) -->
                <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top:20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;">
                        <div>
                            <h3 style="margin:0 0 4px 0;">Retensi & Pembersihan Otomatis (Auto-Prune)</h3>
                            <p class="description" style="margin:0;">
                                Tugas latar belakang (WP-Cron) akan menghapus log yang berusia lebih lama dari ambang batas retensi secara otomatis setiap hari.
                            </p>
                        </div>

                        <form method="post" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:0;">
                            <?php wp_nonce_field('mikrotek_wpt_audit_retention_action', 'mikrotek_wpt_nonce'); ?>
                            <input type="hidden" name="mikrotek_wpt_action" value="save_retention_policy">

                            <select name="audit_log_retention">
                                <option value="7" <?php selected($retention_val, '7'); ?>>Hapus Log > 7 Hari</option>
                                <option value="30" <?php selected($retention_val, '30'); ?>>Hapus Log > 30 Hari (Default)</option>
                                <option value="60" <?php selected($retention_val, '60'); ?>>Hapus Log > 60 Hari</option>
                                <option value="90" <?php selected($retention_val, '90'); ?>>Hapus Log > 90 Hari</option>
                                <option value="180" <?php selected($retention_val, '180'); ?>>Hapus Log > 180 Hari</option>
                                <option value="365" <?php selected($retention_val, '365'); ?>>Hapus Log > 365 Hari (1 Tahun)</option>
                                <option value="0" <?php selected($retention_val, '0'); ?>>Jangan Pernah Dihapus (Permanen)</option>
                            </select>

                            <button type="submit" class="button button-secondary">Simpan Retensi</button>
                            <button type="submit" name="run_manual_prune" value="1" class="button button-secondary" onclick="return confirm('Jalankan pembersihan log kedaluwarsa secara manual sekarang?');">
                                Prune Manual Sekarang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA LOG AUDIT TRAIL -->
                <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top:24px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
                        <h2 style="margin:0;">Catatan Log Aktivitas (Ditemukan <?php echo number_format($total_items); ?> Baris)</h2>

                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                            <form method="get" action="" style="margin:0;">
                                <input type="hidden" name="page" value="mikrotek-wp-toolkit-audit">
                                <?php if (!empty($orderby)) : ?>
                                    <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
                                    <input type="hidden" name="order" value="<?php echo esc_attr(strtolower($order)); ?>">
                                <?php endif; ?>
                                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Cari user, event, IP..." class="regular-text">
                                <button type="submit" class="button">Cari SQL</button>
                                <?php if (!empty($search_query)) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mikrotek-wp-toolkit-audit')); ?>" class="button">Reset</a>
                                <?php endif; ?>
                            </form>

                            <?php if ($total_items > 0 || !empty($search_query)) : ?>
                                <a href="<?php echo esc_url($export_url); ?>" class="button button-secondary">
                                    <span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:3px;"></span> Export CSV
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (empty($logs)) : ?>
                        <p class="description">Tidak ada log aktivitas yang ditemukan.</p>
                    <?php else : ?>
                        <table class="widefat striped" style="margin-top:10px;">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="<?php echo esc_url($build_sort_url('id')); ?>">
                                            ID <?php echo $get_sort_icon('id'); ?>
                                        </a>
                                    </th>
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
                                        <td><code>#<?php echo esc_html($log['id']); ?></code></td>
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

                        <!-- SQL PAGINATION NAV -->
                        <?php if ($total_pages > 1) : ?>
                            <div class="tablenav" style="margin-top:15px;display:flex;justify-content:space-between;align-items:center;">
                                <div class="tablenav-pages">
                                    <span class="displaying-num">Menampilkan Halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?></span>
                                    <?php
                                    $page_links = paginate_links([
                                        'base'      => add_query_arg('paged', '%#%'),
                                        'format'    => '',
                                        'prev_text' => '&laquo; Sebelumnya',
                                        'next_text' => 'Berikutnya &raquo;',
                                        'total'     => $total_pages,
                                        'current'   => $paged,
                                    ]);
                                    echo wp_kses_post($page_links);
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Audit')) {
    class_alias('Mikrotek_WP_Toolkit_Audit', 'MZI_White_Label_Pro_Audit');
}
