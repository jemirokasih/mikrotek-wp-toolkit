<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Tools {

    public static function render_tools_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mikrotek-wp-toolkit'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'migration';

        ?>
        <div class="wrap mikrotek-wpt-wrap">
            <h1>Mikrotek Toolkit Utilities</h1>
            <p class="description">
                Perkakas serbaguna untuk migrasi basis data, pencarian & penggantian URL, serta pemindaian link rusak.
            </p>

            <nav class="nav-tab-wrapper" style="margin-top:15px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mikrotek-wp-toolkit-tools&tab=migration')); ?>" class="nav-tab <?php echo 'migration' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    URL Migration Tool (Search & Replace)
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mikrotek-wp-toolkit-tools&tab=link_checker')); ?>" class="nav-tab <?php echo 'link_checker' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    Broken Link Checker
                </a>
            </nav>

            <?php
            if ('link_checker' === $active_tab) {
                self::render_link_checker_tool();
            } else {
                self::render_migration_tool();
            }
            ?>
        </div>
        <?php
    }

    private static function render_migration_tool() {
        $search = '';
        $replace = '';
        $dry_run = true;
        $use_regex = false;
        $results = null;

        if (isset($_POST['mikrotek_wpt_action']) && 'url_replace' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_url_replace_action', 'mikrotek_wpt_nonce');

            $search    = isset($_POST['search_string']) ? wp_unslash($_POST['search_string']) : '';
            $replace   = isset($_POST['replace_string']) ? wp_unslash($_POST['replace_string']) : '';
            $dry_run   = !isset($_POST['execute_real']);
            $use_regex = isset($_POST['use_regex']);

            if (!empty($search)) {
                $results = self::process_search_replace($search, $replace, $dry_run, $use_regex);
            }
        }

        ?>
        <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 20px;">
            <h2>URL Migration & Database Search Replace</h2>
            <p class="description">
                Perbarui URL lama ke URL baru di seluruh tabel basis data WordPress (termasuk data ter-serialisasi / serialized arrays) secara aman.
            </p>

            <form method="post" action="">
                <?php wp_nonce_field('mikrotek_wpt_url_replace_action', 'mikrotek_wpt_nonce'); ?>
                <input type="hidden" name="mikrotek_wpt_action" value="url_replace">

                <table class="form-table mikrotek-wpt-form-table">
                    <tr>
                        <th scope="row"><label for="search_string">String / URL Lama (Search)</label></th>
                        <td>
                            <input type="text" id="search_string" name="search_string" value="<?php echo esc_attr($search); ?>" class="regular-text" placeholder="http://domain-lama.com" required>
                            <p class="description">Masukkan string atau URL lama yang ingin diganti di seluruh basis data.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="replace_string">String / URL Baru (Replace)</label></th>
                        <td>
                            <input type="text" id="replace_string" name="replace_string" value="<?php echo esc_attr($replace); ?>" class="regular-text" placeholder="https://domain-baru.com">
                            <p class="description">Masukkan string atau URL baru pengganti.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Mode Pengujian (Dry Run)</th>
                        <td>
                            <label>
                                <input type="checkbox" name="execute_real" value="1" <?php checked(!$dry_run); ?>>
                                <strong>Jalankan Perubahan Nyata di Database (LIVE Execute)</strong>
                            </label>
                            <p class="description" style="color:#d97706;">
                                Jika tidak dicentang, tool hanya akan melakukan pemindaian uji coba (Dry Run Simulation) tanpa mengubah data.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Pencarian Regex</th>
                        <td>
                            <label>
                                <input type="checkbox" name="use_regex" value="1" <?php checked($use_regex); ?>>
                                Gunakan Pencarian Regular Expression (Regex)
                            </label>
                        </td>
                    </tr>
                </table>

                <div class="mikrotek-wpt-submit-row">
                    <button type="submit" class="button button-primary">
                        <?php echo $dry_run ? 'Jalankan Simulasi (Dry Run)' : 'Jalankan Proses Penggantian Database'; ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if (null !== $results) : ?>
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 20px;">
                <h2>Hasil Laporan Search & Replace</h2>
                <div class="notice notice-<?php echo $dry_run ? 'info' : 'success'; ?>" style="margin-top: 10px;">
                    <p>
                        Mode: <strong><?php echo $dry_run ? 'Simulasi Dry Run (Data Tidak Diubah)' : 'LIVE Execution (Data Diperbarui)'; ?></strong>
                        | Total Perubahan: <strong><?php echo esc_html($results['total_updates']); ?></strong> data diperbarui dari <strong><?php echo esc_html($results['tables_scanned']); ?></strong> tabel.
                    </p>
                </div>

                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>Nama Tabel</th>
                            <th>Jumlah Data Ditemukan/Diubah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['details'] as $table_name => $detail) : ?>
                            <tr>
                                <td><code><?php echo esc_html($table_name); ?></code></td>
                                <td><strong><?php echo esc_html($detail['count']); ?></strong> baris</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php
    }

    private static function render_link_checker_tool() {
        $scan_results = null;
        $scan_limit = 30;

        if (isset($_POST['mikrotek_wpt_action']) && 'scan_links' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_link_checker_action', 'mikrotek_wpt_nonce');

            $scan_limit = isset($_POST['scan_limit']) ? absint($_POST['scan_limit']) : 30;
            $post_types = isset($_POST['post_types']) && is_array($_POST['post_types']) ? array_map('sanitize_key', $_POST['post_types']) : ['post', 'page'];

            $scan_results = self::scan_broken_links($post_types, $scan_limit);
        }

        ?>
        <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 20px;">
            <h2>Lightweight Broken Link Checker</h2>
            <p class="description">
                Pindai link rusak (404 Not Found, Timeout, Server Error) di dalam konten pos/halaman secara instan tanpa memberatkan server background.
            </p>

            <form method="post" action="">
                <?php wp_nonce_field('mikrotek_wpt_link_checker_action', 'mikrotek_wpt_nonce'); ?>
                <input type="hidden" name="mikrotek_wpt_action" value="scan_links">

                <table class="form-table mikrotek-wpt-form-table">
                    <tr>
                        <th scope="row">Tipe Konten</th>
                        <td>
                            <label style="margin-right: 15px;">
                                <input type="checkbox" name="post_types[]" value="post" checked> Posts
                            </label>
                            <label style="margin-right: 15px;">
                                <input type="checkbox" name="post_types[]" value="page" checked> Pages
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="scan_limit">Jumlah Konten Dipindai</label></th>
                        <td>
                            <select id="scan_limit" name="scan_limit">
                                <option value="20" <?php selected($scan_limit, 20); ?>>20 Konten Terbaru</option>
                                <option value="50" <?php selected($scan_limit, 50); ?>>50 Konten Terbaru</option>
                                <option value="100" <?php selected($scan_limit, 100); ?>>100 Konten Terbaru</option>
                            </select>
                            <p class="description">Pemindaian dilakukan secara instan pada batch konten terbaru yang dipilih.</p>
                        </td>
                    </tr>
                </table>

                <div class="mikrotek-wpt-submit-row">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-search" style="vertical-align:middle;margin-right:3px;"></span> Mulai Pindai Broken Links
                    </button>
                </div>
            </form>
        </div>

        <?php if (null !== $scan_results) : ?>
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 20px;">
                <h2>Hasil Pemindaian Link</h2>
                <p>
                    Berhasil memindai <strong><?php echo esc_html($scan_results['scanned_posts']); ?></strong> konten dan <strong><?php echo esc_html($scan_results['scanned_links']); ?></strong> total link.
                    Ditemukan <strong style="color:<?php echo !empty($scan_results['broken_links']) ? '#dc2626' : '#059669'; ?>;"><?php echo count($scan_results['broken_links']); ?></strong> link rusak.
                </p>

                <?php if (empty($scan_results['broken_links'])) : ?>
                    <div class="notice notice-success" style="margin-top: 10px;">
                        <p>Tidak ditemukan broken link pada konten yang dipindai. Semua link berjalan normal!</p>
                    </div>
                <?php else : ?>
                    <table class="widefat striped" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Konten / Halaman</th>
                                <th>Broken Link URL</th>
                                <th>Status HTTP</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scan_results['broken_links'] as $item) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($item['post_title']); ?></strong>
                                        <br><span class="description" style="font-size:11px;"><?php echo esc_html(strtoupper($item['post_type'])); ?> (ID: <?php echo esc_html($item['post_id']); ?>)</span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url($item['url']); ?>" target="_blank" style="word-break:break-all;">
                                            <code><?php echo esc_html($item['url']); ?></code>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:#fee2e2;color:#b91c1c;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">
                                            <?php echo esc_html($item['status_text']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(get_edit_post_link($item['post_id'])); ?>" target="_blank" class="button button-small">
                                            Sunting Konten
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php
    }

    private static function scan_broken_links($post_types, $limit) {
        global $wpdb;

        $post_types_placeholder = implode("','", array_map('esc_sql', $post_types));
        $limit = absint($limit);
        if ($limit <= 0) {
            $limit = 30;
        }

        $query = "SELECT ID, post_title, post_content, post_type FROM {$wpdb->posts}
                  WHERE post_status = 'publish' AND post_type IN ('{$post_types_placeholder}')
                  AND post_content LIKE '%href=%'
                  ORDER BY ID DESC LIMIT {$limit}";

        $posts = $wpdb->get_results($query);
        $scanned_links_count = 0;
        $broken_links = [];

        if (empty($posts)) {
            return [
                'scanned_posts' => 0,
                'scanned_links' => 0,
                'broken_links'  => [],
            ];
        }

        foreach ($posts as $post) {
            preg_match_all('/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/i', $post->post_content, $matches);
            if (empty($matches[2])) {
                continue;
            }

            $urls = array_unique($matches[2]);
            foreach ($urls as $url) {
                $url = trim($url);
                if (empty($url) || 0 === strpos($url, '#') || 0 === strpos($url, 'mailto:') || 0 === strpos($url, 'tel:') || 0 === strpos($url, 'javascript:')) {
                    continue;
                }

                $scanned_links_count++;
                $status_code = self::check_url_status($url);

                if ($status_code >= 400 || 0 === $status_code) {
                    $broken_links[] = [
                        'post_id'     => $post->ID,
                        'post_title'  => $post->post_title,
                        'post_type'   => $post->post_type,
                        'url'         => $url,
                        'status'      => $status_code,
                        'status_text' => self::get_http_status_label($status_code),
                    ];
                }
            }
        }

        return [
            'scanned_posts' => count($posts),
            'scanned_links' => $scanned_links_count,
            'broken_links'  => $broken_links,
        ];
    }

    private static function check_url_status($url) {
        if (0 !== strpos($url, 'http://') && 0 !== strpos($url, 'https://')) {
            $url = site_url($url);
        }

        $args = [
            'timeout'     => 4,
            'redirection' => 3,
            'sslverify'   => apply_filters('mikrotek_wpt_sslverify', true),
            'user-agent'  => 'Mikrotek-Broken-Link-Checker/1.0',
        ];

        $response = wp_safe_remote_head($url, $args);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
            $response = wp_safe_remote_get($url, $args);
        }

        if (is_wp_error($response)) {
            return 0;
        }

        return absint(wp_remote_retrieve_response_code($response));
    }

    private static function get_http_status_label($code) {
        $labels = [
            0   => '0 - Timeout / Connection Failed',
            400 => '400 - Bad Request',
            401 => '401 - Unauthorized',
            403 => '403 - Forbidden',
            404 => '404 - Not Found',
            500 => '500 - Internal Server Error',
            502 => '502 - Bad Gateway',
            503 => '503 - Service Unavailable',
            504 => '504 - Gateway Timeout',
        ];

        return isset($labels[$code]) ? $labels[$code] : $code . ' - HTTP Error';
    }

    private static function process_search_replace($search, $replace, $dry_run, $use_regex = false) {
        global $wpdb;

        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        $total_updates = 0;
        $tables_scanned = count($tables);
        $details = [];

        $regex_pattern = $use_regex ? self::format_regex_pattern($search) : '';

        foreach ($tables as $table_row) {
            $table = $table_row[0];

            $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$table}`", ARRAY_A);
            $primary_key = '';

            foreach ($columns as $column) {
                if ('PRI' === $column['Key']) {
                    $primary_key = $column['Field'];
                    break;
                }
            }

            if (empty($primary_key)) {
                continue;
            }

            $table_updates = 0;

            foreach ($columns as $column) {
                $col_name = $column['Field'];
                $col_type = strtolower($column['Type']);

                if (false === strpos($col_type, 'char') && false === strpos($col_type, 'text')) {
                    continue;
                }

                if ($use_regex) {
                    $clean_regex = self::clean_sql_regex($search);
                    $sql = $wpdb->prepare(
                        "SELECT `{$primary_key}`, `{$col_name}` FROM `{$table}` WHERE `{$col_name}` REGEXP %s",
                        $clean_regex
                    );
                } else {
                    $sql = $wpdb->prepare(
                        "SELECT `{$primary_key}`, `{$col_name}` FROM `{$table}` WHERE `{$col_name}` LIKE %s",
                        '%' . $wpdb->esc_like($search) . '%'
                    );
                }

                $rows = $wpdb->get_results($sql, ARRAY_A);

                if (empty($rows)) {
                    continue;
                }

                foreach ($rows as $row) {
                    $id_val = $row[$primary_key];
                    $data_val = $row[$col_name];

                    $replaced_val = self::recursive_unserialize_replace($search, $replace, $data_val, $use_regex, $regex_pattern);

                    if ($replaced_val !== $data_val) {
                        $table_updates++;
                        $total_updates++;

                        if (!$dry_run) {
                            $wpdb->update(
                                $table,
                                [$col_name => $replaced_val],
                                [$primary_key => $id_val]
                            );
                        }
                    }
                }
            }

            if ($table_updates > 0) {
                $details[$table] = [
                    'count' => $table_updates,
                ];
            }
        }

        return [
            'tables_scanned' => $tables_scanned,
            'total_updates'  => $total_updates,
            'details'        => $details,
        ];
    }

    private static function recursive_unserialize_replace($from, $to, $data, $use_regex = false, $regex_pattern = '') {
        if (is_serialized($data)) {
            $unserialized = @unserialize($data);
            if (false !== $unserialized || 'b:0;' === $data) {
                $replaced = self::recursive_unserialize_replace($from, $to, $unserialized, $use_regex, $regex_pattern);
                return serialize($replaced);
            }
        }

        if (is_array($data)) {
            $tmp = [];
            foreach ($data as $key => $value) {
                $tmp[$key] = self::recursive_unserialize_replace($from, $to, $value, $use_regex, $regex_pattern);
            }
            return $tmp;
        }

        if (is_object($data)) {
            $tmp = clone $data;
            foreach (get_object_vars($data) as $key => $value) {
                $tmp->$key = self::recursive_unserialize_replace($from, $to, $value, $use_regex, $regex_pattern);
            }
            return $tmp;
        }

        if (is_string($data)) {
            if ($use_regex && !empty($regex_pattern)) {
                return @preg_replace($regex_pattern, $to, $data);
            }
            return str_replace($from, $to, $data);
        }

        return $data;
    }

    private static function format_regex_pattern($pattern) {
        if (0 === strpos($pattern, '/') && strlen($pattern) > 2 && '/' === substr($pattern, -1)) {
            return $pattern;
        }
        return '/' . str_replace('/', '\/', $pattern) . '/i';
    }

    private static function clean_sql_regex($pattern) {
        $pattern = trim($pattern, '/');
        return $pattern;
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Tools')) {
    class_alias('Mikrotek_WP_Toolkit_Tools', 'MZI_White_Label_Pro_Tools');
}
