<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'mail_method' => [
        'label'       => 'Email Delivery Method',
        'type'        => 'select',
        'default'     => 'default',
        'description' => 'Pilih metode pengiriman email: Default WordPress Mail (PHP Mail) atau Custom SMTP Server.',
        'choices'     => [
            'default' => 'Default WordPress Mail',
            'smtp'    => 'Custom SMTP Server',
        ],
    ],
    'mail_name'   => ['label' => 'Sender Name', 'type' => 'text', 'description' => 'Nama pengirim default untuk email transaksi & notifikasi WordPress.'],
    'mail_email'  => ['label' => 'Sender Email', 'type' => 'email', 'description' => 'Alamat email pengirim default.'],
    'smtp_host'   => ['label' => 'SMTP Host', 'type' => 'text', 'description' => 'Hostname server SMTP. Contoh: smtp.gmail.com atau mail.domainanda.com'],
    'smtp_port'   => ['label' => 'SMTP Port', 'type' => 'number', 'description' => 'Port SMTP. Umumnya 587 (TLS), 465 (SSL), atau 25.'],
    'smtp_encryption' => [
        'label'       => 'Encryption',
        'type'        => 'select',
        'default'     => 'tls',
        'description' => 'Jenis enkripsi koneksi SMTP.',
        'choices'     => [
            'tls'  => 'TLS (Rekomendasi)',
            'ssl'  => 'SSL',
            'none' => 'None (Tidak ada enkripsi)',
        ],
    ],
    'smtp_auth'     => ['label' => 'SMTP Authentication', 'type' => 'checkbox', 'description' => 'Aktifkan jika server SMTP membutuhkan otentikasi username & password.'],
    'smtp_username' => ['label' => 'SMTP Username', 'type' => 'text', 'description' => 'Username atau email login SMTP.'],
    'smtp_password' => ['label' => 'SMTP Password', 'type' => 'password', 'description' => 'Password atau App Password SMTP.'],
];
