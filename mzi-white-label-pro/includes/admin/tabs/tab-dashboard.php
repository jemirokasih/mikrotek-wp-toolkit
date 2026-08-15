<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'dashboard_widget_enabled'        => ['label' => 'Show Custom Welcome Widget', 'type' => 'checkbox', 'description' => 'Menampilkan widget welcome custom di halaman Dashboard WordPress.'],
    'dashboard_widget_title'          => ['label' => 'Widget Title', 'type' => 'text', 'description' => 'Judul widget welcome custom. Jika kosong, default-nya Welcome.'],
    'dashboard_widget_content'        => ['label' => 'Widget Content', 'type' => 'textarea', 'description' => 'Isi widget welcome. Mendukung teks sederhana dan HTML aman WordPress.'],
    'dashboard_hide_selected_widgets' => ['label' => 'Hide Selected Widgets', 'type' => 'checkbox', 'description' => 'Aktifkan untuk menyembunyikan widget dashboard yang dipilih di bawah.'],
    'dashboard_hidden_widgets'        => [
        'label'       => 'Dashboard Widgets',
        'type'        => 'checkbox_group',
        'description' => 'Pilih widget dashboard bawaan/plugin yang ingin disembunyikan dari tampilan dashboard.',
        'choices'     => [
            'dashboard_primary'                => 'WordPress Events and News',
            'dashboard_quick_press'            => 'Quick Draft',
            'dashboard_activity'               => 'Activity',
            'dashboard_right_now'              => 'At a Glance',
            'dashboard_site_health'            => 'Site Health Status',
            'wpseo-dashboard-overview'         => 'Yoast SEO Posts Overview',
            'e-dashboard-overview'             => 'Elementor Overview',
            'wpseo-wincher-dashboard-overview' => 'Yoast SEO / Wincher: Top Keyphrases',
        ],
    ],
];
