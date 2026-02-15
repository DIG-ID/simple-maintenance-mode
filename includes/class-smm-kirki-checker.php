<?php
/**
 * Kirki Dependency Checker
 *
 * Lightweight dependency checker for Kirki plugin.
 * Displays admin notice and handles installation recommendation.
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * Kirki Dependency Checker Class
 *
 * Checks if Kirki is installed and active.
 * Shows admin notice with installation option if not found.
 *
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 * @author     dig.id <hello@dig.id>
 */
class Smm_Kirki_Checker {

	/**
	 * Transient name for dismissed notice.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $dismissed_transient    Transient key for dismissed notice.
	 */
	private $dismissed_transient = 'smm_kirki_notice_dismissed';

	/**
	 * Nonce action for dismiss.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $nonce_action    Nonce action string.
	 */
	private $nonce_action = 'smm_dismiss_kirki';

	/**
	 * Initialize the checker.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_kirki' ) );
		add_action( 'admin_notices', array( $this, 'kirki_notice' ) );
	}

	/**
	 * Check if Kirki is installed and active.
	 *
	 * Handles dismissal of notice via query string parameter.
	 *
	 * @since 1.0.0
	 */
	public function check_kirki() {

		// If Kirki is active, clear any dismissed transients.
		if ( class_exists( 'Kirki' ) ) {
			delete_transient( $this->dismissed_transient );
			return;
		}

		// Handle notice dismissal.
		if ( isset( $_GET['smm_dismiss_kirki'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Verify nonce.
			if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), $this->nonce_action ) ) {
				wp_die( esc_html__( 'Security check failed.', 'smm' ) );
			}

			// Set dismissal transient for 7 days.
			set_transient( $this->dismissed_transient, true, WEEK_IN_SECONDS );

			// Redirect to clean URL.
			wp_safe_redirect( remove_query_arg( array( 'smm_dismiss_kirki', '_wpnonce' ) ) );
			exit;
		}
	}

	/**
	 * Display admin notice if Kirki is not installed.
	 *
	 * Notice includes:
	 * - Installation button
	 * - Dismissal option (7 days)
	 * - Explanation of functionality
	 *
	 * @since 1.0.0
	 */
	public function kirki_notice() {

		// Don't show if Kirki is active.
		if ( class_exists( 'Kirki' ) ) {
			return;
		}

		// Don't show if user dismissed it.
		if ( get_transient( $this->dismissed_transient ) ) {
			return;
		}

		// Only show on relevant admin screens.
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins', 'customize' ), true ) ) {
			return;
		}

		// Only show to users who can install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Generate installation URL.
		$install_url = wp_nonce_url(
			self_admin_url( 'update.php?action=install-plugin&plugin=kirki' ),
			'install-plugin_kirki'
		);

		// Generate dismissal URL.
		$dismiss_url = wp_nonce_url(
			add_query_arg( 'smm_dismiss_kirki', '1' ),
			$this->nonce_action
		);

		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Simple Maintenance Mode:', 'smm' ); ?></strong>
				<?php
				printf(
					/* translators: %s: plugin name */
					esc_html__( 'Para personalizar o conteúdo através do Customizer, recomendamos instalar o plugin %s.', 'smm' ),
					'<strong>' . esc_html__( 'Kirki Customizer Framework', 'smm' ) . '</strong>'
				);
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $install_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Instalar Kirki', 'smm' ); ?>
				</a>
				<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Dispensar por 7 dias', 'smm' ); ?>
				</a>
			</p>
			<p>
				<em>
					<?php esc_html_e( 'Nota: O plugin funciona sem o Kirki, mas perdes a capacidade de personalizar conteúdos através do Customizer. Podes usar ACF Pro como alternativa.', 'smm' ); ?>
				</em>
			</p>
		</div>
		<?php
	}

	/**
	 * Check if Kirki is installed (even if not active).
	 *
	 * @since  1.0.0
	 * @return bool True if Kirki is installed, false otherwise.
	 */
	public static function is_kirki_installed() {
		$plugins = get_plugins();
		return isset( $plugins['kirki/kirki.php'] );
	}

	/**
	 * Get Kirki activation URL.
	 *
	 * @since  1.0.0
	 * @return string Activation URL with nonce.
	 */
	public static function get_kirki_activation_url() {
		return wp_nonce_url(
			self_admin_url( 'plugins.php?action=activate&plugin=kirki/kirki.php' ),
			'activate-plugin_kirki/kirki.php'
		);
	}
}
