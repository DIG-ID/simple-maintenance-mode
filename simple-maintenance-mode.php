<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://dig.id
 * @since             1.0.0
 * @package           Smm
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Maintenance Mode
 * Plugin URI:        https://#
 * Description:       Simple Maintenance Mode is a simple plugin to toggle the maintenance mode in a WordPress site.
 * Version:           1.0.0
 * Author:            dig.id
 * Author URI:        https://dig.id/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smm
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SMM_VERSION', '1.0.0' );

/**
 * The unique identifier of this plugin.
 *
 * @since    1.0.0
 * @access   private
 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
 */
define( 'SMM_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The current version of the plugin.
 *
 * @since    1.0.0
 * @access   private
 * @var      string    $version    The current version of the plugin.
 */
define( 'SMM_URL', plugin_dir_url( __FILE__ ) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-smm-activator.php
 */
function activate_smm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smm-activator.php';
	Smm_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-smm-deactivator.php
 */
function deactivate_smm() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smm-deactivator.php';
	Smm_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smm' );
register_deactivation_hook( __FILE__, 'deactivate_smm' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-smm.php';

/**
 * Wrapper for Smm_Utils::get_content().
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function smm_get_content( $key, $default = '' ) {
	return Smm_Utils::get_content( $key, $default );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_smm() {

	$plugin = new Smm();
	$plugin->run();
}
run_smm();
