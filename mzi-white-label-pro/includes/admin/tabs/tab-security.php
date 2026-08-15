<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'enable_audit_trail'  => ['label' => 'Enable Audit Trail Logging', 'type' => 'checkbox', 'description' => 'Aktifkan untuk mulai merekam aktivitas pengguna (login, logout, edit konten, aktivasi plugin, dan ganti tema). wajib dicentang agar sistem merekam log.'],
    'custom_login_slug'   => ['label' => 'Custom Login URL Slug', 'type' => 'text', 'description' => 'Ganti URL login bawaan (/wp-login.php) dengan slug kustom (contoh: masuk atau client-login) untuk meningkatkan keamanan dari bot/brute-force. Biarkan kosong jika ingin menggunakan URL default.'],
    'hide_login_errors'   => ['label' => 'Obscure Login Errors', 'type' => 'checkbox', 'description' => 'Menyembunyikan pesan error spesifik di halaman login (seperti "username tidak terdaftar" atau "password salah") dan menggantinya dengan pesan umum untuk mencegah pembocoran username (Username Enumeration Protection).'],
    'client_mode'         => ['label' => 'Enable Client Mode', 'type' => 'checkbox', 'description' => 'Mode aman untuk client: menyembunyikan menu sensitif dari user non-administrator.'],
    'hide_update_notices' => ['label' => 'Hide Update Notices In Client Mode', 'type' => 'checkbox', 'description' => 'Menyembunyikan notifikasi update agar user client tidak panik. Administrator tetap bisa melihat update.'],
];
