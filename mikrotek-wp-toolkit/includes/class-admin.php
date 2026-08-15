<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Admin {

    private $tabs = [
        'branding'  => 'Branding',
        'login'     => 'Login Page',
        'menus'     => 'Admin Menus',
        'dashboard' => 'Dashboard',
        'security'  => 'Security',
        'email'     => 'Email',
        'theme'     => 'Admin Theme',
        'advanced'  => 'Advanced',
        'misc'      => 'Misc',
        'compat'    => 'Compatibility',
    ];

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_menu() {
        add_menu_page(
            'Mikrotek WP Toolkit',
            'Mikrotek Toolkit',
            'manage_options',
            'mikrotek-wp-toolkit',
            [$this, 'render_admin_page'],
            'dashicons-admin-customizer',
            80
        );

        add_submenu_page(
            'mikrotek-wp-toolkit',
            'Settings',
            'Settings',
            'manage_options',
            'mikrotek-wp-toolkit',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'mikrotek-wp-toolkit',
            'Redirection Manager',
            'Redirections',
            'manage_options',
            'mikrotek-wp-toolkit-redirects',
            ['Mikrotek_WP_Toolkit_Redirects', 'render_redirects_page']
        );

        add_submenu_page(
            'mikrotek-wp-toolkit',
            'Audit Trail Log',
            'Audit Trail',
            'manage_options',
            'mikrotek-wp-toolkit-audit',
            ['Mikrotek_WP_Toolkit_Audit', 'render_audit_page']
        );

        add_submenu_page(
            'mikrotek-wp-toolkit',
            'URL Migration Tool',
            'Tools',
            'manage_options',
            'mikrotek-wp-toolkit-tools',
            ['Mikrotek_WP_Toolkit_Tools', 'render_tools_page']
        );

        add_submenu_page(
            'mikrotek-wp-toolkit',
            'Utility Shortcodes',
            'Shortcodes',
            'manage_options',
            'mikrotek-wp-toolkit-shortcodes',
            ['Mikrotek_WP_Toolkit_Shortcodes', 'render_shortcodes_page']
        );

        add_submenu_page(
            'mikrotek-wp-toolkit',
            'About & Information',
            'About Info',
            'manage_options',
            'mikrotek-wp-toolkit-about',
            ['Mikrotek_WP_Toolkit_About', 'render_about_page']
        );
    }

    public function register_settings() {
        register_setting(
            'mikrotek_wp_toolkit_group',
            Mikrotek_WP_Toolkit_Settings::option_name(),
            [
                'sanitize_callback' => ['Mikrotek_WP_Toolkit_Settings', 'sanitize'],
            ]
        );
    }

    public function enqueue_assets($hook) {
        if (!in_array($hook, [
            'toplevel_page_mikrotek-wp-toolkit',
            'mikrotek-toolkit_page_mikrotek-wp-toolkit-tools',
            'mikrotek-toolkit_page_mikrotek-wp-toolkit-redirects',
            'mikrotek-toolkit_page_mikrotek-wp-toolkit-audit',
            'mikrotek-toolkit_page_mikrotek-wp-toolkit-about',
            'mikrotek-toolkit_page_mikrotek-wp-toolkit-shortcodes',
            // Backward compatibility hook names
            'toplevel_page_mzi-white-label',
            'mzi-white-label_page_mzi-white-label-tools',
            'mzi-white-label_page_mzi-white-label-redirects',
            'mzi-white-label_page_mzi-white-label-audit',
            'mzi-white-label_page_mzi-white-label-about',
            'mzi-white-label_page_mzi-white-label-shortcodes'
        ], true) && false === strpos($hook, 'mikrotek-wp-toolkit')) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style(
            'mikrotek-wpt-admin-css',
            MIKROTEK_WPT_URL . 'assets/css/admin.css',
            [],
            MIKROTEK_WPT_VERSION
        );
        wp_enqueue_script(
            'mikrotek-wpt-admin-js',
            MIKROTEK_WPT_URL . 'assets/js/admin.js',
            ['jquery'],
            MIKROTEK_WPT_VERSION,
            true
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'mikrotek-wp-toolkit'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'branding';

        if (!array_key_exists($active_tab, $this->tabs)) {
            $active_tab = 'branding';
        }

        $tab_file = MIKROTEK_WPT_PATH . 'includes/admin/tabs/tab-' . $active_tab . '.php';
        $fields   = file_exists($tab_file) ? include $tab_file : [];
        $fields   = is_array($fields) ? $fields : [];

        ?>
        <div class="wrap mikrotek-wpt-wrap">
            <h1>Mikrotek WP Toolkit v<?php echo esc_html(MIKROTEK_WPT_VERSION); ?></h1>
            <nav class="nav-tab-wrapper">
                <?php foreach ($this->tabs as $tab => $label) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mikrotek-wp-toolkit&tab=' . $tab)); ?>"
                       class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <form method="post" action="options.php" class="mikrotek-wpt-form" enctype="multipart/form-data">
                <?php
                settings_fields('mikrotek_wp_toolkit_group');
                $this->render_fields($fields);
                submit_button(__('Save Settings', 'mikrotek-wp-toolkit'));
                ?>
            </form>
        </div>
        <?php
    }

    private function render_fields($fields) {
        if (empty($fields)) {
            echo '<p>' . esc_html__('No settings available for this tab.', 'mikrotek-wp-toolkit') . '</p>';
            return;
        }

        echo '<table class="form-table mikrotek-wpt-form-table">';
        foreach ($fields as $key => $field) {
            $value = Mikrotek_WP_Toolkit_Settings::get($key, isset($field['default']) ? $field['default'] : '');
            echo '<tr>';
            echo '<th scope="row">';
            if ('checkbox' === $field['type']) {
                echo '<label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label>';
            } else {
                echo '<label for="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label>';
            }
            echo '</th>';
            echo '<td>';

            switch ($field['type']) {
                case 'text':
                case 'password':
                case 'email':
                    $this->input_field($key, $value, $field['type']);
                    break;
                case 'number':
                    $this->number_field($key, $value, $field);
                    break;
                case 'select':
                    $this->select_field($key, $value, $field);
                    break;
                case 'checkbox':
                    $this->checkbox_field($key, $value);
                    break;
                case 'checkbox_group':
                    $this->checkbox_group_field($key, $value, $field);
                    break;
                case 'textarea':
                    $this->textarea_field($key, $value);
                    break;
                case 'image':
                    $this->image_field($key, $value);
                    break;
                case 'color':
                    $this->color_field($key, $value);
                    break;
            }

            if (!empty($field['description'])) {
                echo '<p class="description">' . esc_html($field['description']) . '</p>';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        $field_keys = array_keys($fields);
        echo '<input type="hidden" name="' . esc_attr(Mikrotek_WP_Toolkit_Settings::field_name('__fields')) . '" value="' . esc_attr(implode(',', $field_keys)) . '">';
    }

    private function input_field($key, $value, $type = 'text') {
        ?>
        <input type="<?php echo esc_attr($type); ?>"
               class="regular-text"
               id="<?php echo esc_attr($key); ?>"
               name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>"
               value="<?php echo esc_attr($value); ?>">
        <?php
    }

    private function checkbox_field($key, $value) {
        ?>
        <input type="checkbox"
               id="<?php echo esc_attr($key); ?>"
               name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>"
               value="1" <?php checked($value, '1'); ?>>
        <?php
    }

    private function checkbox_group_field($key, $values, $field) {
        $values = is_array($values) ? $values : [];
        $choices = isset($field['choices']) ? $field['choices'] : (isset($field['options']) ? $field['options'] : []);
        ?>
        <fieldset>
            <?php foreach ($choices as $choice_value => $label) : ?>
                <label style="display:block;margin-bottom:6px;">
                    <input type="checkbox"
                           name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::option_name() . '[' . $key . '][]'); ?>"
                           value="<?php echo esc_attr($choice_value); ?>"
                           <?php checked(in_array($choice_value, $values, true)); ?>>
                    <?php echo esc_html($label); ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    private function number_field($key, $value, $field) {
        ?>
        <input type="number"
               class="small-text"
               id="<?php echo esc_attr($key); ?>"
               min="0"
               name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>"
               value="<?php echo esc_attr($value); ?>">
        <?php if (!empty($field['suffix'])) : ?>
            <span><?php echo esc_html($field['suffix']); ?></span>
        <?php endif; ?>
        <?php
    }

    private function select_field($key, $value, $field) {
        $choices = isset($field['choices']) ? $field['choices'] : (isset($field['options']) ? $field['options'] : []);
        ?>
        <select id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>">
            <?php foreach ($choices as $choice_value => $label) : ?>
                <option value="<?php echo esc_attr($choice_value); ?>" <?php selected($value, $choice_value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private function textarea_field($key, $value) {
        ?>
        <textarea class="large-text"
                  rows="5"
                  id="<?php echo esc_attr($key); ?>"
                  name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>"><?php echo esc_textarea($value); ?></textarea>
        <?php
    }

    private function image_field($key, $value) {
        $has_image = !empty($value);
        ?>
        <div class="mikrotek-wpt-image-picker">
            <input type="text"
                   class="regular-text mikrotek-image-input"
                   name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>"
                   id="<?php echo esc_attr($key); ?>"
                   value="<?php echo esc_attr($value); ?>">
            <button type="button" class="button button-secondary mikrotek-upload-button" data-target="<?php echo esc_attr($key); ?>">
                <?php esc_html_e('Upload / Select', 'mikrotek-wp-toolkit'); ?>
            </button>
            <button type="button" class="button button-link-delete mikrotek-remove-button" data-target="<?php echo esc_attr($key); ?>" style="<?php echo $has_image ? '' : 'display:none;'; ?>">
                <?php esc_html_e('Remove', 'mikrotek-wp-toolkit'); ?>
            </button>
            <div class="mikrotek-wpt-preview-wrap" id="<?php echo esc_attr($key); ?>_preview_wrap" style="<?php echo $has_image ? '' : 'display:none;'; ?>">
                <img id="<?php echo esc_attr($key); ?>_preview"
                     src="<?php echo esc_url($value); ?>"
                     alt=""
                     style="max-width:180px;max-height:100px;margin-top:8px;border:1px solid #ccd0d4;padding:4px;background:#fff;border-radius:4px;">
            </div>
        </div>
        <?php
    }

    private function color_field($key, $value) {
        ?>
        <input type="color"
               id="<?php echo esc_attr($key); ?>"
               name="<?php echo esc_attr(Mikrotek_WP_Toolkit_Settings::field_name($key)); ?>"
               value="<?php echo esc_attr($value ? $value : '#000000'); ?>">
        <?php
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Admin')) {
    class_alias('Mikrotek_WP_Toolkit_Admin', 'MZI_White_Label_Pro_Admin');
}
