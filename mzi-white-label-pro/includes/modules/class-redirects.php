<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Redirects {

    const OPTION_NAME = 'mzi_white_label_redirects';

    public function __construct() {
        add_action('template_redirect', [$this, 'handle_redirection'], 1);
    }

    public static function get_rules() {
        $rules = get_option(self::OPTION_NAME, []);
        return is_array($rules) ? $rules : [];
    }

    public static function save_rules($rules) {
        update_option(self::OPTION_NAME, is_array($rules) ? $rules : []);
    }

    public function handle_redirection() {
        if (is_admin()) {
            return;
        }

        $rules = self::get_rules();
        if (empty($rules)) {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI']) ? rawurldecode(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if (empty($request_uri)) {
            return;
        }

        $path = strtok($request_uri, '?');
        $path_normalized = untrailingslashit($path);

        foreach ($rules as $index => $rule) {
            if (empty($rule['status'])) {
                continue;
            }

            $from_raw = trim($rule['from_url']);
            if (empty($from_raw)) {
                continue;
            }

            $from = untrailingslashit($from_raw);
            if (0 !== strpos($from, 'http://') && 0 !== strpos($from, 'https://')) {
                if ('/' !== substr($from, 0, 1)) {
                    $from = '/' . $from;
                }
            }

            if ($from === $path_normalized || $from === $path || $from_raw === $request_uri) {
                $rules[$index]['hits'] = isset($rule['hits']) ? absint($rule['hits']) + 1 : 1;
                self::save_rules($rules);

                $to_url = trim($rule['to_url']);
                $type   = isset($rule['type']) ? absint($rule['type']) : 301;
                if (!in_array($type, [301, 302, 307], true)) {
                    $type = 301;
                }

                wp_redirect($to_url, $type);
                exit;
            }
        }
    }

    public static function render_redirects_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mzi-white-label-pro'));
        }

        $rules = self::get_rules();
        $message = '';
        $message_type = 'updated';

        // Handle Add New Redirect
        if (isset($_POST['mzi_wlp_action']) && 'add_redirect' === $_POST['mzi_wlp_action']) {
            check_admin_referer('mzi_wlp_redirect_add_action', 'mzi_wlp_nonce');

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

            if (check_admin_referer('mzi_wlp_redirect_item_' . $rule_id)) {
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
        <div class="wrap mzi-wlp-wrap">
            <h1>MZI Redirection Manager</h1>
            <p class="description">
                Kelola aturan pengalihan URL (Redirection) dari link lama ke link baru dengan tipe Permanen (301) atau Temporer (302 / 307).
            </p>

            <?php if (!empty($message)) : ?>
                <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible" style="margin-top: 15px;">
                    <p><strong><?php echo esc_html($message); ?></strong></p>
                </div>
            <?php endif; ?>

            <!-- Form Add New Redirect -->
            <div class="mzi-wlp-card" style="margin-top: 20px;">
                <h2>Tambah Redirection Baru</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('mzi_wlp_redirect_add_action', 'mzi_wlp_nonce'); ?>
                    <input type="hidden" name="mzi_wlp_action" value="add_redirect">

                    <table class="form-table mzi-wlp-form-table">
                        <tr>
                            <th scope="row"><label for="from_url">From URL / Path</label></th>
                            <td>
                                <input type="text" id="from_url" name="from_url" class="large-text" required
                                       placeholder="/url-lama atau /artikel-lama">
                                <p class="description">URL asal yang ingin dialihkan (dapat berupa relative path seperti <code>/url-lama</code> atau full URL).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="to_url">To Destination URL</label></th>
                            <td>
                                <input type="text" id="to_url" name="to_url" class="large-text" required
                                       placeholder="/url-baru atau https://domain-tujuan.com/artikel-baru">
                                <p class="description">URL tujuan tempat pengunjung akan diarahkan.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="type">Redirect Type</label></th>
                            <td>
                                <select id="type" name="type">
                                    <option value="301">301 - Permanent Moved (Direkomendasikan SEO)</option>
                                    <option value="302">302 - Temporary Moved (Sementara)</option>
                                    <option value="307">307 - Temporary Redirect (HTTP 1.1 Strict)</option>
                                </select>
                                <p class="description">Pilih 301 untuk pengalihan permanen atau 302/307 untuk sementara.</p>
                            </td>
                        </tr>
                    </table>

                    <div class="mzi-wlp-submit-row">
                        <button type="submit" class="button button-primary">Tambah Aturan Redirection</button>
                    </div>
                </form>
            </div>

            <!-- List Redirect Rules -->
            <div class="mzi-wlp-card" style="margin-top: 24px;">
                <h2>Daftar Redirection Aktif (<?php echo count($rules); ?>)</h2>

                <?php if (empty($rules)) : ?>
                    <p class="description" style="padding: 12px 0;">Belum ada aturan redirection yang dibuat.</p>
                <?php else : ?>
                    <table class="widefat striped" style="margin-top: 12px;">
                        <thead>
                            <tr>
                                <th>From (URL Asal)</th>
                                <th>To (URL Tujuan)</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Hits (Pengunjung)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule) :
                                $toggle_url = wp_nonce_url(
                                    admin_url('admin.php?page=mzi-white-label-redirects&action=toggle&rule_id=' . $rule['id']),
                                    'mzi_wlp_redirect_item_' . $rule['id']
                                );
                                $delete_url = wp_nonce_url(
                                    admin_url('admin.php?page=mzi-white-label-redirects&action=delete&rule_id=' . $rule['id']),
                                    'mzi_wlp_redirect_item_' . $rule['id']
                                );
                                ?>
                                <tr>
                                    <td><code><?php echo esc_html($rule['from_url']); ?></code></td>
                                    <td><a href="<?php echo esc_url($rule['to_url']); ?>" target="_blank"><code><?php echo esc_html($rule['to_url']); ?></code></a></td>
                                    <td>
                                        <span class="badge" style="background:#e0e7ff;color:#3730a3;padding:2px 8px;border-radius:4px;font-weight:600;font-size:12px;">
                                            HTTP <?php echo esc_html($rule['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($rule['status'])) : ?>
                                            <span style="color:#059669;font-weight:600;">● Active</span>
                                        <?php else : ?>
                                            <span style="color:#9ca3af;font-weight:600;">○ Disabled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo esc_html(isset($rule['hits']) ? $rule['hits'] : 0); ?></strong> hits</td>
                                    <td>
                                        <a href="<?php echo esc_url($toggle_url); ?>" class="button button-small">
                                            <?php echo !empty($rule['status']) ? 'Disable' : 'Enable'; ?>
                                        </a>
                                        <a href="<?php echo esc_url($delete_url); ?>" class="button button-small button-link-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus aturan redirection ini?');">
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
