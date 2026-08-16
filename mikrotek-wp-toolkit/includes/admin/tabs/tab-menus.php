<?php

if (!defined('ABSPATH')) {
    exit;
}

// Full Documentation Guide HTML Card at bottom
$guide_html = '
<div class="mikrotek-wpt-card" style="background:#fff;border:1px solid #ccd0d4;padding:20px;border-radius:8px;margin-top:24px;">
    <h2 style="margin:0 0 10px 0;display:flex;align-items:center;gap:8px;">
        <span class="dashicons dashicons-book" style="color:#4f46e5;"></span> Panduan Pengaturan Menu Admin
    </h2>
    <p class="description" style="margin-bottom:16px;">
        Panduan ringkas untuk membatasi akses menu admin WordPress sesuai kebutuhan klien (Client Mode).
    </p>

    <div style="display:grid;grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
        <!-- Box 1 -->
        <div style="background:#fafafa;border:1px solid #e2e8f0;padding:14px;border-radius:6px;">
            <h4 style="margin:0 0 6px 0;color:#1e293b;font-size:14px;">1. Pembatasan Role (Client Mode)</h4>
            <p style="margin:0;font-size:12px;color:#475569;line-height:1.5;">
                Gunakan <strong>Target Roles For Client Mode</strong> untuk menentukan role mana saja yang akan dikenakan penyembunyian menu. Akun <strong>Administrator</strong> utama tidak akan terpengaruh.
            </p>
        </div>

        <!-- Box 2 -->
        <div style="background:#fafafa;border:1px solid #e2e8f0;padding:14px;border-radius:6px;">
            <h4 style="margin:0 0 6px 0;color:#1e293b;font-size:14px;">2. Menyembunyikan Menu Admin</h4>
            <p style="margin:0;font-size:12px;color:#475569;line-height:1.5;">
                Centang daftar menu WordPress bawaan pada <strong>Hide Core Admin Menus</strong>. Menu yang dicentang akan disembunyikan untuk role target yang dipilih.
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
    /*
    |--------------------------------------------------------------------------
    | HIDDEN FEATURES (Recorded in hidden-feature.txt)
    |--------------------------------------------------------------------------
    | 'custom_hidden_menus' => Custom Menu Slugs
    | 'menu_renames'        => Rename Admin Menus
    |--------------------------------------------------------------------------
    */
    'menu_guide_card' => [
        'type'    => 'html',
        'content' => $guide_html,
    ],
];
