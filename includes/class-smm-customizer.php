<?php
/**
 * Customizer panel for the maintenance page: state, content, contacts, look,
 * social links and contact form.
 *
 * @package simple-maintenance-mode
 * @subpackage Customizer
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Customizer Integration Class
 *
 * Registers all maintenance mode customizer fields using native WordPress API.
 *
 * Fields included:
 * - Toggle (ON/OFF)
 * - Content (title, description)
 * - Contact (email, phone, address)
 * - Visual (logo, colors)
 * - Social (facebook, instagram, twitter, linkedin)
 * - Form (contact form shortcode)
 *
 * @since 1.0.0
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
		add_action( 'customize_register', array( $this, 'register_customizer' ), 10 );
		add_action( 'customize_preview_init', array( $this, 'enqueue_preview_scripts' ) );
	}

	/**
	 * Register all Customizer fields.
	 *
	 * @since 1.0.0
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 */
	public function register_customizer( $wp_customize ) {

		// Main Panel.
		$wp_customize->add_panel(
			$this->panel_id,
			array(
				'priority'    => 160,
				'capability'  => 'manage_options',
				'title'       => __( 'Maintenance Mode', 'simple-maintenance-mode' ),
				'description' => __( 'Turn maintenance mode on or off and edit the maintenance page.', 'simple-maintenance-mode' ),
			)
		);

		// Register sections.
		$this->register_toggle_section( $wp_customize );
		$this->register_content_section( $wp_customize );
		$this->register_contact_section( $wp_customize );
		$this->register_visual_section( $wp_customize );
		$this->register_social_section( $wp_customize );
		$this->register_form_section( $wp_customize );
	}

	/**
	 * Register toggle section (ON/OFF).
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private function register_toggle_section( $wp_customize ) {

		// Section.
		$wp_customize->add_section(
			'smm_toggle',
			array(
				'title'    => __( 'Status', 'simple-maintenance-mode' ),
				'panel'    => $this->panel_id,
				'priority' => 5,
			)
		);

		// Setting: Toggle.
		$wp_customize->add_setting(
			'smm_enabled',
			array(
				'default'           => 0,
				'type'              => 'option',
				'capability'        => 'manage_options',
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		// Control: Checkbox.
		$wp_customize->add_control(
			'smm_enabled',
			array(
				'label'       => __( 'Maintenance mode', 'simple-maintenance-mode' ),
				'description' => __( 'Turn on to show the maintenance page to visitors.', 'simple-maintenance-mode' ),
				'section'     => 'smm_toggle',
				'type'        => 'checkbox',
			)
		);
	}

	/**
	 * Register content section (title, description).
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private function register_content_section( $wp_customize ) {

		// Section.
		$wp_customize->add_section(
			'smm_content',
			array(
				'title'    => __( 'Content', 'simple-maintenance-mode' ),
				'panel'    => $this->panel_id,
				'priority' => 10,
			)
		);

		// Setting: Title.
		$wp_customize->add_setting(
			$this->option_name . '[title]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Control: Title.
		$wp_customize->add_control(
			'smm_title',
			array(
				'label'       => __( 'Title', 'simple-maintenance-mode' ),
				'section'     => 'smm_content',
				'settings'    => $this->option_name . '[title]',
				'type'        => 'text',
				'input_attrs' => array( 'placeholder' => __( 'Under maintenance', 'simple-maintenance-mode' ) ),
			)
		);

		// Setting: Description.
		$wp_customize->add_setting(
			$this->option_name . '[description]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'wp_kses_post',
			)
		);

		// Control: Description.
		$wp_customize->add_control(
			'smm_description',
			array(
				'label'       => __( 'Message', 'simple-maintenance-mode' ),
				'section'     => 'smm_content',
				'settings'    => $this->option_name . '[description]',
				'type'        => 'textarea',
				'input_attrs' => array( 'placeholder' => __( 'We will be back soon. We apologise for the inconvenience and thank you for your patience.', 'simple-maintenance-mode' ) ),
			)
		);
	}

	/**
	 * Register contact section (email, phone, address).
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private function register_contact_section( $wp_customize ) {

		// Section.
		$wp_customize->add_section(
			'smm_contact',
			array(
				'title'       => __( 'Contact details', 'simple-maintenance-mode' ),
				'panel'       => $this->panel_id,
				'priority'    => 20,
				'description' => __( 'Shown on the maintenance page. Leave a field empty to hide it.', 'simple-maintenance-mode' ),
			)
		);

		// Setting: Email.
		$wp_customize->add_setting(
			$this->option_name . '[email]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'sanitize_email',
			)
		);

		// Control: Email.
		$wp_customize->add_control(
			'smm_email',
			array(
				'label'    => __( 'Email', 'simple-maintenance-mode' ),
				'section'  => 'smm_contact',
				'settings' => $this->option_name . '[email]',
				'type'     => 'email',
			)
		);

		// Setting: Phone.
		$wp_customize->add_setting(
			$this->option_name . '[phone]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Control: Phone.
		$wp_customize->add_control(
			'smm_phone',
			array(
				'label'    => __( 'Phone', 'simple-maintenance-mode' ),
				'section'  => 'smm_contact',
				'settings' => $this->option_name . '[phone]',
				'type'     => 'tel',
			)
		);

		// Setting: Address.
		$wp_customize->add_setting(
			$this->option_name . '[address]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);

		// Control: Address.
		$wp_customize->add_control(
			'smm_address',
			array(
				'label'    => __( 'Address', 'simple-maintenance-mode' ),
				'section'  => 'smm_contact',
				'settings' => $this->option_name . '[address]',
				'type'     => 'textarea',
			)
		);
	}

	/**
	 * Register visual section (logo, colors).
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private function register_visual_section( $wp_customize ) {

		// Section.
		$wp_customize->add_section(
			'smm_visual',
			array(
				'title'    => __( 'Appearance', 'simple-maintenance-mode' ),
				'panel'    => $this->panel_id,
				'priority' => 30,
			)
		);

		// Setting: Logo.
		$wp_customize->add_setting(
			$this->option_name . '[logo]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => 'absint',
			)
		);

		// Control: Logo (Media).
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'smm_logo',
				array(
					'label'     => __( 'Logo', 'simple-maintenance-mode' ),
					'section'   => 'smm_visual',
					'settings'  => $this->option_name . '[logo]',
					'mime_type' => 'image',
				)
			)
		);

		// Setting: Background Color.
		$wp_customize->add_setting(
			$this->option_name . '[bg_color]',
			array(
				'default'           => '#ffffff',
				'type'              => 'option',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);

		// Control: Background Color.
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'smm_bg_color',
				array(
					'label'    => __( 'Background colour', 'simple-maintenance-mode' ),
					'section'  => 'smm_visual',
					'settings' => $this->option_name . '[bg_color]',
				)
			)
		);

		// Setting: Text Color.
		$wp_customize->add_setting(
			$this->option_name . '[text_color]',
			array(
				'default'           => '#000000',
				'type'              => 'option',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);

		// Control: Text Color.
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'smm_text_color',
				array(
					'label'    => __( 'Text colour', 'simple-maintenance-mode' ),
					'section'  => 'smm_visual',
					'settings' => $this->option_name . '[text_color]',
				)
			)
		);
	}

	/**
	 * Register social section (Facebook, Instagram, Twitter, LinkedIn).
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private function register_social_section( $wp_customize ) {

		// Section.
		$wp_customize->add_section(
			'smm_social',
			array(
				'title'       => __( 'Social networks', 'simple-maintenance-mode' ),
				'panel'       => $this->panel_id,
				'priority'    => 40,
				'description' => __( 'Links to the social networks. Leave a field empty to hide it.', 'simple-maintenance-mode' ),
			)
		);

		// Social networks.
		$social_networks = array(
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'twitter'   => 'Twitter',
			'linkedin'  => 'LinkedIn',
		);

		foreach ( $social_networks as $key => $label ) {
			// Setting.
			$wp_customize->add_setting(
				$this->option_name . '[social_' . $key . ']',
				array(
					'default'           => '',
					'type'              => 'option',
					'sanitize_callback' => 'esc_url_raw',
				)
			);

			// Control.
			$wp_customize->add_control(
				'smm_social_' . $key,
				array(
					'label'       => $label,
					'section'     => 'smm_social',
					'settings'    => $this->option_name . '[social_' . $key . ']',
					'type'        => 'url',
					'placeholder' => 'https://',
				)
			);
		}
	}

	/**
	 * Register form section (Contact Form shortcode).
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  WP_Customize_Manager $wp_customize Customizer instance.
	 */
	private function register_form_section( $wp_customize ) {

		// Section.
		$wp_customize->add_section(
			'smm_form',
			array(
				'title'       => __( 'Contact form', 'simple-maintenance-mode' ),
				'panel'       => $this->panel_id,
				'priority'    => 50,
				'description' => __( 'Shortcode of a contact form (e.g. Contact Form 7, WPForms).', 'simple-maintenance-mode' ),
			)
		);

		// Setting: Shortcode.
		$wp_customize->add_setting(
			$this->option_name . '[form_shortcode]',
			array(
				'default'           => '',
				'type'              => 'option',
				'sanitize_callback' => array( $this, 'sanitize_shortcode' ),
			)
		);

		// Control: Shortcode.
		$wp_customize->add_control(
			'smm_form_shortcode',
			array(
				'label'       => __( 'Form shortcode', 'simple-maintenance-mode' ),
				'description' => __( 'Example: [contact-form-7 id="123"]', 'simple-maintenance-mode' ),
				'section'     => 'smm_form',
				'settings'    => $this->option_name . '[form_shortcode]',
				'type'        => 'text',
				'placeholder' => '[contact-form-7 id="123"]',
			)
		);
	}

	/**
	 * Sanitize shortcode input.
	 *
	 * Allows only valid shortcode format.
	 *
	 * @since  1.0.0
	 * @param  string $input Shortcode input.
	 * @return string        Sanitized shortcode.
	 */
	public function sanitize_shortcode( $input ) {
		// Remove any dangerous tags but keep shortcode brackets.
		return wp_kses( $input, array() );
	}

	/**
	 * Enqueue preview refresh script.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_script(
			'smm-customizer-preview',
			plugin_dir_url( __DIR__ ) . 'admin/js/smm-customizer-preview.js',
			array( 'customize-preview', 'jquery' ),
			SMM_VERSION,
			true
		);
	}
}
