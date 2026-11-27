<?php
/**
 * Default maintenance page.
 *
 * Themes can override it in `<theme>/simple-maintenance-mode/maintenance.php`.
 * Template variables are available through `get_query_var( 'smm_vars' )`.
 * Every Customizer value is user input and is escaped on output.
 *
 * @package simple-maintenance-mode
 * @subpackage Public
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$smm_vars  = (array) get_query_var( 'smm_vars' );
$smm_title = smm_get_content( 'title', __( 'Under maintenance', 'simple-maintenance-mode' ) );
$smm_text  = smm_get_content( 'description', __( 'We will be back soon. We apologise for the inconvenience and thank you for your patience.', 'simple-maintenance-mode' ) );

$smm_address = trim( smm_get_content( 'address' ) );
$smm_email   = sanitize_email( smm_get_content( 'email' ) );
$smm_phone   = trim( smm_get_content( 'phone' ) );
// tel: links take digits and a leading + only.
$smm_phone_href = preg_replace( '/(?!^\+)[^0-9]/', '', $smm_phone );

$smm_socials = array(
	'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
	'instagram' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
	'youtube'   => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
	'linkedin'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php
		$smm_document_title = $smm_title . ' – ' . ( $smm_vars['site_name'] ?? '' );
		if ( current_theme_supports( 'title-tag' ) ) {
			// The theme prints <title> in wp_head(): give it ours instead of adding a second one.
			add_filter(
				'pre_get_document_title',
				static function () use ( $smm_document_title ) {
					return $smm_document_title;
				},
				PHP_INT_MAX
			);
		} else {
			echo '<title>' . esc_html( $smm_document_title ) . '</title>' . "\n";
		}
		wp_head();
		?>
	</head>
	<body <?php body_class( 'smm-maintenance' ); ?>>
		<?php wp_body_open(); ?>

		<div class="wrapper flex flex-col md:flex-row justify-stretch items-stretch min-h-dvh">

			<div class="left bg-blue-shade-1 w-full md:w-2/3 flex flex-col justify-center py-32 px-16 md:p-32 text-blue-shade-4">
				<div class="smm-container flex flex-col gap-8 md:gap-8">
					<?php
					// Built by wp_get_attachment_image() from an attachment ID (absint), so core escapes it.
					$smm_logo = Smm_Utils::get_image_tag( 'logo', 'full' );
					if ( $smm_logo ) :
						?>
						<div class="custom-logo max-w-48">
							<?php echo $smm_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup from wp_get_attachment_image(), escaped by core. ?>
						</div>
					<?php else : ?>
						<img src="<?php echo esc_url( SMM_URL . 'public/images/logo-default.svg' ); ?>" alt="<?php echo esc_attr( $smm_vars['site_name'] ?? '' ); ?>" class="w-full max-w-24 object-cover">
					<?php endif; ?>
					<h1 class="font-montserrat font-bold tracking-tight uppercase !text-4xl italic"><?php echo esc_html( $smm_title ); ?></h1>
					<p class="!text-lg"><?php echo wp_kses_post( $smm_text ); ?></p>
				</div>
				<?php if ( ! empty( $smm_vars['is_preview'] ) ) : ?>
					<p class="!text-xs font-sourcesanspro bg-slate-800 text-slate-100 p-3 mt-8 inline-block self-start"><em><?php esc_html_e( 'Maintenance mode preview.', 'simple-maintenance-mode' ); ?></em></p>
				<?php endif; ?>
			</div>

			<aside class="right bg-blue-shade-3 text-blue-shade-1 w-full md:w-1/3 flex flex-col justify-center p-16 font-sourcesanspro text-base gap-8">
				<?php if ( '' !== $smm_address ) : ?>
					<div class="address">
						<p class="!text-base"><?php echo nl2br( esc_html( $smm_address ) ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( is_email( $smm_email ) || '' !== $smm_phone_href ) : ?>
					<div class="phone-and-email flex flex-col gap-2">
						<?php if ( is_email( $smm_email ) ) : ?>
							<a class="!text-base" href="<?php echo esc_url( 'mailto:' . $smm_email ); ?>"><?php echo esc_html( $smm_email ); ?></a>
						<?php endif; ?>
						<?php if ( '' !== $smm_phone_href ) : ?>
							<a class="!text-base" href="<?php echo esc_url( 'tel:' . $smm_phone_href ); ?>"><?php echo esc_html( $smm_phone ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="socials flex gap-4">
					<?php
					foreach ( $smm_socials as $smm_network => $smm_icon ) :
						$smm_url = Smm_Utils::get_social( $smm_network );
						if ( ! $smm_url ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $smm_url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $smm_network ) ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-<?php echo esc_attr( $smm_network ); ?>-icon lucide-<?php echo esc_attr( $smm_network ); ?> stroke-blue-shade-3"><?php echo $smm_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG paths defined above. ?></svg></a>
						<?php
					endforeach;
					?>
				</div>
			</aside>

		</div>

	<?php wp_footer(); ?>
	</body>
</html>
