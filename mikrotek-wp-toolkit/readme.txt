=== Mikrotek WP Toolkit ===
Contributors: mikrotek, jemirokasih
Tags: white-label, branding, security, audit-trail, redirection
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 4.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional White Label Branding, Security, Audit Trail, Redirection & Utility Toolkit for WordPress.

== Description ==

Mikrotek WP Toolkit helps replace default WordPress branding, simplify the admin experience for clients, customize the login page, manage dashboard widgets, monitor user activities with audit trail, manage URL redirections, perform safe database search & replace migrations, check broken links, and keep sensitive update/security notices away from non-administrator users.

== Installation ==

1. Upload the `mikrotek-wp-toolkit` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access 'Mikrotek Toolkit' in the admin menu to configure your settings.

== Changelog ==

= 4.0.1 =
* Fixed Default Featured Image support for Elementor Posts, Loop Grid, Archive Posts, and Dynamic Image widgets.
* Added fallback hooks for `post_thumbnail_id`, `get_post_metadata`, `post_thumbnail_url`, `wp_get_attachment_image_src`, and Elementor image rendering.

= 4.0.0 =
* Complete rebranding to 100% standalone Mikrotek WP Toolkit.
* Added modular v4 architecture.
* Overhauled Admin Menus UI/UX with 1-click preset chips.
* Added Enterprise Custom MySQL Table Audit Trail (wp_mikrotek_audit_logs) with health metrics and auto-pruning.
* Added Redirection Manager (301, 302, 307).
* Added Database URL Migration Tool (Search & Replace).
* Added Lightweight Broken Link Checker.
* Added Custom Login URL Changer & 404 Guard.
* Added Username Enumeration Protection.
* Added Utility Shortcodes collection.
* Added Maintenance Mode / Coming Soon module.
* Added About Info Sub-menu.
* Purged all legacy code, aliases, and stub files.
