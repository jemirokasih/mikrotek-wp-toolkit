<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Redirects {

    const OPTION_RULES = 'mikrotek_wp_toolkit_redirect_rules';
    const OLD_OPTION_RULES = 'mzi_white_label_redirect_rules';

    public function __construct() {
        add_action('template_redirect', [$this, 'handle_frontend_redirects'], 1);
    }

    public static function get_rules() {
        $rules = get_option(self::OPTION_RULES, null);
        if (null === $rules || false === $rules) {
            $old_rules = get_option(self::OLD_OPTION_RULES, []);
            if (!empty($old_rules) && is_array($old_rules)) {
                update_option(self::OPTION_RULES, $old_rules);
                return $old_rules;
            }
            $rules = [];
        }
        return is_array($rules) ? $rules : [];
    }

    public static function save_rules($rules) {
        update_option(self::OPTION_RULES, is_array($rules) ? $rules : []);
    }

    public function handle_frontend_redirects() {
        if (is_admin()) {
            return;
        }

        $rules = self::get_rules();
        if (empty($rules)) {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? rawurldecode(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $req_path    = untrailingslashit(wp_parse_url($request_uri, PHP_URL_PATH));

        foreach ($rules as $index => $rule) {
            if (empty($rule['status']) || empty($rule['from_url']) || empty($rule['to_url'])) {
                continue;
            }

            $from_path = untrailingslashit(wp_parse_url($rule['from_url'], PHP_URL_PATH));
            if (empty($from_path)) {
                $from_path = untrailingslashit($rule['from_url']);
            }

            if ($req_path === $from_path || $request_uri === $rule['from_url']) {
                $rules[$index]['hits'] = isset($rule['hits']) ? absint($rule['hits']) + 1 : 1;
                self::save_rules($rules);

                $to_url = $rule['to_url'];
                $type   = in_array(absint($rule['type']), [301, 302, 307], true) ? absint($rule['type']) : 301;

                if (0 !== strpos($to_url, 'http://') && 0 !== strpos($to_url, 'https://')) {
                    $to_url = home_url('/' . ltrim($to_url, '/'));
                }

                wp_redirect(wp_sanitize_redirect($to_url), $type);
                exit;
            }
        }
    }

    public static function render_redirects_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mikrotek-wp-toolkit'));
        }

        $rules = self::get_rules();
        $message = '';
        $message_type = 'updated';

        // Handle Add New Redirect
        if (isset($_POST['mikrotek_wpt_action']) && 'add_redirect' === $_POST['mikrotek_wpt_action']) {
            check_admin_referer('mikrotek_wpt_redirect_add_action', 'mikrotek_wpt_nonce');

            $from_url = isset($_POST['from_url']) ? trim(sanitize_text_field(wp_unslash($_POST['from_url']))) : '';
            $to_url   = isset($_POST['to_url']) ? trim(sanitize_text_field(wp_unslash($_POST['to_url']))) : '';
            $type     = isset($_POST['type']) ? absint($_POST['type']) : 301;

            if (!empty($from_url) && !empty($to_url)) {
                $new_rule = [
                    'id'       => 'red_' . uniqid(),
                    'from_url' => $from_url,
                    'to_url'   => $to_url,
                    'type'     => in_array($type, [301, 302, 307], true) ? $type : 301,
                    'status'   => 1,
                    'hits'     => 0,
                    'created'  => current_time('mysql'),
                ];

                $rules[] = $new_rule;
                self::save_rules($rules);
                $message = 'Aturan redirection baru berhasil ditambahkan.';
            } else {
                $message = 'Mohon isi URL Asal (From) dan URL Tujuan (To) dengan benar.';
                $message_type = 'error';
            }
        }

        // Handle Actions: Delete or Toggle Status
        if (isset($_GET['action']) && isset($_GET['rule_id'])) {
            $action  = sanitize_key($_GET['action']);
            $rule_id = sanitize_key($_GET['rule_id']);

            if (check_admin_referer('mikrotek_wpt_redirect_item_' . $rule_id)) {
                if ('delete' === $action) {
                    $rules = array_values(array_filter($rules, function($item) use ($rule_id) {
                        return isset($item['id']) && $item['id'] !== $rule_id;
                    }));
                    self::save_rules($rules);
                    $message = 'Aturan redirection berhasil dihapus.';
                } elseif ('toggle' === $action) {
                    foreach ($rules as $i => $item) {
                        if (isset($item['id']) && $item['id'] === $rule_id) {
                            $rules[$i]['status'] = empty($item['status']) ? 1 : 0;
                            break;
                        }
                    }
                    self::save_rules($rules);
                    $message = 'Status redirection berhasil diperbarui.';
                }
            }
        }

        ?>
        <div class="wrap mikrotek-wpt-wrap">
            <h1>Mikrotek Redirection Manager</h1>
            <p class="description">
                Kelola pengalihan URL 301 (Permanent), 302 (Temporary), dan 307 (Strict Temporary) dengan mudah untuk mencegah 404 Not Found dan menjaga kualitas SEO.
            </p>

            <?php if (!empty($message)) : ?>
                <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible" style="margin-top: 15px;">
                    <p><strong><?php echo esc_html($message); ?></strong></p>
                </div>
            <?php endif; ?>

            <!-- Form Tambah Redirection -->
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 20px;">
                <h2>Tambah Aturan Redirection Baru</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('mikrotek_wpt_redirect_add_action', 'mikrotek_wpt_nonce'); ?>
                    <input type="hidden" name="mikrotek_wpt_action" value="add_redirect">

                    <table class="form-table mikrotek-wpt-form-table">
                        <tr>
                            <th scope="row"><label for="from_url">URL Asal (From Path)</label></th>
                            <td>
                                <input type="text" id="from_url" name="from_url" class="regular-text" placeholder="/halaman-lama atau /produk-lama" required>
                                <p class="description">Masukkan slug path atau URL lama (contoh: <code>/about-us</code> atau <code>/diskon-2025</code>).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="to_url">URL Tujuan (To Target)</label></th>
                            <td>
                                <input type="text" id="to_url" name="to_url" class="regular-text" placeholder="/halaman-baru atau https://domain.com/tujuan" required>
                                <p class="description">Masukkan slug path baru atau URL lengkap tujuan pengalihan.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="type">Tipe Redirection (HTTP Status)</label></th>
                            <td>
                                <select id="type" name="type">
                                    <option value="301">301 - Moved Permanently (Direkomendasikan untuk SEO)</option>
                                    <option value="302">302 - Found / Temporary Redirect</option>
                                    <option value="307">307 - Temporary Redirect (Strict Method)</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <div class="mikrotek-wpt-submit-row">
                        <button type="submit" class="button button-primary">Tambah Redirection</button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Redirection -->
            <div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top: 24px;">
                <h2>Daftar Redirection Aktif (<?php echo count($rules); ?>)</h2>

                <?php if (empty($rules)) : ?>
                    <p class="description">Belum ada aturan redirection yang dibuat.</p>
                <?php else : ?>
                    <table class="widefat striped" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>URL Asal (From)</th>
                                <th>URL Tujuan (To)</th>
                                <th>Tipe HTTP</th>
                                <th>Jumlah Pengalihan (Hits)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule) : ?>
                                <?php
                                $toggle_url = wp_nonce_url(
                                    admin_url('admin.php?page=mikrotek-wp-toolkit-redirects&action=toggle&rule_id=' . $rule['id']),
                                    'mikrotek_wpt_redirect_item_' . $rule['id']
                                );
                                $delete_url = wp_nonce_url(
                                    admin_url('admin.php?page=mikrotek-wp-toolkit-redirects&action=delete&rule_id=' . $rule['id']),
                                    'mikrotek_wpt_redirect_item_' . $rule['id']
                                );
                                ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($rule['status'])) : ?>
                                            <span class="badge" style="background:#dcfce7;color:#15803d;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">ACTIVE</span>
                                        <?php else : ?>
                                            <span class="badge" style="background:#fee2e2;color:#b91c1c;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">DISABLED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?php echo esc_html($rule['from_url']); ?></code></td>
                                    <td><a href="<?php echo esc_url($rule['to_url']); ?>" target="_blank"><code><?php echo esc_html($rule['to_url']); ?></code></a></td>
                                    <td>
                                        <span class="badge" style="background:#e0e7ff;color:#3730a3;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11px;">
                                            HTTP <?php echo esc_html($rule['type']); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo esc_html(isset($rule['hits']) ? $rule['hits'] : 0); ?></strong> hits</td>
                                    <td>
                                        <a href="<?php echo esc_url($toggle_url); ?>" class="button button-small">
                                            <?php echo !empty($rule['status']) ? 'Disable' : 'Enable'; ?>
                                        </a>
                                        <a href="<?php echo esc_url($delete_url); ?>" class="button button-small button-link-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus redirection ini?');">
                                            Hapus
                                        </a>
                                    </td>
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
if (!class_exists('MZI_White_Label_Pro_Redirects')) {
    class_alias('Mikrotek_WP_Toolkit_Redirects', 'MZI_White_Label_Pro_Redirects');
}
