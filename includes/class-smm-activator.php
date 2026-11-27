<?php

/**
 * Fired during plugin activation
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 * @author     dig.id <hello@dig.id>
 */
class Smm_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Check WordPress version.
		global $wp_version;
		if ( version_compare( $wp_version, '5.8', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				'Este plugin requer WordPress 5.8 ou superior.',
				'Plugin Activation Error',
				array( 'back_link' => true )
			);
		}

		// Check PHP version.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				'Este plugin requer PHP 7.4 ou superior.',
				'Plugin Activation Error',
				array( 'back_link' => true )
			);
		}

		// Set default option if doesn't exist.
		if ( false === get_option( 'smm_enabled' ) ) {
			add_option( 'smm_enabled', 0, '', 'no' );
		}
	}
}
