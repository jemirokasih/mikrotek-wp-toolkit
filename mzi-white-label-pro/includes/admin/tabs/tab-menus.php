<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
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
        'description' => 'Hanya berlaku saat Client Mode aktif dan user bukan administrator. Menu Wordfence tetap dilindungi jika Compatibility Guard aktif.',
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
