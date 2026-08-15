<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Dashboard {

    public function __construct() {
        add_action('wp_dashboard_setup', [$this, 'manage_dashboard_widgets'], 999);
        add_action('wp_dashboard_setup', [$this, 'add_custom_dashboard_widget']);
    }

    public function manage_dashboard_widgets() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('dashboard_hide_selected_widgets')) {
            return;
        }

        $hidden_widgets = Mikrotek_WP_Toolkit_Settings::get('dashboard_hidden_widgets', []);
        $hidden_widgets = is_array($hidden_widgets) ? $hidden_widgets : [];

        foreach ($hidden_widgets as $widget_id) {
            $this->remove_widget(sanitize_text_field($widget_id));
        }
    }

    public function add_custom_dashboard_widget() {
        if (!Mikrotek_WP_Toolkit_Settings::enabled('dashboard_widget_enabled')) {
            return;
        }

        $title = Mikrotek_WP_Toolkit_Settings::get('dashboard_widget_title');
        $title = !empty($title) ? $title : __('Welcome', 'mikrotek-wp-toolkit');

        wp_add_dashboard_widget(
            'mikrotek_wpt_custom_welcome_widget',
            esc_html($title),
            [$this, 'render_custom_dashboard_widget']
        );
    }

    public function render_custom_dashboard_widget() {
        $content = Mikrotek_WP_Toolkit_Settings::get('dashboard_widget_content');
        if (!empty($content)) {
            echo wp_kses_post(nl2br($content));
        } else {
            echo '<p>' . esc_html__('Selamat datang di dashboard situs Anda.', 'mikrotek-wp-toolkit') . '</p>';
        }
    }

    private function remove_widget($widget_id) {
        global $wp_meta_boxes;

        if (!is_array($wp_meta_boxes) || !isset($wp_meta_boxes['dashboard'])) {
            return;
        }

        foreach ($wp_meta_boxes['dashboard'] as $context => $priorities) {
            if (!is_array($priorities)) {
                continue;
            }

            foreach ($priorities as $priority => $widgets) {
                if (is_array($widgets) && isset($widgets[$widget_id])) {
                    unset($wp_meta_boxes['dashboard'][$context][$priority][$widget_id]);
                }
            }
        }
    }
}

// Class alias for backward compatibility
if (!class_exists('MZI_White_Label_Pro_Dashboard')) {
    class_alias('Mikrotek_WP_Toolkit_Dashboard', 'MZI_White_Label_Pro_Dashboard');
}
