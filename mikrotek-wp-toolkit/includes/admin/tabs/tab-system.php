<?php

if (!defined('ABSPATH')) {
    exit;
}

$server_software = !empty($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '-';

?>
<table class="widefat striped mzi-wlp-system-table">
    <tbody>
        <tr>
            <td><strong>PHP Version</strong></td>
            <td><?php echo esc_html(phpversion()); ?></td>
        </tr>
        <tr>
            <td><strong>WordPress Version</strong></td>
            <td><?php echo esc_html(get_bloginfo('version')); ?></td>
        </tr>
        <tr>
            <td><strong>Memory Limit</strong></td>
            <td><?php echo esc_html(ini_get('memory_limit')); ?></td>
        </tr>
        <tr>
            <td><strong>Server Software</strong></td>
            <td><?php echo esc_html($server_software); ?></td>
        </tr>
    </tbody>
</table>
