<?php
/**
 * Core plugin class: wires the hooks and decides who and what may pass while
 * maintenance mode is on.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Core plugin class.
 *
 * Loads the other classes, registers their hooks through Smm_Loader and holds
 * the access rules shared by the admin and public sides.
 *
 * Renamed from `Smm` in 1.1.0 to follow the plugin prefix.
 *
 * @since 1.0.0
 */
class Smm_Plugin {

	/**
	 * Option holding the state (1 on / 0 off). Public contract: do not rename.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_ENABLED = 'smm_enabled';

	/**
	 * Nonce action for the admin bar toggle.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const NONCE_ACTION = 'smm_toggle_maintenance';

	/**
	 * Registers all hooks of the plugin.
	 *
	 * @since 1.0.0
	 * @var Smm_Loader
	 */
	protected $loader;

	/**
	 * Plugin slug, used as handle prefix for assets.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $version;

	/**
	 * Sets up the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = defined( 'SMM_VERSION' ) ? SMM_VERSION : '1.0.0';
		$this->plugin_name = 'smm';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_common_hooks();
		$this->init_customizer();

		$updater = new Smm_Updater();
		$updater->register();
	}

	/**
	 * Loads the plugin classes and creates the hook loader.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_dependencies() {
		require_once SMM_DIR . 'includes/smm-vite.php';
		require_once SMM_DIR . 'includes/class-smm-loader.php';
		require_once SMM_DIR . 'includes/class-smm-i18n.php';
		require_once SMM_DIR . 'includes/class-smm-utils.php';
		require_once SMM_DIR . 'includes/class-smm-common.php';
		require_once SMM_DIR . 'includes/class-smm-customizer.php';
		require_once SMM_DIR . 'includes/class-smm-updater.php';
		require_once SMM_DIR . 'admin/class-smm-admin.php';
		require_once SMM_DIR . 'public/class-smm-public.php';

		$this->loader = new Smm_Loader();
	}

	/**
	 * Loads the translations on `init`.
	 *
	 * Since WordPress 6.7, loading translations before `init` triggers a notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_locale() {
		$plugin_i18n = new Smm_I18n();
		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Registers the admin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Smm_Admin( $this );

		$this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'add_admin_bar_menu', 100 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notice' );
		$this->loader->add_action( 'admin_post_smm_toggle', $plugin_admin, 'handle_toggle' );
	}

	/**
	 * Registers the public hooks.
	 *
	 * Robots and sitemaps are caught on `parse_request` because Yoast SEO serves
	 * its sitemaps on `pre_get_posts`, before `template_redirect` runs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_public_hooks() {
		$plugin_public = new Smm_Public( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'parse_request', $plugin_public, 'block_crawler_files', 0 );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect', 0 );
	}

	/**
	 * Registers the hooks shared by the admin and public sides.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_common_hooks() {
		$plugin_common = new Smm_Common( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_common, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_common, 'enqueue_styles' );
	}

	/**
	 * Registers the Customizer panel.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_customizer() {
		new Smm_Customizer();
	}

	/**
	 * Registers all hooks with WordPress.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Plugin slug, used as handle prefix for assets.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Hook loader.
	 *
	 * @since 1.0.0
	 * @return Smm_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Whether maintenance mode is on.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_enabled() {
		return smm_is_enabled();
	}

	/**
	 * Whether the current user sees the normal site while maintenance is on.
	 *
	 * Administrators and editors: editors have to see the site to keep working
	 * on its content.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function user_can_bypass() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$can = current_user_can( 'manage_options' ) || current_user_can( 'edit_others_pages' );

		/**
		 * Filters whether the current user sees the normal site while maintenance is on.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $can True for administrators and editors.
		 */
		return (bool) apply_filters( 'smm_user_can_bypass', $can );
	}

	/**
	 * Whether the current user may turn maintenance on or off and preview it.
	 *
	 * Administrators only: turning maintenance off publishes the whole site,
	 * which is a decision for whoever manages the site, not for content editors.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function user_can_toggle() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		/**
		 * Filters whether the current user may turn maintenance mode on or off.
		 *
		 * Example, to let editors toggle again on one site:
		 * `return $can || current_user_can( 'edit_others_pages' );`
		 *
		 * @since 1.1.0
		 *
		 * @param bool $can True for users with `manage_options`.
		 */
		return (bool) apply_filters( 'smm_user_can_toggle', current_user_can( 'manage_options' ) );
	}

	/**
	 * Whether the current request must pass even while maintenance is on.
	 *
	 * Never blocked: the admin (including admin-ajax.php), login, cron, WP-CLI
	 * and the REST API. The dig.id Control Center agent talks to the site over
	 * REST and has to keep working during maintenance.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function should_allow_request() {
		if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return true;
		}

		$script = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';
		if ( in_array( $script, array( 'wp-login.php', 'wp-cron.php', 'admin-ajax.php' ), true ) ) {
			return true;
		}

		if ( $this->is_rest_request() ) {
			return true;
		}

		// Front-end AJAX endpoints such as WooCommerce's ?wc-ajax= run on template_redirect.
		if ( wp_doing_ajax() ) {
			$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing decision, nothing is changed.

			/**
			 * Filters whether a front-end AJAX request passes while maintenance is on.
			 *
			 * @since 1.0.0
			 *
			 * @param bool   $allow  Default false.
			 * @param string $action The `action` request parameter.
			 */
			return $this->user_can_bypass() || (bool) apply_filters( 'smm_allow_ajax_action', false, $action );
		}

		/**
		 * Filters whether the current request passes while maintenance is on.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $allow Default false.
		 * @param string $uri   The request URI.
		 */
		return (bool) apply_filters( 'smm_allow_request', false, $this->get_request_uri() );
	}

	/**
	 * Whether the request targets the REST API, pretty (/wp-json/) or plain (?rest_route=).
	 *
	 * REST requests are normally served on `parse_request`, before any of the
	 * plugin's checks run. This is a second line of defence for setups where
	 * that is not the case.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_rest_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		if ( isset( $_GET['rest_route'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing decision, nothing is changed.
			return true;
		}
		$prefix = preg_quote( rest_get_url_prefix(), '#' );
		return (bool) preg_match( '#(^|/)' . $prefix . '(/|$)#', $this->get_request_path() );
	}

	/**
	 * Whether the request is for robots.txt or an XML sitemap (WordPress core or Yoast SEO).
	 *
	 * These get a plain-text 503 instead of the HTML maintenance page.
	 *
	 * @since 1.1.0
	 *
	 * @param array $query_vars Parsed query variables of the request.
	 * @return bool
	 */
	public function is_robots_or_sitemap_request( $query_vars ) {
		$vars = array(
			'robots',             // robots.txt (core).
			'sitemap',            // Core sitemaps and Yoast SEO sitemaps.
			'sitemap-stylesheet', // Core sitemap XSL.
			'yoast-sitemap-xsl',  // Yoast SEO sitemap XSL.
		);
		foreach ( $vars as $var ) {
			if ( ! empty( $query_vars[ $var ] ) ) {
				return true;
			}
		}

		// Also by path, for rewrite rules not yet flushed or other SEO plugins.
		return (bool) preg_match( '#(^|/)(robots\.txt|[^/]*sitemap[^/]*\.(xml|xsl)(\.gz)?)$#i', $this->get_request_path() );
	}

	/**
	 * Raw request URI (path and query), sanitised.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	private function get_request_uri() {
		return isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	}

	/**
	 * Request path relative to the site's home path, without query string.
	 *
	 * Relative so that installs in a subdirectory match the same patterns.
	 *
	 * @since 1.1.0
	 * @return string Path starting with a slash.
	 */
	private function get_request_path() {
		$path = (string) wp_parse_url( $this->get_request_uri(), PHP_URL_PATH );
		$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$home = untrailingslashit( $home );
		if ( '' !== $home && 0 === strpos( $path, $home ) ) {
			$path = substr( $path, strlen( $home ) );
		}
		return '/' . ltrim( $path, '/' );
	}

	/**
	 * Toggle URL with nonce.
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
	 * Path of the maintenance template: the theme's override or the plugin's default.
	 *
	 * Themes override it in `<theme>/simple-maintenance-mode/maintenance.php`.
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
