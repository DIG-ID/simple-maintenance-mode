<?php
/**
 * Removes the plugin's options when it is deleted from the Plugins screen.
 *
 * @package simple-maintenance-mode
 * @subpackage Core
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Deletes the plugin's options on the current site.
 *
 * @since 1.1.0
 * @return void
 */
function smm_uninstall_site() {
	delete_option( 'smm_enabled' );
	delete_option( 'smm_customizer' );
}

if ( is_multisite() ) {
	$smm_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $smm_site_ids as $smm_site_id ) {
		switch_to_blog( $smm_site_id );
		smm_uninstall_site();
		restore_current_blog();
	}
} else {
	smm_uninstall_site();
}
