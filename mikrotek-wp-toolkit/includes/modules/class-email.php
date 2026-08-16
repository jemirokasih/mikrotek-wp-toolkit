<?php

if (!defined('ABSPATH')) {
    exit;
}

class Mikrotek_WP_Toolkit_Email {

    public function __construct() {
        add_filter('wp_mail_from_name', [$this, 'custom_mail_name']);
        add_filter('wp_mail_from', [$this, 'custom_mail_email']);
        add_action('phpmailer_init', [$this, 'configure_smtp']);
    }

    public function custom_mail_name() {
        return Mikrotek_WP_Toolkit_Settings::get('mail_name', get_bloginfo('name'));
    }

    public function custom_mail_email() {
        return Mikrotek_WP_Toolkit_Settings::get('mail_email', get_option('admin_email'));
    }

    public function configure_smtp($phpmailer) {
        $mail_method = Mikrotek_WP_Toolkit_Settings::get('mail_method', 'default');
        if ('smtp' !== $mail_method) {
            return;
        }

        $host       = Mikrotek_WP_Toolkit_Settings::get('smtp_host');
        $port       = absint(Mikrotek_WP_Toolkit_Settings::get('smtp_port', 587));
        $encryption = Mikrotek_WP_Toolkit_Settings::get('smtp_encryption', 'tls');
        $auth       = Mikrotek_WP_Toolkit_Settings::enabled('smtp_auth', true);
        $username   = Mikrotek_WP_Toolkit_Settings::get('smtp_username');
        $password   = Mikrotek_WP_Toolkit_Settings::get('smtp_password');

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


