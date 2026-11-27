<?php
/**
 * Runs on plugin deactivation.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin deactivation.
 *
 * @since 1.0.0
 */
class Smm_Deactivator {

	/**
	 * Turns maintenance mode off.
	 *
	 * Without this, reactivating the plugin later would put the site straight
	 * back into maintenance. Fires `smm_toggled` if it was on.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate() {
		smm_disable();
	}
}
