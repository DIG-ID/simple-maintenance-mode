# Simple Maintenance Mode

dig.id's maintenance plugin. One click in the admin bar shows visitors a maintenance page (HTTP 503), while administrators and editors keep seeing the normal site.

- **Requires:** WordPress 5.8+, PHP 7.4+
- **Licence:** GPL-2.0-or-later
- **Changelog:** [CHANGELOG.md](CHANGELOG.md)

---

## What it does

| Request | Maintenance on |
|---|---|
| Anonymous visitors | Maintenance page, HTTP **503** + `Retry-After` + `X-Robots-Tag: noindex, nofollow`, never cached (`DONOTCACHEPAGE`, no-cache headers) |
| Administrators and editors (logged in) | Normal site |
| `robots.txt` and XML sitemaps (core and Yoast SEO) | Plain-text 503, same headers |
| wp-admin, `admin-ajax.php`, `wp-login.php`, `wp-cron.php`, WP-CLI | Never blocked |
| REST API (`/wp-json/…` and `?rest_route=…`) | Never blocked: the dig.id Control Center agent uses it during maintenance |
| Front-end AJAX (e.g. WooCommerce `?wc-ajax=`) | Blocked for visitors unless allowed with `smm_allow_ajax_action` |

### Who can do what

| | Administrator | Editor |
|---|---|---|
| Sees the normal site while maintenance is on | yes | yes |
| Sees the "Maintenance ON" state in the admin bar and the admin notice | yes | yes |
| Turns maintenance on or off, previews the page (`?smm_preview=1`) | yes | **no** (since 1.1.0) |

To let editors toggle again on one site, add to the theme:

```php
add_filter( 'smm_user_can_toggle', function ( $can ) {
	return $can || current_user_can( 'edit_others_pages' );
} );
```

---

## Usage

- **Toggle:** the "Maintenance ON/OFF" button in the admin bar.
- **Preview:** "View Preview" under that button, or add `?smm_preview=1` to any URL. Answers 200, without the 503 headers.
- **Content:** Appearance → Customize → Maintenance Mode (title, message, contact details, logo, social links). The Customizer can also turn maintenance on and off. Empty fields are not shown.
- **Languages:** the default texts are translated into German (de_CH, de_DE) and Portuguese (pt_PT). Texts entered in the Customizer replace them.

### WP-CLI

```bash
wp smm enable
wp smm disable
wp smm status [--format=json]
```

### PHP API

For themes, deploy scripts and the dig.id Control Center agent:

```php
smm_is_enabled(); // bool, after the smm_enabled filter.
smm_enable();     // true if it changed, false if it was already on.
smm_disable();    // true if it changed, false if it was already off.
```

These functions do not check nonces or capabilities: check permissions before calling them on behalf of a user. The state is stored in the `smm_enabled` option (`1` / `0`).

---

## Customising the page

### Template override

Copy `public/partials/smm-public-display.php` to `wp-content/themes/<theme>/simple-maintenance-mode/maintenance.php` and edit it there. Variables are available through `get_query_var( 'smm_vars' )`:

```php
array(
	'site_name'             => 'Site name',
	'home_url'              => 'https://example.com/',
	'login_url'             => 'https://example.com/wp-login.php',
	'is_preview'            => false, // true for ?smm_preview=1 and the Customizer.
	'is_customizer'         => false,
	'is_maintenance_active' => true,
);
```

Escape every value you print: Customizer content is user input.

### Content helpers

```php
smm_get_content( 'title', __( 'Under maintenance', 'simple-maintenance-mode' ) );
Smm_Utils::get_social( 'instagram' );
Smm_Utils::get_image_tag( 'logo', 'full' );
```

`smm_get_content()` returns the Customizer value, or the `smm_content_{key}` filter applied to the fallback.

---

## Hooks

### Filters

| Filter | Arguments | Purpose |
|---|---|---|
| `smm_enabled` | `bool $enabled` | Force maintenance on or off |
| `smm_user_can_bypass` | `bool $can` | Who sees the normal site (default: administrators and editors) |
| `smm_user_can_toggle` | `bool $can` | Who can turn maintenance on or off and preview it (default: `manage_options`) — since 1.1.0 |
| `smm_allow_request` | `bool $allow, string $uri` | Let other front-end requests through |
| `smm_allow_ajax_action` | `bool $allow, string $action` | Let front-end AJAX actions through |
| `smm_maintenance_template` | `string $template` | Path of the template |
| `smm_template_vars` | `array $vars` | Template variables |
| `smm_content_{key}` | `string $fallback` | Content when the Customizer field is empty |
| `smm_image_{key}` | `string $fallback` | Image URL when none is set |

### Actions

| Action | Arguments | Fires |
|---|---|---|
| `smm_toggled` | `bool $enabled, int $user_id` | Whenever maintenance is turned on or off: admin bar, Customizer, PHP API, WP-CLI, plugin deactivation. `$user_id` is 0 without a logged-in user (WP-CLI without `--user`, the Control Center agent). Since 1.1.0. |

```php
add_action( 'smm_toggled', function ( $enabled, $user_id ) {
	error_log( sprintf( 'Maintenance %s by user %d', $enabled ? 'on' : 'off', $user_id ) );
}, 10, 2 );
```

---

## Development

Requires Node `^20.19.0 || >=22.12.0`.

```bash
composer install
composer lint:php      # must pass with 0 errors and 0 warnings
composer lint:php-fix

npm ci
npm run dev            # Vite dev server with HMR — Sass changes show without reloading, PHP changes reload the page
npm run prod           # production build to dist/ (hashed files + dist/.vite/manifest.json)
```

### Assets

- Sources: `public/sass/smm-public.sass` (maintenance page, Tailwind 3) and `assets/sass/smm-common.sass` (admin bar button). Entries are listed in `vite.config.mjs`.
- PHP enqueues them by source path through `includes/smm-vite.php` (`smm_vite_enqueue_style()`), which reads the manifest.
- `dist/` is **not** committed: the release workflow builds it into the zip. A git checkout without `npm run prod` works, but the maintenance page has no plugin styles.
- `npm run dev` writes `dist/hot`; WordPress then loads the styles from the dev server, only when `WP_ENVIRONMENT_TYPE` is `local` or `development` and the server answers. The dev server uses the Local SSL certificate of the host (default `testing.digid`). For another site, create an untracked `.env.local` with `DEV_HOST=control-client.digid` (and `DEV_PORT=5174` to run two servers at once).
- Loading rules: the page CSS only on the maintenance page (and its preview); the button CSS only where the admin bar shows the button. Visitors of a site with maintenance off load nothing from the plugin.

Translations: after changing strings, regenerate `languages/simple-maintenance-mode.pot` with `wp i18n make-pot . languages/simple-maintenance-mode.pot --domain=simple-maintenance-mode --exclude=vendor,node_modules`, update the `.po` files and run `wp i18n make-mo languages` and `wp i18n make-php languages`.

Project rules: [CLAUDE.md](CLAUDE.md).
