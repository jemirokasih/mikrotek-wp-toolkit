<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Tools {

    public static function render_tools_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mzi-white-label-pro'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $active_tool = isset($_GET['tool']) ? sanitize_key(wp_unslash($_GET['tool'])) : 'migration';

        ?>
        <div class="wrap mzi-wlp-wrap">
            <h1>MZI Tools & Utilities</h1>
            <p class="description">
                Perkakas pembantu untuk pengelolaan migrasi URL database dan pemindaian broken link secara ringan & cepat.
            </p>

            <nav class="nav-tab-wrapper" style="margin-top: 16px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mzi-white-label-tools&tool=migration')); ?>"
                   class="nav-tab <?php echo 'migration' === $active_tool ? 'nav-tab-active' : ''; ?>">
                    URL Migration Tool (Search & Replace)
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mzi-white-label-tools&tool=link-checker')); ?>"
                   class="nav-tab <?php echo 'link-checker' === $active_tool ? 'nav-tab-active' : ''; ?>">
                    Broken Link Checker (Ringan)
                </a>
            </nav>

            <?php
            if ('link-checker' === $active_tool) {
                self::render_link_checker_tool();
            } else {
                self::render_migration_tool();
            }
            ?>
        </div>
        <?php
    }

    private static function render_migration_tool() {
        $results = null;
        $old_url = '';
        $new_url = '';
        $dry_run = false;
        $use_regex = false;

        if (isset($_POST['mzi_wlp_action']) && 'replace_urls' === $_POST['mzi_wlp_action']) {
            check_admin_referer('mzi_wlp_url_replace_action', 'mzi_wlp_nonce');

            $old_url   = isset($_POST['old_url']) ? trim(wp_unslash($_POST['old_url'])) : '';
            $new_url   = isset($_POST['new_url']) ? trim(wp_unslash($_POST['new_url'])) : '';
            $dry_run   = isset($_POST['dry_run']) && '1' === $_POST['dry_run'];
            $use_regex = isset($_POST['use_regex']) && '1' === $_POST['use_regex'];

            if (!empty($old_url)) {
                $results = self::process_search_replace($old_url, $new_url, $dry_run, $use_regex);
            }
        }

        ?>
        <div class="notice notice-warning" style="margin-top: 15px; border-left-color: #f0b849;">
            <p>
                <strong>PERHATIAN:</strong> Sangat disarankan untuk membuat <strong>Backup Database</strong> terlebih dahulu sebelum melakukan eksekusi penggantian URL secara langsung.
            </p>
        </div>

        <?php if (null !== $results) : ?>
            <div class="notice notice-info is-dismissible" style="margin-top: 15px;">
                <p>
                    <strong>Hasil <?php echo $dry_run ? 'Dry Run (Simulasi)' : 'Migrasi URL'; ?>:</strong>
                    Ditemukan <strong><?php echo esc_html($results['total_updates']); ?></strong> perubahan dari total <strong><?php echo esc_html($results['tables_scanned']); ?></strong> tabel yang dipindai <?php echo $use_regex ? '(Menggunakan Mode Regex)' : ''; ?>.
                </p>
            </div>
        <?php endif; ?>

        <div class="mzi-wlp-card" style="margin-top: 20px;">
            <form method="post" action="">
                <?php wp_nonce_field('mzi_wlp_url_replace_action', 'mzi_wlp_nonce'); ?>
                <input type="hidden" name="mzi_wlp_action" value="replace_urls">

                <table class="form-table mzi-wlp-form-table">
                    <tr>
                        <th scope="row"><label for="old_url">Old URL / Text / Pattern</label></th>
                        <td>
                            <input type="text" id="old_url" name="old_url" class="large-text" required
                                   placeholder="http://old-domain.com"
                                   value="<?php echo esc_attr($old_url); ?>">
                            <p class="description">Masukkan URL/domain lama yang ingin diganti, atau pola Regex jika mode Regex diaktifkan.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="new_url">New URL / Text</label></th>
                        <td>
                            <input type="text" id="new_url" name="new_url" class="large-text"
                                   placeholder="https://new-domain.com"
                                   value="<?php echo esc_attr($new_url); ?>">
                            <p class="description">Masukkan URL atau teks baru pengganti.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Regular Expression</th>
                        <td>
                            <label>
                                <input type="checkbox" name="use_regex" value="1" <?php checked($use_regex); ?>>
                                <strong>Gunakan Regular Expression (Regex)</strong> — Aktifkan untuk pencarian menggunakan pencocokan pola regex (contoh: <code>/https?:\/\/(www\.)?old-domain\.com/i</code>).
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Mode Dry Run</th>
                        <td>
                            <label>
                                <input type="checkbox" name="dry_run" value="1" <?php checked($dry_run || null === $results); ?>>
                                <strong>Simulasi (Dry Run)</strong> — Hanya hitung baris yang akan berubah tanpa mengubah isi database secara langsung.
                            </label>
                        </td>
                    </tr>
                </table>

                <div class="mzi-wlp-submit-row">
                    <button type="submit" class="button button-primary">
                        <?php echo ($dry_run || null === $results) ? 'Jalankan Simulasi (Dry Run)' : 'Proses Penggantian URL Database'; ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if (null !== $results && !empty($results['details'])) : ?>
            <div class="mzi-wlp-card" style="margin-top: 20px;">
                <h2>Detail Tabel yang Diproses</h2>
                <table class="widefat striped" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Nama Tabel</th>
                            <th>Kolom</th>
                            <th>Baris Terpengaruh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['details'] as $detail) : ?>
                            <tr>
                                <td><code><?php echo esc_html($detail['table']); ?></code></td>
                                <td><code><?php echo esc_html($detail['column']); ?></code></td>
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

        if (isset($_POST['mzi_wlp_action']) && 'scan_links' === $_POST['mzi_wlp_action']) {
            check_admin_referer('mzi_wlp_link_checker_action', 'mzi_wlp_nonce');

            $scan_limit = isset($_POST['scan_limit']) ? absint($_POST['scan_limit']) : 30;
            $post_types = isset($_POST['post_types']) && is_array($_POST['post_types']) ? array_map('sanitize_key', $_POST['post_types']) : ['post', 'page'];

            $scan_results = self::scan_broken_links($post_types, $scan_limit);
        }

        ?>
        <div class="mzi-wlp-card" style="margin-top: 20px;">
            <h2>Lightweight Broken Link Checker</h2>
            <p class="description">
                Pindai link rusak (404 Not Found, Timeout, Server Error) di dalam konten pos/halaman secara instan tanpa memberatkan server background.
            </p>

            <form method="post" action="">
                <?php wp_nonce_field('mzi_wlp_link_checker_action', 'mzi_wlp_nonce'); ?>
                <input type="hidden" name="mzi_wlp_action" value="scan_links">

                <table class="form-table mzi-wlp-form-table">
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

                <div class="mzi-wlp-submit-row">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-search" style="vertical-align:middle;margin-right:3px;"></span> Mulai Pindai Broken Links
                    </button>
                </div>
            </form>
        </div>

        <?php if (null !== $scan_results) : ?>
            <div class="mzi-wlp-card" style="margin-top: 20px;">
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
                                        <span class="badge" style="background:#fee2e2;color:#991b1b;padding:4px 8px;border-radius:4px;font-weight:600;font-size:12px;">
                                            <?php echo esc_html($item['status_text']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(get_edit_post_link($item['post_id'])); ?>" class="button button-small" target="_blank">
                                            Edit Konten
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

    private static function scan_broken_links($post_types = ['post', 'page'], $limit = 30) {
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
        $broken_links = [];
        $scanned_links_count = 0;

        if (empty($posts)) {
            return [
                'scanned_posts' => 0,
                'scanned_links' => 0,
                'broken_links'  => [],
            ];
        }

        $url_cache = [];

        foreach ($posts as $post) {
            preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\']/i', $post->post_content, $matches);
            if (empty($matches[1])) {
                continue;
            }

            $urls = array_unique($matches[1]);
            foreach ($urls as $url) {
                $url = trim($url);

                if (empty($url) || '#' === $url[0] || 0 === strpos($url, 'mailto:') || 0 === strpos($url, 'tel:') || 0 === strpos($url, 'javascript:')) {
                    continue;
                }

                $scanned_links_count++;

                if (!isset($url_cache[$url])) {
                    $status_code = self::check_url_status($url);
                    $url_cache[$url] = $status_code;
                } else {
                    $status_code = $url_cache[$url];
                }

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
            'sslverify'   => false,
            'user-agent'  => 'MZI-Broken-Link-Checker/1.0',
        ];

        $response = wp_remote_head($url, $args);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
            $response = wp_remote_get($url, $args);
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

            foreach ($columns as $column) {
                $col_name = $column['Field'];
                $type = strtolower($column['Type']);

                if (strpos($type, 'char') === false && strpos($type, 'text') === false && strpos($type, 'blob') === false) {
                    continue;
                }

                if ($use_regex) {
                    $clean_regex = trim($search, '/#~@');
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

                $changed_in_col = 0;

                foreach ($rows as $row) {
                    $pk_val = $row[$primary_key];
                    $original_val = $row[$col_name];
                    $new_val = self::recursive_unserialize_replace($search, $replace, $original_val, $use_regex, $regex_pattern);

                    if ($original_val !== $new_val) {
                        $changed_in_col++;
                        $total_updates++;

                        if (!$dry_run) {
                            $wpdb->update(
                                $table,
                                [$col_name => $new_val],
                                [$primary_key => $pk_val]
                            );
                        }
                    }
                }

                if ($changed_in_col > 0) {
                    $details[] = [
                        'table'  => $table,
                        'column' => $col_name,
                        'count'  => $changed_in_col,
                    ];
                }
            }
        }

        return [
            'total_updates'  => $total_updates,
            'tables_scanned' => $tables_scanned,
            'details'        => $details,
        ];
    }

    private static function format_regex_pattern($pattern) {
        if (preg_match('/^([\/#~@]).*\1[a-z]*$/i', $pattern)) {
            return $pattern;
        }

        return '/' . str_replace('/', '\/', $pattern) . '/i';
    }

    private static function recursive_unserialize_replace($search, $replace, $data, $use_regex = false, $regex_pattern = '') {
        if (is_string($data)) {
            if (is_serialized($data)) {
                $unserialized = @unserialize($data);
                if (false !== $unserialized || 'b:0;' === $data) {
                    $replaced = self::recursive_unserialize_replace($search, $replace, $unserialized, $use_regex, $regex_pattern);
                    return serialize($replaced);
                }
            }

            if ($use_regex) {
                $pattern = !empty($regex_pattern) ? $regex_pattern : self::format_regex_pattern($search);
                $res = @preg_replace($pattern, $replace, $data);
                return null !== $res ? $res : $data;
            }

            return str_replace($search, $replace, $data);
        } elseif (is_array($data)) {
            $tmp = [];
            foreach ($data as $key => $value) {
                $tmp_key = self::recursive_unserialize_replace($search, $replace, $key, $use_regex, $regex_pattern);
                $tmp[$tmp_key] = self::recursive_unserialize_replace($search, $replace, $value, $use_regex, $regex_pattern);
            }
            return $tmp;
        } elseif (is_object($data)) {
            $tmp = clone $data;
            foreach ($data as $key => $value) {
                $tmp_key = self::recursive_unserialize_replace($search, $replace, $key, $use_regex, $regex_pattern);
                $tmp->$tmp_key = self::recursive_unserialize_replace($search, $replace, $value, $use_regex, $regex_pattern);
            }
            return $tmp;
        }

        return $data;
    }
}
