<?php
/**
 * Front end: decides which requests get the maintenance page and sends it.
 *
 * @package simple-maintenance-mode
 * @subpackage Public
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Front-end behaviour of the plugin.
 *
 * @since 1.0.0
 */
class Smm_Public {

	/**
	 * Seconds sent in the Retry-After header.
	 *
	 * @since 1.1.0
	 * @var int
	 */
	const RETRY_AFTER = 600;

	/**
	 * Core plugin class.
	 *
	 * @since 1.0.0
	 * @var Smm_Plugin
	 */
	private $core;

	/**
	 * Plugin slug, used as asset handle.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $plugin_name;

	/**
	 * True while the maintenance page is being rendered.
	 *
	 * @since 1.1.0
	 * @var bool
	 */
	private $rendering_page = false;

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
	 * Enqueues the maintenance page stylesheet — only on the maintenance page.
	 *
	 * Before 1.1.0 it loaded on every front-end page, maintenance on or off,
	 * and its Tailwind base reset applied to the whole site.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		if ( ! $this->rendering_page ) {
			return;
		}
		smm_vite_enqueue_style( $this->plugin_name, 'public/sass/smm-public.sass' );
	}

	/**
	 * Answers robots.txt and sitemaps with a plain-text 503 while maintenance is on.
	 *
	 * One consistent "temporarily unavailable" signal for crawlers: serving the
	 * sitemaps would send them to URLs that all answer 503, and the HTML page
	 * makes no sense where a crawler expects text or XML.
	 *
	 * @since 1.1.0
	 *
	 * @param WP $wp Current WordPress environment.
	 * @return void
	 */
	public function block_crawler_files( $wp ) {
		if ( ! $this->core->is_enabled() || $this->core->user_can_bypass() ) {
			return;
		}
		if ( ! $this->core->is_robots_or_sitemap_request( $wp->query_vars ) ) {
			return;
		}

		$this->send_unavailable_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo esc_html__( 'Temporarily unavailable for maintenance.', 'simple-maintenance-mode' );
		exit;
	}

	/**
	 * Shows the maintenance page when it applies.
	 *
	 * - Maintenance on: visitors get the page with a 503, unless they can bypass
	 *   or the request is one that must always pass.
	 * - `?smm_preview=1`: users who can toggle see the page with a 200, on or off.
	 * - Customizer preview while maintenance is on: the page, so it can be edited live.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function template_redirect() {
		$is_active         = $this->core->is_enabled();
		$is_manual_preview = $this->is_manual_preview();

		if ( ! $is_active && ! $is_manual_preview ) {
			return;
		}

		if ( $is_manual_preview || ( $is_active && is_customize_preview() ) ) {
			$this->show_maintenance_template( true );
			return;
		}

		if ( $this->core->user_can_bypass() || $this->core->should_allow_request() ) {
			return;
		}

		$this->show_maintenance_template( false );
	}

	/**
	 * Whether the request is a manual preview (`?smm_preview=1`) by a user who can toggle.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_manual_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview of a public page, gated by capability.
		if ( ! isset( $_GET['smm_preview'] ) || '1' !== $_GET['smm_preview'] ) {
			return false;
		}
		return $this->core->user_can_toggle();
	}

	/**
	 * Sends the 503 headers and tells caching plugins not to store the response.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function send_unavailable_headers() {
		status_header( 503 );
		header( 'Retry-After: ' . self::RETRY_AFTER );
		header( 'X-Robots-Tag: noindex, nofollow', true );
		nocache_headers();

		// Constant names are fixed by the caching plugins (WP Rocket, W3TC, SG Optimizer…).
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Read by caching plugins.
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Read by caching plugins.
		}
		if ( ! defined( 'DONOTMINIFY' ) ) {
			define( 'DONOTMINIFY', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Read by caching plugins.
		}
	}

	/**
	 * Sends the maintenance page and stops.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $is_preview True for previews: answered with 200, no 503 headers.
	 * @return void
	 */
	private function show_maintenance_template( $is_preview = false ) {
		if ( $is_preview ) {
			nocache_headers();
		} else {
			$this->send_unavailable_headers();
		}

		/**
		 * Filters the path of the maintenance template.
		 *
		 * @since 1.0.0
		 *
		 * @param string $template Theme override or the plugin's default template.
		 */
		$template = apply_filters( 'smm_maintenance_template', $this->core->get_maintenance_template() );

		if ( ! is_readable( $template ) ) {
			wp_die(
				esc_html__( 'Maintenance template not found.', 'simple-maintenance-mode' ),
				esc_html__( 'Maintenance', 'simple-maintenance-mode' ),
				array( 'response' => $is_preview ? 200 : 503 )
			);
		}

		$smm_vars = array(
			'site_name'             => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'home_url'              => home_url( '/' ),
			'login_url'             => wp_login_url(),
			'is_preview'            => $is_preview,
			'is_customizer'         => is_customize_preview(),
			'is_maintenance_active' => $this->core->is_enabled(),
		);

		/**
		 * Filters the variables available to the template through `get_query_var( 'smm_vars' )`.
		 *
		 * @since 1.0.0
		 *
		 * @param array $smm_vars Template variables.
		 */
		$smm_vars = apply_filters( 'smm_template_vars', $smm_vars );
		set_query_var( 'smm_vars', $smm_vars );

		// wp_head() in the template fires wp_enqueue_scripts, which loads the page CSS.
		$this->rendering_page = true;

		include $template;
		exit;
	}
}
