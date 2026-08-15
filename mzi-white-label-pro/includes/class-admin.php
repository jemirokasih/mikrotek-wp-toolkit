<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Admin {

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
            'MZI White Label',
            'MZI White Label',
            'manage_options',
            'mzi-white-label',
            [$this, 'settings_page'],
            'dashicons-admin-customizer',
            2
        );

        add_submenu_page(
            'mzi-white-label',
            'Settings',
            'Settings',
            'manage_options',
            'mzi-white-label',
            [$this, 'settings_page']
        );

        add_submenu_page(
            'mzi-white-label',
            'Redirection Manager',
            'Redirections',
            'manage_options',
            'mzi-white-label-redirects',
            ['MZI_White_Label_Pro_Redirects', 'render_redirects_page']
        );

        add_submenu_page(
            'mzi-white-label',
            'Audit Trail Log',
            'Audit Trail',
            'manage_options',
            'mzi-white-label-audit',
            ['MZI_White_Label_Pro_Audit', 'render_audit_page']
        );

        add_submenu_page(
            'mzi-white-label',
            'URL Migration Tool',
            'Tools',
            'manage_options',
            'mzi-white-label-tools',
            ['MZI_White_Label_Pro_Tools', 'render_tools_page']
        );

        add_submenu_page(
            'mzi-white-label',
            'Utility Shortcodes',
            'Shortcodes',
            'manage_options',
            'mzi-white-label-shortcodes',
            ['MZI_White_Label_Pro_Shortcodes', 'render_shortcodes_page']
        );

        add_submenu_page(
            'mzi-white-label',
            'About & Information',
            'About Info',
            'manage_options',
            'mzi-white-label-about',
            ['MZI_White_Label_Pro_About', 'render_about_page']
        );
    }

    public function register_settings() {
        register_setting(
            'mzi_white_label_group',
            MZI_White_Label_Pro_Settings::option_name(),
            [
                'sanitize_callback' => ['MZI_White_Label_Pro_Settings', 'sanitize'],
            ]
        );
    }

    public function enqueue_assets($hook) {
        if (!in_array($hook, ['toplevel_page_mzi-white-label', 'mzi-white-label_page_mzi-white-label-tools', 'mzi-white-label_page_mzi-white-label-redirects', 'mzi-white-label_page_mzi-white-label-audit', 'mzi-white-label_page_mzi-white-label-about', 'mzi-white-label_page_mzi-white-label-shortcodes'], true)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style(
            'mzi-wlp-admin',
            MZI_WLP_URL . 'assets/css/admin.css',
            ['wp-color-picker'],
            MZI_WLP_VERSION
        );
        wp_enqueue_script(
            'mzi-wlp-admin',
            MZI_WLP_URL . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            MZI_WLP_VERSION,
            true
        );
        wp_localize_script(
            'mzi-wlp-admin',
            'mziWlpMedia',
            [
                'title'      => __('Select Image', 'mzi-white-label-pro'),
                'buttonText' => __('Use this image', 'mzi-white-label-pro'),
            ]
        );
    }

    public function settings_page() {
        $settings = MZI_White_Label_Pro_Settings::all();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab routing in the admin page URL.
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'branding';

        if (!isset($this->tabs[$active_tab])) {
            $active_tab = 'branding';
        }

        ?>
        <div class="wrap mzi-wlp-wrap">
            <h1>MZI White Label Pro v<?php echo esc_html(MZI_WLP_VERSION); ?></h1>

            <nav class="nav-tab-wrapper">
                <?php foreach ($this->tabs as $tab => $label) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mzi-white-label&tab=' . $tab)); ?>"
                       class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php
            $fields = $this->get_tab_fields($active_tab);
            if (!empty($fields)) {
                $this->render_fields_tab($fields, $settings);
            } elseif ('system' === $active_tab) {
                $this->render_system_tab();
            } elseif ('changelog' === $active_tab) {
                $this->render_changelog_tab();
            }
            ?>
        </div>
        <?php
    }

    private function get_tab_fields($tab) {
        $file = MZI_WLP_PATH . 'includes/admin/tabs/tab-' . $tab . '.php';
        if (file_exists($file)) {
            $result = require $file;
            if (is_array($result)) {
                return $result;
            }
        }
        return [];
    }

    private function render_fields_tab($fields, $settings) {
        $this->open_form(array_keys($fields));
        ?>
        <div class="mzi-wlp-card">
            <table class="form-table mzi-wlp-form-table">
                <?php foreach ($fields as $key => $field) : ?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($field['label']); ?>
                            </label>
                        </th>
                        <td><?php $this->render_field($key, $field, $settings); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="mzi-wlp-submit-row">
                <?php submit_button(); ?>
            </div>
        </div>
        <?php
        $this->close_form();
    }

    private function render_field($key, $field, $settings) {
        $value = isset($settings[$key]) ? $settings[$key] : (isset($field['default']) ? $field['default'] : '');

        switch ($field['type']) {
            case 'checkbox':
                $this->checkbox_field($key, $value);
                break;
            case 'checkbox_group':
                $this->checkbox_group_field($key, $value, $field);
                break;
            case 'color':
                $this->input_field($key, $value, 'text', 'mzi-wlp-color-field');
                break;
            case 'email':
                $this->input_field($key, $value, 'email');
                break;
            case 'password':
                $this->input_field($key, $value, 'password');
                break;
            case 'number':
                $this->number_field($key, $value, $field);
                break;
            case 'image':
                $this->image_field($key, $value);
                break;
            case 'select':
                $this->select_field($key, $value, $field);
                break;
            case 'textarea':
                $this->textarea_field($key, $value);
                break;
            case 'text':
            default:
                $this->input_field($key, $value, 'text');
                break;
        }

        if (!empty($field['description'])) {
            echo '<p class="description">' . esc_html($field['description']) . '</p>';
        }
    }

    private function render_system_tab() {
        $file = MZI_WLP_PATH . 'includes/admin/tabs/tab-system.php';
        if (file_exists($file)) {
            require $file;
        }
    }

    private function render_changelog_tab() {
        $file = MZI_WLP_PATH . 'includes/admin/tabs/tab-changelog.php';
        if (file_exists($file)) {
            require $file;
        }
    }

    private function open_form($fields) {
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('mzi_white_label_group'); ?>
            <input type="hidden"
                   name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name('__fields')); ?>"
                   value="<?php echo esc_attr(implode(',', $fields)); ?>">
        <?php
    }

    private function close_form() {
        ?>
        </form>
        <?php
    }

    private function input_field($key, $value, $type, $class = '') {
        ?>
        <input type="<?php echo esc_attr($type); ?>"
               class="regular-text <?php echo esc_attr($class); ?>"
               name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name($key)); ?>"
               value="<?php echo esc_attr($value); ?>">
        <?php
    }

    private function checkbox_field($key, $value) {
        ?>
        <label>
            <input type="checkbox"
                   name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name($key)); ?>"
                   value="1"
                   <?php checked($value, '1'); ?>>
            <?php esc_html_e('Enabled', 'mzi-white-label-pro'); ?>
        </label>
        <?php
    }

    private function checkbox_group_field($key, $value, $field) {
        $value = is_array($value) ? $value : [];
        ?>
        <fieldset class="mzi-wlp-checkbox-group">
            <?php foreach ($field['choices'] as $choice_value => $label) : ?>
                <label>
                    <input type="checkbox"
                           name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::option_name() . '[' . $key . '][]'); ?>"
                           value="<?php echo esc_attr($choice_value); ?>"
                           <?php checked(in_array($choice_value, $value, true)); ?>>
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
               min="0"
               name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name($key)); ?>"
               value="<?php echo esc_attr($value); ?>">
        <?php if (!empty($field['suffix'])) : ?>
            <span><?php echo esc_html($field['suffix']); ?></span>
        <?php endif; ?>
        <?php
    }

    private function select_field($key, $value, $field) {
        ?>
        <select name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name($key)); ?>">
            <?php foreach ($field['choices'] as $choice_value => $label) : ?>
                <option value="<?php echo esc_attr($choice_value); ?>" <?php selected($value, $choice_value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    private function textarea_field($key, $value) {
        ?>
        <textarea class="large-text code mzi-wlp-textarea"
                  rows="6"
                  name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name($key)); ?>"><?php echo esc_textarea($value); ?></textarea>
        <?php
    }

    private function image_field($key, $value) {
        $has_image = !empty($value);
        ?>
        <div class="mzi-wlp-media-field">
            <input type="text"
                   class="regular-text mzi-wlp-media-url"
                   name="<?php echo esc_attr(MZI_White_Label_Pro_Settings::field_name($key)); ?>"
                   id="<?php echo esc_attr($key); ?>"
                   value="<?php echo esc_attr($value); ?>">

            <button type="button" class="button button-secondary mzi-upload-button" data-target="<?php echo esc_attr($key); ?>">
                <span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:2px;"></span> <?php esc_html_e('Upload / Select', 'mzi-white-label-pro'); ?>
            </button>
            <button type="button" class="button button-link-delete mzi-remove-button" data-target="<?php echo esc_attr($key); ?>" style="<?php echo $has_image ? '' : 'display:none;'; ?>">
                <?php esc_html_e('Remove', 'mzi-white-label-pro'); ?>
            </button>

            <div class="mzi-wlp-preview-wrap" id="<?php echo esc_attr($key); ?>_preview_wrap" style="<?php echo $has_image ? '' : 'display:none;'; ?>">
                <img id="<?php echo esc_attr($key); ?>_preview"
                     class="mzi-wlp-image-preview"
                     src="<?php echo esc_url($value); ?>"
                     alt="">
            </div>
        </div>
        <?php
    }
}
