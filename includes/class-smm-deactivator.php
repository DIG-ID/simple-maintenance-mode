<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 * @author     dig.id <hello@dig.id>
 */
class Smm_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// Turn off maintenance mode when plugin is deactivated.
		// This prevents the site from being locked if plugin is deactivated while active.
		update_option( 'smm_enabled', 0, false );

	}

}
