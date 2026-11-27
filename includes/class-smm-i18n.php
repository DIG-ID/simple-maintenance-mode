<?php
/**
 * Loads the plugin translations.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Translation loading.
 *
 * Renamed from `Smm_i18n` in 1.1.0 (class names are case-insensitive in PHP).
 *
 * @since 1.0.0
 */
class Smm_I18n {

	/**
	 * Loads the translations shipped in the plugin's `languages/` folder.
	 *
	 * Translations in `wp-content/languages/plugins/` still take precedence, so
	 * a site can override them.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'simple-maintenance-mode',
			false,
			dirname( plugin_basename( SMM_FILE ) ) . '/languages/'
		);
	}
}
