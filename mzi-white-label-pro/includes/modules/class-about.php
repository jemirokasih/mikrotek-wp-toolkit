<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_About {

    public static function render_about_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mzi-white-label-pro'));
        }

        $current_version = MZI_WLP_VERSION;
        $latest_version  = MZI_WLP_VERSION;
        $changelog       = class_exists('MZI_White_Label_Pro_Changelog') ? MZI_White_Label_Pro_Changelog::entries() : [];

        $check_message = '';
        if (isset($_POST['mzi_wlp_action']) && 'check_updates' === $_POST['mzi_wlp_action']) {
            check_admin_referer('mzi_wlp_check_updates_action', 'mzi_wlp_nonce');
            $check_message = 'Versi plugin Anda (' . $current_version . ') adalah versi terbaru. Tidak ada pembaharuan tersedia saat ini.';
        }

        ?>
        <div class="wrap mzi-wlp-wrap">
            <h1>Tentang MZI White Label Pro</h1>
            <p class="description">
                Informasi versi plugin, status pembaharuan, ringkasan sistem, dan catatan perubahan (changelog).
            </p>

            <?php if (!empty($check_message)) : ?>
                <div class="notice notice-success is-dismissible" style="margin-top: 15px;">
                    <p><strong><?php echo esc_html($check_message); ?></strong></p>
                </div>
            <?php endif; ?>

            <!-- Grid Layout -->
            <div style="display:grid;grid-template-columns: 2fr 1fr;gap:20px;margin-top:20px;">
                <!-- Left Column -->
                <div>
                    <!-- Card 1: Informasi Plugin & Pembaharuan -->
                    <div class="mzi-wlp-card">
                        <div style="display:flex;align-items:center;gap:15px;margin-bottom:15px;">
                            <div style="background:#4f46e5;color:#fff;width:54px;height:54px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                <span class="dashicons dashicons-admin-customizer" style="font-size:32px;width:32px;height:32px;"></span>
                            </div>
                            <div>
                                <h2 style="margin:0;font-size:20px;">MZI White Label Pro</h2>
                                <p class="description" style="margin:2px 0 0 0;">Solusi Lengkap White Label, Security, Redirection & Audit Trail WordPress.</p>
                            </div>
                        </div>

                        <hr style="border:0;border-top:1px solid #e2e8f0;margin:15px 0;">

                        <table class="form-table mzi-wlp-form-table" style="margin:0;">
                            <tr>
                                <th scope="row">Versi Terpasang</th>
                                <td>
                                    <span class="badge" style="background:#e0e7ff;color:#3730a3;padding:4px 10px;border-radius:6px;font-weight:700;font-size:13px;">
                                        v<?php echo esc_html($current_version); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Pengembang (Developer)</th>
                                <td><strong>MZI Creative Team</strong></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Card 2: Log Pembaharuan (Changelog) -->
                    <div class="mzi-wlp-card" style="margin-top:20px;">
                        <h2>Log Pembaharuan (Changelog)</h2>
                        <p class="description">Riwayat perubahan dan fitur baru pada setiap versi rilis.</p>

                        <?php if (empty($changelog)) : ?>
                            <p class="description">Belum ada riwayat changelog tersedia.</p>
                        <?php else : ?>
                            <div style="margin-top:15px;">
                                <?php foreach ($changelog as $version => $categories) : ?>
                                    <div style="border-left:3px solid #4f46e5;padding-left:15px;margin-bottom:20px;">
                                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                                            <h3 style="margin:0;font-size:16px;">Versi <?php echo esc_html($version); ?></h3>
                                        </div>

                                        <?php if (is_array($categories)) : ?>
                                            <?php foreach ($categories as $cat_name => $items) : ?>
                                                <div style="margin-bottom:8px;">
                                                    <strong style="color:#475569;font-size:13px;"><?php echo esc_html($cat_name); ?>:</strong>
                                                    <ul style="list-style-type:disc;margin-left:18px;margin-top:4px;color:#334155;">
                                                        <?php if (is_array($items)) : ?>
                                                            <?php foreach ($items as $item) : ?>
                                                                <li style="margin-bottom:3px;"><?php echo esc_html($item); ?></li>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: System Environment -->
                <div>
                    <div class="mzi-wlp-card">
                        <h2>Informasi Sistem Server</h2>
                        <p class="description" style="margin-bottom:12px;">Ringkasan lingkungan runtime WordPress & PHP.</p>

                        <table class="widefat striped" style="font-size:12px;">
                            <tbody>
                                <tr>
                                    <td><strong>WordPress</strong></td>
                                    <td><code>v<?php echo esc_html(get_bloginfo('version')); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>PHP Version</strong></td>
                                    <td><code>v<?php echo esc_html(PHP_VERSION); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>Web Server</strong></td>
                                    <td><code><?php echo esc_html(isset($_SERVER['SERVER_SOFTWARE']) ? strtok($_SERVER['SERVER_SOFTWARE'], ' ') : 'N/A'); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Limit</strong></td>
                                    <td><code><?php echo esc_html(WP_MEMORY_LIMIT); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>Max Upload Size</strong></td>
                                    <td><code><?php echo esc_html(size_format(wp_max_upload_size())); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>MySQL Version</strong></td>
                                    <td><code><?php global $wpdb; echo esc_html($wpdb->db_version()); ?></code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
