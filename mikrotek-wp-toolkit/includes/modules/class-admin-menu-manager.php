<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Admin_Menu_Manager {

    public function __construct() {
        add_action('admin_menu', [$this, 'apply_menu_rules'], 1000);
    }

    public function apply_menu_rules() {
        if (!$this->should_apply()) {
            return;
        }

        $this->rename_menus();
        $this->hide_menus();
    }

    private function should_apply() {
        if (!MZI_White_Label_Pro_Settings::enabled('client_mode')) {
            return false;
        }

        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return false;
        }

        $target_roles = MZI_White_Label_Pro_Settings::get('client_mode_roles', []);
        if (is_array($target_roles) && !empty($target_roles)) {
            $user_roles = (array) $user->roles;
            return (bool) array_intersect($user_roles, $target_roles);
        }

        return !current_user_can('administrator');
    }

    private function hide_menus() {
        $slugs = MZI_White_Label_Pro_Settings::get('hidden_menus', []);
        $slugs = is_array($slugs) ? $slugs : [];
        $custom_slugs = $this->lines_to_array(MZI_White_Label_Pro_Settings::get('custom_hidden_menus'));

        foreach (array_unique(array_merge($slugs, $custom_slugs)) as $slug) {
            if (MZI_White_Label_Pro_Compatibility::is_protected_menu_slug($slug)) {
                continue;
            }

            remove_menu_page($slug);
        }
    }

    private function rename_menus() {
        global $menu;

        if (!is_array($menu)) {
            return;
        }

        $rules = $this->parse_rename_rules(MZI_White_Label_Pro_Settings::get('menu_renames'));

        if (empty($rules)) {
            return;
        }

        foreach ($menu as $index => $item) {
            if (empty($item[2]) || empty($rules[$item[2]])) {
                continue;
            }

            if (MZI_White_Label_Pro_Compatibility::is_protected_menu_slug($item[2])) {
                continue;
            }

            $menu[$index][0] = esc_html($rules[$item[2]]);
        }
    }

    private function lines_to_array($value) {
        $lines = preg_split('/\r\n|\r|\n/', (string) $value);
        $lines = array_map('trim', $lines);

        return array_values(array_filter($lines));
    }

    private function parse_rename_rules($value) {
        $rules = [];

        foreach ($this->lines_to_array($value) as $line) {
            $parts = array_map('trim', explode('|', $line, 2));

            if (2 !== count($parts) || '' === $parts[0] || '' === $parts[1]) {
                continue;
            }

            $rules[$parts[0]] = $parts[1];
        }

        return $rules;
    }
}
