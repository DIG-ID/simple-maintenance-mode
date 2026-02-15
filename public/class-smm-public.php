<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Smm
 * @subpackage Smm/public
 * @author     dig.id <hello@dig.id>
 */
class Smm_Public {

	/**
	 * The core plugin class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Smm    $core    The core plugin class.
	 */
	private $core;
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $core ) {

		$this->core        = $core;
		$this->plugin_name = $core->get_plugin_name();
		$this->version     = $core->get_version();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smm_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smm_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smm-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smm_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smm_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smm-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Main template_redirect hook.
	 *
	 * Decides whether to show the maintenance template based on:
	 * - Plugin enabled status
	 * - User bypass capabilities
	 * - Request exceptions (login, cron, etc.)
	 * - Preview mode (?smm_preview=1)
	 * - Customizer preview (is_customize_preview) - ONLY if maintenance is ON
	 *
	 * @since 1.0.0
	 */
	public function template_redirect() {

		// Check if we're in Customizer preview.
		$is_customizer_preview = is_customize_preview();

		// Check if manual preview mode is active.
		$is_manual_preview = $this->is_manual_preview();

		// Check if maintenance mode is active.
		$is_active = $this->core->is_enabled();

		// If not active and not in manual preview mode, exit early.
		if ( ! $is_active && ! $is_manual_preview ) {
			return;
		}

		// If in Customizer preview AND maintenance is ON, show template.
		if ( $is_customizer_preview && $is_active ) {
			$this->show_maintenance_template( true );
			return;
		}

		// If manual preview mode (for admins only).
		if ( $is_manual_preview ) {
			$this->show_maintenance_template( true );
			return;
		}

		// If maintenance is active, check bypass and exceptions.
		if ( $is_active ) {
			if ( $this->core->user_can_bypass() || $this->core->should_allow_request() ) {
				return;
			}

			// Show maintenance template (not preview).
			$this->show_maintenance_template( false );
		}
	}


	/**
	 * Check if manual preview mode is active.
	 *
	 * Manual preview mode allows admins to preview the maintenance page
	 * using the ?smm_preview=1 parameter.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return bool True if manual preview is active, false otherwise.
	 */
	private function is_manual_preview() {
		// Check for preview parameter.
		if ( ! isset( $_GET['smm_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		// Verify value is '1'.
		if ( '1' !== $_GET['smm_preview'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		// Only allow for users with manage_options capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Include the maintenance template and send proper HTTP headers.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  bool $is_preview Whether this is a preview (don't send 503 headers).
	 */
	private function show_maintenance_template( $is_preview = false ) {

		// Only set 503 headers if NOT in preview mode.
		if ( ! $is_preview ) {
			status_header( 503 );
			header( 'Retry-After: 600' );
			header( 'X-Robots-Tag: noindex, nofollow', true );
			nocache_headers();

			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
				define( 'DONOTCACHEOBJECT', true );
			}
			if ( ! defined( 'DONOTMINIFY' ) ) {
				define( 'DONOTMINIFY', true );
			}
		}

		// Get template path (theme override or plugin default).
		$template = $this->core->get_maintenance_template();
		$template = apply_filters( 'smm_maintenance_template', $template );

		if ( ! is_readable( $template ) ) {
			wp_die(
				esc_html__( 'Maintenance template not found.', 'smm' ),
				esc_html__( 'Maintenance', 'smm' ),
				array( 'response' => 503 )
			);
		}

		// Disponibiliza variáveis úteis para o template.
		$smm_vars = array(
			'site_name'             => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'home_url'              => home_url( '/' ),
			'login_url'             => wp_login_url(),
			'is_preview'            => $is_preview,
			'is_customizer'         => is_customize_preview(),
			'is_maintenance_active' => $this->core->is_enabled(),
		);
		set_query_var( 'smm_vars', $smm_vars );

		/**
		 * Filter maintenance template variables.
		 *
		 * @since 1.0.0
		 *
		 * @param array $smm_vars Template variables.
		 */
		$smm_vars = apply_filters( 'smm_template_vars', $smm_vars );

		set_query_var( 'smm_vars', $smm_vars );

		// Include the template.
		include $template;
		exit;
	}

}
