<?php
/**
 * Utility functions for the plugin.
 *
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * Class Smm_Utils
 *
 * Contains static helper methods.
 */
class Smm_Utils {

	/**
	 * Get content from ACF options or filter.
	 *
	 * @param string $key     The content key (e.g., 'title', 'description').
	 * @param string $default Default value if not found.
	 *
	 * @return string
	 */
	public static function get_content( $key, $default = '' ) {
		// If ACF is active, try to get from options page.
		if ( function_exists( 'get_field' ) ) {
			$value = get_field( 'smm_' . $key, 'option' );
			if ( is_string( $value ) && $value !== '' ) {
				return $value;
			}
		}

		// Fallback to filter (allows themes/plugins to override).
		return (string) apply_filters( 'smm_content_' . $key, $default );
	}

	// You can add other utility methods here in the future.
}