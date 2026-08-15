<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'default_featured_image'     => ['label' => 'Default Featured Image', 'type' => 'image', 'description' => 'Gambar default yang otomatis digunakan sebagai fallback jika post, page, atau custom post type tidak memiliki featured image.'],
    'hide_plugin_update_notices' => ['label' => 'Hide Plugin Update Notices', 'type' => 'checkbox', 'description' => 'Menyembunyikan notifikasi & badge pembaruan (update) plugin di area admin.'],
    'hide_theme_update_notices'  => ['label' => 'Hide Theme Update Notices', 'type' => 'checkbox', 'description' => 'Menyembunyikan notifikasi & badge pembaruan (update) tema di area admin.'],
    'hide_core_update_notices'   => ['label' => 'Hide Core WP Update Notices', 'type' => 'checkbox', 'description' => 'Menyembunyikan notifikasi & badge pembaruan versi inti WordPress.'],
    'disable_screen_options'     => ['label' => 'Disable Screen Options Tab', 'type' => 'checkbox', 'description' => 'Menyembunyikan tab "Screen Options" di pojok kanan atas halaman admin.'],
    'disable_help_tab'           => ['label' => 'Disable Help Tab', 'type' => 'checkbox', 'description' => 'Menyembunyikan tab "Help" di pojok kanan atas halaman admin.'],
];
