<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 * @author     dig.id <hello@dig.id>
 */
class Smm {

	/**
	 * Option name for enabled status.
	 */
	const OPTION_ENABLED = 'smm_enabled';

	/**
	 * Nonce action for toggle.
	 */
	const NONCE_ACTION = 'smm_toggle_maintenance';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Smm_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SMM_VERSION' ) ) {
			$this->version = SMM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'smm';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_common_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smm_Loader. Orchestrates the hooks of the plugin.
	 * - Smm_i18n. Defines internationalization functionality.
	 * - Smm_Admin. Defines all hooks for the admin area.
	 * - Smm_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smm-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smm-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-smm-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-smm-public.php';

		/**
		 * The class responsible for defining all actions that occur in the common area in both admin area and public-facing.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smm-common.php';

		/**
	 * The class responsible for utility functions.
	 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smm-utils.php';

		$this->loader = new Smm_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smm_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smm_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Smm_Admin( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'add_admin_bar_menu', 100 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notice' );
		$this->loader->add_action( 'admin_post_smm_toggle', $plugin_admin, 'handle_toggle' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Smm_Public( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect', 0 );

	}


	/**
	 * Register all of the hooks related to the common functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_common_hooks() {

		$plugin_common = new Smm_Common( $this );

    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_common, 'enqueue_styles' );
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_common, 'enqueue_scripts' );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_common, 'enqueue_styles' );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_common, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Smm_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Verifica se o modo manutenção está ativo.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) apply_filters( 'smm_enabled', (int) get_option( self::OPTION_ENABLED, 0 ) === 1 );
	}

	/**
	 * Verifica se o utilizador atual pode fazer bypass.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function user_can_bypass() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$can = current_user_can( 'manage_options' ) || current_user_can( 'edit_others_pages' );
		return (bool) apply_filters( 'smm_user_can_bypass', $can );
	}

	/**
	 * Verifica se o pedido atual deve ser permitido (excepções).
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function should_allow_request() {
			if ( is_admin() ) {
				return true;
			}

			$script = $_SERVER['SCRIPT_NAME'] ?? '';
			$uri    = $_SERVER['REQUEST_URI'] ?? '';

			if ( strpos( $script, 'wp-login.php' ) !== false ) return true;
			if ( strpos( $script, 'wp-cron.php' )  !== false ) return true;

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				$allow = (bool) apply_filters( 'smm_allow_rest_request', false, $uri );
				return $this->user_can_bypass() || $allow;
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$action = $_REQUEST['action'] ?? '';
				$allow  = (bool) apply_filters( 'smm_allow_ajax_action', false, $action );
				return $this->user_can_bypass() || $allow;
			}

			if ( apply_filters( 'smm_allow_request', false, $uri ) ) {
				return true;
			}

			return false;
	}

	/**
	 * Retorna a URL para toggle (com nonce).
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_toggle_url() {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=smm_toggle' ),
			self::NONCE_ACTION
		);
	}

	/**
	 * Obtém o caminho do template de manutenção.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_maintenance_template() {
		$template = locate_template( 'simple-maintenance-mode/maintenance.php' );
		if ( ! empty( $template ) ) {
			return $template;
		}
		return SMM_DIR . 'public/partials/smm-public-display.php';
	}

}
