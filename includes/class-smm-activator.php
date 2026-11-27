<?php
/**
 * Runs on plugin activation.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin activation.
 *
 * The WordPress and PHP minimums are enforced by WordPress itself from the
 * "Requires at least" and "Requires PHP" plugin headers.
 *
 * @since 1.0.0
 */
class Smm_Activator {

	/**
	 * Creates the state option (off) when it does not exist yet.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( 'smm_enabled' ) ) {
			add_option( 'smm_enabled', 0, '', false );
		}
	}
}
