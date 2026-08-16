<?php

if (!defined('ABSPATH')) {
    exit;
}

$slug_presets = [
    ['slug' => 'edit.php?post_type=product', 'label' => 'Products (WooCommerce)'],
    ['slug' => 'woocommerce', 'label' => 'WooCommerce'],
    ['slug' => 'elementor', 'label' => 'Elementor'],
    ['slug' => 'wpcf7', 'label' => 'Contact Form 7'],
    ['slug' => 'edit.php?post_type=page', 'label' => 'Pages'],
    ['slug' => 'upload.php', 'label' => 'Media'],
    ['slug' => 'edit-comments.php', 'label' => 'Comments'],
    ['slug' => 'tools.php', 'label' => 'Tools'],
    ['slug' => 'plugins.php', 'label' => 'Plugins'],
    ['slug' => 'options-general.php', 'label' => 'Settings'],
];

$rename_presets = [
    ['rule' => 'edit.php|Artikel Blog', 'label' => 'Posts ➔ Artikel Blog'],
    ['rule' => 'edit.php?post_type=page|Halaman Website', 'label' => 'Pages ➔ Halaman Website'],
    ['rule' => 'upload.php|Galeri Media', 'label' => 'Media ➔ Galeri Media'],
    ['rule' => 'edit-comments.php|Ulasan Pelanggan', 'label' => 'Comments ➔ Ulasan Pelanggan'],
    ['rule' => 'plugins.php|Ekstensi Web', 'label' => 'Plugins ➔ Ekstensi Web'],
    ['rule' => 'options-general.php|Pengaturan Sistem', 'label' => 'Settings ➔ Pengaturan Sistem'],
];

// Custom Hidden Menus Description HTML
$hidden_menus_desc = '
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #4f46e5;padding:12px 16px;border-radius:6px;margin-top:8px;">
    <strong style="color:#1e293b;display:block;margin-bottom:6px;font-size:13px;">💡 Cara Menemukan Slug Menu Lain (Custom Post Type / Plugin):</strong>
    <p style="margin:0 0 10px 0;color:#475569;font-size:12px;line-height:1.5;">
        Arahkan kursor tetikus Anda ke menu yang ingin disembunyikan di bilah kiri WordPress Admin. Perhatikan alamat URL yang tampil di pojok bawah browser (atau klik menu tersebut). Salin bagian URL setelah <code>wp-admin/</code>.
    </p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:11px;color:#334155;margin-bottom:10px;">
        <span style="background:#e0e7ff;color:#3730a3;padding:4px 8px;border-radius:4px;">
            Contoh 1: <code>edit.php?post_type=product</code> (Produk WooCommerce)
        </span>
        <span style="background:#e0e7ff;color:#3730a3;padding:4px 8px;border-radius:4px;">
            Contoh 2: <code>admin.php?page=elementor</code> (Elementor)
        </span>
        <span style="background:#e0e7ff;color:#3730a3;padding:4px 8px;border-radius:4px;">
            Contoh 3: <code>wpcf7</code> (Contact Form 7)
        </span>
    </div>
    <div style="margin-top:8px;">
        <strong style="color:#334155;display:block;margin-bottom:6px;font-size:12px;">+ Klik Chip Preset Di Bawah Untuk Menyisipkan Slug Otomatis:</strong>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">';

foreach ($slug_presets as $preset) {
    $hidden_menus_desc .= '<button type="button" class="button button-small" onclick="mikrotekAppendPreset(\'custom_hidden_menus\', \'' . esc_js($preset['slug']) . '\')">+ ' . esc_html($preset['label']) . '</button>';
}

$hidden_menus_desc .= '
        </div>
    </div>
</div>';

// Menu Renames Description HTML
$menu_renames_desc = '
<div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #059669;padding:12px 16px;border-radius:6px;margin-top:8px;">
    <strong style="color:#1e293b;display:block;margin-bottom:6px;font-size:13px;">📝 Format Penulisan Rename Menu:</strong>
    <p style="margin:0 0 8px 0;color:#475569;font-size:12px;line-height:1.5;">
        Tulis satu aturan per baris menggunakan format dipisahkan karakter garis tegak <code>|</code>: <br>
        <code style="color:#047857;font-weight:700;">slug_menu|Nama Baru Menu</code>
    </p>
    <div style="margin-top:8px;">
        <strong style="color:#334155;display:block;margin-bottom:6px;font-size:12px;">+ Klik Contoh Preset Di Bawah Untuk Mengubah Nama Menu Otomatis:</strong>
        <div style="display:flex;flex-wrap:wrap;gap:6px;">';

foreach ($rename_presets as $preset) {
    $menu_renames_desc .= '<button type="button" class="button button-small" onclick="mikrotekAppendPreset(\'menu_renames\', \'' . esc_js($preset['rule']) . '\')">+ ' . esc_html($preset['label']) . '</button>';
}

$menu_renames_desc .= '
        </div>
    </div>
</div>';

// Full Documentation Guide HTML Card at bottom
$guide_html = '
<div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top:24px;">
    <h2 style="margin:0 0 10px 0;display:flex;align-items:center;gap:8px;">
        <span class="dashicons dashicons-book" style="color:#4f46e5;"></span> Panduan Lengkap Kustomisasi & Pembatasan Menu Admin
    </h2>
    <p class="description" style="margin-bottom:16px;">
        Panduan ringkas bagi pengembang web untuk menyesuaikan antarmuka WordPress admin sesuai kebutuhan klien (Client-Ready Site).
    </p>

    <div style="display:grid;grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));gap:16px;">
        <!-- Box 1 -->
        <div style="background:#fafafa;border:1px solid #e2e8f0;padding:14px;border-radius:6px;">
            <h4 style="margin:0 0 6px 0;color:#1e293b;font-size:14px;">1. Pembatasan Role Pengguna (Client Mode)</h4>
            <p style="margin:0;font-size:12px;color:#475569;line-height:1.5;">
                Gunakan <strong>Target Roles For Client Mode</strong> untuk menentukan role mana saja yang akan dikenakan penyembunyian menu. Akun <strong>Administrator</strong> utama tidak akan pernah terpengaruh oleh pembatasan ini.
            </p>
        </div>

        <!-- Box 2 -->
        <div style="background:#fafafa;border:1px solid #e2e8f0;padding:14px;border-radius:6px;">
            <h4 style="margin:0 0 6px 0;color:#1e293b;font-size:14px;">2. Menyembunyikan Menu Bawaan</h4>
            <p style="margin:0;font-size:12px;color:#475569;line-height:1.5;">
                Cukup centang daftar menu WordPress standar pada opsi <strong>Hide Menus In Client Mode</strong>. Menu seperti Plugins, Appearance, Tools, atau Settings akan hilang dari tampilan role target.
            </p>
        </div>

        <!-- Box 3 -->
        <div style="background:#fafafa;border:1px solid #e2e8f0;padding:14px;border-radius:6px;">
            <h4 style="margin:0 0 6px 0;color:#1e293b;font-size:14px;">3. Custom Menu Slugs (Plugin / CPT)</h4>
            <p style="margin:0;font-size:12px;color:#475569;line-height:1.5;">
                Untuk menyembunyikan menu dari plugin tambahan (seperti WooCommerce, Elementor, atau SEO), salin slug URL-nya ke kolom <strong>Custom Menu Slugs</strong> (satu slug per baris).
            </p>
        </div>

        <!-- Box 4 -->
        <div style="background:#fafafa;border:1px solid #e2e8f0;padding:14px;border-radius:6px;">
            <h4 style="margin:0 0 6px 0;color:#1e293b;font-size:14px;">4. Mengubah Nama Menu (Rename Menus)</h4>
            <p style="margin:0;font-size:12px;color:#475569;line-height:1.5;">
                Gunakan format <code>slug|Nama Baru</code> di kolom <strong>Rename Menus</strong> untuk mengubah istilah teknis bawaan WordPress agar lebih ramah bagi klien (misal: <em>Posts</em> jadi <em>Artikel</em>).
            </p>
        </div>
    </div>
</div>';

return [
    'client_mode_roles' => [
        'label'       => 'Target Roles For Client Mode',
        'type'        => 'checkbox_group',
        'choices'     => [
            'editor'      => 'Editor',
            'author'      => 'Author',
            'contributor' => 'Contributor',
            'subscriber'  => 'Subscriber',
        ],
        'description' => 'Pilih role pengguna yang akan dikenakan pembatasan Client Mode. Jika kosong, aturan akan otomatis berlaku untuk seluruh pengguna non-administrator.',
    ],
    'hidden_menus' => [
        'label'       => 'Hide Core Admin Menus',
        'type'        => 'checkbox_group',
        'choices'     => [
            'index.php'               => 'Dashboard',
            'edit.php'                => 'Posts',
            'upload.php'              => 'Media',
            'edit.php?post_type=page' => 'Pages',
            'edit-comments.php'       => 'Comments',
            'themes.php'              => 'Appearance',
            'plugins.php'             => 'Plugins',
            'users.php'               => 'Users',
            'tools.php'               => 'Tools',
            'options-general.php'     => 'Settings',
        ],
        'description' => 'Centang menu WordPress bawaan yang ingin disembunyikan untuk role target. <em>(Catatan: Menu Wordfence tetap dilindungi jika Compatibility Guard aktif)</em>.',
    ],
    'custom_hidden_menus' => [
        'label'       => 'Custom Menu Slugs (Hidden)',
        'type'        => 'textarea',
        'description' => $hidden_menus_desc,
    ],
    'menu_renames' => [
        'label'       => 'Rename Admin Menus',
        'type'        => 'textarea',
        'description' => $menu_renames_desc,
    ],
    'menu_guide_card' => [
        'type'    => 'html',
        'content' => $guide_html,
    ],
];
