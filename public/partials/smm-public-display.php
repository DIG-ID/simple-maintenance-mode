<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://dig.id
 * @since      1.0.0
 *
 * @package    Smm
 * @subpackage Smm/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" >
		<title>Em Manutenção - <?php echo esc_html( get_query_var('smm_vars')['site_name'] ); ?> </title>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( 'smm-maintenance' ); ?>>
		<?php do_action( 'wp_body_open' ); ?>

		<div class="wrapper flex flex-col md:flex-row justify-stretch items-stretch min-h-dvh">

			<div class="left bg-blue-shade-1 w-full md:w-2/3 flex flex-col justify-center py-32 px-16 md:p-32 text-blue-shade-4">
				<div class="smm-container flex flex-col gap-8 md:gap-8">
					<?php
					$logo = Smm_Utils::get_image_tag( 'logo', 'full' );
					if ( $logo ) :
						?>
						<div class="custom-logo max-w-48">
							<?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<?php
					else :
						?>
							<img src="<?php echo esc_url( SMM_URL . 'public/images/logo-default.svg' ); ?>" alt="default logo" class="w-full max-w-24 object-cover">
						<?php
					endif;
					?>
					<h1 class="font-montserrat font-bold tracking-tight uppercase text-4xl italic"><?php echo esc_html( smm_get_content( 'title', 'Em Manutenção' ) ); ?></h1>
					<p class=""><?php echo wp_kses_post( smm_get_content( 'description', 'Estaremos de volta brevemente, pedimos desculpa pelo o transtorno causado. Agradecemos a tua compreensão e paciência.' ) ); ?></p>
				</div>
				<?php if ( get_query_var('smm_vars')['is_preview'] ) : ?>
					<p class="text-xs font-sourcesanspro bg-slate-800 text-slate-100 p-3 mt-8 inline-block self-start"><em>Pré‑visualização do modo manutenção.</em></p>
				<?php endif; ?>
			</div>

			<aside class="right bg-blue-shade-3 text-blue-shade-1 w-full md:w-1/3 flex flex-col justify-center p-16 font-sourcesanspro text-base gap-8">
				<div class="address">
					<p><?php echo nl2br( smm_get_content( 'address', 'addresss' ) ); ?></p>
				</div>
				<div class="phone-and-email flex flex-col gap-2">
					<a href="mailto:<?php echo esc_html( smm_get_content( 'email', 'email' ) ); ?>"><?php echo esc_html( smm_get_content( 'email', 'email' ) ); ?></a>
					<a href="tel:<?php echo esc_html( smm_get_content( 'phone', 'phone' ) ); ?>"><?php echo esc_html( smm_get_content( 'phone', 'phone' ) ); ?></a>
				</div>
				<div class="socials flex gap-4">
					<a href="<?php echo esc_url( Smm_Utils::get_social( 'facebook' ) ); ?>" target="_blank" rel="noopener" class="w-9 h-9 p-2 bg-blue-shade-1 rounded-full flex justify-center items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-facebook-icon lucide-facebook stroke-blue-shade-3"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
					<a href="<?php echo esc_url( Smm_Utils::get_social( 'instagram' ) ); ?>" target="_blank" rel="noopener" class="w-9 h-9 p-2 bg-blue-shade-1 rounded-full flex justify-center items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-instagram-icon lucide-instagram stroke-blue-shade-3"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg></a>
					<a href="<?php echo esc_url( Smm_Utils::get_social( 'youtube' ) ); ?>" target="_blank" rel="noopener" class="w-9 h-9 p-2 bg-blue-shade-1 rounded-full flex justify-center items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-youtube-icon lucide-youtube stroke-blue-shade-3"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg></a>
					<a href="<?php echo esc_url( Smm_Utils::get_social( 'linkedin' ) ); ?>" target="_blank" rel="noopener" class="w-9 h-9 p-2 bg-blue-shade-1 rounded-full flex justify-center items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-linkedin-icon lucide-linkedin stroke-blue-shade-3"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg></a>
				</div>
			</aside>

		</div>

	<?php wp_footer(); ?>
	</body>
</html>