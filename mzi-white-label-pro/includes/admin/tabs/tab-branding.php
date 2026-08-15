<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'admin_logo'  => ['label' => 'Admin Logo', 'type' => 'image', 'description' => 'Logo kecil yang menggantikan logo WordPress di admin bar.'],
    'favicon'     => ['label' => 'Favicon', 'type' => 'image', 'description' => 'Icon browser untuk area admin dan login. Ukuran umum: 32x32 atau 512x512.'],
    'cms_name'    => ['label' => 'CMS Name', 'type' => 'text', 'description' => 'Nama CMS/brand yang menggantikan teks WordPress di area yang aman untuk white label.'],
    'footer_text' => ['label' => 'Footer Text', 'type' => 'text', 'description' => 'Teks footer di dashboard admin. Bisa diisi nama agensi atau support brand.'],
];
