<?php
/**
 * Helpers that read the maintenance page content from the Customizer.
 *
 * Priority: Customizer value > `smm_content_*` filter > fallback.
 *
 * @package simple-maintenance-mode
 * @subpackage Helpers
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Utility Functions Class
 *
 * Contains static helper methods for content retrieval.
 *
 * All methods read from the 'smm_customizer' option array.
 *
 * @since 1.0.0
 */
class Smm_Utils {

	/**
	 * Option name for Customizer values.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private static $option_name = 'smm_customizer';

	/**
	 * Get content from Customizer.
	 *
	 * Priority order:
	 * 1. Customizer value
	 * 2. Filter hook
	 * 3. Default value
	 *
	 * @since  1.0.0
	 * @param  string $key     The content key (e.g., 'title', 'description').
	 * @param  string $fallback Value used when the Customizer field is empty.
	 * @return string          The content value.
	 */
	public static function get_content( $key, $fallback = '' ) {

		// Try Customizer.
		$customizer_value = self::get_from_customizer( $key );
		if ( ! empty( $customizer_value ) ) {
			return $customizer_value;
		}

		// Fallback to filter.
		return (string) apply_filters( 'smm_content_' . $key, $fallback );
	}

	/**
	 * Get value from Customizer.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $key The content key.
	 * @return string|null Content value or null if not found.
	 */
	private static function get_from_customizer( $key ) {
		$options = get_option( self::$option_name, array() );

		if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
			return $options[ $key ];
		}

		return null;
	}

	/**
	 * Get image URL from Customizer.
	 *
	 * The logo field stores an attachment ID.
	 *
	 * @since  1.0.0
	 * @param  string $key     The image key (e.g., 'logo').
	 * @param  string $size    Image size (default: 'full').
	 * @param  string $fallback URL used when no image is set.
	 * @return string          Image URL.
	 */
	public static function get_image( $key, $size = 'full', $fallback = '' ) {

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
		return (string) apply_filters( 'smm_image_' . $key, $fallback );
	}

	/**
	 * Get image tag with attributes.
	 *
	 * Returns complete <img> tag instead of just URL.
	 *
	 * @since  1.0.0
	 * @param  string $key  The image key (e.g., 'logo').
	 * @param  string $size Image size (default: 'full').
	 * @param  array  $attr Additional attributes for img tag.
	 * @return string       Image HTML tag or empty string.
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
	 * @since  1.0.0
	 * @param  string $network Social network key (e.g., 'facebook', 'instagram').
	 * @param  string $fallback URL used when the field is empty.
	 * @return string          Social media URL.
	 */
	public static function get_social( $network, $fallback = '' ) {
		return self::get_content( 'social_' . $network, $fallback );
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
	 * Get contact form shortcode.
	 *
	 * @since  1.0.0
	 * @param  string $fallback Shortcode used when the field is empty.
	 * @return string          Contact form shortcode.
	 */
	public static function get_form_shortcode( $fallback = '' ) {
		return self::get_content( 'form_shortcode', $fallback );
	}

	/**
	 * Render contact form.
	 *
	 * Executes the shortcode and returns the output.
	 *
	 * @since  1.0.0
	 * @return string Contact form HTML or empty string.
	 */
	public static function render_form() {
		$shortcode = self::get_form_shortcode();

		if ( empty( $shortcode ) ) {
			return '';
		}

		return do_shortcode( $shortcode );
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
	 * @since  1.0.0
	 * @return array Customizer options.
	 */
	public static function get_all_customizer_options() {
		return get_option( self::$option_name, array() );
	}

	/**
	 * Check if specific content key exists.
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
	 * @param  string $fallback Value used when the field is empty.
	 * @return string          Sanitized HTML content.
	 */
	public static function get_content_html( $key, $fallback = '' ) {
		$content = self::get_content( $key, $fallback );
		return wp_kses_post( $content );
	}
}
