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
- PHP API for the dig.id Control Center, themes and scripts: `smm_is_enabled()`, `smm_enable()`, `smm_disable()`.
- WP-CLI commands: `wp smm enable`, `wp smm disable`, `wp smm status [--format=json]`.
- `smm_toggled` action (`bool $enabled, int $user_id`), fired once whenever maintenance is turned on or off — admin bar, Customizer, PHP API, WP-CLI or plugin deactivation.
- `smm_user_can_toggle` filter: who can turn maintenance on or off and preview it.
- German (de_CH, de_DE) and Portuguese (pt_PT) translations, shipped in `languages/`.
- Editors who can see the site during maintenance now get a "Maintenance ON" indicator in the admin bar (without the switch).
- **Updates from GitHub Releases**: the plugin checks the latest Release of `DIG-ID/simple-maintenance-mode` (cached 12 hours) and offers it on the normal Updates screen, with "View details". Every downloaded zip is verified against the SHA-256 published with the Release before it is installed; a missing or different checksum stops the update.
- Release workflow: each `vX.Y.Z` tag checks the version against the tag, lints, builds the assets and publishes `simple-maintenance-mode.zip` and `simple-maintenance-mode.zip.sha256`. CI on every push: versions, PHPCS, PHP 7.4 and 8.4 syntax, asset build.
- `composer version:check` and `composer check`.
- Install instructions for themes and the Control Center (pinned version + SHA-256 + `Plugin_Upgrader`) in the README.
- Plugin header: real `Plugin URI`, `Requires at least: 5.8`, `Requires PHP: 7.4`, `Update URI` (stops WordPress offering updates from an unrelated wordpress.org plugin with the same slug).

### Changed
- `package-lock.json` is no longer listed in `.gitignore`, so lockfile updates are committed and asset builds stay reproducible; `.gitignore` reduced to what the project actually produces.
- Code formatting brought in line with the WordPress Coding Standards; full PHPDoc on every file; comments in English.
- **Only administrators (`manage_options`) can turn maintenance on or off** and use `?smm_preview=1`. Editors still see the normal site during maintenance but no longer get the switch. To restore the old behaviour on a site: `add_filter( 'smm_user_can_toggle', function ( $can ) { return $can || current_user_can( 'edit_others_pages' ); } );`
- **Text domain renamed from `smm` to `simple-maintenance-mode`** (matches the plugin slug). Translations made in WPML String Translation under the `smm` domain must be redone under the new domain.
- Default texts of the maintenance page are in English and translatable (they were hard-coded in Portuguese); the Customizer still overrides them. Customizer labels in English.
- `robots.txt` and XML sitemaps (WordPress core and Yoast SEO) answer a plain-text 503 while maintenance is on, instead of the HTML maintenance page or the normal file.
- The REST API (`/wp-json/…` and `?rest_route=…`) is never blocked, for any visitor; the Control Center agent needs it during maintenance.
- Translations load on `init` (WordPress 6.7+ warns about earlier loading).
- Internal classes renamed: `Smm` → `Smm_Plugin`, `Smm_i18n` → `Smm_I18n`; `activate_smm()`, `deactivate_smm()` and `run_smm()` → `smm_activate()`, `smm_deactivate()` and `smm_run()`.
- Build tooling migrated from Laravel Mix to Vite 8 (Tailwind 3 via PostCSS): hashed files in `dist/` resolved through the Vite manifest, and a dev server with HMR (`npm run dev`). `dist/` is built by the release workflow and no longer committed. The maintenance page looks the same (pixel-identical screenshots).
- **The plugin no longer loads any file for visitors when maintenance is off.** The maintenance page CSS (including Tailwind's base reset) used to load on every front-end page of the site; it now loads only on the maintenance page and its preview, and the admin bar button CSS only where the button is shown. Sites whose theme does not include its own CSS reset (old themes without Tailwind) may show small spacing differences on headings, lists and paragraphs — check one or two such sites after updating.

### Removed
- `smm_allow_rest_request` filter: REST requests are always allowed now.
- Empty boilerplate assets that loaded on every page: `public/js/smm-public.js`, `assets/js/smm-common.js`, `admin/js/smm-admin.js` and `admin/css/smm-admin.css`; the unused `assets/css/plugin-main.css`; Laravel Mix files (`webpack.mix.js`, `mix-manifest.json`, `mix.js.map`) and the compiled CSS in the repository.
- Activation checks for the WordPress and PHP versions (WordPress enforces the plugin headers); they also deactivated the wrong file.

### Fixed
- Contact lines with empty fields are hidden; the placeholders `addresss`, `email` and `phone` no longer appear on the page.
- The maintenance page printed two `<title>` tags with themes that support `title-tag`.
- Uninstall also deletes the `smm_customizer` option, and on multisite covers every site (it used a direct query).

### Security
- Address on the maintenance page is escaped (`nl2br( esc_html() )`). It was printed as-is: the Customizer strips tags when saving, but a value from the `smm_content_address` filter or written to the option directly (import, migration, WP-CLI) reached the page unescaped.
- `mailto:` and `tel:` links are built from a sanitised e-mail and a digits-only number and escaped with `esc_url()`.

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
