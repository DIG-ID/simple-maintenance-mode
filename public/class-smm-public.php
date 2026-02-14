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
	 *
	 * @since    1.0.0
	 */
	public function template_redirect() {
		// Verifica se é preview (apenas para admins).
		$is_preview = isset( $_GET['smm_preview'] ) && $_GET['smm_preview'] === '1' && current_user_can( 'manage_options' );
		$is_active  = $this->core->is_enabled();

		// Se não está ativo e não é preview, sai.
		if ( ! $is_active && ! $is_preview ) {
			return;
		}

		// Se está ativo e não é preview, verifica bypass e exceções.
		if ( $is_active && ! $is_preview ) {
			if ( $this->core->user_can_bypass() || $this->core->should_allow_request() ) {
				return;
			}
		}

		// Se chegou aqui, mostra o template de manutenção.
		$this->show_maintenance_template();
	}

	/**
	 * Includes the maintenance template and sends proper HTTP headers.
	 *
	 * @since    1.0.0
	 */
	private function show_maintenance_template() {
		// Define headers para 503 (apenas se não for preview).
		$is_preview = isset( $_GET['smm_preview'] ) && $_GET['smm_preview'] === '1';
		if ( ! $is_preview ) {
			status_header( 503 );
			header( 'Retry-After: 600' );
			header( 'X-Robots-Tag: noindex, nofollow', true );
			nocache_headers();

			if ( ! defined( 'DONOTCACHEPAGE' ) )   define( 'DONOTCACHEPAGE', true );
			if ( ! defined( 'DONOTCACHEOBJECT' ) ) define( 'DONOTCACHEOBJECT', true );
			if ( ! defined( 'DONOTMINIFY' ) )      define( 'DONOTMINIFY', true );
		}

		// Obtém o caminho do template (core decide: tema ou plugin).
		$template = $this->core->get_maintenance_template();
		$template = apply_filters( 'smm_maintenance_template', $template );

		if ( ! is_readable( $template ) ) {
			wp_die( 'Maintenance template not found.', 'Maintenance', array( 'response' => 503 ) );
		}

		// Disponibiliza variáveis úteis para o template.
		$smm_vars = array(
			'site_name'  => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'home_url'   => home_url( '/' ),
			'login_url'  => wp_login_url(),
			'is_preview' => $is_preview,
		);
		set_query_var( 'smm_vars', $smm_vars );

		// Inclui o template.
		include $template;
		exit;
	}

}
