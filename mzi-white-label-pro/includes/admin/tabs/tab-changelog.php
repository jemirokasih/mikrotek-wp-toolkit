<?php

if (!defined('ABSPATH')) {
    exit;
}

foreach (MZI_White_Label_Pro_Changelog::entries() as $version => $groups) :
    ?>
    <div class="mzi-wlp-changelog">
        <h2><?php echo esc_html('MZI White Label Pro v' . $version); ?></h2>

        <?php foreach ($groups as $group => $items) : ?>
            <h3><?php echo esc_html($group); ?></h3>
            <ul>
                <?php foreach ($items as $item) : ?>
                    <li><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
    <?php
endforeach;
