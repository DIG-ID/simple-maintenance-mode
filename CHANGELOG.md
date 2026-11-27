# Changelog

All notable changes to Simple Maintenance Mode are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`).

- **MAJOR** — breaking changes to the public contract (plugin path, the `smm_enabled` and `smm_customizer` options, public filters, template override path, the 503 response).
- **MINOR** — new features.
- **PATCH** — bug fixes and small adjustments.

---

## [Unreleased]

### Added
- Development tooling: Composer with PHPCS (WordPress Coding Standards 3, PHPCompatibilityWP for PHP 7.4+, WordPress stubs) and the `lint:php` / `lint:php-fix` scripts.
- `phpcs.xml.dist`, `.editorconfig`, `.gitattributes` (LF line endings, development files kept out of release archives) and shared VS Code settings.
- `CLAUDE.md` with the project rules and the public contract, and this changelog.

### Changed
- `package-lock.json` is no longer listed in `.gitignore`, so lockfile updates are committed and asset builds stay reproducible; `.gitignore` reduced to what the project actually produces.
- Code formatting brought in line with the WordPress Coding Standards (automatic fixes only, no behaviour change).

## [1.0.0] — 2026-02-18

First version, based on the WordPress Plugin Boilerplate.

### Added
- One-click maintenance toggle in the admin bar (nonce and capability protected), with an admin notice while maintenance is on.
- Maintenance page for anonymous visitors, served with HTTP 503, `Retry-After`, `X-Robots-Tag: noindex` and `DONOTCACHEPAGE`.
- Bypass for logged-in users with the right capability; preview of the maintenance page with `?smm_preview=1`.
- Login, admin, cron, AJAX and REST requests allowed while maintenance is on, configurable through filters.
- Customizer section (`smm_customizer` option) for the page content: logo, title, description, address, contacts and social links, with shortcode support.
- Template override from the theme in `simple-maintenance-mode/`.
- Public filters: `smm_enabled`, `smm_user_can_bypass`, `smm_allow_request`, `smm_allow_rest_request`, `smm_allow_ajax_action`, `smm_maintenance_template`, `smm_template_vars`, `smm_content_*`, `smm_image_*`.
- Styles built with Laravel Mix, Sass and Tailwind CSS.
