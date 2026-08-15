# Changelog

All notable changes to MZI White Label Pro are documented here.

## [4.0.0] - 2026-08-15

### Added
- **Modular v4 Architecture**: Clean separation of admin tabs, settings registry, and dedicated sub-menu modules.
- **Redirection Manager**: Standalone sub-menu (`mzi-white-label-redirects`) for managing 301, 302, and 307 URL redirections with hit counters.
- **Database URL Migration Tool**: Instant database Search & Replace tool supporting safe serialized data replacement and Regular Expression (Regex) matching with Dry Run mode.
- **Lightweight Broken Link Checker**: On-demand link scanner for post/page content to detect 404 and HTTP error links instantly without background server load.
- **Custom Login URL Changer**: Custom login slug support (e.g. `/masuk`, `/rahasia`) with automatic 404 Not Found redirection for direct unauthenticated `/wp-login.php` & `/wp-admin` access.
- **Username Enumeration Protection**: Option to obscure specific login error messages with generic responses.
- **Audit Trail Log Manager**: Complete user action logger (login, logout, post CRUD, plugin activation/deactivation/deletion, theme changes, upgrader installs/updates) with mandatory enable toggle, Search & Filter, Table Column Sorting, and CSV Export.
- **About Info Sub-menu**: Standalone sub-menu (`mzi-white-label-about`) displaying installed plugin version (`v4.0.0`), PHP & MySQL system environment summary, and version release changelog.

### Security
- Applied comprehensive security hardening across all 30 PHP files covering input validation, contextual sanitization, nonce verification, authorization checks, and output escaping.

## [3.0.2] - 2026-05-20

### Fixed
- Fixed WordPress.org Plugin Check findings.
- Removed duplicate Plugin URI from the plugin header.
- Updated WordPress.org contributor username.
- Updated `Tested up to` value for repository submission.
- Improved escaping and request handling compliance.

## [3.0.1] - 2026-05-20

### Added
- Persistent changelog registry so previous release notes stay visible in the plugin admin page.
- Short helper descriptions under every settings option.
- Dashboard widget hide options for Yoast SEO, Elementor, and Yoast SEO / Wincher.
- Client-mode option to hide update notifications from non-administrator users.

## [3.0.0] - 2026-05-20

### Added
- Modular plugin architecture.
- Admin Menu Manager for Client Mode.
- Dashboard Widget Manager.
- Login Page Customizer.
- Compatibility Guard for Wordfence.
