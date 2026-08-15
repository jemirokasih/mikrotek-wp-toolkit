# MZI White Label Pro

Professional white label branding, security, redirection, audit trail, and migration management plugin for WordPress, built for agencies and managed client websites.

MZI White Label Pro helps replace default WordPress branding, simplify the admin experience for clients, customize the login page, manage dashboard widgets, monitor user activities with audit trail, manage URL redirections, perform safe database search & replace migrations, check broken links, and keep sensitive update/security notices away from non-administrator users.

## Key Features

- **Modular v4 Architecture**: Clean separation of admin tabs and standalone sub-menu modules.
- **Brand Customization**: Login logo, background, favicon, login page sidepanel layout, admin bar logo, custom footer, and CMS naming.
- **Custom Login URL & Security**: Change default `/wp-login.php` slug to custom URL (e.g. `/masuk`, `/rahasia`) and block direct unauthenticated access to `/wp-admin` with 404 Not Found response.
- **Username Enumeration Protection**: Obscure specific login error messages with generic responses.
- **Redirection Manager**: Standalone sub-menu for managing 301 (Permanent), 302 (Temporary), and 307 (Strict Temporary) URL redirections with hit counters.
- **Database URL Migration Tool**: Instant database Search & Replace tool supporting safe serialized data handling and Regular Expressions (Regex) with Dry Run mode.
- **Lightweight Broken Link Checker**: On-demand post and page content link scanner for 404/broken links without heavy background server overhead.
- **Audit Trail Log Manager**: Record user login/logout, post CRUD actions, plugin activation/deactivation/deletion, theme changes, and upgrader events with mandatory enable toggle, Search & Filter, Table Column Sorting, and CSV Export.
- **Admin Menu & Widget Manager**: Rename or hide sub-menus for Client Mode, hide selected WordPress, Yoast SEO, and Elementor widgets.
- **About Info Sub-menu**: Standalone sub-menu displaying installed plugin version, PHP & MySQL system environment summary, and version release changelog.
- **Wordfence Compatibility Guard**: Protected by default to avoid interfering with Wordfence firewall and security options.

## Requirements

- WordPress 5.8 or newer.
- PHP 7.4 or newer.
- Administrator access to install and configure the plugin.

## Installation

1. Download `mzi-white-label-pro-v4.0.0.zip` (or `mzi-white-label-pro.zip`) from the GitHub Releases page.
2. In WordPress admin, open **Plugins > Add New > Upload Plugin**.
3. Upload `mzi-white-label-pro.zip`.
4. Activate **MZI White Label Pro**.
5. Open **MZI White Label** in the admin menu and configure your settings.

## License

GPL-2.0. See [LICENSE](LICENSE).

## Author

PT Mikrotek Zemiro Indonesia  
Website: https://mzi.co.id
