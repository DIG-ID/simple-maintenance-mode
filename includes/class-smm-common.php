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
	 * Sets up the class.
	 *
	 * @since 1.0.0
	 *
	 * @param Smm_Plugin $core Core plugin class.
	 */
	public function __construct( $core ) {
		$this->core        = $core;
		$this->plugin_name = $core->get_plugin_name();
	}

	/**
	 * Enqueues the admin bar button styles, only where the button is shown.
	 *
	 * The node is added for users who can toggle or bypass (see
	 * Smm_Admin::add_admin_bar_menu()), so visitors never load this file.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		if ( ! $this->core->user_can_toggle() && ! $this->core->user_can_bypass() ) {
			return;
		}
		smm_vite_enqueue_style( $this->plugin_name . '-common', 'assets/sass/smm-common.sass' );
	}
}
