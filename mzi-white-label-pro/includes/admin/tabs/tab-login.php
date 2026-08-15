<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'login_layout'                 => [
        'label'       => 'Login Layout Mode',
        'type'        => 'select',
        'default'     => 'normal',
        'description' => 'Pilih gaya tata letak halaman login: Normal (Box Tengah Default) atau Sidepanel Split (Panel samping penuh di kiri/kanan).',
        'choices'     => [
            'normal'          => 'Normal (WordPress Default)',
            'sidepanel_left'  => 'Sidepanel Left (Panel Form di Kiri)',
            'sidepanel_right' => 'Sidepanel Right (Panel Form di Kanan)',
        ],
    ],
    'login_logo'                   => ['label' => 'Login Logo', 'type' => 'image', 'description' => 'Logo yang tampil di halaman login WordPress. Gunakan gambar PNG/SVG dengan background transparan untuk hasil rapi.'],
    'login_logo_width'             => ['label' => 'Logo Width', 'type' => 'number', 'suffix' => 'px', 'description' => 'Lebar logo login dalam pixel. Kosongkan atau isi 0 untuk memakai ukuran default.'],
    'login_logo_height'            => ['label' => 'Logo Height', 'type' => 'number', 'suffix' => 'px', 'description' => 'Tinggi area logo login dalam pixel. Berguna jika logo terlihat terlalu kecil atau terpotong.'],
    'login_title'                  => ['label' => 'Login Title', 'type' => 'text', 'description' => 'Teks tooltip/title pada logo login. Default mengikuti nama website.'],
    'login_background'             => ['label' => 'Login Background', 'type' => 'image', 'description' => 'Gambar background halaman login. Akan ditampilkan cover memenuhi layar.'],
    'login_background_color'       => ['label' => 'Background Color', 'type' => 'color', 'description' => 'Warna background fallback jika tidak memakai gambar background. Format: #123456.'],
    'login_button_color'           => ['label' => 'Button Color', 'type' => 'color', 'description' => 'Warna tombol utama di halaman login. Format: #123456.'],
    'login_form_position'          => [
        'label'       => 'Form Position (Mode Normal)',
        'type'        => 'select',
        'description' => 'Posisi form login pada mode Normal.',
        'choices'     => [
            'center' => 'Center',
            'left'   => 'Left',
            'right'  => 'Right',
        ],
    ],
    'hide_login_back_to_site'      => ['label' => 'Hide Back To Site Link', 'type' => 'checkbox', 'description' => 'Menyembunyikan link kembali ke website dari halaman login.'],
    'hide_login_language_switcher' => ['label' => 'Hide Language Switcher', 'type' => 'checkbox', 'description' => 'Menyembunyikan dropdown bahasa WordPress di halaman login.'],
    'login_custom_css'             => ['label' => 'Custom Login CSS', 'type' => 'textarea', 'description' => 'CSS tambahan khusus halaman login. Gunakan hanya untuk penyesuaian kecil.'],
];
