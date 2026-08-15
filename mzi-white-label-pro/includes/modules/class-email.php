<?php

if (!defined('ABSPATH')) {
    exit;
}

class MZI_White_Label_Pro_Email {

    public function __construct() {
        add_filter('wp_mail_from_name', [$this, 'custom_mail_name']);
        add_filter('wp_mail_from', [$this, 'custom_mail_email']);
        add_action('phpmailer_init', [$this, 'configure_smtp']);
    }

    public function custom_mail_name() {
        return MZI_White_Label_Pro_Settings::get('mail_name', get_bloginfo('name'));
    }

    public function custom_mail_email() {
        return MZI_White_Label_Pro_Settings::get('mail_email', get_option('admin_email'));
    }

    public function configure_smtp($phpmailer) {
        $mail_method = MZI_White_Label_Pro_Settings::get('mail_method', 'default');
        if ('smtp' !== $mail_method) {
            return;
        }

        $host       = MZI_White_Label_Pro_Settings::get('smtp_host');
        $port       = absint(MZI_White_Label_Pro_Settings::get('smtp_port', 587));
        $encryption = MZI_White_Label_Pro_Settings::get('smtp_encryption', 'tls');
        $auth       = MZI_White_Label_Pro_Settings::enabled('smtp_auth', true);
        $username   = MZI_White_Label_Pro_Settings::get('smtp_username');
        $password   = MZI_White_Label_Pro_Settings::get('smtp_password');

        if (empty($host)) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host     = $host;
        $phpmailer->Port     = $port > 0 ? $port : 587;
        $phpmailer->SMTPAuth = $auth;

        if ($auth) {
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
        }

        if ('ssl' === $encryption) {
            $phpmailer->SMTPSecure = 'ssl';
        } elseif ('tls' === $encryption) {
            $phpmailer->SMTPSecure = 'tls';
        } else {
            $phpmailer->SMTPSecure = '';
        }
    }
}
