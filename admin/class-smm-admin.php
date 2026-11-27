<?php
/**
 * Admin side: admin bar toggle, notice and the toggle handler.
 *
 * @package simple-maintenance-mode
 * @subpackage Admin
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin behaviour of the plugin.
 *
 * Users who can bypass (administrators and editors) see the state; only users
 * who can toggle (administrators) get the switch, the preview and the
 * "Deactivate now" link.
 *
 * @since 1.0.0
 */
class Smm_Admin {

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
	 * Enqueues the admin stylesheet.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smm-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueues the admin script.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smm-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Adds the maintenance node to the admin bar.
	 *
	 * Users who can toggle get a link that switches the state plus a preview
	 * link; users who can only bypass get the state without a link, so editors
	 * know visitors are seeing the maintenance page.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		$can_toggle = $this->core->user_can_toggle();
		if ( ! $can_toggle && ! $this->core->user_can_bypass() ) {
			return;
		}

		$enabled = $this->core->is_enabled();
		$title   = $enabled
			? '<span class="ab-icon dashicons dashicons-hammer"></span><span class="ab-label">' . esc_html__( 'Maintenance ON', 'simple-maintenance-mode' ) . '</span>'
			: '<span class="ab-icon dashicons dashicons-yes-alt"></span><span class="ab-label">' . esc_html__( 'Maintenance OFF', 'simple-maintenance-mode' ) . '</span>';

		if ( ! $can_toggle ) {
			if ( ! $enabled ) {
				return;
			}
			$wp_admin_bar->add_node(
				array(
					'id'    => 'smm-maintenance',
					'title' => $title,
					'meta'  => array(
						'class' => 'smm-on',
						'title' => esc_html__( 'Visitors are seeing the maintenance page.', 'simple-maintenance-mode' ),
					),
				)
			);
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'smm-maintenance',
				'title' => $title,
				'href'  => $this->core->get_toggle_url(),
				'meta'  => array(
					'class' => $enabled ? 'smm-on' : 'smm-off',
					'title' => $enabled ? esc_html__( 'Deactivate maintenance mode', 'simple-maintenance-mode' ) : esc_html__( 'Activate maintenance mode', 'simple-maintenance-mode' ),
				),
			)
		);

		$wp_admin_bar->add_node(
			array(
				'id'     => 'smm-preview',
				'parent' => 'smm-maintenance',
				'title'  => esc_html__( 'View Preview', 'simple-maintenance-mode' ),
				'href'   => add_query_arg( 'smm_preview', '1', home_url( '/' ) ),
				'meta'   => array( 'target' => '_blank' ),
			)
		);
	}

	/**
	 * Shows an admin notice while maintenance mode is on.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_notice() {
		if ( ! $this->core->is_enabled() || ! $this->core->user_can_bypass() ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Maintenance mode ACTIVE.', 'simple-maintenance-mode' ); ?></strong>
				<?php esc_html_e( 'Visitors are seeing the maintenance page.', 'simple-maintenance-mode' ); ?>
				<?php if ( $this->core->user_can_toggle() ) : ?>
					<a href="<?php echo esc_url( $this->core->get_toggle_url() ); ?>"><?php esc_html_e( 'Deactivate now', 'simple-maintenance-mode' ); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Switches maintenance mode on or off (admin-post.php?action=smm_toggle).
	 *
	 * Capability first, then the nonce: users who may not toggle get a 403
	 * without learning anything about the nonce. `smm_toggled` is fired by the
	 * option hook in smm-functions.php.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_toggle() {
		if ( ! $this->core->user_can_toggle() ) {
			wp_die(
				esc_html__( 'You are not allowed to change the maintenance mode.', 'simple-maintenance-mode' ),
				esc_html__( 'Maintenance', 'simple-maintenance-mode' ),
				array( 'response' => 403 )
			);
		}

		check_admin_referer( Smm_Plugin::NONCE_ACTION );

		// The stored state, not the filtered one: the switch changes what is stored.
		if ( 1 === (int) get_option( Smm_Plugin::OPTION_ENABLED, 0 ) ) {
			smm_disable();
		} else {
			smm_enable();
		}

		$redirect = wp_get_referer();
		wp_safe_redirect( $redirect ? $redirect : admin_url() );
		exit;
	}
}
