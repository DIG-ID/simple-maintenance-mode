<?php
/**
 * Assets shared by the admin and the front end (admin bar button styles).
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Shared assets.
 *
 * @since 1.0.0
 */
class Smm_Common {

	/**
	 * Core plugin class.
	 *
	 * @since 1.0.0
	 * @var Smm_Plugin
	 */
	private $core;

	/**
	 * Plugin slug, used as asset handle prefix.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version, used for cache busting.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $version;

	/**
	 * Sets up the class.
	 *
	 * @since 1.0.0
	 *
	 * @param Smm_Plugin $core Core plugin class.
	 */
	public function __construct( $core ) {
		$this->core        = $core;
		$this->plugin_name = $core->get_plugin_name();
		$this->version     = $core->get_version();
	}

	/**
	 * Enqueues the shared stylesheet.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . '-common', SMM_URL . 'assets/css/smm-common.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueues the shared script.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-common', SMM_URL . 'assets/js/smm-common.js', array( 'jquery' ), $this->version, false );
	}
}
