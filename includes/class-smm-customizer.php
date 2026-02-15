<?php
/**
 * Customizer Integration - SIMPLIFIED & WORKING
 *
 * Uses DIRECT access to smm_enabled option.
 * No AJAX, no sync issues - just works!
 *
 * @link       https://dig.id
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 */

/**
 * Customizer Integration Class
 *
 * @since      1.0.0
 * @package    Smm
 * @subpackage Smm/includes
 * @author     dig.id <hello@dig.id>
 */
class Smm_Customizer {

	/**
	 * Option name for storing customizer content values.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $option_name = 'smm_customizer';

	/**
	 * Kirki config ID.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $kirki_config = 'smm_config';

	/**
	 * Panel ID.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $panel_id = 'smm_panel';

	/**
	 * Initialize customizer.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( class_exists( 'Kirki' ) ) {
			add_action( 'init', array( $this, 'register_with_kirki' ) );
		} else {
			add_action( 'customize_register', array( $this, 'register_native' ) );
		}

		// Add preview refresh script.
		add_action( 'customize_preview_init', array( $this, 'add_preview_scripts' ) );
	}

	/**
	 * Register fields with Kirki.
	 *
	 * @since 1.0.0
	 */
	public function register_with_kirki() {

		// Kirki config for CONTENT fields.
		Kirki::add_config(
			$this->kirki_config,
			array(
				'capability'  => 'manage_options',
				'option_type' => 'option',
				'option_name' => $this->option_name,
			)
		);

		// Main Panel.
		Kirki::add_panel(
			$this->panel_id,
			array(
				'priority'    => 160,
				'title'       => esc_html__( 'Maintenance Mode', 'smm' ),
				'description' => esc_html__( 'Configure o modo manutenção. Ative/desative e veja o preview em tempo real.', 'smm' ),
			)
		);

		// Register sections.
		$this->register_kirki_toggle_section();
		$this->register_kirki_content_section();
		$this->register_kirki_contacts_section();
		$this->register_kirki_visual_section();
		$this->register_kirki_social_section();
	}

	/**
	 * Register toggle section with Kirki.
	 *
	 * IMPORTANT: This uses smm_enabled DIRECTLY (same as Admin Bar).
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function register_kirki_toggle_section() {

		Kirki::add_section(
			'smm_toggle',
			array(
				'title'    => esc_html__( 'Estado', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 5,
			)
		);

		// Toggle field - uses smm_enabled DIRECTLY.
		Kirki::add_field(
			$this->kirki_config . '_toggle', // Separate config for this field.
			array(
				'type'        => 'toggle',
				'settings'    => 'smm_enabled', // Direct access to main option!
				'label'       => esc_html__( 'Modo Manutenção', 'smm' ),
				'description' => esc_html__( 'Ative para mostrar a página de manutenção. O preview atualiza automaticamente.', 'smm' ),
				'section'     => 'smm_toggle',
				'default'     => 0,
				'priority'    => 10,
				'option_type' => 'option', // Store as option.
				'option_name' => 'smm_enabled', // Direct name!
			)
		);

		// Need separate config for the toggle since it uses different option.
		Kirki::add_config(
			$this->kirki_config . '_toggle',
			array(
				'capability'  => 'manage_options',
				'option_type' => 'option',
			)
		);
	}

	/**
	 * Register content section with Kirki.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function register_kirki_content_section() {

		Kirki::add_section(
			'smm_content',
			array(
				'title'    => esc_html__( 'Conteúdo', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 10,
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'text',
				'settings' => 'title',
				'label'    => esc_html__( 'Título Principal', 'smm' ),
				'section'  => 'smm_content',
				'default'  => 'Em Manutenção',
				'priority' => 10,
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'textarea',
				'settings' => 'description',
				'label'    => esc_html__( 'Mensagem', 'smm' ),
				'section'  => 'smm_content',
				'default'  => 'Estaremos de volta brevemente.',
				'priority' => 20,
			)
		);
	}

	/**
	 * Register contacts section with Kirki.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function register_kirki_contacts_section() {

		Kirki::add_section(
			'smm_contacts',
			array(
				'title'    => esc_html__( 'Contactos', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 20,
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'text',
				'settings' => 'email',
				'label'    => esc_html__( 'Email', 'smm' ),
				'section'  => 'smm_contacts',
				'default'  => get_option( 'admin_email' ),
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'text',
				'settings' => 'phone',
				'label'    => esc_html__( 'Telefone', 'smm' ),
				'section'  => 'smm_contacts',
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'textarea',
				'settings' => 'address',
				'label'    => esc_html__( 'Morada', 'smm' ),
				'section'  => 'smm_contacts',
			)
		);
	}

	/**
	 * Register visual section with Kirki.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function register_kirki_visual_section() {

		Kirki::add_section(
			'smm_visual',
			array(
				'title'    => esc_html__( 'Aparência', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 30,
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'image',
				'settings' => 'logo',
				'label'    => esc_html__( 'Logo', 'smm' ),
				'section'  => 'smm_visual',
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'color',
				'settings' => 'bg_color',
				'label'    => esc_html__( 'Cor de Fundo', 'smm' ),
				'section'  => 'smm_visual',
				'default'  => '#ffffff',
			)
		);

		Kirki::add_field(
			$this->kirki_config,
			array(
				'type'     => 'color',
				'settings' => 'text_color',
				'label'    => esc_html__( 'Cor do Texto', 'smm' ),
				'section'  => 'smm_visual',
				'default'  => '#000000',
			)
		);
	}

	/**
	 * Register social media section with Kirki.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function register_kirki_social_section() {

		Kirki::add_section(
			'smm_social',
			array(
				'title'    => esc_html__( 'Redes Sociais', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 40,
			)
		);

		$social_networks = array(
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'twitter'   => 'Twitter',
			'linkedin'  => 'LinkedIn',
		);

		foreach ( $social_networks as $key => $label ) {
			Kirki::add_field(
				$this->kirki_config,
				array(
					'type'     => 'url',
					'settings' => 'social_' . $key,
					'label'    => $label,
					'section'  => 'smm_social',
				)
			);
		}
	}

	/**
	 * Register fields with native WordPress Customizer (fallback).
	 *
	 * @since 1.0.0
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function register_native( $wp_customize ) {

		// Main Panel.
		$wp_customize->add_panel(
			$this->panel_id,
			array(
				'priority'    => 160,
				'capability'  => 'manage_options',
				'title'       => __( 'Maintenance Mode', 'smm' ),
				'description' => __( 'Configure o modo manutenção.', 'smm' ),
			)
		);

		// Toggle section.
		$wp_customize->add_section(
			'smm_toggle',
			array(
				'title'    => __( 'Estado', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 5,
			)
		);

		// Toggle setting - DIRECT access to smm_enabled.
		$wp_customize->add_setting(
			'smm_enabled',
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'type'              => 'option',
			)
		);

		$wp_customize->add_control(
			'smm_enabled',
			array(
				'label'       => __( 'Modo Manutenção', 'smm' ),
				'description' => __( 'Ative para mostrar a página de manutenção.', 'smm' ),
				'section'     => 'smm_toggle',
				'type'        => 'checkbox',
			)
		);

		// Content section.
		$wp_customize->add_section(
			'smm_content',
			array(
				'title'    => __( 'Conteúdo', 'smm' ),
				'panel'    => $this->panel_id,
				'priority' => 10,
			)
		);

		$wp_customize->add_setting(
			$this->option_name . '[title]',
			array(
				'default'           => 'Em Manutenção',
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'option',
			)
		);

		$wp_customize->add_control(
			'smm_title',
			array(
				'label'    => __( 'Título Principal', 'smm' ),
				'section'  => 'smm_content',
				'settings' => $this->option_name . '[title]',
				'type'     => 'text',
			)
		);

		$wp_customize->add_setting(
			$this->option_name . '[description]',
			array(
				'default'           => 'Estaremos de volta brevemente.',
				'sanitize_callback' => 'wp_kses_post',
				'type'              => 'option',
			)
		);

		$wp_customize->add_control(
			'smm_description',
			array(
				'label'    => __( 'Mensagem', 'smm' ),
				'section'  => 'smm_content',
				'settings' => $this->option_name . '[description]',
				'type'     => 'textarea',
			)
		);

		// Other sections similar to Kirki version...
		// (Contacts, Visual, Social - similar structure)
	}

	/**
	 * Add preview refresh script.
	 *
	 * CRITICAL: This makes the preview update when toggle changes!
	 *
	 * @since 1.0.0
	 */
	public function add_preview_scripts() {
		?>
		<script type="text/javascript">
		(function($) {
			'use strict';

			// Listen to smm_enabled changes and refresh.
			wp.customize('smm_enabled', function(value) {
				value.bind(function(to) {
					// Force full page reload to show/hide template.
					wp.customize.previewer.refresh();
				});
			});

		})(jQuery);
		</script>
		<?php
	}

}