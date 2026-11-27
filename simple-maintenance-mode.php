<?php
/**
 * Plugin bootstrap: header, constants, activation hooks and start-up.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Maintenance Mode
 * Plugin URI:        https://github.com/DIG-ID/simple-maintenance-mode
 * Description:       Toggle a maintenance page (HTTP 503) for visitors with one click, while logged-in editors keep seeing the site.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            dig.id
 * Author URI:        https://dig.id/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       simple-maintenance-mode
 * Domain Path:       /languages
 * Update URI:        https://github.com/DIG-ID/simple-maintenance-mode
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version. Keep in sync with the header above, README.txt and CHANGELOG.md.
 *
 * @since 1.0.0
 */
define( 'SMM_VERSION', '1.1.0' );

/**
 * Absolute path to the main plugin file.
 *
 * @since 1.1.0
 */
define( 'SMM_FILE', __FILE__ );

/**
 * Absolute path to the plugin directory, with trailing slash.
 *
 * @since 1.0.0
 */
define( 'SMM_DIR', plugin_dir_path( __FILE__ ) );

/**
 * URL of the plugin directory, with trailing slash.
 *
 * @since 1.0.0
 */
define( 'SMM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Runs on plugin activation.
 *
 * @since 1.1.0
 * @return void
 */
function smm_activate() {
	require_once SMM_DIR . 'includes/class-smm-activator.php';
	Smm_Activator::activate();
}

/**
 * Runs on plugin deactivation.
 *
 * @since 1.1.0
 * @return void
 */
function smm_deactivate() {
	require_once SMM_DIR . 'includes/class-smm-deactivator.php';
	Smm_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'smm_activate' );
register_deactivation_hook( __FILE__, 'smm_deactivate' );

require_once SMM_DIR . 'includes/smm-functions.php';
require_once SMM_DIR . 'includes/class-smm-plugin.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once SMM_DIR . 'includes/class-smm-cli.php';
	WP_CLI::add_command( 'smm', 'Smm_CLI' );
}

/**
 * Starts the plugin.
 *
 * Everything is registered through hooks, so running this while the plugin
 * file loads does not affect the page life cycle.
 *
 * @since 1.1.0
 * @return void
 */
function smm_run() {
	$smm_plugin = new Smm_Plugin();
	$smm_plugin->run();
}
smm_run();
