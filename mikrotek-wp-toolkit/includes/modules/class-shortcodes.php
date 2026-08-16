<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Shortcodes {

    public function __construct() {
        $shortcodes = [
            'year'           => 'shortcode_year',
            'date'           => 'shortcode_date',
            'time'           => 'shortcode_time',
            'countdown'      => 'shortcode_countdown',
            'url'            => 'shortcode_url',
            'user'           => 'shortcode_user',
            'user_name'      => 'shortcode_user_name',
            'post_title'     => 'shortcode_post_title',
            'post_modified'  => 'shortcode_post_modified',
            'reading_time'   => 'shortcode_reading_time',
            'word_count'     => 'shortcode_word_count',
            'author'         => 'shortcode_author',
            'category'       => 'shortcode_category',
            'excerpt'        => 'shortcode_excerpt',
            'featured_image' => 'shortcode_featured_image',
            'site_title'     => 'shortcode_site_title',
            'site_tagline'   => 'shortcode_site_tagline',
            'user_ip'        => 'shortcode_user_ip',
        ];

        foreach ($shortcodes as $tag => $method) {
            add_shortcode('mikrotek_' . $tag, [$this, $method]);
            add_shortcode('wpt_' . $tag, [$this, $method]);
            add_shortcode('mzi_' . $tag, [$this, $method]);
        }
    }

    // 1. [mikrotek_year] / [wpt_year] / [mzi_year]
    public function shortcode_year() {
        return date('Y');
    }

    // 2. [mikrotek_date format="F j, Y"]
    public function shortcode_date($atts) {
        $a = shortcode_atts(['format' => get_option('date_format', 'F j, Y')], $atts);
        return date_i18n($a['format']);
    }

    // 3. [mikrotek_time format="H:i"]
    public function shortcode_time($atts) {
        $a = shortcode_atts(['format' => get_option('time_format', 'H:i')], $atts);
        return date_i18n($a['format']);
    }

    // 4. [mikrotek_countdown date="2026-12-31 23:59:59" text="Event Ended"]
    public function shortcode_countdown($atts) {
        $a = shortcode_atts([
            'date' => date('Y') . '-12-31 23:59:59',
            'text' => 'Event Complete',
        ], $atts);

        $target_time = strtotime($a['date']);
        if (!$target_time) {
            return '<span class="mikrotek-countdown-error">Invalid date format</span>';
        }

        $id = 'wpt_cd_' . uniqid();
        $target_iso = date('c', $target_time);

        ob_start();
        ?>
        <div id="<?php echo esc_attr($id); ?>" class="mikrotek-countdown-timer" style="display:inline-flex;gap:10px;align-items:center;font-family:sans-serif;font-weight:600;">
            <span class="wpt-cd-box" style="background:#1e293b;color:#fff;padding:6px 12px;border-radius:6px;min-width:45px;text-align:center;">
                <span class="wpt-cd-days">00</span><small style="display:block;font-weight:400;font-size:10px;color:#94a3b8;">HARI</small>
            </span>
            <span class="wpt-cd-box" style="background:#1e293b;color:#fff;padding:6px 12px;border-radius:6px;min-width:45px;text-align:center;">
                <span class="wpt-cd-hours">00</span><small style="display:block;font-weight:400;font-size:10px;color:#94a3b8;">JAM</small>
            </span>
            <span class="wpt-cd-box" style="background:#1e293b;color:#fff;padding:6px 12px;border-radius:6px;min-width:45px;text-align:center;">
                <span class="wpt-cd-mins">00</span><small style="display:block;font-weight:400;font-size:10px;color:#94a3b8;">MENIT</small>
            </span>
            <span class="wpt-cd-box" style="background:#1e293b;color:#fff;padding:6px 12px;border-radius:6px;min-width:45px;text-align:center;">
                <span class="wpt-cd-secs">00</span><small style="display:block;font-weight:400;font-size:10px;color:#94a3b8;">DETIK</small>
            </span>
        </div>
        <script>
        (function() {
            var target = new Date("<?php echo esc_js($target_iso); ?>").getTime();
            var timer = setInterval(function() {
                var now = new Date().getTime();
                var diff = target - now;
                var el = document.getElementById("<?php echo esc_js($id); ?>");
                if (!el) { clearInterval(timer); return; }
                if (diff <= 0) {
                    clearInterval(timer);
                    el.innerHTML = "<span>" + <?php echo wp_json_encode(esc_html($a['text'])); ?> + "</span>";
                    return;
                }
                var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var secs = Math.floor((diff % (1000 * 60)) / 1000);
                el.querySelector('.wpt-cd-days').innerText = String(days).padStart(2, '0');
                el.querySelector('.wpt-cd-hours').innerText = String(hours).padStart(2, '0');
                el.querySelector('.wpt-cd-mins').innerText = String(mins).padStart(2, '0');
                el.querySelector('.wpt-cd-secs').innerText = String(secs).padStart(2, '0');
            }, 1000);
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    // 5. [mikrotek_url type="home|site|theme|login"]
    public function shortcode_url($atts) {
        $a = shortcode_atts(['type' => 'home'], $atts);
        switch ($a['type']) {
            case 'theme':
                return get_stylesheet_directory_uri();
            case 'login':
                return wp_login_url();
            case 'site':
                return site_url();
            case 'home':
            default:
                return home_url();
        }
    }

    // 6. [mikrotek_user field="display_name" guest="Tamu"]
    public function shortcode_user($atts) {
        $a = shortcode_atts([
            'field' => 'display_name',
            'guest' => 'Guest',
        ], $atts);

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return esc_html($a['guest']);
        }

        $field = sanitize_key($a['field']);
        switch ($field) {
            case 'user_login':
                return esc_html($user->user_login);
            case 'user_email':
                return esc_html($user->user_email);
            case 'first_name':
                return esc_html($user->first_name);
            case 'last_name':
                return esc_html($user->last_name);
            case 'role':
                return esc_html(!empty($user->roles) ? implode(', ', $user->roles) : '');
            case 'display_name':
            default:
                return esc_html($user->display_name);
        }
    }

    // 7. [mikrotek_user_name guest="Tamu"]
    public function shortcode_user_name($atts) {
        return $this->shortcode_user($atts);
    }

    // 8. [mikrotek_post_title]
    public function shortcode_post_title() {
        return get_the_title();
    }

    // 9. [mikrotek_post_modified format="F j, Y"]
    public function shortcode_post_modified($atts) {
        $a = shortcode_atts(['format' => get_option('date_format', 'F j, Y')], $atts);
        return get_the_modified_date($a['format']);
    }

    // 10. [mikrotek_reading_time wpm="200"]
    public function shortcode_reading_time($atts) {
        $a = shortcode_atts(['wpm' => 200], $atts);
        $wpm = absint($a['wpm']);
        if ($wpm <= 0) {
            $wpm = 200;
        }

        $post = get_post();
        if (!$post) {
            return '1 min read';
        }

        $word_count = str_word_count(strip_tags($post->post_content));
        $minutes = ceil($word_count / $wpm);
        if ($minutes < 1) {
            $minutes = 1;
        }

        return sprintf('%d min read', $minutes);
    }

    // 11. [mikrotek_word_count]
    public function shortcode_word_count() {
        $post = get_post();
        if (!$post) {
            return '0';
        }
        return (string) str_word_count(strip_tags($post->post_content));
    }

    // 12. [mikrotek_author field="display_name"]
    public function shortcode_author($atts) {
        $a = shortcode_atts(['field' => 'display_name'], $atts);
        $author_id = get_the_author_meta('ID');
        if (!$author_id) {
            return '';
        }

        $field = sanitize_key($a['field']);
        if ('description' === $field) {
            return esc_html(get_the_author_meta('description', $author_id));
        }

        return esc_html(get_the_author_meta('display_name', $author_id));
    }

    // 13. [mikrotek_category link="false"]
    public function shortcode_category($atts) {
        $a = shortcode_atts(['link' => 'false'], $atts);
        $categories = get_the_category();
        if (empty($categories)) {
            return '';
        }

        $items = [];
        $with_link = ('true' === strtolower($a['link']));

        foreach ($categories as $cat) {
            if ($with_link) {
                $items[] = '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
            } else {
                $items[] = esc_html($cat->name);
            }
        }

        return implode(', ', $items);
    }

    // 14. [mikrotek_excerpt length="20"]
    public function shortcode_excerpt($atts) {
        $a = shortcode_atts(['length' => 20], $atts);
        $length = absint($a['length']);
        if ($length <= 0) {
            $length = 20;
        }

        $post = get_post();
        if (!$post) {
            return '';
        }

        $excerpt = get_the_excerpt($post);
        return esc_html(wp_trim_words($excerpt, $length, '...'));
    }

    // 15. [mikrotek_featured_image size="medium" output="html" class=""]
    public function shortcode_featured_image($atts) {
        $a = shortcode_atts([
            'size'   => 'medium',
            'output' => 'html',
            'class'  => '',
        ], $atts);

        $post_id = get_the_ID();
        if (!$post_id || !has_post_thumbnail($post_id)) {
            return '';
        }

        $size = sanitize_key($a['size']);

        if ('url' === strtolower($a['output'])) {
            return esc_url(get_the_post_thumbnail_url($post_id, $size));
        }

        return get_the_post_thumbnail($post_id, $size, ['class' => sanitize_html_class($a['class'])]);
    }

    // 16. [mikrotek_site_title]
    public function shortcode_site_title() {
        return esc_html(get_bloginfo('name'));
    }

    // 17. [mikrotek_site_tagline]
    public function shortcode_site_tagline() {
        return esc_html(get_bloginfo('description'));
    }

    // 18. [mikrotek_user_ip]
    public function shortcode_user_ip() {
        return esc_html(isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'Unknown IP');
    }

    // Render Admin Documentation & Preview Page
    public static function render_shortcodes_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mikrotek-wp-toolkit'));
        }

        $shortcode_list = [
            [
                'code'        => '[wpt_year]',
                'title'       => 'Tahun Saat Ini (Current Year)',
                'desc'        => 'Menampilkan tahun 4 digit secara dinamis (contoh: ' . date('Y') . '). Sangat cocok untuk teks hak cipta/copyright footer. Alias: [mikrotek_year] atau [mzi_year].',
                'params'      => 'Tidak ada parameter.',
                'example'     => '© [wpt_year] NamaPerusahaan. All Rights Reserved.',
                'preview'     => date('Y'),
            ],
            [
                'code'        => '[wpt_date format="F j, Y"]',
                'title'       => 'Tanggal Format Kustom',
                'desc'        => 'Menampilkan tanggal saat ini sesuai format tanggal PHP/WordPress. Alias: [mikrotek_date].',
                'params'      => '<code>format</code> (Default: format tanggal WP)',
                'example'     => 'Hari ini tanggal [wpt_date format="l, j F Y"]',
                'preview'     => date_i18n('l, j F Y'),
            ],
            [
                'code'        => '[wpt_time format="H:i"]',
                'title'       => 'Waktu Saat Ini',
                'desc'        => 'Menampilkan jam/waktu lokal server WordPress saat ini. Alias: [mikrotek_time].',
                'params'      => '<code>format</code> (Default: format waktu WP)',
                'example'     => 'Waktu server: [wpt_time format="H:i:s T"]',
                'preview'     => date_i18n('H:i:s T'),
            ],
            [
                'code'        => '[wpt_countdown date="2026-12-31 23:59:59" text="Selesai"]',
                'title'       => 'Hitung Mundur (Countdown Timer)',
                'desc'        => 'Menampilkan timer hitung mundur interaktif berbasis JavaScript (Hari, Jam, Menit, Detik). Alias: [mikrotek_countdown].',
                'params'      => '<code>date</code> (YYYY-MM-DD HH:MM:SS), <code>text</code> (Pesan saat waktu habis)',
                'example'     => '[wpt_countdown date="' . date('Y') . '-12-31 23:59:59" text="Promo Berakhir!"]',
                'preview'     => (new self())->shortcode_countdown(['date' => date('Y') . '-12-31 23:59:59']),
            ],
            [
                'code'        => '[wpt_url type="home"]',
                'title'       => 'URL Situs & Tema',
                'desc'        => 'Menghasilkan URL situs secara dinamis. Alias: [mikrotek_url].',
                'params'      => '<code>type</code>: <code>home</code> | <code>site</code> | <code>theme</code> | <code>login</code>',
                'example'     => '<a href="[wpt_url type="login"]">Masuk Ke Akun</a>',
                'preview'     => home_url(),
            ],
            [
                'code'        => '[wpt_user field="display_name" guest="Tamu"]',
                'title'       => 'Informasi Pengguna Login',
                'desc'        => 'Menampilkan detail pengguna yang sedang login saat ini. Alias: [mikrotek_user].',
                'params'      => '<code>field</code>: <code>display_name</code> | <code>user_login</code> | <code>user_email</code> | <code>first_name</code> | <code>last_name</code> | <code>role</code>, <code>guest</code> (Teks jika belum login)',
                'example'     => 'Halo, selamat datang [wpt_user field="display_name" guest="Pengunjung"]!',
                'preview'     => (new self())->shortcode_user([]),
            ],
            [
                'code'        => '[wpt_user_name]',
                'title'       => 'Nama Pengguna (Shortcut)',
                'desc'        => 'Shortcut cepat untuk menampilkan nama tampilan pengguna yang sedang masuk. Alias: [mikrotek_user_name].',
                'params'      => '<code>guest</code> (Teks fallback)',
                'example'     => 'Selamat datang kembali, [wpt_user_name]!',
                'preview'     => (new self())->shortcode_user([]),
            ],
            [
                'code'        => '[wpt_post_title]',
                'title'       => 'Judul Pos / Halaman',
                'desc'        => 'Menampilkan judul dari artikel atau halaman tempat shortcode dipasang. Alias: [mikrotek_post_title].',
                'params'      => 'Tidak ada parameter.',
                'example'     => 'Anda sedang membaca: [wpt_post_title]',
                'preview'     => 'Contoh Judul Artikel',
            ],
            [
                'code'        => '[wpt_post_modified format="F j, Y"]',
                'title'       => 'Tanggal Terakhir Diperbarui',
                'desc'        => 'Menampilkan tanggal kapan artikel/halaman terakhir disunting. Alias: [mikrotek_post_modified].',
                'params'      => '<code>format</code> (Default: format tanggal WP)',
                'example'     => 'Artikel ini terakhir diperbarui pada [wpt_post_modified]',
                'preview'     => date_i18n(get_option('date_format', 'F j, Y')),
            ],
            [
                'code'        => '[wpt_reading_time wpm="200"]',
                'title'       => 'Estimasi Waktu Baca (Reading Time)',
                'desc'        => 'Kalkulasi estimasi lama waktu membaca berdasarkan jumlah kata dalam konten. Alias: [mikrotek_reading_time].',
                'params'      => '<code>wpm</code> (Words per minute, Default: 200)',
                'example'     => '⏱️ [wpt_reading_time wpm="200"]',
                'preview'     => '2 min read',
            ],
            [
                'code'        => '[wpt_word_count]',
                'title'       => 'Jumlah Kata Konten',
                'desc'        => 'Menampilkan total jumlah kata pada artikel/halaman. Alias: [mikrotek_word_count].',
                'params'      => 'Tidak ada parameter.',
                'example'     => 'Total kata dalam artikel: [wpt_word_count] kata',
                'preview'     => '450',
            ],
            [
                'code'        => '[wpt_author field="display_name"]',
                'title'       => 'Penulis Artikel (Author)',
                'desc'        => 'Menampilkan nama atau biografi penulis artikel. Alias: [mikrotek_author].',
                'params'      => '<code>field</code>: <code>display_name</code> | <code>description</code>',
                'example'     => 'Ditulis oleh: [wpt_author field="display_name"]',
                'preview'     => (new self())->shortcode_user([]),
            ],
            [
                'code'        => '[wpt_category link="false"]',
                'title'       => 'Kategori Artikel',
                'desc'        => 'Menampilkan daftar kategori pos (teks biasa atau dengan tautan URL). Alias: [mikrotek_category].',
                'params'      => '<code>link</code>: <code>true</code> | <code>false</code>',
                'example'     => 'Kategori: [wpt_category link="true"]',
                'preview'     => 'Berita, Tutorial',
            ],
            [
                'code'        => '[wpt_excerpt length="20"]',
                'title'       => 'Ringkasan Artikel (Excerpt)',
                'desc'        => 'Menampilkan cuplikan singkat ringkasan pos dengan jumlah kata yang dapat disesuaikan. Alias: [mikrotek_excerpt].',
                'params'      => '<code>length</code> (Default: 20 kata)',
                'example'     => '[wpt_excerpt length="15"]',
                'preview'     => 'Ini adalah contoh cuplikan ringkasan artikel yang akan tampil pada frontend...',
            ],
            [
                'code'        => '[wpt_featured_image size="medium" output="html"]',
                'title'       => 'Gambar Utama (Featured Image)',
                'desc'        => 'Menampilkan tag gambar HTML atau URL langsung dari Featured Image pos. Alias: [mikrotek_featured_image].',
                'params'      => '<code>size</code>: <code>thumbnail</code> | <code>medium</code> | <code>large</code> | <code>full</code>, <code>output</code>: <code>html</code> | <code>url</code>, <code>class</code> (Class CSS)',
                'example'     => '[wpt_featured_image size="large" class="img-responsive"]',
                'preview'     => '<em>[HTML Image Tag / URL Featured Image]</em>',
            ],
            [
                'code'        => '[wpt_site_title]',
                'title'       => 'Judul Situs (Site Title)',
                'desc'        => 'Menampilkan nama situs WordPress. Alias: [mikrotek_site_title].',
                'params'      => 'Tidak ada parameter.',
                'example'     => 'Selamat datang di [wpt_site_title]',
                'preview'     => get_bloginfo('name'),
            ],
            [
                'code'        => '[wpt_site_tagline]',
                'title'       => 'Slogan Situs (Site Tagline)',
                'desc'        => 'Menampilkan deskripsi/slogan situs WordPress. Alias: [mikrotek_site_tagline].',
                'params'      => 'Tidak ada parameter.',
                'example'     => '[wpt_site_title] - [wpt_site_tagline]',
                'preview'     => get_bloginfo('description'),
            ],
            [
                'code'        => '[wpt_user_ip]',
                'title'       => 'Alamat IP Pengunjung',
                'desc'        => 'Menampilkan alamat IP publik milik pengunjung. Alias: [mikrotek_user_ip].',
                'params'      => 'Tidak ada parameter.',
                'example'     => 'IP Anda: [wpt_user_ip]',
                'preview'     => (new self())->shortcode_user_ip(),
            ],
        ];

        ?>
        <div class="wrap mikrotek-wpt-wrap">
            <h1>Kumpulan Shortcode Mikrotek WP Toolkit</h1>
            <p class="description">
                Gunakan shortcode praktis ini di dalam Editor Post, Page, Widget, atau Page Builder (Elementor, Gutenberg, Divi) untuk mengurutkan informasi dinamis tanpa membebankan performa situs.
            </p>

            <div style="margin-top:20px;display:grid;grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));gap:20px;">
                <?php foreach ($shortcode_list as $item) : ?>
                    <div class="mikrotek-wpt-card" style="display:flex;flex-direction:column;justify-content:space-between;margin:0;background:#fff;border:1px solid #ccd0d4;padding:16px;border-radius:8px;">
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;">
                                <h2 style="margin:0;font-size:16px;color:#1e293b;"><?php echo esc_html($item['title']); ?></h2>
                                <button type="button" class="button button-small mikrotek-copy-code" data-code="<?php echo esc_attr($item['code']); ?>" title="Salin Shortcode">
                                    <span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;vertical-align:middle;"></span> Copy
                                </button>
                            </div>

                            <div style="background:#f1f5f9;border-left:4px solid #4f46e5;padding:8px 12px;border-radius:4px;margin-bottom:12px;">
                                <code style="color:#312e81;font-weight:700;font-size:13px;word-break:break-all;"><?php echo esc_html($item['code']); ?></code>
                            </div>

                            <p style="margin:0 0 10px 0;color:#475569;font-size:13px;line-height:1.5;">
                                <?php echo esc_html($item['desc']); ?>
                            </p>

                            <p style="margin:0 0 8px 0;font-size:12px;color:#64748b;">
                                <strong>Parameter:</strong> <?php echo wp_kses_post($item['params']); ?>
                            </p>

                            <div style="background:#fafafa;border:1px solid #e2e8f0;padding:8px 10px;border-radius:6px;font-size:12px;margin-bottom:10px;">
                                <strong style="color:#334155;display:block;margin-bottom:3px;">Contoh Penggunaan:</strong>
                                <code><?php echo esc_html($item['example']); ?></code>
                            </div>
                        </div>

                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:8px 10px;border-radius:6px;font-size:12px;margin-top:5px;">
                            <strong style="color:#166534;display:block;margin-bottom:2px;">Live Preview Hasil:</strong>
                            <div style="color:#14532d;font-weight:600;">
                                <?php echo wp_kses_post($item['preview']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var copyBtns = document.querySelectorAll('.mikrotek-copy-code');
            copyBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var code = this.getAttribute('data-code');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).then(function() {
                            btn.innerText = 'Copied!';
                            setTimeout(function() {
                                btn.innerHTML = '<span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;vertical-align:middle;"></span> Copy';
                            }, 2000);
                        });
                    } else {
                        var temp = document.createElement('textarea');
                        temp.value = code;
                        document.body.appendChild(temp);
                        temp.select();
                        document.execCommand('copy');
                        document.body.removeChild(temp);
                        btn.innerText = 'Copied!';
                        setTimeout(function() {
                            btn.innerHTML = '<span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;vertical-align:middle;"></span> Copy';
                        }, 2000);
                    }
                });
            });
        });
        </script>
        <?php
    }
}


