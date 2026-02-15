<?php
/**
 * Utility Functions
 *
 * Helper functions for retrieving content from Customizer.
 * Priority order: Customizer > Filters > Defaults
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * Utility Functions Class
 *
 * Contains static helper methods for content retrieval.
 *
 * The get_content() method checks sources in priority order:
 * 1. WordPress Customizer (if configured)
 * 2. Filter hooks (for programmatic override)
 * 3. Default values (fallback)
 *
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 * @author     dig.id <hello@dig.id>
 */
class Smm_Utils {

	/**
	 * Option name for Customizer values.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $option_name    Database option name.
	 */
	private static $option_name = 'smm_customizer';

	/**
	 * Get content from Customizer or default.
	 *
	 * Checks in priority order:
	 * 1. Customizer (Kirki or native)
	 * 2. Filter hook
	 * 3. Default value
	 *
	 * @since  1.0.0
	 * @param  string $key     The content key (e.g., 'title', 'description').
	 * @param  string $default Default value if not found.
	 * @return string          The content value.
	 */
	public static function get_content( $key, $default = '' ) {

		// Try Customizer first.
		$customizer_value = self::get_from_customizer( $key );
		if ( ! empty( $customizer_value ) ) {
			return $customizer_value;
		}

		// Fallback to filter (allows themes/plugins to override).
		return (string) apply_filters( 'smm_content_' . $key, $default );
	}

	/**
	 * Get value from Customizer.
	 *
	 * Retrieves value from WordPress Customizer options.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $key The content key.
	 * @return string|null Content value or null if not found.
	 */
	private static function get_from_customizer( $key ) {

		// Get all customizer options.
		$options = get_option( self::$option_name, array() );

		if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return null;
	}

	/**
	 * Get image URL from Customizer.
	 *
	 * Handles image fields that store attachment IDs.
	 *
	 * @since  1.0.0
	 * @param  string $key     The image key (e.g., 'logo').
	 * @param  string $size    Image size (default: 'full').
	 * @param  string $default Default URL if not found.
	 * @return string          Image URL.
	 */
	public static function get_image( $key, $size = 'full', $default = '' ) {

		// Try Customizer (stores attachment ID).
		$options = get_option( self::$option_name, array() );
		if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
			$image_id = absint( $options[ $key ] );
			if ( $image_id ) {
				$image_url = wp_get_attachment_image_url( $image_id, $size );
				if ( $image_url ) {
					return $image_url;
				}
			}
		}

		// Filter.
		return (string) apply_filters( 'smm_image_' . $key, $default );
	}

	/**
	 * Get image tag with attributes.
	 *
	 * Returns complete <img> tag instead of just URL.
	 *
	 * @since  1.0.0
	 * @param  string $key     The image key (e.g., 'logo').
	 * @param  string $size    Image size (default: 'full').
	 * @param  array  $attr    Additional attributes for img tag.
	 * @return string          Image HTML tag or empty string.
	 */
	public static function get_image_tag( $key, $size = 'full', $attr = array() ) {

		$options = get_option( self::$option_name, array() );
		if ( ! isset( $options[ $key ] ) || empty( $options[ $key ] ) ) {
			return '';
		}

		$image_id = absint( $options[ $key ] );
		if ( ! $image_id ) {
			return '';
		}

		// Set default alt text if not provided.
		if ( ! isset( $attr['alt'] ) ) {
			$attr['alt'] = get_bloginfo( 'name' );
		}

		return wp_get_attachment_image( $image_id, $size, false, $attr );
	}

	/**
	 * Get social media URL.
	 *
	 * Convenience method for social network URLs.
	 *
	 * @since  1.0.0
	 * @param  string $network Social network key (e.g., 'facebook', 'instagram').
	 * @param  string $default Default URL.
	 * @return string          Social media URL.
	 */
	public static function get_social( $network, $default = '' ) {
		return self::get_content( 'social_' . $network, $default );
	}

	/**
	 * Get all social media links.
	 *
	 * Returns array of all configured social networks.
	 *
	 * @since  1.0.0
	 * @return array Array of social networks with URLs.
	 */
	public static function get_all_social() {
		$networks = array( 'facebook', 'instagram', 'twitter', 'linkedin' );
		$social   = array();

		foreach ( $networks as $network ) {
			$url = self::get_social( $network );
			if ( ! empty( $url ) ) {
				$social[ $network ] = $url;
			}
		}

		return $social;
	}

	/**
	 * Check if Customizer has any content configured.
	 *
	 * @since  1.0.0
	 * @return bool True if Customizer has content, false otherwise.
	 */
	public static function has_customizer_content() {
		$options = get_option( self::$option_name, array() );
		return ! empty( $options );
	}

	/**
	 * Get all customizer options.
	 *
	 * Retrieves all Customizer values as an array.
	 * Useful for debugging or bulk operations.
	 *
	 * @since  1.0.0
	 * @return array Customizer options.
	 */
	public static function get_all_customizer_options() {
		return get_option( self::$option_name, array() );
	}

	/**
	 * Clear customizer cache.
	 *
	 * Forces refresh of Customizer data.
	 * Useful after programmatic updates.
	 *
	 * @since  1.0.0
	 * @return bool True on success, false on failure.
	 */
	public static function clear_customizer_cache() {
		wp_cache_delete( self::$option_name, 'options' );
		return true;
	}

	/**
	 * Check if specific content key exists in Customizer.
	 *
	 * @since  1.0.0
	 * @param  string $key The content key to check.
	 * @return bool        True if key exists and has value, false otherwise.
	 */
	public static function has_content( $key ) {
		$value = self::get_from_customizer( $key );
		return ! empty( $value );
	}

	/**
	 * Get content with HTML formatting preserved.
	 *
	 * Similar to get_content() but preserves HTML tags.
	 * Uses wp_kses_post() for security.
	 *
	 * @since  1.0.0
	 * @param  string $key     The content key.
	 * @param  string $default Default value.
	 * @return string          Sanitized HTML content.
	 */
	public static function get_content_html( $key, $default = '' ) {
		$content = self::get_content( $key, $default );
		return wp_kses_post( $content );
	}

}
