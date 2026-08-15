<?php

if (!defined('ABSPATH')) {
    exit;
}

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
        'label'       => 'Hide Menus In Client Mode',
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
        'description' => 'Menu yang akan disembunyikan untuk role target. Menu Wordfence tetap dilindungi jika Compatibility Guard aktif.',
    ],
    'custom_hidden_menus' => [
        'label'       => 'Custom Menu Slugs',
        'type'        => 'textarea',
        'description' => 'Satu slug menu per baris. Contoh: edit.php?post_type=product',
    ],
    'menu_renames' => [
        'label'       => 'Rename Menus',
        'type'        => 'textarea',
        'description' => 'Satu aturan per baris dengan format slug|Label Baru. Contoh: edit.php|Articles',
    ],
];
