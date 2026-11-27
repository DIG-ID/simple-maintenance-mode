=== Simple Maintenance Mode ===
Contributors: digid
Tags: maintenance, maintenance mode, 503, coming soon
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Toggle a maintenance page (HTTP 503) for visitors with one click, while logged-in editors keep seeing the site.

== Description ==

dig.id's maintenance plugin.

* One-click toggle in the admin bar (administrators), admin notice while maintenance is on.
* Visitors get the maintenance page with HTTP 503, Retry-After and noindex; caching plugins are told not to store it.
* Administrators and editors keep seeing the normal site.
* The admin, login, cron, WP-CLI and the REST API are never blocked.
* Page content edited in the Customizer; template can be overridden by the theme.
* Translated into German (de_CH, de_DE) and Portuguese (pt_PT).
* WP-CLI: `wp smm enable|disable|status`. PHP: `smm_enable()`, `smm_disable()`, `smm_is_enabled()`.

Full documentation: README.md. Source: https://github.com/DIG-ID/simple-maintenance-mode

== Installation ==

1. Upload the `simple-maintenance-mode` folder to `/wp-content/plugins/`, or upload the zip in Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. Use the "Maintenance OFF/ON" button in the admin bar.

== Changelog ==

See CHANGELOG.md.
