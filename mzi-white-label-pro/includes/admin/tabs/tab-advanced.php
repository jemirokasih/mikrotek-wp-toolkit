<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'disable_gutenberg' => ['label' => 'Disable Gutenberg', 'type' => 'checkbox', 'description' => 'Mematikan block editor untuk post agar editor klasik bisa digunakan oleh tema/plugin yang membutuhkannya.'],
    'disable_comments'  => ['label' => 'Disable Comments', 'type' => 'checkbox', 'description' => 'Menutup komentar dan ping baru di frontend. Tidak menghapus komentar lama.'],
    'disable_heartbeat' => ['label' => 'Disable Heartbeat', 'type' => 'checkbox', 'description' => 'Mematikan WordPress Heartbeat untuk mengurangi request admin. Halaman Wordfence tetap dilindungi.'],
    'disable_xmlrpc'    => ['label' => 'Disable XMLRPC', 'type' => 'checkbox', 'description' => 'Mematikan XML-RPC jika website tidak memakai aplikasi/layanan yang membutuhkannya.'],
];
