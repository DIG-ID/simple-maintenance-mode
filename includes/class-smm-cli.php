<?php
/**
 * WP-CLI commands: `wp smm enable|disable|status`.
 *
 * @package simple-maintenance-mode
 * @subpackage CLI
 * @since 1.1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Turns maintenance mode on or off and shows its state.
 *
 * ## EXAMPLES
 *
 *     wp smm enable
 *     wp smm disable
 *     wp smm status
 *     wp smm status --format=json
 *
 * @since 1.1.0
 */
class Smm_CLI {

	/**
	 * Turns maintenance mode on.
	 *
	 * ## EXAMPLES
	 *
	 *     wp smm enable
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function enable() {
		if ( smm_enable() ) {
			WP_CLI::success( 'Maintenance mode is on.' );
			return;
		}
		WP_CLI::success( 'Maintenance mode was already on.' );
	}

	/**
	 * Turns maintenance mode off.
	 *
	 * ## EXAMPLES
	 *
	 *     wp smm disable
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function disable() {
		if ( smm_disable() ) {
			WP_CLI::success( 'Maintenance mode is off.' );
			return;
		}
		WP_CLI::success( 'Maintenance mode was already off.' );
	}

	/**
	 * Shows whether maintenance mode is on.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: text
	 * options:
	 *   - text
	 *   - json
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp smm status
	 *
	 * @since 1.1.0
	 *
	 * @param array $args       Positional arguments (unused).
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function status( $args, $assoc_args ) {
		$enabled = smm_is_enabled();

		if ( isset( $assoc_args['format'] ) && 'json' === $assoc_args['format'] ) {
			WP_CLI::line( (string) wp_json_encode( array( 'enabled' => $enabled ) ) );
			return;
		}
		WP_CLI::line( $enabled ? 'Maintenance mode: on' : 'Maintenance mode: off' );
	}
}
