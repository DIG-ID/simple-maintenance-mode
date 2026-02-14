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

		<div class="wrapper flex justify-stretch items-stretch min-h-dvh">
			<div class="left bg-blue-shade-1 w-2/3 flex flex-col justify-center p-32 text-blue-shade-4">
				<div class="smm-container">
					<h1 class=" font-light leading-loose uppercase text-4xl italic"><?php echo esc_html( smm_get_content( 'title', 'Em Manutenção' ) ); ?></h1>
					<p class=""><?php echo wp_kses_post( smm_get_content( 'description', 'Estaremos de volta brevemente, pedimos desculpa pelo o transtorno causado. Agradecemos a tua compreensão e paciência.' ) ); ?></p>
					form

				</div>
				<?php if ( get_query_var('smm_vars')['is_preview'] ) : ?>
					<p class="text-xs font-sourcesanspro bg-slate-800 text-slate-100 p-3 mt-8 inline-block self-start"><em>Pré‑visualização do modo manutenção.</em></p>
				<?php endif; ?>
			</div>
			<div class="right bg-blue-shade-3 text-blue-shade-1  w-1/3 flex flex-col justify-center p-16 font-sourcesanspro text-base">
				<div class="block">
					<p><?php echo esc_html( smm_get_content( 'address', 'addresss' ) ); ?></p>
				</div>
				<div class="block">
					<p><?php echo esc_html( smm_get_content( 'email', 'email' ) ); ?></p>
					<p><?php echo esc_html( smm_get_content( 'phone', 'phone' ) ); ?></p>
				</div>
				<div class="block">
					<p>socials</p>
				</div>
			</div>
		</div>

	<?php wp_footer(); ?>
	</body>
</html>