<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'enable_maintenance_mode' => [
        'label'       => 'Maintenance Mode / Coming Soon',
        'type'        => 'checkbox',
        'description' => 'Aktifkan halaman pemeliharaan (Maintenance Mode). Pengunjung non-admin akan melihat halaman pemeliharaan kustom. Administrator tetap dapat mengakses situs secara normal.',
    ],
    'maintenance_mode_type' => [
        'label'       => 'Mode Respons HTTP',
        'type'        => 'select',
        'options'     => [
            '503' => '503 Service Unavailable (Maintenance)',
            '200' => '200 OK (Coming Soon)',
        ],
        'description' => 'Gunakan 503 untuk pemeliharaan sementara (disarankan untuk SEO) atau 200 untuk Coming Soon.',
    ],
    'maintenance_title' => [
        'label'       => 'Judul Pemeliharaan',
        'type'        => 'text',
        'description' => 'Teks judul kecil/badge (contoh: Under Maintenance / Coming Soon).',
    ],
    'maintenance_headline' => [
        'label'       => 'Headline Utama',
        'type'        => 'text',
        'description' => 'Judul pesan utama di halaman pemeliharaan (contoh: Kami Akan Segera Kembali!).',
    ],
    'maintenance_message' => [
        'label'       => 'Pesan Deskripsi',
        'type'        => 'textarea',
        'description' => 'Pesan penjelasan untuk pengunjung.',
    ],
    'maintenance_logo' => [
        'label'       => 'Logo Maintenance',
        'type'        => 'image',
        'description' => 'Logo kustom untuk halaman maintenance (opsional).',
    ],
    'maintenance_bg_color' => [
        'label'       => 'Warna Latar Belakang (Background)',
        'type'        => 'color',
        'description' => 'Pilih warna latar belakang halaman maintenance.',
    ],
    'disable_rest_api' => [
        'label'       => 'Restriksi REST API (Require Login)',
        'type'        => 'checkbox',
        'description' => 'Membatasi akses REST API hanya untuk pengguna yang telah login.',
    ],
    'disable_rest_users' => [
        'label'       => 'Blokir REST API User Enumeration',
        'type'        => 'checkbox',
        'description' => 'Mematikan endpoint REST API (/wp/v2/users) untuk mencegah pembocoran username oleh pengunjung publik.',
    ],
    'disable_gutenberg' => [
        'label'       => 'Disable Gutenberg',
        'type'        => 'checkbox',
        'description' => 'Mematikan block editor untuk post agar editor klasik bisa digunakan oleh tema/plugin yang membutuhkannya.',
    ],
    'disable_comments' => [
        'label'       => 'Disable Comments',
        'type'        => 'checkbox',
        'description' => 'Menutup komentar dan ping baru di frontend. Tidak menghapus komentar lama.',
    ],
    'disable_heartbeat' => [
        'label'       => 'Disable Heartbeat',
        'type'        => 'checkbox',
        'description' => 'Mematikan WordPress Heartbeat untuk mengurangi request admin. Halaman Wordfence tetap dilindungi.',
    ],
    'disable_xmlrpc' => [
        'label'       => 'Disable XMLRPC',
        'type'        => 'checkbox',
        'description' => 'Mematikan XML-RPC jika website tidak memakai aplikasi/layanan yang membutuhkannya.',
    ],
];
