<?php

if (!defined('ABSPATH')) {
    exit;
}

return [
    'protect_wordfence' => [
        'label'       => 'Protect Wordfence Areas',
        'type'        => 'checkbox',
        'default'     => '1',
        'description' => 'Direkomendasikan. Menjaga halaman, teks, menu, dan heartbeat Wordfence agar tidak terkena white-label override.',
    ],
];
