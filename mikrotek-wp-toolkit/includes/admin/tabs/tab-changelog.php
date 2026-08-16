<?php

if (!defined('ABSPATH')) {
    exit;
}

foreach (Mikrotek_WP_Toolkit_Changelog::entries() as $version => $groups) : ?>
    <div class="mikrotek-wpt-changelog-entry">
        <h2><?php echo esc_html('Mikrotek WP Toolkit v' . $version); ?></h2>
        <?php foreach ($groups as $group => $items) : ?>
            <h3><?php echo esc_html($group); ?></h3>
            <ul>
                <?php foreach ($items as $item) : ?>
                    <li><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
