<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smm
 * @subpackage Smm/admin
 * @author     dig.id <hello@dig.id>
 */
class Smm_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $core ) {

		$this->core        = $core;
		$this->plugin_name = $core->get_plugin_name();
		$this->version     = $core->get_version();
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smm-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smm-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add the toggle button to the admin bar.
	 *
	 * @since    1.0.0
	 * @param    WP_Admin_Bar $wp_admin_bar    The admin bar object.
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {

		// Apenas para utilizadores com bypass.
		if ( ! $this->core->user_can_bypass() ) {
			return;
		}

		$enabled = $this->core->is_enabled();
		$title   = $enabled
			? '<span class="ab-icon dashicons dashicons-hammer"></span><span class="ab-label">' . esc_html__( 'Maintenance ON', 'smm' ) . '</span>'
			: '<span class="ab-icon dashicons dashicons-yes-alt"></span><span class="ab-label">' . esc_html__( 'Maintenance OFF', 'smm' ) . '</span>';

		$wp_admin_bar->add_node(
			array(
				'id'    => 'smm-maintenance',
				'title' => $title,
				'href'  => $this->core->get_toggle_url(),
				'meta'  => array(
					'class' => $enabled ? 'smm-on' : 'smm-off',
					'title' => $enabled ? esc_html__( 'Deactivate maintenance mode', 'smm' ) : esc_html__( 'Activate maintenance mode', 'smm' ),
				),
			)
		);

		// Adiciona submenu de preview.
		$wp_admin_bar->add_node(
			array(
				'id'     => 'smm-preview',
				'parent' => 'smm-maintenance',
				'title'  => esc_html__( 'View Preview', 'smm' ),
				'href'   => home_url( '/?smm_preview=1' ),
				'meta'   => array( 'target' => '_blank' ),
			)
		);
	}

	/**
	 * Display an admin notice when maintenance mode is active.
	 *
	 * @since    1.0.0
	 */
	public function admin_notice() {

		if ( ! $this->core->is_enabled() || ! $this->core->user_can_bypass() ) {
			return;
		}

		$toggle_url = $this->core->get_toggle_url();
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Maintenance mode ACTIVE.', 'smm' ); ?></strong> 
				<?php esc_html_e( 'Visitors are seeing the maintenance page.', 'smm' ); ?>
				<a href="<?php echo esc_url( $toggle_url ); ?>"><?php esc_html_e( 'Deactivate now', 'smm' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle the toggle action (admin-post).
	 *
	 * @since    1.0.0
	 */
	public function handle_toggle() {

		// Verifica permissões.
		if ( ! is_user_logged_in() || ! $this->core->user_can_bypass() ) {
			wp_die( 'Unauthorized.', 403 );
		}

		// Verifica o nonce.
		check_admin_referer( Smm::NONCE_ACTION );

		// Alterna o estado.
		$new_state = $this->core->is_enabled() ? 0 : 1;
		$new_state = absint( $new_state ); // Force integer 0 or 1.
		update_option( Smm::OPTION_ENABLED, $new_state, false );

		// Redireciona de volta.
		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}
}
